<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DataReceiverController extends Controller
{
    /**
     * Recebe dados via HTTP POST.
     * Aceita autenticação via token (header X-API-Token) ou sessão (para frontend).
     */
    public function receive(Request $request)
    {
        try {
            Log::info('Requisição recebida em DataReceiverController', [
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
            
            // Se não autenticado, ainda permite processar, mas sem user_id
            if (!$user) {
                Log::info('Requisição sem autenticação - processando sem user_id', [
                    'has_token' => $request->hasHeader('X-API-Token'),
                    'has_session' => Auth::check(),
                    'token_valido' => $this->isTokenValid($request),
                ]);
            }

            // Validação básica - pode ser customizada conforme necessário
            // Aceita qualquer payload, não exige estrutura específica
            $rawData = $request->all();
            
            Log::info('Dados recebidos (raw)', [
                'user_id' => $user?->id ?? null,
                'data_keys' => is_array($rawData) ? array_keys($rawData) : 'não é array',
                'data_count' => is_array($rawData) ? count($rawData) : 1,
                'is_numeric_array' => is_array($rawData) && array_keys($rawData) === range(0, count($rawData) - 1),
            ]);

            // Processar formato do n8n: pode vir como array com objeto aninhado ou formato simples
            // Formato n8n: [{"success": true, "data": {"user_id": 1, "data": {...}}}]
            // Formato simples: {"resume_url": "...", "valor_total_consulta": ...}
            $receivedData = $rawData;
            
            // Se é array numérico (formato n8n), extrair o primeiro elemento
            if (is_array($rawData) && !empty($rawData) && array_keys($rawData) === range(0, count($rawData) - 1)) {
                Log::debug('Payload detectado como array numérico (formato n8n)', [
                    'array_length' => count($rawData),
                ]);
                
                // Pegar o primeiro elemento do array
                $firstItem = $rawData[0] ?? null;
                
                if (is_array($firstItem) && isset($firstItem['data'])) {
                    // Formato: [{"success": true, "data": {"user_id": 1, "data": {...}}}]
                    $nestedData = $firstItem['data'];
                    
                    // Se tem 'data' dentro de 'data', é o formato aninhado do n8n
                    if (isset($nestedData['data']) && is_array($nestedData['data'])) {
                        Log::debug('Formato n8n aninhado detectado', [
                            'has_user_id' => isset($nestedData['user_id']),
                            'nested_data_keys' => array_keys($nestedData['data'] ?? []),
                        ]);
                        
                        // Extrair dados do formato aninhado
                        $receivedData = $nestedData['data'];
                        
                        // Se o user_id está no nível intermediário, usar ele
                        if (isset($nestedData['user_id']) && !isset($receivedData['user_id'])) {
                            $receivedData['user_id'] = $nestedData['user_id'];
                        }
                    } else {
                        // Formato: [{"success": true, "data": {...}}] - dados diretos
                        $receivedData = $nestedData;
                    }
                } else {
                    // Se o primeiro elemento não tem estrutura esperada, usar como está
                    $receivedData = $firstItem;
                }
            } elseif (is_array($rawData) && isset($rawData['data']) && is_array($rawData['data'])) {
                // Formato: {"data": {"user_id": 1, "data": {...}}}
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
                'has_resume_url' => isset($receivedData['resume_url']),
                'has_valor_total' => isset($receivedData['valor_total_consulta']),
            ]);

            // Se contém dados de confirmação RAF, armazenar em cache
            $hasResumeUrl = isset($receivedData['resume_url']) && !empty($receivedData['resume_url']);
            $hasValorTotal = isset($receivedData['valor_total_consulta']) && ($receivedData['valor_total_consulta'] !== null && $receivedData['valor_total_consulta'] !== '');
            
            Log::debug('Verificando se deve armazenar dados RAF', [
                'has_resume_url' => $hasResumeUrl,
                'has_valor_total' => $hasValorTotal,
                'resume_url' => $receivedData['resume_url'] ?? null,
                'valor_total' => $receivedData['valor_total_consulta'] ?? null,
                'resume_url_type' => gettype($receivedData['resume_url'] ?? null),
                'valor_total_type' => gettype($receivedData['valor_total_consulta'] ?? null),
            ]);
            
            if ($hasResumeUrl && $hasValorTotal) {
                // Garantir que user_id seja sempre int, mesmo quando vem como string do payload
                $userId = null;
                if ($user?->id) {
                    $userId = (int) $user->id;
                } elseif (isset($receivedData['user_id'])) {
                    $userId = (int) $receivedData['user_id'];
                }
                
                Log::debug('Condições atendidas, preparando para armazenar', [
                    'user_id' => $userId,
                    'user_id_type' => gettype($userId),
                    'resume_url' => $receivedData['resume_url'],
                    'valor_total_consulta' => $receivedData['valor_total_consulta'],
                    'authenticated_user_id' => $user?->id,
                    'received_user_id' => $receivedData['user_id'] ?? null,
                ]);
                
                if (!empty($receivedData['resume_url'])) {
                    // Normalizar resume_url para usar como parte da chave
                    $resumeUrlHash = md5($receivedData['resume_url']);
                    
                    // Preparar dados para armazenar
                    $cacheData = [
                        'user_id' => $userId,
                        'resume_url' => $receivedData['resume_url'],
                        'qnt_participantes' => (int) ($receivedData['qnt_participantes'] ?? $receivedData['qtd_participantes_unicos'] ?? 0),
                        'qtd_participantes_unicos' => (int) ($receivedData['qtd_participantes_unicos'] ?? $receivedData['qnt_participantes'] ?? 0),
                        'valor_total_consulta' => (float) $receivedData['valor_total_consulta'],
                        'custo_unitario' => (float) ($receivedData['custo_unitario'] ?? 0),
                        'received_at' => now()->toIso8601String(),
                    ];
                    
                    // Armazenar em cache com chave privada (se tiver user_id)
                    if ($userId) {
                        $cacheKey = "raf_confirmation:{$userId}:{$resumeUrlHash}";
                        Cache::put($cacheKey, $cacheData, 3600);
                        
                        Log::info('Dados RAF armazenados em cache (privado)', [
                            'user_id' => $userId,
                            'resume_url' => $receivedData['resume_url'],
                            'cache_key' => $cacheKey,
                        ]);
                    }
                    
                    // Sempre armazenar também com chave pública (acessível por resume_url apenas)
                    $publicCacheKey = "raf_confirmation_public:{$resumeUrlHash}";
                    Cache::put($publicCacheKey, $cacheData, 3600);
                    
                    // Criar/atualizar lista global de resume_urls no cache público
                    // Isso permite buscar dados mesmo quando a lista privada está vazia (problema de cache compartilhado)
                    $globalRecentListKey = "raf_recent_list_global";
                    $globalRecentList = Cache::get($globalRecentListKey, []);
                    
                    // Adicionar resume_url à lista global (máximo 20 itens para não ficar muito grande)
                    $globalRecentList[] = [
                        'resume_url' => $receivedData['resume_url'],
                        'resume_url_hash' => $resumeUrlHash,
                        'user_id' => $userId,
                        'timestamp' => now()->toIso8601String(),
                    ];
                    
                    // Manter apenas os 20 mais recentes
                    $globalRecentList = array_slice($globalRecentList, -20);
                    Cache::put($globalRecentListKey, $globalRecentList, 3600);
                    
                    Log::info('Dados RAF armazenados em cache (público)', [
                        'resume_url' => $receivedData['resume_url'],
                        'cache_key' => $publicCacheKey,
                        'cache_data' => $cacheData,
                        'global_list_count' => count($globalRecentList),
                    ]);
                    
                    // Armazenar resume_url em uma lista de dados recentes do usuário
                    // Isso permite que o frontend busque dados recentes sem saber o resume_url específico
                    if ($userId) {
                        $recentListKey = "raf_recent_list:{$userId}";
                        $recentList = Cache::get($recentListKey, []);
                        
                        // #region agent log
                        try {
                            $debugLogPath = '/opt/hub_contabil/.cursor/debug.log';
                            $debugLogDir = dirname($debugLogPath);
                            if (!is_dir($debugLogDir)) {
                                @mkdir($debugLogDir, 0755, true);
                            }
                            if (is_dir($debugLogDir) && is_writable($debugLogDir)) {
                                file_put_contents($debugLogPath, json_encode([
                                    'sessionId' => 'debug-session',
                                    'runId' => 'run1',
                                    'hypothesisId' => 'A',
                                    'location' => 'DataReceiverController.php:191',
                                    'message' => 'Salvando lista de recentes - ANTES',
                                    'data' => [
                                        'user_id' => $userId,
                                        'user_id_type' => gettype($userId),
                                        'user_id_string' => (string)$userId,
                                        'recent_list_key' => $recentListKey,
                                        'cache_driver' => config('cache.default'),
                                        'cache_store' => config('cache.stores.' . config('cache.default') . '.driver') ?? 'unknown',
                                        'app_env' => config('app.env'),
                                        'current_count' => count($recentList),
                                        'resume_url' => $receivedData['resume_url'] ?? null,
                                    ],
                                    'timestamp' => time() * 1000
                                ]) . "\n", FILE_APPEND);
                            }
                        } catch (\Throwable $e) {
                            // Ignorar erros de log de debug - não são críticos
                        }
                        // #endregion
                        
                        Log::debug('Lista de recentes antes de adicionar', [
                            'user_id' => $userId,
                            'user_id_type' => gettype($userId),
                            'current_count' => count($recentList),
                            'recent_list' => $recentList,
                        ]);
                        
                        // Verificar se o resume_url já existe na lista para evitar duplicatas
                        $exists = false;
                        foreach ($recentList as $item) {
                            if (($item['resume_url'] ?? '') === $receivedData['resume_url']) {
                                $exists = true;
                                break;
                            }
                        }
                        
                        // Adicionar resume_url à lista apenas se não existir (máximo 10 itens)
                        if (!$exists) {
                            $recentList[] = [
                                'resume_url' => $receivedData['resume_url'],
                                'resume_url_hash' => $resumeUrlHash,
                                'timestamp' => now()->toIso8601String(),
                            ];
                            
                            // Manter apenas os 10 mais recentes
                            $recentList = array_slice($recentList, -10);
                            
                            Cache::put($recentListKey, $recentList, 3600);
                            
                            // #region agent log
                            try {
                                $debugLogPath = '/opt/hub_contabil/.cursor/debug.log';
                                $debugLogDir = dirname($debugLogPath);
                                if (!is_dir($debugLogDir)) {
                                    @mkdir($debugLogDir, 0755, true);
                                }
                                if (is_dir($debugLogDir) && is_writable($debugLogDir)) {
                                    file_put_contents($debugLogPath, json_encode([
                                        'sessionId' => 'debug-session',
                                        'runId' => 'run1',
                                        'hypothesisId' => 'A',
                                        'location' => 'DataReceiverController.php:250',
                                        'message' => 'Salvando lista de recentes - DEPOIS de put',
                                        'data' => [
                                            'user_id' => $userId,
                                            'user_id_type' => gettype($userId),
                                            'user_id_string' => (string)$userId,
                                            'recent_list_key' => $recentListKey,
                                            'cache_driver' => config('cache.default'),
                                            'cache_store' => config('cache.stores.' . config('cache.default') . '.driver') ?? 'unknown',
                                            'app_env' => config('app.env'),
                                            'stored_count' => count($recentList),
                                            'resume_url' => $receivedData['resume_url'],
                                            'resume_url_hash' => $resumeUrlHash,
                                        ],
                                        'timestamp' => time() * 1000
                                    ]) . "\n", FILE_APPEND);
                                }
                            } catch (\Throwable $e) {
                                // Ignorar erros de log de debug - não são críticos
                            }
                            // #endregion
                            
                            Log::info('Lista de dados recentes atualizada', [
                                'user_id' => $userId,
                                'recent_list_count' => count($recentList),
                                'latest_resume_url' => $recentList[count($recentList) - 1]['resume_url'] ?? null,
                                'recent_list' => $recentList,
                            ]);
                            
                            // Verificar se foi armazenado corretamente
                            $verifyList = Cache::get($recentListKey, []);
                            
                            // #region agent log
                            try {
                                $debugLogPath = '/opt/hub_contabil/.cursor/debug.log';
                                $debugLogDir = dirname($debugLogPath);
                                if (!is_dir($debugLogDir)) {
                                    @mkdir($debugLogDir, 0755, true);
                                }
                                if (is_dir($debugLogDir) && is_writable($debugLogDir)) {
                                    file_put_contents($debugLogPath, json_encode([
                                        'sessionId' => 'debug-session',
                                        'runId' => 'run1',
                                        'hypothesisId' => 'A',
                                        'location' => 'DataReceiverController.php:296',
                                        'message' => 'Verificação pós-armazenamento',
                                        'data' => [
                                            'user_id' => $userId,
                                            'user_id_type' => gettype($userId),
                                            'user_id_string' => (string)$userId,
                                            'recent_list_key' => $recentListKey,
                                            'cache_driver' => config('cache.default'),
                                            'cache_store' => config('cache.stores.' . config('cache.default') . '.driver') ?? 'unknown',
                                            'app_env' => config('app.env'),
                                            'stored_count' => count($verifyList),
                                            'expected_count' => count($recentList),
                                            'matches' => count($verifyList) === count($recentList),
                                            'verify_list_keys' => array_map(fn($item) => $item['resume_url'] ?? null, $verifyList),
                                        ],
                                        'timestamp' => time() * 1000
                                    ]) . "\n", FILE_APPEND);
                                }
                            } catch (\Throwable $e) {
                                // Ignorar erros de log de debug - não são críticos
                            }
                            // #endregion
                            
                            Log::debug('Verificação pós-armazenamento da lista', [
                                'user_id' => $userId,
                                'stored_count' => count($verifyList),
                                'matches' => count($verifyList) === count($recentList),
                                'verify_list' => $verifyList,
                            ]);
                        } else {
                            Log::debug('Resume URL já existe na lista de recentes, não adicionando duplicata', [
                                'user_id' => $userId,
                                'resume_url' => $receivedData['resume_url'],
                            ]);
                        }
                    } else {
                        Log::warning('User ID não disponível, não armazenando na lista de recentes', [
                            'received_user_id' => $receivedData['user_id'] ?? null,
                            'authenticated_user' => $user?->id ?? null,
                        ]);
                    }
                } else {
                    Log::warning('Resume URL vazio, não armazenando dados', [
                        'user_id' => $userId,
                        'resume_url_value' => $receivedData['resume_url'] ?? null,
                    ]);
                }
            } else {
                Log::debug('Dados não contêm resume_url ou valor_total_consulta, não armazenando', [
                    'has_resume_url' => isset($receivedData['resume_url']),
                    'has_valor_total' => isset($receivedData['valor_total_consulta']),
                    'received_data_keys' => array_keys($receivedData),
                ]);
            }

            // Preparar resposta
            $responseData = [
                'received_at' => now()->toIso8601String(),
                'user_id' => $user?->id ?? $receivedData['user_id'] ?? null,
                'data' => $receivedData,
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
     * Busca os dados mais recentes do cache para o usuário autenticado.
     * Útil quando não se sabe o resume_url específico.
     */
    public function getLatestData(Request $request)
    {
        try {
            $user = Auth::user();
            
            Log::debug('getLatestData chamado', [
                'user_authenticated' => $user !== null,
                'user_id' => $user?->id,
                'has_session' => Auth::check(),
                'session_id' => $request->session()->getId(),
                'expects_json' => $request->expectsJson(),
                'is_api_route' => $request->is('api/*'),
                'headers' => [
                    'accept' => $request->header('Accept'),
                    'x-requested-with' => $request->header('X-Requested-With'),
                    'authorization' => $request->hasHeader('Authorization') ? 'presente' : 'ausente',
                ],
                'has_cookies' => !empty($request->cookies->all()),
            ]);
            
            if (!$user) {
                Log::warning('getLatestData: usuário não autenticado', [
                    'session_id' => $request->session()->getId(),
                    'expects_json' => $request->expectsJson(),
                    'is_api_route' => $request->is('api/*'),
                    'has_cookies' => !empty($request->cookies->all()),
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

            // Buscar lista de resume_urls recentes
            $recentListKey = "raf_recent_list:{$user->id}";
            $recentList = Cache::get($recentListKey, []);
            
            // #region agent log
            try {
                $debugLogPath = '/opt/hub_contabil/.cursor/debug.log';
                $debugLogDir = dirname($debugLogPath);
                if (!is_dir($debugLogDir)) {
                    @mkdir($debugLogDir, 0755, true);
                }
                if (is_dir($debugLogDir) && is_writable($debugLogDir)) {
                    file_put_contents($debugLogPath, json_encode([
                        'sessionId' => 'debug-session',
                        'runId' => 'run1',
                        'hypothesisId' => 'B',
                        'location' => 'DataReceiverController.php:644',
                        'message' => 'Buscando lista de recentes - getLatestData',
                        'data' => [
                            'user_id' => $user->id,
                            'user_id_type' => gettype($user->id),
                            'user_id_string' => (string)$user->id,
                            'recent_list_key' => $recentListKey,
                            'cache_driver' => config('cache.default'),
                            'cache_store' => config('cache.stores.' . config('cache.default') . '.driver') ?? 'unknown',
                            'app_env' => config('app.env'),
                            'recent_list_count' => count($recentList),
                            'recent_list_keys' => array_map(fn($item) => $item['resume_url'] ?? null, $recentList),
                        ],
                        'timestamp' => time() * 1000
                    ]) . "\n", FILE_APPEND);
                }
            } catch (\Throwable $e) {
                // Ignorar erros de log de debug - não são críticos
            }
            // #endregion
            
            Log::debug('Lista de recentes recuperada', [
                'user_id' => $user->id,
                'recent_list_count' => count($recentList),
                'recent_list' => $recentList,
            ]);
            
            if (empty($recentList)) {
                Log::info('getLatestData: lista de recentes vazia, tentando buscar na lista global', [
                    'user_id' => $user->id,
                ]);
                
                // Fallback: buscar na lista global de resume_urls (cache público)
                // Isso resolve o problema de cache não compartilhado entre ambientes
                $globalRecentListKey = "raf_recent_list_global";
                $globalRecentList = Cache::get($globalRecentListKey, []);
                
                if (!empty($globalRecentList)) {
                    Log::debug('getLatestData: lista global encontrada', [
                        'user_id' => $user->id,
                        'global_list_count' => count($globalRecentList),
                    ]);
                    
                    // Buscar o resume_url mais recente do usuário na lista global
                    $userRecentItems = array_filter($globalRecentList, function($item) use ($user) {
                        return isset($item['user_id']) && (int)$item['user_id'] === $user->id;
                    });
                    
                    if (!empty($userRecentItems)) {
                        // Pegar o mais recente (último da lista filtrada)
                        $latest = end($userRecentItems);
                        $resumeUrlHash = $latest['resume_url_hash'] ?? md5($latest['resume_url'] ?? '');
                        
                        // Tentar buscar no cache público
                        $publicCacheKey = "raf_confirmation_public:{$resumeUrlHash}";
                        $cachedData = Cache::get($publicCacheKey);
                        
                        if ($cachedData && isset($cachedData['resume_url'])) {
                            Log::info('getLatestData: dados encontrados via lista global', [
                                'user_id' => $user->id,
                                'resume_url' => $cachedData['resume_url'],
                            ]);
                            
                            // Continuar com o fluxo normal abaixo para retornar os dados
                        } else {
                            Log::warning('getLatestData: resume_url encontrado na lista global mas dados não encontrados no cache', [
                                'user_id' => $user->id,
                                'resume_url_hash' => $resumeUrlHash,
                            ]);
                        }
                    } else {
                        Log::debug('getLatestData: nenhum item do usuário encontrado na lista global', [
                            'user_id' => $user->id,
                            'global_list_count' => count($globalRecentList),
                        ]);
                    }
                } else {
                    Log::debug('getLatestData: lista global também está vazia, tentando buscar diretamente na tabela de cache', [
                        'user_id' => $user->id,
                        'cache_driver' => config('cache.default'),
                    ]);
                    
                    // Estratégia alternativa: buscar diretamente na tabela de cache do banco de dados
                    // Isso funciona mesmo quando há problema de cache compartilhado entre ambientes
                    if (config('cache.default') === 'database') {
                        try {
                            $cachePrefix = config('cache.prefix');
                            $cacheTable = config('cache.stores.database.table', 'cache');
                            
                            // Buscar caches públicos mais recentes do usuário diretamente no banco
                            $publicCaches = \DB::table($cacheTable)
                                ->where('key', 'like', $cachePrefix . 'raf_confirmation_public:%')
                                ->orderBy('expiration', 'desc')
                                ->limit(10)
                                ->get();
                            
                            foreach ($publicCaches as $cacheRow) {
                                // Remover o prefixo da chave
                                $keyWithoutPrefix = str_replace($cachePrefix, '', $cacheRow->key);
                                
                                // Tentar buscar o cache usando a chave completa
                                $publicCachedData = Cache::get($keyWithoutPrefix);
                                
                                if ($publicCachedData && isset($publicCachedData['resume_url']) && isset($publicCachedData['user_id']) && (int)$publicCachedData['user_id'] === $user->id) {
                                    $cachedData = $publicCachedData;
                                    // Extrair o hash da chave
                                    $resumeUrlHash = str_replace('raf_confirmation_public:', '', $keyWithoutPrefix);
                                    Log::info('getLatestData: dados encontrados via busca direta na tabela de cache', [
                                        'user_id' => $user->id,
                                        'resume_url' => $cachedData['resume_url'],
                                        'hash' => $resumeUrlHash,
                                    ]);
                                    break;
                                }
                            }
                        } catch (\Exception $e) {
                            Log::warning('getLatestData: erro ao buscar diretamente na tabela de cache', [
                                'user_id' => $user->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                }
                
                // Se ainda não encontrou dados, retornar 404
                if (empty($cachedData) || !isset($cachedData['resume_url'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Nenhum dado recente encontrado. Aguarde alguns segundos e tente novamente.',
                    ], Response::HTTP_NOT_FOUND);
                } else {
                    // Dados encontrados via lista global ou busca direta - garantir que temos o resume_url_hash
                    if (!isset($resumeUrlHash)) {
                        $resumeUrlHash = md5($cachedData['resume_url']);
                    }
                }
            }
            
            // Se ainda não temos cachedData (pode ter vindo da lista global), buscar usando a lista de recentes
            if (empty($cachedData) && !empty($recentList)) {
                // Pegar o mais recente (último da lista)
                $latest = end($recentList);
                $resumeUrlHash = $latest['resume_url_hash'] ?? md5($latest['resume_url'] ?? '');
                
                // Buscar dados do cache usando o hash
                $cacheKey = "raf_confirmation:{$user->id}:{$resumeUrlHash}";
                $cachedData = Cache::get($cacheKey);
                
                if (!$cachedData) {
                    // Tentar chave pública também
                    $publicCacheKey = "raf_confirmation_public:{$resumeUrlHash}";
                    $cachedData = Cache::get($publicCacheKey);
                }
            } elseif (empty($cachedData)) {
                // Se não temos dados nem lista, não há o que fazer
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum dado recente encontrado.',
                ], Response::HTTP_NOT_FOUND);
            }
            
            // Se chegou aqui e tem cachedData, garantir que temos o resume_url_hash para buscar CSV
            if (!isset($resumeUrlHash) && isset($cachedData['resume_url'])) {
                $resumeUrlHash = md5($cachedData['resume_url']);
            }
            
            if (!$cachedData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados não encontrados no cache.',
                ], Response::HTTP_NOT_FOUND);
            }
            
            // Buscar CSV se disponível
            $csvCacheKey = "raf_csv:{$resumeUrlHash}";
            $csvCacheData = Cache::get($csvCacheKey);
            
            if ($csvCacheData) {
                $cachedData['csv'] = $csvCacheData['csv'];
                $cachedData['csv_filename'] = $csvCacheData['filename'] ?? 'resultado.csv';
                $cachedData['has_csv'] = true;
            } else {
                $cachedData['has_csv'] = false;
            }
            
            Log::debug('Dados RAF mais recentes recuperados do cache', [
                'user_id' => $user->id,
                'resume_url' => $latest['resume_url'] ?? null,
                'has_csv' => $cachedData['has_csv'] ?? false,
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $cachedData,
                'resume_url' => $latest['resume_url'] ?? null,
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados recentes do cache', [
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
}

