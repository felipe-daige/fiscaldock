<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RafConsultaPendente;
use App\Models\RafRelatorioProcessado;
use App\Services\CreditService;
use App\Services\Sped\SpedUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DataReceiverController extends Controller
{
    public function __construct(
        protected CreditService $creditService,
        protected SpedUploadService $spedUploadService
    ) {}
    /**
     * Recebe dados via HTTP POST.
     * Agora espera apenas 'id' e 'user_id' do n8n e busca dados do banco de dados.
     * Aceita autenticação via token (header X-API-Token) ou sessão (para frontend).
     */
    public function receive(Request $request)
    {
        try {
            Log::info('Requisição recebida em DataReceiverController::receive', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'headers' => [
                    'x-api-token' => $request->hasHeader('X-API-Token') ? 'presente' : 'ausente',
                    'content-type' => $request->header('Content-Type'),
                    'accept' => $request->header('Accept'),
                ],
                'body' => $request->all(),
            ]);
            
            // Verifica autenticação via token ou sessão (opcional)
            $user = $this->authenticate($request);
            
            // Processar payload - pode vir em formato n8n ou simples
            $rawData = $request->all();
            $receivedData = $rawData;
            
            // Se é array numérico (formato n8n), extrair o primeiro elemento
            if (is_array($rawData) && !empty($rawData) && array_keys($rawData) === range(0, count($rawData) - 1)) {
                $firstItem = $rawData[0] ?? null;
                if (is_array($firstItem) && isset($firstItem['data'])) {
                    $nestedData = $firstItem['data'];
                    if (isset($nestedData['data']) && is_array($nestedData['data'])) {
                        $receivedData = $nestedData['data'];
                        if (isset($nestedData['user_id']) && !isset($receivedData['user_id'])) {
                            $receivedData['user_id'] = $nestedData['user_id'];
                        }
                    } else {
                        $receivedData = $nestedData;
                    }
                } else {
                    $receivedData = $firstItem;
                }
            } elseif (is_array($rawData) && isset($rawData['data']) && is_array($rawData['data'])) {
                if (isset($rawData['data']['data']) && is_array($rawData['data']['data'])) {
                    $receivedData = $rawData['data']['data'];
                    if (isset($rawData['data']['user_id']) && !isset($receivedData['user_id'])) {
                        $receivedData['user_id'] = $rawData['data']['user_id'];
                    }
                } else {
                    $receivedData = $rawData['data'];
                }
            }
            
            Log::info('Dados processados (final)', [
                'user_id' => $user?->id ?? null,
                'data_keys' => is_array($receivedData) ? array_keys($receivedData) : 'não é array',
                'has_id' => isset($receivedData['id']),
                'has_user_id' => isset($receivedData['user_id']),
            ]);

            // Validar que temos 'id' e 'user_id'
            if (!isset($receivedData['id']) || !isset($receivedData['user_id'])) {
                Log::warning('Payload inválido: faltam campos obrigatórios', [
                    'has_id' => isset($receivedData['id']),
                    'has_user_id' => isset($receivedData['user_id']),
                    'data_keys' => is_array($receivedData) ? array_keys($receivedData) : 'não é array',
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Campos obrigatórios ausentes: id e user_id são necessários.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $id = (int) $receivedData['id'];
            $userId = (int) $receivedData['user_id'];

            Log::info('Buscando dados RAF do banco de dados', [
                'id' => $id,
                'user_id' => $userId,
                'authenticated_user_id' => $user?->id,
            ]);

            // Buscar registro do banco de dados
            $registro = RafConsultaPendente::find($id);
            
            if (!$registro) {
                Log::warning('Registro RAF não encontrado no banco de dados', [
                    'id' => $id,
                    'user_id' => $userId,
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Registro não encontrado.',
                ], Response::HTTP_NOT_FOUND);
            }
            
            // Validar que o user_id corresponde
            if ((int) $registro->user_id !== $userId) {
                Log::warning('Tentativa de acesso a registro de outro usuário', [
                    'id' => $id,
                    'expected_user_id' => $userId,
                    'actual_user_id' => $registro->user_id,
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso negado.',
                ], Response::HTTP_FORBIDDEN);
            }
            
            // Atualizar registro com valores do n8n (se enviados)
            $updated = false;
            
            if (isset($receivedData['qtd_participantes'])) {
                $registro->qtd_participantes = (int) $receivedData['qtd_participantes'];
                $updated = true;
            }
            if (isset($receivedData['valor_total_consulta'])) {
                $registro->valor_total_consulta = (float) $receivedData['valor_total_consulta'];
                $updated = true;
            }
            if (isset($receivedData['custo_unitario'])) {
                $registro->custo_unitario = (float) $receivedData['custo_unitario'];
                $updated = true;
            }
            if (isset($receivedData['resume_url'])) {
                $registro->resume_url = $receivedData['resume_url'];
                $updated = true;
            }
            
            // Marcar que o n8n enviou os dados (orquestrador)
            if (!$registro->n8n_received_at) {
                $registro->n8n_received_at = now();
                $updated = true;
            }
            
            // Salvar apenas se houver alterações
            if ($updated) {
                $saved = $registro->save();
                
                // Recarregar do banco para confirmar que foi salvo
                $registro->refresh();
                
                Log::info('Registro RAF atualizado com dados do n8n', [
                    'id' => $id,
                    'user_id' => $userId,
                    'n8n_received_at' => $registro->n8n_received_at,
                    'qtd_participantes' => $registro->qtd_participantes,
                    'valor_total_consulta' => $registro->valor_total_consulta,
                    'custo_unitario' => $registro->custo_unitario,
                    'saved' => $saved,
                ]);
            }

            // Buscar dados formatados
            $formattedData = $this->getRafConsultaFromDatabase($id, $userId);

            if (!$formattedData) {
                Log::error('Erro ao formatar dados após marcar n8n_received_at', [
                    'id' => $id,
                    'user_id' => $userId,
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao processar dados.',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            Log::info('Dados RAF recuperados do banco de dados com sucesso', [
                'id' => $id,
                'user_id' => $userId,
                'has_resume_url' => isset($formattedData['resume_url']),
                'has_valor_total' => isset($formattedData['valor_total_consulta']),
                'n8n_received_at' => $registro->n8n_received_at?->toIso8601String(),
            ]);

            // Notificação será verificada diretamente do banco de dados via SSE
            // O campo n8n_received_at já foi marcado acima, indicando que está pronto

            // Preparar resposta
            $responseData = [
                'received_at' => now()->toIso8601String(),
                'user_id' => $userId,
                'data' => $formattedData,
                'processed' => true,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Dados recebidos com sucesso.',
                'data' => $responseData,
            ], Response::HTTP_OK);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Erro de validação na API', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
            
        } catch (\Exception $e) {
            Log::error('Erro inesperado na API DataReceiverController', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor. Tente novamente mais tarde.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Busca dados de consulta RAF do banco de dados.
     * 
     * @param int $id ID do registro em raf_consulta_pendente
     * @param int $userId ID do usuário (para validação)
     * @return array|null Dados formatados ou null se não encontrado
     */
    private function getRafConsultaFromDatabase(int $id, int $userId): ?array
    {
        try {
            $registro = RafConsultaPendente::find($id);
            
            if (!$registro) {
                Log::warning('Registro RAF não encontrado', [
                    'id' => $id,
                    'user_id' => $userId,
                ]);
                return null;
            }
            
            // Validar que o user_id corresponde
            if ((int) $registro->user_id !== $userId) {
                Log::warning('Tentativa de acesso a registro de outro usuário', [
                    'id' => $id,
                    'expected_user_id' => $userId,
                    'actual_user_id' => $registro->user_id,
                ]);
                return null;
            }
            
            // Formatar dados no formato esperado pelo frontend
            return [
                'id' => $registro->id,
                'user_id' => $registro->user_id,
                'resume_url' => $registro->resume_url,
                'qtd_participantes_unicos' => $registro->qtd_participantes,
                'valor_total_consulta' => (float) $registro->valor_total_consulta,
                'custo_unitario' => (float) $registro->custo_unitario,
                'tipo_efd' => $registro->tipo_efd,
                'tipo_consulta' => $registro->tipo_consulta,
                'created_at' => $registro->created_at?->toIso8601String(),
                'updated_at' => $registro->updated_at?->toIso8601String(),
            ];
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar registro RAF do banco de dados', [
                'id' => $id,
                'user_id' => $userId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Autentica via token API ou sessão.
     * Retorna o usuário autenticado ou null.
     */
    private function authenticate(Request $request): ?User
    {
        // Tenta autenticação via token API (para n8n)
        $apiToken = $request->header('X-API-Token') ?? $request->input('api_token');
        $expectedToken = config('services.api.token');
        
        if (!empty($apiToken) && !empty($expectedToken) && $apiToken === $expectedToken) {
            // Token válido - busca user_id no payload (opcional)
            $userId = $request->input('user_id');
            if ($userId) {
                $user = User::find($userId);
                if ($user) {
                    Log::info('Usuário autenticado via token API com user_id', ['user_id' => $userId]);
                    return $user;
                }
                Log::warning('User ID não encontrado', ['user_id' => $userId]);
            }
            
            // Se não tiver user_id, tenta pegar da sessão
            $user = Auth::user();
            if ($user) {
                Log::info('Usuário autenticado via token API com sessão', ['user_id' => $user->id]);
                return $user;
            }
            
            // Se não tiver user_id nem sessão, busca o primeiro usuário
            $user = User::orderBy('id')->first();
            if ($user) {
                Log::info('Usuário autenticado via token API (fallback para primeiro usuário)', ['user_id' => $user->id]);
                return $user;
            }
            
            Log::warning('Token válido mas nenhum usuário encontrado no sistema');
            return null;
        }
        
        // Fallback: autenticação via sessão (para frontend)
        return Auth::user();
    }
    
    /**
     * Verifica se o token API é válido.
     */
    private function isTokenValid(Request $request): bool
    {
        $apiToken = $request->header('X-API-Token') ?? $request->input('api_token');
        $expectedToken = config('services.api.token');
        return !empty($apiToken) && !empty($expectedToken) && $apiToken === $expectedToken;
    }

    /**
     * Busca dados armazenados em cache por resume_url.
     * Requer autenticação do usuário.
     */
    public function getData(Request $request, string $resumeUrl)
    {
        try {
            // Decodificar URL se necessário
            $resumeUrl = urldecode($resumeUrl);
            
            // Verifica autenticação
            $user = Auth::user();
            
            if (!$user) {
                Log::warning('getData: usuário não autenticado', [
                    'resume_url' => $resumeUrl,
                    'session_id' => $request->session()->getId(),
                    'expects_json' => $request->expectsJson(),
                    'is_api_route' => $request->is('api/*'),
                    'headers' => [
                        'accept' => $request->header('Accept'),
                        'x-requested-with' => $request->header('X-Requested-With'),
                    ],
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado.',
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Buscar dados em cache
            $resumeUrlHash = md5($resumeUrl);
            $cacheKey = "raf_confirmation:{$user->id}:{$resumeUrlHash}";
            $cachedData = Cache::get($cacheKey);

            if (!$cachedData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados não encontrados.',
                ], Response::HTTP_NOT_FOUND);
            }

            // Validar que o user_id do cache corresponde ao usuário autenticado
            if (isset($cachedData['user_id']) && (int) $cachedData['user_id'] !== $user->id) {
                Log::warning('Tentativa de acesso a dados de outro usuário', [
                    'user_id' => $user->id,
                    'cache_user_id' => $cachedData['user_id'] ?? null,
                    'resume_url' => $resumeUrl,
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso negado.',
                ], Response::HTTP_FORBIDDEN);
            }

            return response()->json([
                'success' => true,
                'data' => $cachedData,
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados RAF do cache', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'resume_url' => $resumeUrl ?? null,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar dados.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Busca dados armazenados em cache por resume_url (endpoint público).
     * Não requer autenticação.
     */
    public function getDataPublic(Request $request, string $resumeUrl)
    {
        try {
            // Decodificar URL se necessário
            $resumeUrl = urldecode($resumeUrl);
            
            // Buscar dados em cache usando chave pública
            $resumeUrlHash = md5($resumeUrl);
            $publicCacheKey = "raf_confirmation_public:{$resumeUrlHash}";
            $cachedData = Cache::get($publicCacheKey);

            if (!$cachedData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados não encontrados.',
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'success' => true,
                'data' => $cachedData,
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados RAF do cache (público)', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'resume_url' => $resumeUrl ?? null,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar dados.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Recebe notificação do n8n com CSV em base64.
     * Recebe id, user_id, filename e csv_base64 diretamente, sem fazer query no banco.
     * Armazena no cache para envio via SSE ao frontend.
     * Aceita autenticação via token (header X-API-Token) ou sessão (para frontend).
     */
    public function receiveCsv(Request $request)
    {
        try {
            Log::info('Requisição recebida em receiveCsv', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'headers' => [
                    'x-api-token' => $request->hasHeader('X-API-Token') ? 'presente' : 'ausente',
                    'content-type' => $request->header('Content-Type'),
                ],
                'body_keys' => array_keys($request->all()),
            ]);

            // Verifica autenticação via token ou sessão (opcional)
            $user = $this->authenticate($request);

            // Processar payload - pode vir em formato n8n ou simples
            $rawData = $request->all();
            $receivedData = $rawData;

            // Se é array numérico (formato n8n), extrair o primeiro elemento
            if (is_array($rawData) && !empty($rawData) && array_keys($rawData) === range(0, count($rawData) - 1)) {
                $firstItem = $rawData[0] ?? null;
                if (is_array($firstItem) && isset($firstItem['data'])) {
                    $nestedData = $firstItem['data'];
                    if (isset($nestedData['data']) && is_array($nestedData['data'])) {
                        $receivedData = $nestedData['data'];
                        if (isset($nestedData['user_id']) && !isset($receivedData['user_id'])) {
                            $receivedData['user_id'] = $nestedData['user_id'];
                        }
                        if (isset($nestedData['id']) && !isset($receivedData['id'])) {
                            $receivedData['id'] = $nestedData['id'];
                        }
                    } else {
                        $receivedData = $nestedData;
                    }
                } else {
                    $receivedData = $firstItem;
                }
            } elseif (is_array($rawData) && isset($rawData['data']) && is_array($rawData['data'])) {
                if (isset($rawData['data']['data']) && is_array($rawData['data']['data'])) {
                    $receivedData = $rawData['data']['data'];
                    if (isset($rawData['data']['user_id']) && !isset($receivedData['user_id'])) {
                        $receivedData['user_id'] = $rawData['data']['user_id'];
                    }
                    if (isset($rawData['data']['id']) && !isset($receivedData['id'])) {
                        $receivedData['id'] = $rawData['data']['id'];
                    }
                } else {
                    $receivedData = $rawData['data'];
                }
            }

            // Validar campos obrigatórios: id, user_id, filename e csv_base64
            if (empty($receivedData['id']) || empty($receivedData['user_id']) || empty($receivedData['filename']) || empty($receivedData['csv_base64'])) {
                Log::warning('Dados incompletos em receiveCsv', [
                    'has_id' => !empty($receivedData['id']),
                    'has_user_id' => !empty($receivedData['user_id']),
                    'has_filename' => !empty($receivedData['filename']),
                    'has_csv_base64' => !empty($receivedData['csv_base64']),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Campos obrigatórios ausentes: id, user_id, filename e csv_base64 são necessários.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $relatorioId = (int) $receivedData['id'];
            $userId = (int) ($user?->id ?? $receivedData['user_id']);
            $filename = $receivedData['filename'];
            $csvBase64 = $receivedData['csv_base64'];

            // Decodificar BASE64 para CSV
            try {
                $csvContent = base64_decode($csvBase64, true);
                if ($csvContent === false || empty(trim($csvContent))) {
                    throw new \Exception('Falha ao decodificar base64 ou CSV vazio');
                }
            } catch (\Exception $e) {
                Log::error('Erro ao decodificar CSV base64', [
                    'id' => $relatorioId,
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao decodificar CSV base64.',
                ], Response::HTTP_BAD_REQUEST);
            }

            // Obter resume_url se fornecido, caso contrário usar null
            $resumeUrl = $receivedData['resume_url'] ?? null;

            Log::info('CSV recebido e decodificado com sucesso', [
                'id' => $relatorioId,
                'user_id' => $userId,
                'filename' => $filename,
                'csv_size' => strlen($csvContent),
                'resume_url' => $resumeUrl,
            ]);

            // Buscar consulta pendente pelo resume_url para obter o ID correto e dados adicionais
            $consultaPendente = null;
            if (!empty($resumeUrl)) {
                Log::info('Buscando consulta pendente pelo resume_url', [
                    'resume_url' => $resumeUrl,
                    'user_id' => $userId,
                ]);
                $consultaPendente = RafConsultaPendente::where('resume_url', $resumeUrl)
                    ->where('user_id', $userId)
                    ->first();
                Log::info('Consulta pendente encontrada', [
                    'encontrada' => $consultaPendente !== null,
                    'consulta_id' => $consultaPendente?->id,
                    'resume_url' => $resumeUrl,
                ]);
            } else {
                Log::info('Resume URL vazio, nao buscando consulta pendente');
            }

            // Determinar o ID para notificação: usar o ID da consulta pendente se encontrada
            // (que é o que o frontend está aguardando via SSE), senão usar o ID recebido
            $idParaNotificacao = $consultaPendente?->id ?? $relatorioId;
            Log::info('ID para notificacao determinado', [
                'id_para_notificacao' => $idParaNotificacao,
                'consulta_pendente_id' => $consultaPendente?->id,
                'relatorio_id_recebido' => $relatorioId,
            ]);

            // Salvar ou atualizar o CSV no banco de dados
            // Se temos resume_url, usar como chave de busca (mais confiável que ID)
            // Caso contrário, usar o ID recebido
            // Preencher todos os campos obrigatórios da tabela
            Log::info('Iniciando salvamento do CSV no banco de dados', [
                'tem_resume_url' => !empty($resumeUrl),
                'resume_url' => $resumeUrl,
                'relatorio_id' => $relatorioId,
                'user_id' => $userId,
                'filename' => $filename,
                'csv_size' => strlen($csvBase64),
            ]);
            
            if (!empty($resumeUrl)) {
                try {
                    $relatorioProcessado = RafRelatorioProcessado::updateOrCreate(
                        ['resume_url' => $resumeUrl],
                        [
                            'user_id' => $userId,
                            'report_csv_base64' => $csvBase64,
                            'filename' => $filename,
                            'document_type' => $consultaPendente?->tipo_efd ?? 'EFD Fiscal',
                            'consultant_type' => $consultaPendente?->tipo_consulta ?? 'completa',
                            'total_participants' => $consultaPendente?->qtd_participantes ?? 0,
                            'total_price' => $consultaPendente?->valor_total_consulta ?? 0.00,
                        ]
                    );
                    Log::info('CSV salvo/atualizado com sucesso usando resume_url', [
                        'relatorio_id' => $relatorioProcessado->id,
                        'wasRecentlyCreated' => $relatorioProcessado->wasRecentlyCreated,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Erro ao salvar CSV usando resume_url', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    throw $e;
                }
            } else {
                // Se não tem resume_url, tentar atualizar pelo ID ou criar novo
                try {
                    $relatorioProcessado = RafRelatorioProcessado::updateOrCreate(
                        ['id' => $relatorioId],
                        [
                            'user_id' => $userId,
                            'report_csv_base64' => $csvBase64,
                            'filename' => $filename,
                            'document_type' => $consultaPendente?->tipo_efd ?? 'EFD Fiscal',
                            'consultant_type' => $consultaPendente?->tipo_consulta ?? 'completa',
                            'total_participants' => $consultaPendente?->qtd_participantes ?? 0,
                            'total_price' => $consultaPendente?->valor_total_consulta ?? 0.00,
                        ]
                    );
                    // Se não tinha resume_url, usar o ID do registro salvo para notificação
                    $idParaNotificacao = $relatorioProcessado->id;
                    Log::info('CSV salvo/atualizado com sucesso usando ID', [
                        'relatorio_id' => $relatorioProcessado->id,
                        'wasRecentlyCreated' => $relatorioProcessado->wasRecentlyCreated,
                        'id_para_notificacao_atualizado' => $idParaNotificacao,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Erro ao salvar CSV usando ID', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    throw $e;
                }
            }

            Log::info('CSV salvo no banco de dados', [
                'id' => $relatorioProcessado->id,
                'consulta_pendente_id' => $consultaPendente?->id,
                'user_id' => $userId,
                'filename' => $filename,
                'id_para_notificacao' => $idParaNotificacao,
            ]);

            // Notificação será verificada diretamente do banco de dados via SSE
            // O SSE verifica RafRelatorioProcessado periodicamente para encontrar CSVs prontos
            
            // Retornar apenas sucesso - dados serão enviados via SSE
            return response()->json([
                'success' => true,
                'message' => 'CSV recebido e armazenado. Será enviado via SSE ao frontend.',
            ], Response::HTTP_OK);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Erro de validação na API receiveCsv', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);

        } catch (\Exception $e) {
            Log::error('Erro inesperado na API receiveCsv', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor. Tente novamente mais tarde.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Endpoint SSE para notificar o frontend quando dados estiverem prontos.
     * O frontend se conecta a este endpoint e recebe notificações em tempo real.
     * Verifica diretamente o banco de dados (RafConsultaPendente e RafRelatorioProcessado)
     * para garantir que notificações sejam entregues mesmo em ambientes diferentes.
     */
    public function streamNotifications(Request $request)
    {
        $user = Auth::user();
        
        // Obter relatorio_id da query string se fornecido
        $requestedRelatorioId = $request->query('relatorio_id');
        if ($requestedRelatorioId) {
            $requestedRelatorioId = (int) $requestedRelatorioId;
        }
        
        Log::info('SSE streamNotifications chamado', [
            'user_id' => $user?->id,
            'authenticated' => Auth::check(),
            'relatorio_id' => $requestedRelatorioId,
        ]);
        
        if (!$user) {
            Log::warning('SSE: usuário não autenticado');
            return response('Unauthorized', 401);
        }

        // Enviar comentário inicial para manter conexão viva
        return response()->stream(function () use ($user, $requestedRelatorioId) {
            // Enviar comentário inicial
            echo ": SSE connection established\n\n";
            
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
            
            try {
                Log::info('SSE: conexão estabelecida', ['user_id' => $user->id]);
            } catch (\Exception $e) {
                // Ignorar erros de log
            }
            
            $lastNotificationTime = null;
            $notifiedCsvIds = []; // IDs de CSVs já notificados nesta sessão
            $notifiedDataReadyIds = []; // IDs de data_ready já notificados nesta sessão
            $isFirstDbCheck = true; // Flag para primeira verificação no banco
            $connectionEstablishedAt = now(); // Timestamp de quando a conexão SSE foi estabelecida
            
            while (true) {
                try {
                    // ========================================
                    // 1. Verificar RafConsultaPendente para notificações data_ready
                    // Busca registros que precisam de confirmação de créditos
                    // IMPORTANTE: Filtrar apenas registros atualizados APÓS a conexão SSE
                    // ========================================
                    $pendenteQuery = RafConsultaPendente::where('user_id', $user->id)
                        ->whereNotNull('n8n_received_at')
                        ->where('n8n_received_at', '>', $connectionEstablishedAt)
                        ->whereNotNull('resume_url')
                        ->whereNotNull('valor_total_consulta')
                        ->whereNotIn('id', $notifiedDataReadyIds);
                    
                    // Se estamos aguardando um relatorio_id específico, filtrar por ele
                    if ($requestedRelatorioId) {
                        $pendenteQuery->where('id', $requestedRelatorioId);
                    }
                    
                    $pendente = $pendenteQuery->orderBy('n8n_received_at', 'desc')->first();
                    
                    if ($pendente) {
                        // Enviar notificação data_ready
                        // Converter valores para float pois campos decimal vêm como string do banco
                        $dataReadyNotification = [
                            'type' => 'data_ready',
                            'data' => [
                                'id' => $pendente->id,
                                'user_id' => $pendente->user_id,
                                'resume_url' => $pendente->resume_url,
                                'valor_total_consulta' => (float) $pendente->valor_total_consulta,
                                'qtd_participantes_unicos' => (int) $pendente->qtd_participantes,
                                'custo_unitario' => (float) $pendente->custo_unitario,
                            ],
                        ];
                        
                        $data = json_encode($dataReadyNotification);
                        echo "data: {$data}\n\n";
                        
                        if (ob_get_level() > 0) {
                            ob_flush();
                        }
                        flush();
                        
                        // Marcar como notificado
                        $notifiedDataReadyIds[] = $pendente->id;
                        
                        // Atualizar lastNotificationTime
                        if ($pendente->n8n_received_at) {
                            $lastNotificationTime = $pendente->n8n_received_at;
                        }
                        
                        try {
                            Log::info('SSE: data_ready enviado do banco de dados', [
                                'user_id' => $user->id,
                                'relatorio_id' => $pendente->id,
                            ]);
                        } catch (\Exception $e) {
                            // Ignorar erros de log
                        }
                    }
                    
                    // ========================================
                    // 3. Verificar banco de dados para CSV pronto
                    // ========================================
                    $query = RafRelatorioProcessado::where('user_id', $user->id)
                        ->whereNotNull('report_csv_base64')
                        ->where('report_csv_base64', '!=', '')
                        ->whereNotIn('id', $notifiedCsvIds);
                    
                    // Se estamos aguardando um relatorio_id específico, pode ser da consulta pendente
                    if ($requestedRelatorioId) {
                        // Logar apenas na primeira iteração para evitar poluição de logs
                        if ($isFirstDbCheck) {
                            Log::info('SSE: Iniciando busca por CSV para relatorio_id especifico', [
                                'user_id' => $user->id,
                                'requested_relatorio_id' => $requestedRelatorioId,
                            ]);
                        }
                        
                        // Primeiro tentar buscar diretamente pelo ID
                        // Criar uma nova query para não modificar a query base
                        $idQuery = RafRelatorioProcessado::where('user_id', $user->id)
                            ->whereNotNull('report_csv_base64')
                            ->where('report_csv_base64', '!=', '')
                            ->where('id', $requestedRelatorioId)
                            ->whereNotIn('id', $notifiedCsvIds);
                        
                        $csvFromDb = $idQuery->first();
                        
                        // Se não encontrou, pode ser que o ID seja da consulta pendente
                        // Buscar pela consulta pendente e então pelo resume_url
                        if (!$csvFromDb) {
                            $consultaPendente = RafConsultaPendente::where('id', $requestedRelatorioId)
                                ->where('user_id', $user->id)
                                ->first();
                            
                            if ($consultaPendente && !empty($consultaPendente->resume_url)) {
                                // Buscar o relatório processado pelo resume_url
                                // IMPORTANTE: Quando há requestedRelatorioId específico, NÃO aplicar whereNotIn
                                // porque o usuário está aguardando especificamente esse relatório
                                // e ele pode ter sido notificado anteriormente (ex: reconexão SSE)
                                $csvFromDb = RafRelatorioProcessado::where('user_id', $user->id)
                                    ->whereNotNull('report_csv_base64')
                                    ->where('report_csv_base64', '!=', '')
                                    ->where('resume_url', $consultaPendente->resume_url)
                                    ->first();
                            }
                        }
                    } else {
                        // Quando não há relatorio_id específico (modalidade gratuita),
                        // APENAS buscar relatórios criados APÓS a conexão ser estabelecida.
                        // Isso evita retornar relatórios antigos instantaneamente.
                        // Sempre filtrar por relatórios criados após a conexão SSE
                        $query->where('updated_at', '>', $connectionEstablishedAt);
                        
                        $csvFromDb = $query->orderBy('updated_at', 'desc')->first();
                    }
                    
                    // Marcar que já fez a primeira verificação
                    if ($isFirstDbCheck) {
                        $isFirstDbCheck = false;
                    }
                    
                    if ($csvFromDb) {
                        Log::info('SSE: CSV encontrado no banco, preparando notificacao', [
                            'user_id' => $user->id,
                            'csv_id' => $csvFromDb->id,
                            'requested_relatorio_id' => $requestedRelatorioId,
                            'resume_url' => $csvFromDb->resume_url,
                            'filename' => $csvFromDb->filename,
                            'updated_at' => $csvFromDb->updated_at,
                        ]);
                        
                        // Enviar notificação csv_ready do banco de dados
                        // Usar filename do banco ou fallback para nome genérico
                        $csvFilename = !empty($csvFromDb->filename) 
                            ? $csvFromDb->filename 
                            : 'raf_relatorio_' . $csvFromDb->id . '.csv';
                        
                        // Se estamos aguardando um relatorio_id específico (da consulta pendente),
                        // usar esse ID na notificação para que o frontend reconheça
                        $relatorioIdParaNotificacao = $requestedRelatorioId ?? $csvFromDb->id;
                        
                        $csvNotificationData = [
                            'type' => 'csv_ready',
                            'user_id' => $user->id,
                            'relatorio_id' => $relatorioIdParaNotificacao,
                            'csv_filename' => $csvFilename,
                            'timestamp' => $csvFromDb->updated_at->toIso8601String(),
                        ];
                        
                        Log::info('SSE: Enviando notificacao csv_ready', [
                            'notification_data' => $csvNotificationData,
                        ]);
                        
                        $data = json_encode([
                            'type' => 'csv_ready',
                            'data' => $csvNotificationData,
                        ]);
                        echo "data: {$data}\n\n";
                        
                        if (ob_get_level() > 0) {
                            ob_flush();
                        }
                        flush();
                        
                        // Marcar como notificado
                        $notifiedCsvIds[] = $csvFromDb->id;
                        $lastNotificationTime = $csvFromDb->updated_at;
                        
                        try {
                            Log::info('SSE: csv_ready enviado do banco de dados com sucesso', [
                                'user_id' => $user->id,
                                'relatorio_id' => $csvFromDb->id,
                                'relatorio_id_para_notificacao' => $relatorioIdParaNotificacao,
                            ]);
                        } catch (\Exception $e) {
                            // Ignorar erros de log
                        }
                    }
                    // Nota: Não logar quando CSV não é encontrado para evitar poluição de logs
                    // O sistema faz polling a cada segundo até o CSV estar disponível
                    
                    // Aguardar 1 segundo antes da próxima verificação (mais responsivo)
                    usleep(1000000); // 1 segundo em microsegundos
                    
                    // Verificar se a conexão ainda está ativa
                    if (connection_aborted()) {
                        break;
                    }
                } catch (\Exception $e) {
                    // Log do erro mas não quebrar a conexão
                    try {
                        Log::error('SSE: erro no loop', [
                            'user_id' => $user->id,
                            'message' => $e->getMessage(),
                        ]);
                    } catch (\Exception $logError) {
                        // Ignorar erros de log
                    }
                    // Continuar o loop mesmo com erro
                    usleep(1000000);
                    if (connection_aborted()) {
                        break;
                    }
                }
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }

    /**
     * Busca CSV do banco de dados por ID do relatório.
     * Aceita tanto ID de raf_relatorio_processado quanto ID de raf_consulta_pendente.
     * Retorna o CSV decodificado do base64 armazenado na tabela raf_relatorio_processado.
     * 
     * @param int $id ID do relatório (pode ser de raf_relatorio_processado ou raf_consulta_pendente)
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function getCsv(int $id)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                Log::warning('getCsv: usuário não autenticado', [
                    'id' => $id,
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado.',
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Primeiro tentar buscar diretamente em raf_relatorio_processado
            $relatorio = RafRelatorioProcessado::find($id);
            
            // Se não encontrou, pode ser que o ID seja da consulta pendente
            // Buscar pela consulta pendente e então pelo resume_url
            if (!$relatorio) {
                $consultaPendente = RafConsultaPendente::where('id', $id)
                    ->where('user_id', $user->id)
                    ->first();
                
                if ($consultaPendente && !empty($consultaPendente->resume_url)) {
                    // Buscar o relatório processado pelo resume_url
                    $relatorio = RafRelatorioProcessado::where('resume_url', $consultaPendente->resume_url)
                        ->where('user_id', $user->id)
                        ->first();
                }
            }
            
            if (!$relatorio) {
                Log::warning('Relatório não encontrado em getCsv', [
                    'id' => $id,
                    'user_id' => $user?->id,
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Relatório não encontrado.',
                ], Response::HTTP_NOT_FOUND);
            }
            
            // Validar que o relatório pertence ao usuário autenticado
            if ((int) $relatorio->user_id !== $user->id) {
                Log::warning('Tentativa de acesso a relatório de outro usuário em getCsv', [
                    'id' => $id,
                    'authenticated_user_id' => $user->id,
                    'relatorio_user_id' => $relatorio->user_id,
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso negado.',
                ], Response::HTTP_FORBIDDEN);
            }
            
            // Validar que tem CSV em base64
            if (empty($relatorio->report_csv_base64)) {
                Log::warning('Relatório sem CSV em base64', [
                    'id' => $id,
                    'user_id' => $user->id,
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'CSV não disponível para este relatório.',
                ], Response::HTTP_NOT_FOUND);
            }
            
            // Decodificar base64 para CSV
            try {
                $csvContent = base64_decode($relatorio->report_csv_base64, true);
                if ($csvContent === false || empty(trim($csvContent))) {
                    throw new \Exception('Falha ao decodificar base64 ou CSV vazio');
                }
            } catch (\Exception $e) {
                Log::error('Erro ao decodificar CSV base64 em getCsv', [
                    'id' => $id,
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao decodificar CSV.',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            
            // Usar filename do banco ou fallback para nome genérico
            $filename = !empty($relatorio->filename) 
                ? $relatorio->filename 
                : 'raf_relatorio_' . $id . '.csv';
            
            // Garantir extensão .csv
            if (!str_ends_with(strtolower($filename), '.csv')) {
                $filename .= '.csv';
            }
            
            Log::info('CSV recuperado do banco com sucesso', [
                'id' => $id,
                'user_id' => $user->id,
                'csv_size' => strlen($csvContent),
                'filename' => $filename,
            ]);
            
            // Retornar CSV como resposta de texto
            return response($csvContent, 200, [
                'Content-Type' => 'text/csv;charset=utf-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro inesperado em getCsv', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor. Tente novamente mais tarde.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Confirma o uso de créditos e envia approved/denied para o resume_url.
     * Recebe id e user_id, faz query na tabela raf_consulta_pendente e envia webhook.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmCredits(Request $request)
    {
        try {
            Log::info('Requisição recebida em DataReceiverController::confirmCredits', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'body' => $request->all(),
            ]);

            // Validar id e user_id
            $validated = $request->validate([
                'id' => 'required|integer',
                'user_id' => 'required|integer',
            ]);

            $id = (int) $validated['id'];
            $userId = (int) $validated['user_id'];

            // Verificar autenticação do usuário
            $user = Auth::user();
            if (!$user || (int) $user->id !== $userId) {
                Log::warning('Tentativa de confirmação com user_id diferente do autenticado', [
                    'authenticated_user_id' => $user?->id,
                    'requested_user_id' => $userId,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado ou não autorizado.',
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Query na tabela raf_consulta_pendente
            $registro = RafConsultaPendente::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$registro) {
                Log::warning('Registro não encontrado para confirmação de créditos', [
                    'id' => $id,
                    'user_id' => $userId,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Registro não encontrado.',
                ], Response::HTTP_NOT_FOUND);
            }

            // Buscar resume_url da tabela
            $resumeUrl = $registro->resume_url;
            if (empty($resumeUrl)) {
                Log::error('Resume URL não encontrado no registro', [
                    'id' => $id,
                    'user_id' => $userId,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'URL de confirmação não encontrada.',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            // Calcular valor de créditos necessário
            $valorTotalConsulta = (float) $registro->valor_total_consulta;
            $valorCreditos = $valorTotalConsulta;
            $saldoAtual = $this->creditService->getBalance($user);

            Log::info('Processando confirmação de créditos', [
                'user_id' => $userId,
                'registro_id' => $id,
                'saldo_atual' => $saldoAtual,
                'valor_solicitado' => $valorCreditos,
                'resume_url' => $resumeUrl,
            ]);

            // Verificar se tem créditos suficientes
            if (!$this->creditService->hasEnough($user, $valorCreditos)) {
                Log::warning('Créditos insuficientes para operação', [
                    'user_id' => $userId,
                    'saldo_atual' => $saldoAtual,
                    'valor_solicitado' => $valorCreditos,
                ]);

                // Envia denied para o webhook
                $webhookResult = $this->spedUploadService->sendWebhookStatus($resumeUrl, 'denied');

                if (!$webhookResult['success']) {
                    Log::error('Falha ao enviar denied para webhook', [
                        'resume_url' => $resumeUrl,
                        'error' => $webhookResult['message'] ?? 'Erro desconhecido',
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'insufficient_credits' => true,
                    'credits' => $saldoAtual,
                    'required' => $valorCreditos,
                    'message' => 'Créditos insuficientes. Entre em contato pelo telefone (69) 99999-9999 para adquirir mais créditos.',
                ], Response::HTTP_PAYMENT_REQUIRED);
            }

            // Descontar os créditos
            $deducted = $this->creditService->deduct($user, $valorCreditos);

            if (!$deducted) {
                Log::error('Falha ao descontar créditos', [
                    'user_id' => $userId,
                    'valor' => $valorCreditos,
                ]);

                // Envia denied para o webhook
                $webhookResult = $this->spedUploadService->sendWebhookStatus($resumeUrl, 'denied');

                if (!$webhookResult['success']) {
                    Log::error('Falha ao enviar denied para webhook após falha no desconto', [
                        'resume_url' => $resumeUrl,
                        'error' => $webhookResult['message'] ?? 'Erro desconhecido',
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao processar créditos. Tente novamente.',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            // Enviar approved para o webhook
            $webhookResult = $this->spedUploadService->sendWebhookStatus($resumeUrl, 'approved');

            if (!$webhookResult['success']) {
                // Reembolsar os créditos em caso de falha no webhook
                $this->creditService->add($user, $valorCreditos);

                Log::warning('Webhook falhou, créditos reembolsados', [
                    'user_id' => $userId,
                    'registro_id' => $id,
                    'valor' => $valorCreditos,
                    'error' => $webhookResult['message'] ?? 'Erro desconhecido',
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $webhookResult['message'] ?? 'Erro ao enviar confirmação. Créditos foram reembolsados.',
                    'credits' => $this->creditService->getBalance($user),
                ], Response::HTTP_BAD_GATEWAY);
            }

            Log::info('Confirmação de créditos processada com sucesso', [
                'user_id' => $userId,
                'registro_id' => $id,
                'valor' => $valorCreditos,
                'novo_saldo' => $this->creditService->getBalance($user),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Créditos confirmados com sucesso.',
                'credits' => $this->creditService->getBalance($user),
            ], Response::HTTP_OK);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Erro de validação na confirmação de créditos', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);

        } catch (\Exception $e) {
            Log::error('Erro inesperado na confirmação de créditos', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor. Tente novamente mais tarde.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

