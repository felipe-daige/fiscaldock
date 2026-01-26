<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MonitoramentoConsulta;
use App\Models\Participante;
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
            if (isset($receivedData['tab_id'])) {
                $registro->tab_id = $receivedData['tab_id'];
                $updated = true;
            }
            
            // IMPORTANTE: Limpar processing_started_at para permitir nova confirmação
            // Isso é necessário quando o n8n envia novos dados para o mesmo registro
            if ($registro->processing_started_at !== null) {
                $registro->processing_started_at = null;
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
     * Canal SSE com n8n para notificar quando relatório foi processado.
     * O n8n é responsável por todas as alterações no banco de dados.
     * Este método apenas valida que o registro existe e retorna sucesso.
     * O SSE (streamNotifications) verifica o banco diretamente para notificar o frontend.
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

            // Validar apenas id e user_id (campos obrigatórios)
            if (empty($receivedData['id']) || empty($receivedData['user_id'])) {
                Log::warning('Dados incompletos em receiveCsv', [
                    'has_id' => !empty($receivedData['id']),
                    'has_user_id' => !empty($receivedData['user_id']),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Campos obrigatórios ausentes: id e user_id são necessários.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $relatorioId = (int) $receivedData['id'];
            $userId = (int) ($user?->id ?? $receivedData['user_id']);

            Log::info('Validando registro em receiveCsv', [
                'relatorio_id' => $relatorioId,
                'user_id' => $userId,
            ]);

            // Buscar registro no banco (já salvo pelo n8n)
            $relatorioProcessado = RafRelatorioProcessado::find($relatorioId);

            if (!$relatorioProcessado) {
                Log::warning('Registro não encontrado em receiveCsv', [
                    'relatorio_id' => $relatorioId,
                    'user_id' => $userId,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Registro não encontrado.',
                ], Response::HTTP_NOT_FOUND);
            }

            // Validar user_id
            if ((int) $relatorioProcessado->user_id !== $userId) {
                Log::warning('Tentativa de acesso a registro de outro usuário em receiveCsv', [
                    'relatorio_id' => $relatorioId,
                    'expected_user_id' => $userId,
                    'actual_user_id' => $relatorioProcessado->user_id,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Acesso negado.',
                ], Response::HTTP_FORBIDDEN);
            }

            // Retornar sucesso - CSV já está no banco, salvo pelo n8n
            // O SSE verifica o banco diretamente e notifica o frontend quando CSV está pronto
            Log::info('Registro validado com sucesso em receiveCsv', [
                'relatorio_id' => $relatorioProcessado->id,
                'user_id' => $userId,
                'document_type' => $relatorioProcessado->document_type,
                'consultant_type' => $relatorioProcessado->consultant_type,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Registro validado com sucesso. SSE notificará frontend quando CSV estiver pronto.',
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
        
        // Obter tab_id da query string se fornecido
        $requestedTabId = $request->query('tab_id');
        
        Log::info('SSE streamNotifications chamado', [
            'user_id' => $user?->id,
            'authenticated' => Auth::check(),
            'relatorio_id' => $requestedRelatorioId,
            'tab_id' => $requestedTabId,
        ]);
        
        if (!$user) {
            Log::warning('SSE: usuário não autenticado');
            return response('Unauthorized', 401);
        }

        // Enviar comentário inicial para manter conexão viva
        return response()->stream(function () use ($user, $requestedRelatorioId, $requestedTabId) {
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
            $notifiedErrorIds = []; // IDs de erros já notificados nesta sessão
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
                    
                    // Filtrar por tab_id se fornecido para isolar notificações entre abas
                    // IMPORTANTE: Também incluir registros sem tab_id, pois o n8n pode criar
                    // novos registros sem tab_id quando envia dados para /api/data/receive
                    if ($requestedTabId) {
                        $pendenteQuery->where(function ($q) use ($requestedTabId) {
                            $q->where('tab_id', $requestedTabId)
                              ->orWhereNull('tab_id'); // Incluir registros sem tab_id
                        });
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
                                'tab_id' => $pendente->tab_id,
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
                    // 2. Verificar erros de processamento
                    // ========================================
                    // IMPORTANTE: Só buscar erros que ocorreram APÓS a conexão SSE ser estabelecida
                    // (ou poucos segundos antes, para capturar erros quase simultâneos).
                    // Usar uma margem pequena de 3 segundos para evitar race conditions,
                    // mas não muito grande para evitar pegar erros de envios anteriores.
                    $errorTimeThreshold = $connectionEstablishedAt->copy()->subSeconds(3);
                    
                    $errorQuery = RafConsultaPendente::where('user_id', $user->id)
                        ->where('status', 'error')
                        ->whereNotNull('error_at')
                        ->whereNotIn('id', $notifiedErrorIds)
                        ->where('error_at', '>=', $errorTimeThreshold);
                    
                    // Se temos tab_id ou relatorio_id específico, filtrar por eles
                    // Isso garante que só pegamos erros relevantes para esta sessão
                    if ($requestedTabId && $requestedRelatorioId) {
                        // Buscar por tab_id OU relatorio_id
                        $errorQuery->where(function ($q) use ($requestedRelatorioId, $requestedTabId) {
                            $q->where('tab_id', $requestedTabId)
                              ->orWhere('id', $requestedRelatorioId);
                        });
                    } elseif ($requestedTabId) {
                        $errorQuery->where('tab_id', $requestedTabId);
                    } elseif ($requestedRelatorioId) {
                        $errorQuery->where('id', $requestedRelatorioId);
                    }
                    
                    $errorConsulta = $errorQuery->orderBy('error_at', 'desc')->first();
                    
                    if ($errorConsulta) {
                        // Enviar notificação de erro
                        // Incluir tab_id para permitir que o frontend valide pelo tab_id
                        // mesmo quando o relatorio_id é diferente (caso de erro INVALID_SPED)
                        $errorNotification = [
                            'type' => 'error',
                            'data' => [
                                'relatorio_id' => $errorConsulta->id,
                                'tab_id' => $errorConsulta->tab_id,
                                'code' => $errorConsulta->error_code,
                                'message' => $errorConsulta->error_message,
                                'credits_refunded' => (bool) $errorConsulta->credits_refunded,
                                'recoverable' => true, // Pode ser baseado no error_code no futuro
                            ],
                        ];
                        
                        $data = json_encode($errorNotification);
                        echo "data: {$data}\n\n";
                        
                        if (ob_get_level() > 0) {
                            ob_flush();
                        }
                        flush();
                        
                        // Marcar como notificado
                        $notifiedErrorIds[] = $errorConsulta->id;
                        
                        // Atualizar lastNotificationTime
                        if ($errorConsulta->error_at) {
                            $lastNotificationTime = $errorConsulta->error_at;
                        }
                        
                        try {
                            Log::info('SSE: error enviado do banco de dados', [
                                'user_id' => $user->id,
                                'relatorio_id' => $errorConsulta->id,
                                'error_code' => $errorConsulta->error_code,
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
                            $consultaPendenteQuery = RafConsultaPendente::where('id', $requestedRelatorioId)
                                ->where('user_id', $user->id);
                            
                            // Filtrar por tab_id se fornecido para isolar notificações entre abas
                            if ($requestedTabId) {
                                $consultaPendenteQuery->where('tab_id', $requestedTabId);
                            }
                            
                            $consultaPendente = $consultaPendenteQuery->first();
                            
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
                        
                        // Obter tab_id da consulta pendente se disponível
                        $tabIdParaNotificacao = null;
                        if ($requestedRelatorioId) {
                            $consultaPendenteParaTabId = RafConsultaPendente::where('id', $requestedRelatorioId)
                                ->where('user_id', $user->id)
                                ->first();
                            if ($consultaPendenteParaTabId) {
                                $tabIdParaNotificacao = $consultaPendenteParaTabId->tab_id;
                            }
                        }
                        
                        $csvNotificationData = [
                            'type' => 'csv_ready',
                            'user_id' => $user->id,
                            'relatorio_id' => $relatorioIdParaNotificacao,
                            'tab_id' => $tabIdParaNotificacao,
                            'csv_filename' => $csvFilename,
                            'timestamp' => $csvFromDb->updated_at->toIso8601String(),
                            // Tipo de consulta para determinar exibição de CND
                            'consultant_type' => $csvFromDb->consultant_type,
                            // Dados da empresa
                            'razao_social_empresa' => $csvFromDb->razao_social_empresa,
                            'cnpj_empresa_analisada' => $csvFromDb->cnpj_empresa_analisada,
                            'data_inicial_analisada' => $csvFromDb->data_inicial_analisada?->format('Y-m-d'),
                            'data_final_analisada' => $csvFromDb->data_final_analisada?->format('Y-m-d'),
                            'total_participants' => $csvFromDb->total_participants,
                            // Situação Cadastral
                            'qnt_situacao_nula' => $csvFromDb->qnt_situacao_nula ?? 0,
                            'qnt_situacao_ativa' => $csvFromDb->qnt_situacao_ativa ?? 0,
                            'qnt_situacao_suspensa' => $csvFromDb->qnt_situacao_suspensa ?? 0,
                            'qnt_situacao_inapta' => $csvFromDb->qnt_situacao_inapta ?? 0,
                            'qnt_situacao_baixada' => $csvFromDb->qnt_situacao_baixada ?? 0,
                            // Regime Tributário
                            'qnt_simples' => $csvFromDb->qnt_simples ?? 0,
                            'qnt_presumido' => $csvFromDb->qnt_presumido ?? 0,
                            'qnt_real' => $csvFromDb->qnt_real ?? 0,
                            'qnt_regime_indeterminado' => $csvFromDb->qnt_regime_indeterminado ?? 0,
                            // CND (só exibido no frontend se consultant_type = 'completa')
                            'qnt_cnd_regular' => $csvFromDb->qnt_cnd_regular ?? 0,
                            'qnt_cnd_pendencia' => $csvFromDb->qnt_cnd_pendencia ?? 0,
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
                    'message' => 'Créditos insuficientes. Entre em contato pelo telefone (67) 99984-4366 para adquirir mais créditos.',
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

    /**
     * Endpoint para n8n reportar erros de processamento.
     * POST /api/data/error
     * Aceita autenticação via token API (para n8n) ou sessão.
     */
    public function receiveError(Request $request)
    {
        try {
            Log::info('Requisição recebida em DataReceiverController::receiveError', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'headers' => [
                    'x-api-token' => $request->hasHeader('X-API-Token') ? 'presente' : 'ausente',
                    'content-type' => $request->header('Content-Type'),
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
                    } else {
                        $receivedData = $nestedData;
                    }
                } else {
                    $receivedData = $firstItem;
                }
            } elseif (is_array($rawData) && isset($rawData['data']) && is_array($rawData['data'])) {
                if (isset($rawData['data']['data']) && is_array($rawData['data']['data'])) {
                    $receivedData = $rawData['data']['data'];
                } else {
                    $receivedData = $rawData['data'];
                }
            }

            // Validar campos obrigatórios
            // resume_url pode ser string, false, ou null (quando SPED é inválido)
            $validated = $request->validate([
                'resume_url' => 'nullable', // Aceita string, false, null
                'error_code' => 'required|string|max:50',
                'error_message' => 'required|string|max:500',
                'refund_credits' => 'sometimes|boolean',
                'id' => 'sometimes|integer', // ID da consulta (alternativa quando resume_url é false)
                'user_id' => 'sometimes|integer', // ID do usuário (alternativa quando resume_url é false)
                'tab_id' => 'sometimes|string|max:36', // tab_id para isolar notificações entre abas
            ], [], [
                'resume_url' => 'URL de retomada',
                'error_code' => 'código do erro',
                'error_message' => 'mensagem de erro',
                'refund_credits' => 'reembolsar créditos',
                'id' => 'ID da consulta',
                'user_id' => 'ID do usuário',
                'tab_id' => 'ID da aba',
            ]);

            // Verificar se resume_url é false (arquivo inválido, não há consulta para buscar por resume_url)
            $resumeUrl = $validated['resume_url'];
            $isInvalidFile = ($resumeUrl === false || $resumeUrl === 'false');
            
            if ($isInvalidFile) {
                $resumeUrl = null;
            }

            // Buscar a consulta pendente
            $consulta = null;
            $userIdToSearch = $receivedData['user_id'] ?? $validated['user_id'] ?? $user?->id;
            $tabIdToSearch = $receivedData['tab_id'] ?? $validated['tab_id'] ?? null;

            Log::debug('receiveError: Iniciando busca de consulta pendente', [
                'is_invalid_file' => $isInvalidFile,
                'user_id_to_search' => $userIdToSearch,
                'tab_id_to_search' => $tabIdToSearch,
                'resume_url' => $resumeUrl,
                'error_code' => $validated['error_code'],
            ]);

            // Quando é arquivo inválido (INVALID_SPED), priorizar busca por tab_id
            // pois o resume_url não existe e o registro pode ter sido criado em um ID diferente
            if ($isInvalidFile && $userIdToSearch && $tabIdToSearch) {
                // Estratégia prioritária para arquivo inválido: buscar por tab_id + user_id
                // Não filtrar por status para encontrar qualquer registro da mesma aba
                Log::debug('receiveError: Buscando por tab_id (arquivo inválido)', [
                    'user_id' => $userIdToSearch,
                    'tab_id' => $tabIdToSearch,
                ]);
                
                $consulta = RafConsultaPendente::where('user_id', $userIdToSearch)
                    ->where('tab_id', $tabIdToSearch)
                    ->where('created_at', '>=', now()->subMinutes(30))
                    ->orderBy('created_at', 'desc')
                    ->first();
                    
                if ($consulta) {
                    Log::info('Consulta encontrada por tab_id para erro de arquivo inválido', [
                        'consulta_id' => $consulta->id,
                        'user_id' => $userIdToSearch,
                        'tab_id' => $tabIdToSearch,
                        'error_code' => $validated['error_code'],
                    ]);
                } else {
                    Log::warning('receiveError: Nenhuma consulta encontrada por tab_id (arquivo inválido)', [
                        'user_id' => $userIdToSearch,
                        'tab_id' => $tabIdToSearch,
                        'total_registros_user' => RafConsultaPendente::where('user_id', $userIdToSearch)
                            ->where('created_at', '>=', now()->subMinutes(30))
                            ->count(),
                        'registros_com_tab_id' => RafConsultaPendente::where('user_id', $userIdToSearch)
                            ->where('tab_id', $tabIdToSearch)
                            ->where('created_at', '>=', now()->subMinutes(30))
                            ->count(),
                    ]);
                }
            }

            // Estratégia 1: Buscar por resume_url se fornecido e válido (apenas se não for arquivo inválido)
            if (!$consulta && !$isInvalidFile && !empty($resumeUrl) && is_string($resumeUrl)) {
                Log::debug('receiveError: Buscando por resume_url', ['resume_url' => $resumeUrl]);
                $consulta = RafConsultaPendente::where('resume_url', $resumeUrl)->first();
                if ($consulta) {
                    Log::debug('receiveError: Consulta encontrada por resume_url', ['consulta_id' => $consulta->id]);
                }
            }

            // Estratégia 2: Se não encontrou e temos id/user_id, buscar por eles
            if (!$consulta && isset($validated['id']) && isset($validated['user_id'])) {
                Log::debug('receiveError: Buscando por id/user_id', [
                    'id' => $validated['id'],
                    'user_id' => $validated['user_id'],
                ]);
                $consulta = RafConsultaPendente::where('id', $validated['id'])
                    ->where('user_id', $validated['user_id'])
                    ->first();
                if ($consulta) {
                    Log::debug('receiveError: Consulta encontrada por id/user_id', ['consulta_id' => $consulta->id]);
                }
            }

            // Estratégia 3: Buscar por tab_id + user_id (para casos não-invalidos também)
            if (!$consulta && $userIdToSearch && $tabIdToSearch) {
                Log::debug('receiveError: Buscando por tab_id (caso não-inválido)', [
                    'user_id' => $userIdToSearch,
                    'tab_id' => $tabIdToSearch,
                ]);
                $consulta = RafConsultaPendente::where('user_id', $userIdToSearch)
                    ->where('tab_id', $tabIdToSearch)
                    ->where('status', 'pending')
                    ->where('created_at', '>=', now()->subMinutes(30))
                    ->orderBy('created_at', 'desc')
                    ->first();
                    
                if ($consulta) {
                    Log::info('Consulta encontrada por tab_id para atualizar com erro', [
                        'consulta_id' => $consulta->id,
                        'user_id' => $userIdToSearch,
                        'tab_id' => $tabIdToSearch,
                    ]);
                } else {
                    Log::debug('receiveError: Nenhuma consulta encontrada por tab_id (caso não-inválido)', [
                        'user_id' => $userIdToSearch,
                        'tab_id' => $tabIdToSearch,
                    ]);
                }
            }

            // Estratégia 4: Se ainda não encontrou, buscar consulta mais recente do usuário
            if (!$consulta && $userIdToSearch) {
                Log::debug('receiveError: Buscando consulta mais recente do usuário', ['user_id' => $userIdToSearch]);
                $consulta = RafConsultaPendente::where('user_id', $userIdToSearch)
                    ->where('status', 'pending')
                    ->where('created_at', '>=', now()->subMinutes(30))
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($consulta) {
                    Log::debug('receiveError: Consulta mais recente encontrada', ['consulta_id' => $consulta->id]);
                }
            }

            // Se encontrou consulta existente, atualizar com o erro
            if ($consulta) {
                $consulta->update([
                    'status' => 'error',
                    'error_code' => $validated['error_code'],
                    'error_message' => $validated['error_message'],
                    'error_at' => now(),
                ]);
                
                Log::info('Consulta existente atualizada com erro', [
                    'consulta_id' => $consulta->id,
                    'user_id' => $consulta->user_id,
                    'tab_id' => $consulta->tab_id,
                    'error_code' => $validated['error_code'],
                    'is_invalid_file' => $isInvalidFile,
                ]);
                
                // Se é arquivo inválido, retornar sucesso (SSE vai notificar o usuário)
                if ($isInvalidFile) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Erro registrado na consulta existente. Usuário será notificado via SSE.',
                        'consulta_id' => $consulta->id,
                    ], Response::HTTP_OK);
                }
            } else {
                // Não encontrou consulta, criar nova apenas como fallback
                if ($userIdToSearch) {
                    $consulta = RafConsultaPendente::create([
                        'user_id' => $userIdToSearch,
                        'tab_id' => $tabIdToSearch,
                        'status' => 'error',
                        'error_code' => $validated['error_code'],
                        'error_message' => $validated['error_message'],
                        'error_at' => now(),
                    ]);
                    
                    Log::info('Registro de erro criado (fallback - consulta não encontrada)', [
                        'consulta_id' => $consulta->id,
                        'user_id' => $userIdToSearch,
                        'tab_id' => $tabIdToSearch,
                        'error_code' => $validated['error_code'],
                        'is_invalid_file' => $isInvalidFile,
                    ]);
                    
                    // Se é arquivo inválido, retornar sucesso (SSE vai notificar o usuário)
                    if ($isInvalidFile) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Erro registrado. Usuário será notificado via SSE.',
                            'consulta_id' => $consulta->id,
                        ], Response::HTTP_OK);
                    }
                } else {
                    Log::warning('Não foi possível criar consulta de erro - user_id não disponível', [
                        'resume_url' => $resumeUrl,
                        'id' => $validated['id'] ?? null,
                        'error_code' => $validated['error_code'],
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'user_id não disponível para registrar erro.',
                    ], Response::HTTP_BAD_REQUEST);
                }
            }

            // Obter usuário da consulta se não foi autenticado via token
            if (!$user) {
                $user = $consulta->user;
            }

            // Verificar se precisa reembolsar créditos
            $creditsRefunded = false;
            $newBalance = null;

            if ($validated['refund_credits'] ?? false) {
                try {
                    // Verificar se a consulta já tinha créditos descontados
                    // Se o status não for 'error' ainda, pode ser que os créditos já tenham sido descontados
                    // Nesse caso, reembolsar
                    if ($user && $consulta->valor_total_consulta > 0) {
                        $this->creditService->add($user, $consulta->valor_total_consulta);
                        $creditsRefunded = true;
                        $newBalance = $this->creditService->getBalance($user);

                        Log::info('Créditos reembolsados ao reportar erro', [
                            'user_id' => $user->id,
                            'consulta_id' => $consulta->id,
                            'valor_reembolsado' => $consulta->valor_total_consulta,
                            'novo_saldo' => $newBalance,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Erro ao reembolsar créditos', [
                        'user_id' => $user?->id,
                        'consulta_id' => $consulta->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Continuar mesmo se o reembolso falhar
                }
            }

            // Atualizar status para erro
            $updateData = [
                'status' => 'error',
                'error_code' => $validated['error_code'],
                'error_message' => $validated['error_message'],
                'credits_refunded' => $creditsRefunded,
                'error_at' => now(),
            ];
            
            // Atualizar tab_id se foi enviado e a consulta não tinha (para garantir notificação na aba correta)
            $tabIdFromPayload = $receivedData['tab_id'] ?? $validated['tab_id'] ?? null;
            if ($tabIdFromPayload && empty($consulta->tab_id)) {
                $updateData['tab_id'] = $tabIdFromPayload;
            }
            
            $consulta->update($updateData);

            Log::warning('Erro recebido do n8n e registrado', [
                'consulta_id' => $consulta->id,
                'user_id' => $consulta->user_id,
                'tab_id' => $consulta->tab_id,
                'error_code' => $validated['error_code'],
                'error_message' => $validated['error_message'],
                'credits_refunded' => $creditsRefunded,
            ]);

            $response = [
                'success' => true,
                'message' => 'Erro registrado com sucesso.',
            ];

            if ($creditsRefunded && $newBalance !== null) {
                $response['credits_refunded'] = true;
                $response['new_balance'] = $newBalance;
            }

            return response()->json($response, Response::HTTP_OK);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Erro de validação na API receiveError', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);

        } catch (\Exception $e) {
            Log::error('Erro inesperado na API receiveError', [
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
     * Recebe progresso da importação/processamento de arquivo SPED (enviado pelo n8n).
     * Armazena em cache para o SSE ler e enviar ao frontend.
     * NÃO edita banco de dados - apenas cache.
     *
     * POST /api/monitoramento/sped/importacao-txt/progress
     *
     * Payload esperado (novo formato - n8n controla 100% do progresso):
     * {
     *   "user_id": 1,
     *   "tab_id": "550e8400-e29b-41d4-a716-446655440000",
     *   "progresso": 45,
     *   "mensagem": "Identificando participantes...",
     *   "status": "processando"
     * }
     *
     * Payload legado (ainda suportado para compatibilidade):
     * {
     *   "importacao_id": 123,
     *   "status": "processando",
     *   "total_cnpjs": 150,
     *   "processados": 75,
     *   "importados": 70,
     *   "duplicados": 5
     * }
     */
    public function receiveImportacaoTxtProgress(Request $request)
    {
        try {
            Log::info('Requisição recebida em receiveImportacaoTxtProgress', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'headers' => [
                    'x-api-token' => $request->hasHeader('X-API-Token') ? 'presente' : 'ausente',
                    'content-type' => $request->header('Content-Type'),
                ],
                'body' => $request->all(),
            ]);

            // Verifica autenticação via token
            if (!$this->isTokenValid($request)) {
                Log::warning('Token inválido em receiveImportacaoTxtProgress');
                return response()->json([
                    'success' => false,
                    'message' => 'Token de API inválido.',
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Detectar formato do payload (novo vs legado)
            $hasNewFormat = $request->has('user_id') && $request->has('tab_id') && $request->has('progresso');
            $hasLegacyFormat = $request->has('importacao_id');

            if ($hasNewFormat) {
                // Novo formato: n8n controla 100% do progresso
                return $this->handleNewProgressFormat($request);
            } elseif ($hasLegacyFormat) {
                // Formato legado: compatibilidade com implementação anterior
                return $this->handleLegacyProgressFormat($request);
            } else {
                Log::warning('Formato de payload não reconhecido em receiveImportacaoTxtProgress', [
                    'request_data' => $request->all(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Formato de payload inválido. Use user_id+tab_id+progresso ou importacao_id.',
                ], Response::HTTP_BAD_REQUEST);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Erro de validação em receiveImportacaoTxtProgress', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);

        } catch (\Exception $e) {
            Log::error('Erro inesperado em receiveImportacaoTxtProgress', [
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
     * Processa novo formato de progresso (user_id + tab_id).
     * n8n controla 100% do progresso (percentual + mensagem).
     *
     * Quando status="erro", pode incluir:
     * - error_code: Código do erro (ex: "API_TIMEOUT", "INFOSIMPLES_ERROR")
     * - error_message: Mensagem descritiva do erro
     */
    private function handleNewProgressFormat(Request $request)
    {
        // Debug: logar dados raw antes da validação
        Log::info('DEBUG handleNewProgressFormat - request raw', [
            'has_dados_key' => $request->has('dados'),
            'dados_is_array' => is_array($request->input('dados')),
            'dados_type' => gettype($request->input('dados')),
            'dados_not_empty' => !empty($request->input('dados')),
        ]);

        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'tab_id' => 'required|string|max:36',
            'progresso' => 'required|integer|min:0|max:100',
            'mensagem' => 'nullable|string|max:255',
            'status' => 'required|in:iniciando,processando,concluido,erro',
            // Campos opcionais para erros
            'error_code' => 'nullable|string|max:50',
            'error_message' => 'nullable|string|max:500',
            // Campo flexível para dados agregados (nome empresa, totais, etc.)
            // Não usar 'array' pois pode rejeitar arrays aninhados complexos do n8n
            'dados' => 'nullable',
        ]);

        // Debug: logar dados após validação
        Log::info('DEBUG handleNewProgressFormat - after validation', [
            'validated_has_dados' => isset($validated['dados']),
            'validated_dados_not_empty' => !empty($validated['dados'] ?? null),
        ]);

        // Chave do cache: progresso:{user_id}:{tab_id}
        $cacheKey = "progresso:{$validated['user_id']}:{$validated['tab_id']}";

        // Dados para cache (repassa exatamente o que n8n enviou)
        $cacheData = [
            'user_id' => $validated['user_id'],
            'tab_id' => $validated['tab_id'],
            'progresso' => $validated['progresso'],
            'mensagem' => $validated['mensagem'] ?? null,
            'status' => $validated['status'],
            'updated_at' => now()->toIso8601String(),
        ];

        // Adicionar campos de erro se fornecidos
        if (!empty($validated['error_code'])) {
            $cacheData['error_code'] = $validated['error_code'];
        }
        if (!empty($validated['error_message'])) {
            $cacheData['error_message'] = $validated['error_message'];
        }

        // Sempre incluir campo dados no cache (mesmo se vazio, para consistência)
        // Isso evita que dados válidos sejam perdidos por condições restritivas
        $cacheData['dados'] = $validated['dados'] ?? [];

        // Armazena em cache (TTL 10 minutos)
        Cache::put($cacheKey, $cacheData, 600);

        Log::info('Progresso armazenado em cache (novo formato)', [
            'cache_key' => $cacheKey,
            'user_id' => $validated['user_id'],
            'tab_id' => $validated['tab_id'],
            'progresso' => $validated['progresso'],
            'status' => $validated['status'],
            'has_error' => !empty($validated['error_code']),
            'has_dados' => !empty($validated['dados']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Progresso atualizado.',
            'progresso' => $validated['progresso'],
        ], Response::HTTP_OK);
    }

    /**
     * Processa formato legado de progresso (importacao_id).
     * Mantido para compatibilidade com implementações anteriores.
     */
    private function handleLegacyProgressFormat(Request $request)
    {
        $validated = $request->validate([
            'importacao_id' => 'required|integer',
            'status' => 'required|in:processando,concluido,erro',
            'total_cnpjs' => 'sometimes|integer|min:0',
            'processados' => 'sometimes|integer|min:0',
            'importados' => 'sometimes|integer|min:0',
            'duplicados' => 'sometimes|integer|min:0',
            'error_message' => 'sometimes|string|max:500',
        ]);

        $importacaoId = $validated['importacao_id'];
        $cacheKey = "importacao_progresso_{$importacaoId}";

        // Extrair valores com defaults
        $total = $validated['total_cnpjs'] ?? 0;
        $processados = $validated['processados'] ?? 0;
        $importados = $validated['importados'] ?? 0;
        $duplicados = $validated['duplicados'] ?? 0;

        // Calcular porcentagem
        $porcentagem = $total > 0 ? (int) round(($processados / $total) * 100) : 0;

        // Dados para cache
        $cacheData = [
            'status' => $validated['status'],
            'total_cnpjs' => $total,
            'processados' => $processados,
            'importados' => $importados,
            'duplicados' => $duplicados,
            'porcentagem' => $porcentagem,
            'updated_at' => now()->toIso8601String(),
        ];

        // Se houver mensagem de erro, incluir
        if (!empty($validated['error_message'])) {
            $cacheData['error_message'] = $validated['error_message'];
        }

        // Armazena em cache (expira em 10 minutos)
        Cache::put($cacheKey, $cacheData, 600);

        Log::info('Progresso de importação armazenado em cache (formato legado)', [
            'importacao_id' => $importacaoId,
            'cache_key' => $cacheKey,
            'status' => $validated['status'],
            'porcentagem' => $porcentagem,
            'processados' => $processados,
            'total' => $total,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Progresso atualizado.',
            'porcentagem' => $porcentagem,
        ], Response::HTTP_OK);
    }

    /**
     * Recebe resultado de consulta do Monitoramento (enviado pelo n8n).
     * n8n pode escrever diretamente no PostgreSQL, mas também pode usar este
     * endpoint para notificar Laravel e permitir lógica adicional.
     *
     * POST /api/monitoramento/consulta/resultado
     *
     * Payload esperado:
     * {
     *   "consulta_id": 789,
     *   "status": "sucesso", // ou "erro"
     *   "resultado": {...}, // JSON com dados das APIs
     *   "situacao_geral": "regular", // "regular", "atencao", "irregular"
     *   "tem_pendencias": false,
     *   "proxima_validade": "2026-07-15", // menor validade das certidões
     *   "error_code": "TIMEOUT", // se status=erro
     *   "error_message": "API não respondeu", // se status=erro
     *   "participante": { // dados para atualizar participante
     *     "razao_social": "EMPRESA EXEMPLO LTDA",
     *     "situacao_cadastral": "Ativa",
     *     "regime_tributario": "Simples Nacional"
     *   }
     * }
     */
    public function receiveMonitoramentoConsulta(Request $request)
    {
        try {
            Log::info('Requisição recebida em receiveMonitoramentoConsulta', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'headers' => [
                    'x-api-token' => $request->hasHeader('X-API-Token') ? 'presente' : 'ausente',
                    'content-type' => $request->header('Content-Type'),
                ],
                'body' => $request->all(),
            ]);

            // Verifica autenticação via token
            if (!$this->isTokenValid($request)) {
                Log::warning('Token inválido em receiveMonitoramentoConsulta');
                return response()->json([
                    'success' => false,
                    'message' => 'Token de API inválido.',
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Validar payload
            $validated = $request->validate([
                'consulta_id' => 'required|integer',
                'status' => 'required|in:sucesso,erro,processando',
                'resultado' => 'sometimes|array',
                'situacao_geral' => 'sometimes|in:regular,atencao,irregular',
                'tem_pendencias' => 'sometimes|boolean',
                'proxima_validade' => 'sometimes|nullable|date',
                'error_code' => 'sometimes|string|max:50',
                'error_message' => 'sometimes|string|max:500',
                'participante' => 'sometimes|array',
                'participante.razao_social' => 'sometimes|string|max:255',
                'participante.situacao_cadastral' => 'sometimes|string|max:100',
                'participante.regime_tributario' => 'sometimes|string|max:100',
            ]);

            $consultaId = $validated['consulta_id'];

            // Buscar consulta
            $consulta = MonitoramentoConsulta::find($consultaId);

            if (!$consulta) {
                Log::warning('Consulta não encontrada em receiveMonitoramentoConsulta', [
                    'consulta_id' => $consultaId,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Consulta não encontrada.',
                ], Response::HTTP_NOT_FOUND);
            }

            // Atualizar consulta
            $updateData = [
                'status' => $validated['status'],
                'executado_em' => now(),
            ];

            if ($validated['status'] === 'sucesso') {
                if (isset($validated['resultado'])) {
                    $updateData['resultado'] = $validated['resultado'];
                }
                if (isset($validated['situacao_geral'])) {
                    $updateData['situacao_geral'] = $validated['situacao_geral'];
                }
                if (isset($validated['tem_pendencias'])) {
                    $updateData['tem_pendencias'] = $validated['tem_pendencias'];
                }
                if (isset($validated['proxima_validade'])) {
                    $updateData['proxima_validade'] = $validated['proxima_validade'];
                }
            } elseif ($validated['status'] === 'erro') {
                if (isset($validated['error_code'])) {
                    $updateData['error_code'] = $validated['error_code'];
                }
                if (isset($validated['error_message'])) {
                    $updateData['error_message'] = $validated['error_message'];
                }
            }

            $consulta->update($updateData);

            Log::info('Consulta atualizada com resultado', [
                'consulta_id' => $consultaId,
                'status' => $validated['status'],
            ]);

            // Atualizar participante se dados foram fornecidos
            if (isset($validated['participante']) && !empty($validated['participante'])) {
                $participante = Participante::find($consulta->participante_id);

                if ($participante) {
                    $participanteUpdate = array_filter([
                        'razao_social' => $validated['participante']['razao_social'] ?? null,
                        'situacao_cadastral' => $validated['participante']['situacao_cadastral'] ?? null,
                        'regime_tributario' => $validated['participante']['regime_tributario'] ?? null,
                        'ultima_consulta_em' => now(),
                    ]);

                    if (!empty($participanteUpdate)) {
                        $participante->update($participanteUpdate);

                        Log::info('Participante atualizado com dados da consulta', [
                            'participante_id' => $participante->id,
                            'consulta_id' => $consultaId,
                        ]);
                    }
                }
            }

            // Armazenar em cache para SSE (notificação em tempo real)
            $cacheKey = "monitoramento_consulta_resultado_{$consultaId}";
            Cache::put($cacheKey, [
                'consulta_id' => $consultaId,
                'user_id' => $consulta->user_id,
                'status' => $validated['status'],
                'situacao_geral' => $validated['situacao_geral'] ?? null,
                'updated_at' => now()->toIso8601String(),
            ], 300); // Cache por 5 minutos

            return response()->json([
                'success' => true,
                'message' => 'Resultado da consulta processado com sucesso.',
                'consulta_id' => $consultaId,
            ], Response::HTTP_OK);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Erro de validação em receiveMonitoramentoConsulta', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);

        } catch (\Exception $e) {
            Log::error('Erro inesperado em receiveMonitoramentoConsulta', [
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
     * Recebe progresso de importação de XMLs do Monitoramento (enviado pelo n8n).
     *
     * POST /api/monitoramento/xml/importacao/progress
     *
     * Payload esperado:
     * {
     *   "user_id": 1,
     *   "tab_id": "uuid",
     *   "importacao_id": 456,
     *   "progresso": 45,
     *   "status": "processando",
     *   "mensagem": "Processando XML 67 de 150...",
     *   "dados": {
     *     "total_xmls": 150,
     *     "xmls_processados": 67,
     *     "participantes_novos": 23,
     *     "participantes_atualizados": 15,
     *     "erros": [{"arquivo": "x.xml", "motivo": "XML inválido"}]
     *   }
     * }
     */
    public function receiveXmlImportacaoProgress(Request $request)
    {
        try {
            Log::info('Requisição recebida em receiveXmlImportacaoProgress', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'headers' => [
                    'x-api-token' => $request->hasHeader('X-API-Token') ? 'presente' : 'ausente',
                    'content-type' => $request->header('Content-Type'),
                ],
                'body' => $request->all(),
            ]);

            // Verifica autenticação via token
            if (!$this->isTokenValid($request)) {
                Log::warning('Token inválido em receiveXmlImportacaoProgress');
                return response()->json([
                    'success' => false,
                    'message' => 'Token de API inválido.',
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Validar payload
            $validated = $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'tab_id' => 'required|string|max:36',
                'progresso' => 'required|integer|min:0|max:100',
                'mensagem' => 'nullable|string|max:255',
                'status' => 'required|in:iniciando,processando,concluido,erro',
                'importacao_id' => 'nullable|integer',
                'error_code' => 'nullable|string|max:50',
                'error_message' => 'nullable|string|max:500',
                'dados' => 'nullable',
            ]);

            // Chave do cache: progresso:{user_id}:{tab_id}
            $cacheKey = "progresso:{$validated['user_id']}:{$validated['tab_id']}";

            // Dados para cache (repassa exatamente o que n8n enviou)
            $cacheData = [
                'user_id' => $validated['user_id'],
                'tab_id' => $validated['tab_id'],
                'progresso' => $validated['progresso'],
                'mensagem' => $validated['mensagem'] ?? null,
                'status' => $validated['status'],
                'updated_at' => now()->toIso8601String(),
            ];

            // Adicionar importacao_id se fornecido
            if (!empty($validated['importacao_id'])) {
                $cacheData['importacao_id'] = $validated['importacao_id'];
            }

            // Adicionar campos de erro se fornecidos
            if (!empty($validated['error_code'])) {
                $cacheData['error_code'] = $validated['error_code'];
            }
            if (!empty($validated['error_message'])) {
                $cacheData['error_message'] = $validated['error_message'];
            }

            // Sempre incluir campo dados no cache
            $cacheData['dados'] = $validated['dados'] ?? [];

            // Armazena em cache (TTL 10 minutos)
            Cache::put($cacheKey, $cacheData, 600);

            Log::info('Progresso XML armazenado em cache', [
                'cache_key' => $cacheKey,
                'user_id' => $validated['user_id'],
                'tab_id' => $validated['tab_id'],
                'progresso' => $validated['progresso'],
                'status' => $validated['status'],
                'has_error' => !empty($validated['error_code']),
                'has_dados' => !empty($validated['dados']),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Progresso atualizado.',
                'progresso' => $validated['progresso'],
            ], Response::HTTP_OK);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Erro de validação em receiveXmlImportacaoProgress', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);

        } catch (\Exception $e) {
            Log::error('Erro inesperado em receiveXmlImportacaoProgress', [
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

