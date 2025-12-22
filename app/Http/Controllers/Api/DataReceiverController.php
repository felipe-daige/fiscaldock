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
            $receivedData = $request->all();
            
            Log::info('Dados recebidos com sucesso', [
                'user_id' => $user?->id ?? null,
                'data_keys' => is_array($receivedData) ? array_keys($receivedData) : 'não é array',
                'data_count' => is_array($receivedData) ? count($receivedData) : 1,
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
                $userId = $user?->id ?? $receivedData['user_id'] ?? null;
                
                Log::debug('Condições atendidas, preparando para armazenar', [
                    'user_id' => $userId,
                    'resume_url' => $receivedData['resume_url'],
                    'valor_total_consulta' => $receivedData['valor_total_consulta'],
                ]);
                
                if (!empty($receivedData['resume_url'])) {
                    // Normalizar resume_url para usar como parte da chave
                    $resumeUrlHash = md5($receivedData['resume_url']);
                    
                    // Preparar dados para armazenar
                    $cacheData = [
                        'user_id' => $userId ? (int) $userId : null,
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
                    
                    Log::info('Dados RAF armazenados em cache (público)', [
                        'resume_url' => $receivedData['resume_url'],
                        'cache_key' => $publicCacheKey,
                        'cache_data' => $cacheData,
                    ]);
                    
                    // Armazenar resume_url em uma lista de dados recentes do usuário
                    // Isso permite que o frontend busque dados recentes sem saber o resume_url específico
                    if ($userId) {
                        $recentListKey = "raf_recent_list:{$userId}";
                        $recentList = Cache::get($recentListKey, []);
                        
                        Log::debug('Lista de recentes antes de adicionar', [
                            'user_id' => $userId,
                            'current_count' => count($recentList),
                            'recent_list' => $recentList,
                        ]);
                        
                        // Adicionar resume_url à lista (máximo 10 itens)
                        $recentList[] = [
                            'resume_url' => $receivedData['resume_url'],
                            'resume_url_hash' => $resumeUrlHash,
                            'timestamp' => now()->toIso8601String(),
                        ];
                        
                        // Manter apenas os 10 mais recentes
                        $recentList = array_slice($recentList, -10);
                        
                        Cache::put($recentListKey, $recentList, 3600);
                        
                        Log::info('Lista de dados recentes atualizada', [
                            'user_id' => $userId,
                            'recent_list_count' => count($recentList),
                            'latest_resume_url' => $recentList[count($recentList) - 1]['resume_url'] ?? null,
                            'recent_list' => $recentList,
                        ]);
                        
                        // Verificar se foi armazenado corretamente
                        $verifyList = Cache::get($recentListKey, []);
                        Log::debug('Verificação pós-armazenamento da lista', [
                            'user_id' => $userId,
                            'stored_count' => count($verifyList),
                            'matches' => count($verifyList) === count($recentList),
                        ]);
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
                'user_id' => $user?->id ?? null,
                'data' => $receivedData,
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
            
            if (!$user) {
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
            ]);
            
            if (!$user) {
                Log::warning('getLatestData: usuário não autenticado');
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado.',
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Buscar lista de resume_urls recentes
            $recentListKey = "raf_recent_list:{$user->id}";
            $recentList = Cache::get($recentListKey, []);
            
            Log::debug('Lista de recentes recuperada', [
                'user_id' => $user->id,
                'recent_list_count' => count($recentList),
                'recent_list' => $recentList,
            ]);
            
            if (empty($recentList)) {
                Log::info('getLatestData: nenhum dado recente encontrado', [
                    'user_id' => $user->id,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum dado recente encontrado.',
                ], Response::HTTP_NOT_FOUND);
            }
            
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
            
            if (!$cachedData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados não encontrados no cache.',
                ], Response::HTTP_NOT_FOUND);
            }
            
            Log::debug('Dados RAF mais recentes recuperados do cache', [
                'user_id' => $user->id,
                'resume_url' => $latest['resume_url'] ?? null,
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
}

