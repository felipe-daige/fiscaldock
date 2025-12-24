<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RafConsultaPendente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DataReceiverController extends Controller
{
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
            
            // Marcar que o n8n enviou os dados (orquestrador)
            if (!$registro->n8n_received_at) {
                $registro->n8n_received_at = now();
                $saved = $registro->save();
                
                // Recarregar do banco para confirmar que foi salvo
                $registro->refresh();
                
                Log::info('n8n_received_at marcado no registro RAF', [
                    'id' => $id,
                    'user_id' => $userId,
                    'n8n_received_at' => $registro->n8n_received_at,
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

            // Notificar frontend via cache que dados estão prontos
            // O frontend está escutando via SSE e verificará este cache
            $notificationKey = "raf_data_ready:{$userId}:{$id}";
            $notification = [
                'id' => $id,
                'user_id' => $userId,
                'resume_url' => $formattedData['resume_url'],
                'valor_total_consulta' => $formattedData['valor_total_consulta'],
                'qtd_participantes_unicos' => $formattedData['qtd_participantes_unicos'],
                'custo_unitario' => $formattedData['custo_unitario'],
                'timestamp' => now()->toIso8601String(),
            ];
            
            Cache::put($notificationKey, $notification, 300); // 5 minutos de TTL
            
            // Adicionar ao índice de notificações do usuário
            $indexKey = "raf_notifications_index:{$userId}";
            $notificationIds = Cache::get($indexKey, []);
            if (!in_array($id, $notificationIds)) {
                $notificationIds[] = $id;
                Cache::put($indexKey, $notificationIds, 300);
            }
            
            Log::info('Notificação SSE salva no cache', [
                'user_id' => $userId,
                'id' => $id,
                'notification_key' => $notificationKey,
            ]);

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
        
        Log::debug('Autenticação API', [
            'token_recebido' => $apiToken ? substr($apiToken, 0, 10) . '...' : null,
            'token_esperado' => $expectedToken ? substr($expectedToken, 0, 10) . '...' : null,
            'token_valido' => !empty($apiToken) && !empty($expectedToken) && $apiToken === $expectedToken,
            'user_id_body' => $request->input('user_id'),
            'auth_user' => Auth::user()?->id,
        ]);
        
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
            
            Log::debug('getData chamado', [
                'resume_url' => $resumeUrl,
                'user_authenticated' => $user !== null,
                'user_id' => $user?->id,
                'has_session' => Auth::check(),
                'expects_json' => $request->expectsJson(),
                'is_api_route' => $request->is('api/*'),
                'headers' => [
                    'accept' => $request->header('Accept'),
                    'x-requested-with' => $request->header('X-Requested-With'),
                ],
            ]);
            
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

            Log::debug('Dados RAF recuperados do cache', [
                'user_id' => $user->id,
                'resume_url' => $resumeUrl,
                'cache_key' => $cacheKey,
            ]);

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

            Log::debug('Dados RAF recuperados do cache (público)', [
                'resume_url' => $resumeUrl,
                'cache_key' => $publicCacheKey,
            ]);

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
     * Busca os dados mais recentes do banco de dados para o usuário autenticado.
     * Busca diretamente da tabela raf_consulta_pendente (sem cache).
     */
    public function getLatestData(Request $request)
    {
        try {
            $user = Auth::user();
            
            Log::debug('getLatestData chamado', [
                'user_authenticated' => $user !== null,
                'user_id' => $user?->id,
                'has_session' => Auth::check(),
            ]);
            
            if (!$user) {
                Log::warning('getLatestData: usuário não autenticado');
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado.',
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Primeiro, tentar buscar registro com n8n_received_at (processado pelo n8n)
            $registro = RafConsultaPendente::where('user_id', $user->id)
                ->whereNotNull('n8n_received_at')
                ->orderBy('n8n_received_at', 'desc')
                ->first();

            // Se não encontrou, buscar o registro mais recente que tenha resume_url e valor_total_consulta
            // (mesmo sem n8n_received_at, pode ter sido processado mas n8n_received_at não foi salvo)
            if (!$registro) {
                $registro = RafConsultaPendente::where('user_id', $user->id)
                    ->whereNotNull('resume_url')
                    ->whereNotNull('valor_total_consulta')
                    ->where('created_at', '>=', now()->subHours(24)) // Últimas 24 horas
                    ->orderBy('created_at', 'desc')
                    ->first();
            }

            if (!$registro) {
                Log::info('getLatestData: nenhum registro encontrado para o usuário', [
                    'user_id' => $user->id,
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Dados ainda não disponíveis. Aguarde o processamento do n8n.',
                    'n8n_received' => false,
                ], Response::HTTP_NOT_FOUND);
            }
            
            // Se encontrou registro sem n8n_received_at mas com dados, ainda retornar
            // (pode ter sido processado mas n8n_received_at não foi salvo por algum motivo)
            if (!$registro->n8n_received_at && $registro->resume_url && !is_null($registro->valor_total_consulta)) {
                Log::info('getLatestData: retornando registro sem n8n_received_at mas com dados válidos', [
                    'user_id' => $user->id,
                    'registro_id' => $registro->id,
                    'has_resume_url' => !empty($registro->resume_url),
                    'has_valor_total' => !is_null($registro->valor_total_consulta),
                ]);
            }

            // Formatar dados no formato esperado pelo frontend
            $formattedData = [
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

            // Buscar CSV do cache se disponível (usando resume_url como chave)
            $resumeUrlHash = md5($registro->resume_url);
            $csvCacheKey = "raf_csv:{$resumeUrlHash}";
            $csvCacheData = Cache::get($csvCacheKey);
            
            if ($csvCacheData) {
                $formattedData['csv'] = $csvCacheData['csv'];
                $formattedData['csv_filename'] = $csvCacheData['filename'] ?? 'resultado.csv';
                $formattedData['has_csv'] = true;
            } else {
                $formattedData['has_csv'] = false;
            }
            
            Log::info('Dados RAF mais recentes recuperados do banco de dados', [
                'user_id' => $user->id,
                'registro_id' => $registro->id,
                'resume_url' => $registro->resume_url,
                'has_csv' => $formattedData['has_csv'] ?? false,
                'n8n_received_at' => $registro->n8n_received_at?->toIso8601String(),
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $formattedData,
                'resume_url' => $registro->resume_url,
                'n8n_received' => true,
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados recentes do banco de dados', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar dados.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Recebe CSV em base64 do n8n e armazena no cache.
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

            // Validar campos obrigatórios
            if (empty($receivedData['csv']) || empty($receivedData['resume_url'])) {
                Log::warning('Dados CSV incompletos', [
                    'has_csv' => !empty($receivedData['csv']),
                    'has_resume_url' => !empty($receivedData['resume_url']),
                    'has_filename' => !empty($receivedData['filename']),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Campos obrigatórios ausentes: csv e resume_url são necessários.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $csvBase64 = $receivedData['csv'];
            $resumeUrl = $receivedData['resume_url'];
            $filename = $receivedData['filename'] ?? 'resultado.csv';
            $userId = $user?->id ?? $receivedData['user_id'] ?? null;

            // Decodificar base64 para string CSV
            try {
                $csvContent = base64_decode($csvBase64, true);
                if ($csvContent === false) {
                    throw new \Exception('Falha ao decodificar base64');
                }
            } catch (\Exception $e) {
                Log::error('Erro ao decodificar CSV base64', [
                    'error' => $e->getMessage(),
                    'resume_url' => $resumeUrl,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao decodificar CSV base64. Verifique o formato.',
                ], Response::HTTP_BAD_REQUEST);
            }

            // Validar que o conteúdo decodificado não está vazio
            if (empty(trim($csvContent))) {
                Log::warning('CSV decodificado está vazio', [
                    'resume_url' => $resumeUrl,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'CSV decodificado está vazio.',
                ], Response::HTTP_BAD_REQUEST);
            }

            // Normalizar resume_url para usar como parte da chave
            $resumeUrlHash = md5($resumeUrl);

            // Armazenar CSV no cache
            $csvCacheKey = "raf_csv:{$resumeUrlHash}";
            $csvCacheData = [
                'csv' => $csvContent,
                'filename' => $filename,
                'resume_url' => $resumeUrl,
                'user_id' => $userId ? (int) $userId : null,
                'received_at' => now()->toIso8601String(),
            ];

            Cache::put($csvCacheKey, $csvCacheData, 3600);

            Log::info('CSV armazenado em cache', [
                'user_id' => $userId,
                'resume_url' => $resumeUrl,
                'filename' => $filename,
                'csv_size' => strlen($csvContent),
                'cache_key' => $csvCacheKey,
            ]);

            // Atualizar dados de confirmação se existirem, adicionando referência ao CSV
            if ($userId) {
                $confirmationCacheKey = "raf_confirmation:{$userId}:{$resumeUrlHash}";
                $confirmationData = Cache::get($confirmationCacheKey);
                if ($confirmationData) {
                    $confirmationData['has_csv'] = true;
                    $confirmationData['csv_filename'] = $filename;
                    Cache::put($confirmationCacheKey, $confirmationData, 3600);
                }
            }

            // Atualizar também cache público
            $publicConfirmationCacheKey = "raf_confirmation_public:{$resumeUrlHash}";
            $publicConfirmationData = Cache::get($publicConfirmationCacheKey);
            if ($publicConfirmationData) {
                $publicConfirmationData['has_csv'] = true;
                $publicConfirmationData['csv_filename'] = $filename;
                Cache::put($publicConfirmationCacheKey, $publicConfirmationData, 3600);
            }

            return response()->json([
                'success' => true,
                'message' => 'CSV recebido e armazenado com sucesso.',
                'data' => [
                    'resume_url' => $resumeUrl,
                    'filename' => $filename,
                    'csv_size' => strlen($csvContent),
                    'received_at' => now()->toIso8601String(),
                ],
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
     * Verifica apenas o cache, sem fazer queries no banco.
     */
    public function streamNotifications(Request $request)
    {
        $user = Auth::user();
        
        Log::info('SSE streamNotifications chamado', [
            'user_id' => $user?->id,
            'authenticated' => Auth::check(),
        ]);
        
        if (!$user) {
            Log::warning('SSE: usuário não autenticado');
            return response('Unauthorized', 401);
        }

        // Enviar comentário inicial para manter conexão viva
        return response()->stream(function () use ($user) {
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
            
            while (true) {
                try {
                    // Verificar apenas o cache - buscar notificações do usuário
                    // Usar uma chave de índice para rastrear notificações
                    $indexKey = "raf_notifications_index:{$user->id}";
                    $notificationIds = Cache::get($indexKey, []);
                    
                    foreach ($notificationIds as $notificationId) {
                        $key = "raf_data_ready:{$user->id}:{$notificationId}";
                        $notification = Cache::get($key);
                        
                        if ($notification && isset($notification['timestamp'])) {
                            $notificationTime = \Carbon\Carbon::parse($notification['timestamp']);
                            
                            // Se é uma notificação nova (mais recente que a última processada)
                            if (!$lastNotificationTime || $notificationTime->isAfter($lastNotificationTime)) {
                                // Enviar evento SSE
                                $data = json_encode([
                                    'type' => 'data_ready',
                                    'data' => $notification,
                                ]);
                                echo "data: {$data}\n\n";
                                
                                if (ob_get_level() > 0) {
                                    ob_flush();
                                }
                                flush();
                                
                                // Remover notificação do cache após enviar
                                Cache::forget($key);
                                
                                // Atualizar índice
                                $notificationIds = array_filter($notificationIds, fn($id) => $id !== $notificationId);
                                Cache::put($indexKey, $notificationIds, 300);
                                
                                // Atualizar lastNotificationTime
                                $lastNotificationTime = $notificationTime;
                                
                                // Sair do loop após enviar notificação
                                break;
                            }
                        }
                    }
                    
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
}

