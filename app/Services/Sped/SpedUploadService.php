<?php

namespace App\Services\Sped;

use App\Models\RafConsultaPendente;
use App\Services\CsvParserService;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SpedUploadService
{
    public function __construct(
        protected CsvParserService $csvParser
    ) {}

    /**
     * Faz upload de arquivo SPED para o webhook e retorna dados parseados.
     *
     * @param UploadedFile $file Arquivo SPED
     * @param string $tipo Tipo do SPED (EFD Contribuições ou EFD Fiscal)
     * @param string|null $originalName Nome original do arquivo (opcional)
     * @param bool $isAuthenticated Se é requisição autenticada (afeta timeout e logs)
     * @param string $modalidade Modalidade da consulta: 'gratuito' ou 'completa'
     * @param int|null $userId ID do usuário (opcional, para requisições autenticadas)
     * @return array{success: bool, headers?: array, rows?: array, csv?: string, filename?: string, message?: string, errors?: array}
     */
    public function uploadAndProcess(
        UploadedFile $file,
        string $tipo,
        ?string $originalName = null,
        bool $isAuthenticated = false,
        string $modalidade = 'gratuito',
        ?int $userId = null
    ): array {
        $fileName = match ($tipo) {
            'EFD Contribuições' => 'sped_contribuicoes.txt',
            'EFD Fiscal' => 'sped_fiscal.txt',
            default => 'sped.txt',
        };

        // Seleciona a URL do webhook baseado no tipo de SPED e modalidade
        $webhookUrl = $this->getWebhookUrl($tipo, $modalidade);
        
        Log::debug('Webhook URL selecionada', [
            'tipo' => $tipo,
            'modalidade' => $modalidade,
            'webhook_url' => $webhookUrl,
            'config_sped_fiscal_url' => config('services.webhook.sped_fiscal_url'),
            'config_sped_fiscal_completa_url' => config('services.webhook.sped_fiscal_completa_url'),
        ]);
        
        $webhookUser = config('services.webhook.username');
        $webhookPass = config('services.webhook.password');

        if (empty($webhookUrl)) {
            return [
                'success' => false,
                'message' => 'Webhook não configurado.',
            ];
        }

        // Timeout de 1 hora (3600 segundos) para processamento que pode demorar até 1 hora
        $timeout = 3600;
        $http = Http::timeout($timeout);

        if (!empty($webhookUser) && !empty($webhookPass)) {
            $http = $http->withBasicAuth($webhookUser, $webhookPass);
        }

        try {
            // Preparar payload com tipo e user_id (se fornecido)
            $payload = ['tipo' => $tipo];
            if ($userId !== null) {
                $payload['user_id'] = $userId;
            }
            
            $response = $http->attach('sped', file_get_contents($file->getRealPath()), $fileName)
                ->post($webhookUrl, $payload);
        } catch (\Throwable $e) {
            $logContext = [
                'exception' => $e->getMessage(),
                'exception_class' => get_class($e),
                'tipo' => $tipo,
                'original_name' => $originalName,
            ];

            if ($isAuthenticated) {
                Log::error('Falha ao contatar webhook SPED (auth)', $logContext);
            }

            // Verifica se é um erro de timeout
            $isTimeout = str_contains($e->getMessage(), 'timeout') 
                || str_contains($e->getMessage(), 'Connection timed out')
                || str_contains($e->getMessage(), 'timed out');

            $message = $isTimeout
                ? 'O processamento está demorando mais que o esperado. O SPED pode estar sendo processado em segundo plano. Aguarde alguns minutos e verifique novamente.'
                : 'Falha ao contatar o webhook. Tente novamente em instantes.';

            return [
                'success' => false,
                'message' => $message,
            ];
        }

        $body = $response->body();
        $contentType = $response->header('Content-Type') ?? '';
        $isJsonContentType = str_contains($contentType, 'application/json') || str_contains($contentType, 'text/json');

        // Verifica se a resposta é JSON com resume_url (fluxo de confirmação de créditos)
        // Primeiro tenta detectar pelo Content-Type, depois pelo conteúdo
        $decoded = null;
        $isJson = false;
        
        if ($isJsonContentType) {
            // Se o Content-Type indica JSON, tenta decodificar
            $decoded = json_decode($body, true);
            $isJson = json_last_error() === JSON_ERROR_NONE && is_array($decoded);
        } else {
            // Se não é JSON pelo Content-Type, ainda tenta decodificar (pode ser JSON sem header correto)
            $decoded = json_decode($body, true);
            $isJson = json_last_error() === JSON_ERROR_NONE && is_array($decoded);
            
            // Se decodificou como JSON mas o Content-Type não indica JSON, verifica se é realmente JSON
            // (pode ser CSV que começa com { ou [)
            if ($isJson) {
                // Verifica se o body parece ser realmente JSON (começa com { ou [)
                $trimmedBody = trim($body);
                $isJson = ($trimmedBody[0] ?? '') === '{' || ($trimmedBody[0] ?? '') === '[';
            }
        }

        if ($isJson && is_array($decoded)) {
            // O webhook pode retornar um array com um objeto, então pega o primeiro elemento
            $data = isset($decoded[0]) && is_array($decoded[0]) ? $decoded[0] : $decoded;
            
            Log::debug('Webhook SPED resposta JSON detectada', [
                'tipo' => $tipo,
                'content_type' => $contentType,
                'has_resume_url' => isset($data['resume_url']),
                'has_valor_total' => isset($data['valor_total_consulta']),
                'data_keys' => array_keys($data),
            ]);
            
            if (isset($data['resume_url']) && isset($data['valor_total_consulta'])) {
                // Buscar o registro no banco de dados para obter o relatorio_id
                $relatorioId = null;
                if ($userId && isset($data['resume_url'])) {
                    $relatorio = RafConsultaPendente::where('resume_url', $data['resume_url'])
                        ->where('user_id', $userId)
                        ->first();
                    if ($relatorio) {
                        $relatorioId = $relatorio->id;
                    }
                }
                
                Log::info('Webhook SPED aguardando confirmação de créditos', [
                    'tipo' => $tipo,
                    'original_name' => $originalName,
                    'valor_total_consulta' => $data['valor_total_consulta'],
                    'qtd_participantes_unicos' => $data['qtd_participantes_unicos'] ?? null,
                    'custo_unitario' => $data['custo_unitario'] ?? null,
                    'resume_url' => $data['resume_url'],
                    'relatorio_id' => $relatorioId,
                ]);
                
                $result = [
                    'success' => true,
                    'needs_confirmation' => true,
                    'resume_url' => $data['resume_url'],
                    'valor_total_consulta' => (float) $data['valor_total_consulta'],
                    'qtd_participantes_unicos' => (int) ($data['qtd_participantes_unicos'] ?? 0),
                    'custo_unitario' => (float) ($data['custo_unitario'] ?? 0),
                    'message' => 'Confirmação de créditos necessária.',
                ];
                
                // Adicionar relatorio_id se encontrado
                if ($relatorioId) {
                    $result['relatorio_id'] = $relatorioId;
                }
                
                return $result;
            } else {
                // JSON válido com apenas "message" = resposta assíncrona (ex: Workflow was started)
                // Isso acontece na modalidade gratuita quando o n8n inicia o workflow mas não retorna resume_url
                if (isset($data['message']) && !isset($data['resume_url'])) {
                    // Buscar o registro mais recente do usuário para obter o relatorio_id
                    // Na modalidade gratuita, o n8n pode criar o registro depois, então tentamos buscar
                    // o registro mais recente criado nos últimos minutos
                    $relatorioId = null;
                    if ($userId) {
                        // Buscar o relatório mais recente do usuário criado nos últimos 5 minutos
                        // Isso cobre o caso onde o n8n já criou o registro
                        $relatorio = RafConsultaPendente::where('user_id', $userId)
                            ->where('created_at', '>=', now()->subMinutes(5))
                            ->orderBy('created_at', 'desc')
                            ->first();
                        
                        if ($relatorio) {
                            $relatorioId = $relatorio->id;
                        }
                    }
                    
                    Log::info('Webhook SPED retornou resposta assíncrona (sem resume_url)', [
                        'tipo' => $tipo,
                        'modalidade' => $modalidade,
                        'message' => $data['message'],
                        'relatorio_id' => $relatorioId,
                        'user_id' => $userId,
                    ]);
                    
                    $result = [
                        'success' => true,
                        'async' => true,
                        'message' => $data['message'] ?? 'Processamento iniciado. Aguarde o relatório.',
                    ];
                    
                    // Adicionar relatorio_id se encontrado
                    if ($relatorioId) {
                        $result['relatorio_id'] = $relatorioId;
                    }
                    
                    return $result;
                }
                
                // JSON válido mas sem resume_url - pode ser resposta de erro ou outro formato
                Log::debug('Webhook SPED retornou JSON mas sem resume_url/valor_total_consulta', [
                    'tipo' => $tipo,
                    'data_keys' => array_keys($data),
                ]);
            }
        }

        $csv = $body;
        
        // Log quando o CSV está vazio (pode indicar que deveria ser JSON)
        if (trim($csv) === '' && $response->successful()) {
            Log::warning('Webhook SPED retornou resposta vazia', [
                'tipo' => $tipo,
                'original_name' => $originalName,
                'status' => $response->status(),
                'content_type' => $contentType,
                'is_authenticated' => $isAuthenticated,
                'user_id' => $userId,
            ]);
            
            // Se a resposta está vazia mas o status é 200, pode ser que o webhook n8n
            // já tenha enviado os dados via /api/data/receive. Verificar cache do usuário.
            if ($isAuthenticated && $userId) {
                $cachedData = null;
                $recentListKey = "raf_recent_list:{$userId}";
                $recentList = Cache::get($recentListKey, []);
                
                Log::debug('Webhook SPED resposta vazia - buscando no cache', [
                    'tipo' => $tipo,
                    'user_id' => $userId,
                    'recent_list_count' => count($recentList),
                ]);
                
                // 1. Buscar primeiro no cache privado usando a lista de recentes
                if (!empty($recentList)) {
                    // Pegar o mais recente (último da lista)
                    $latest = end($recentList);
                    $resumeUrlHash = $latest['resume_url_hash'] ?? md5($latest['resume_url'] ?? '');
                    $cacheKey = "raf_confirmation:{$userId}:{$resumeUrlHash}";
                    $cachedData = Cache::get($cacheKey);
                    
                    if ($cachedData && isset($cachedData['resume_url'])) {
                        Log::info('Webhook SPED resposta vazia - dados encontrados no cache privado', [
                            'tipo' => $tipo,
                            'original_name' => $originalName,
                            'resume_url' => $cachedData['resume_url'],
                            'valor_total_consulta' => $cachedData['valor_total_consulta'] ?? null,
                        ]);
                    }
                }
                
                // 2. Se não encontrou no cache privado, tentar cache público usando a lista de recentes
                if ((!$cachedData || !isset($cachedData['resume_url'])) && !empty($recentList)) {
                    Log::debug('Webhook SPED resposta vazia - tentando buscar no cache público via lista de recentes', [
                        'tipo' => $tipo,
                        'user_id' => $userId,
                        'recent_list_count' => count($recentList),
                    ]);
                    
                    // Tentar cada resume_url da lista (do mais recente para o mais antigo)
                    foreach (array_reverse($recentList) as $item) {
                        $resumeUrlHash = $item['resume_url_hash'] ?? md5($item['resume_url'] ?? '');
                        $publicCacheKey = "raf_confirmation_public:{$resumeUrlHash}";
                        $publicCachedData = Cache::get($publicCacheKey);
                        
                        if ($publicCachedData && isset($publicCachedData['resume_url'])) {
                            $cachedData = $publicCachedData;
                            Log::info('Webhook SPED resposta vazia - dados encontrados no cache público via lista', [
                                'tipo' => $tipo,
                                'original_name' => $originalName,
                                'resume_url' => $cachedData['resume_url'],
                                'valor_total_consulta' => $cachedData['valor_total_consulta'] ?? null,
                            ]);
                            break;
                        }
                    }
                }
                
                // 3. Se ainda não encontrou e a lista está vazia, aguardar um pouco e tentar novamente
                // Isso pode acontecer se os dados ainda não chegaram via webhook
                // OU se há um problema de cache compartilhado entre ambientes
                if ((!$cachedData || !isset($cachedData['resume_url'])) && empty($recentList)) {
                    Log::debug('Webhook SPED resposta vazia - lista de recentes vazia, tentando estratégias alternativas', [
                        'tipo' => $tipo,
                        'user_id' => $userId,
                        'app_env' => config('app.env'),
                        'cache_driver' => config('cache.default'),
                    ]);
                    
                    // Estratégia 1: Aguardar 1 segundo e tentar buscar novamente (dados podem estar chegando)
                    sleep(1);
                    $recentList = Cache::get($recentListKey, []);
                    
                    if (!empty($recentList)) {
                        $latest = end($recentList);
                        $resumeUrlHash = $latest['resume_url_hash'] ?? md5($latest['resume_url'] ?? '');
                        
                        // Tentar cache privado
                        $cacheKey = "raf_confirmation:{$userId}:{$resumeUrlHash}";
                        $cachedData = Cache::get($cacheKey);
                        
                        // Se não encontrou, tentar cache público
                        if (!$cachedData || !isset($cachedData['resume_url'])) {
                            $publicCacheKey = "raf_confirmation_public:{$resumeUrlHash}";
                            $cachedData = Cache::get($publicCacheKey);
                        }
                        
                        if ($cachedData && isset($cachedData['resume_url'])) {
                            Log::info('Webhook SPED resposta vazia - dados encontrados após aguardar', [
                                'tipo' => $tipo,
                                'original_name' => $originalName,
                                'resume_url' => $cachedData['resume_url'],
                                'valor_total_consulta' => $cachedData['valor_total_consulta'] ?? null,
                            ]);
                        }
                    } else {
                        // Estratégia 2: Se a lista ainda está vazia, pode ser problema de cache compartilhado
                        // Tentar buscar diretamente no cache público usando uma busca mais ampla
                        // Como não temos o resume_url, não podemos buscar diretamente
                        // Mas podemos logar isso para debug
                        Log::warning('Webhook SPED resposta vazia - lista de recentes continua vazia após aguardar', [
                            'tipo' => $tipo,
                            'user_id' => $userId,
                            'app_env' => config('app.env'),
                            'cache_driver' => config('cache.default'),
                            'possible_issue' => 'Cache não compartilhado entre ambientes ou lista não foi salva corretamente',
                        ]);
                    }
                }
                
                // Retornar dados encontrados
                if ($cachedData && isset($cachedData['resume_url']) && isset($cachedData['valor_total_consulta'])) {
                    Log::info('Webhook SPED resposta vazia - retornando dados do cache', [
                        'tipo' => $tipo,
                        'original_name' => $originalName,
                        'resume_url' => $cachedData['resume_url'],
                        'valor_total_consulta' => $cachedData['valor_total_consulta'],
                    ]);
                    
                    return [
                        'success' => true,
                        'needs_confirmation' => true,
                        'resume_url' => $cachedData['resume_url'],
                        'valor_total_consulta' => (float) ($cachedData['valor_total_consulta'] ?? 0),
                        'qtd_participantes_unicos' => (int) ($cachedData['qtd_participantes_unicos'] ?? $cachedData['qnt_participantes'] ?? 0),
                        'custo_unitario' => (float) ($cachedData['custo_unitario'] ?? 0),
                        'message' => 'Confirmação de créditos necessária.',
                    ];
                }
                
                // Estratégia 3: Se não encontrou no cache, buscar diretamente no banco de dados
                // Isso resolve o problema de cache não compartilhado entre ambientes (local/produção)
                // O banco de dados é compartilhado, então podemos buscar os dados de confirmação pendentes
                if (!$cachedData || !isset($cachedData['resume_url'])) {
                    Log::debug('Webhook SPED resposta vazia - buscando no banco de dados como fallback', [
                        'tipo' => $tipo,
                        'user_id' => $userId,
                        'app_env' => config('app.env'),
                    ]);
                    
                    try {
                        // Buscar a consulta mais recente do usuário que tenha resume_url e n8n_received_at
                        // n8n_received_at indica que o n8n já enviou os dados e está aguardando confirmação
                        $pendingQuery = RafConsultaPendente::where('user_id', $userId)
                            ->whereNotNull('resume_url')
                            ->whereNotNull('n8n_received_at')
                            ->whereNotNull('valor_total_consulta')
                            ->orderBy('created_at', 'desc')
                            ->first();
                        
                        if ($pendingQuery && $pendingQuery->resume_url) {
                            Log::info('Webhook SPED resposta vazia - dados encontrados no banco de dados', [
                                'tipo' => $tipo,
                                'original_name' => $originalName,
                                'registro_id' => $pendingQuery->id,
                                'resume_url' => $pendingQuery->resume_url,
                                'valor_total_consulta' => $pendingQuery->valor_total_consulta,
                                'qtd_participantes' => $pendingQuery->qtd_participantes,
                            ]);
                            
                            return [
                                'success' => true,
                                'needs_confirmation' => true,
                                'resume_url' => $pendingQuery->resume_url,
                                'valor_total_consulta' => (float) $pendingQuery->valor_total_consulta,
                                'qtd_participantes_unicos' => (int) $pendingQuery->qtd_participantes,
                                'custo_unitario' => (float) $pendingQuery->custo_unitario,
                                'relatorio_id' => $pendingQuery->id,
                                'message' => 'Confirmação de créditos necessária.',
                            ];
                        } else {
                            Log::debug('Webhook SPED resposta vazia - nenhuma consulta pendente encontrada no banco de dados', [
                                'tipo' => $tipo,
                                'user_id' => $userId,
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Erro ao buscar dados no banco de dados (fallback)', [
                            'tipo' => $tipo,
                            'user_id' => $userId,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                }
            }
        }

        if (!$response->successful()) {
            $detail = '';
            $decoded = json_decode($csv, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $detail = $decoded['message'] ?? $decoded['error'] ?? '';
            } else {
                $detail = trim($csv);
            }

            $detail = $detail ? ' Detalhe: ' . mb_substr($detail, 0, 500) : '';

            $logContext = [
                'status' => $response->status(),
                'detail' => $detail,
                'tipo' => $tipo,
                'original_name' => $originalName,
            ];

            if ($isAuthenticated) {
                $logContext['content_disposition'] = $response->header('Content-Disposition');
                Log::warning('Webhook SPED falhou (auth)', $logContext);
            } else {
                Log::warning('Webhook SPED falhou', $logContext);
            }

            return [
                'success' => false,
                'message' => 'Webhook retornou erro (' . $response->status() . ').' . $detail,
            ];
        }

        $parsed = $this->csvParser->parse($csv);

        // Determinar nome do arquivo final
        $filenameFromHeader = $this->extractFilenameFromResponse($response);
        $finalFilename = $this->normalizeCsvFilename(
            $filenameFromHeader
                ?? $originalName
                ?? ($fileName ? ('resultado_' . $fileName) : 'resultado.csv')
        );

        if ($isAuthenticated) {
            Log::info('Webhook SPED sucesso (auth)', [
                'tipo' => $tipo,
                'original_name' => $originalName,
                'status' => $response->status(),
                'header_name' => $response->header('name'),
                'content_disposition' => $response->header('Content-Disposition'),
                'filename_returned' => $filenameFromHeader,
                'filename_final' => $finalFilename,
            ]);
        }

        return [
            'success' => true,
            'headers' => $parsed['headers'],
            'rows' => $parsed['rows'],
            'csv' => $csv,
            'filename' => $finalFilename,
        ];
    }

    /**
     * Envia apenas o status (approved/denied) para o webhook sem aguardar CSV.
     * Usado quando o usuário confirma o uso de créditos.
     *
     * @param string $resumeUrl URL de callback do n8n
     * @param string $status 'approved' ou 'denied'
     * @return array{success: bool, message?: string}
     */
    public function sendWebhookStatus(string $resumeUrl, string $status): array
    {
        $webhookUser = config('services.webhook.username');
        $webhookPass = config('services.webhook.password');

        // Timeout curto (30s) - não precisa aguardar processamento longo
        $timeout = 30;
        $http = Http::timeout($timeout);

        if (!empty($webhookUser) && !empty($webhookPass)) {
            $http = $http->withBasicAuth($webhookUser, $webhookPass);
        }

        // Garantir que o status está no formato correto
        $webhookStatus = match($status) {
            'approved', 'confirmado', 'confirmed' => 'approved',
            'denied', 'negado', 'declined' => 'denied',
            default => 'denied',
        };

        try {
            $response = $http->post($resumeUrl, [
                'status' => $webhookStatus,
            ]);
        } catch (\Throwable $e) {
            Log::error('Falha ao enviar status para webhook', [
                'resume_url' => $resumeUrl,
                'status' => $webhookStatus,
                'exception' => $e->getMessage(),
            ]);

            $isTimeout = str_contains($e->getMessage(), 'timeout') 
                || str_contains($e->getMessage(), 'timed out');

            return [
                'success' => false,
                'message' => $isTimeout
                    ? 'Timeout ao enviar confirmação. Tente novamente.'
                    : 'Falha ao contatar o servidor. Tente novamente.',
            ];
        }

        if (!$response->successful()) {
            $detail = '';
            $body = $response->body();
            $decoded = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $detail = $decoded['message'] ?? $decoded['error'] ?? '';
            } else {
                $detail = trim($body);
            }

            Log::warning('Webhook status falhou', [
                'resume_url' => $resumeUrl,
                'status_code' => $response->status(),
                'status_sent' => $webhookStatus,
                'detail' => mb_substr($detail, 0, 500),
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao enviar confirmação (' . $response->status() . '). ' . mb_substr($detail, 0, 200),
            ];
        }

        Log::info('Webhook status enviado com sucesso', [
            'resume_url' => $resumeUrl,
            'status' => $webhookStatus,
        ]);

        return [
            'success' => true,
        ];
    }

    /**
     * Envia confirmação ou negação para o webhook de resumo do n8n.
     *
     * @param string $resumeUrl URL de callback do n8n
     * @param string $status 'confirmado' ou 'negado' (aceita também 'confirm'/'decline' para compatibilidade)
     * @return array{success: bool, headers?: array, rows?: array, csv?: string, filename?: string, message?: string}
     */
    public function confirmAndResume(string $resumeUrl, string $status): array
    {
        $webhookUser = config('services.webhook.username');
        $webhookPass = config('services.webhook.password');

        // Timeout de 1 hora para processamento
        $timeout = 3600;
        $http = Http::timeout($timeout);

        if (!empty($webhookUser) && !empty($webhookPass)) {
            $http = $http->withBasicAuth($webhookUser, $webhookPass);
        }

        // Converte status para formato esperado pelo webhook n8n
        // Formato esperado: {"status": "approved"} ou {"status": "declined"}
        $webhookStatus = match($status) {
            'confirmado', 'confirmed', 'approved' => 'approved',
            'negado', 'denied', 'declined' => 'declined',
            'confirm' => 'approved', // Compatibilidade com formato antigo
            'decline' => 'declined', // Compatibilidade com formato antigo
            default => 'declined',
        };

        try {
            $response = $http->post($resumeUrl, [
                'status' => $webhookStatus,
            ]);
        } catch (\Throwable $e) {
            Log::error('Falha ao enviar confirmação para webhook', [
                'resume_url' => $resumeUrl,
                'status' => $status,
                'exception' => $e->getMessage(),
            ]);

            $isTimeout = str_contains($e->getMessage(), 'timeout') 
                || str_contains($e->getMessage(), 'timed out');

            return [
                'success' => false,
                'message' => $isTimeout
                    ? 'O processamento está demorando mais que o esperado. Aguarde alguns minutos.'
                    : 'Falha ao contatar o servidor. Tente novamente.',
            ];
        }

        // Se o status foi negado/declined, não espera CSV de volta
        if ($status === 'negado' || $webhookStatus === 'declined') {
            return [
                'success' => false,
                'message' => 'Operação cancelada pelo usuário.',
            ];
        }

        $csv = $response->body();
        $contentType = $response->header('Content-Type') ?? '';

        if (!$response->successful()) {
            $detail = '';
            $decoded = json_decode($csv, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $detail = $decoded['message'] ?? $decoded['error'] ?? '';
            } else {
                $detail = trim($csv);
            }

            Log::warning('Webhook confirmação falhou', [
                'resume_url' => $resumeUrl,
                'status_code' => $response->status(),
                'detail' => mb_substr($detail, 0, 500),
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao processar (' . $response->status() . '). ' . mb_substr($detail, 0, 200),
            ];
        }

        // Verificar se a resposta é JSON (n8n retorna resposta assíncrona)
        // Exemplo: {"message":"Workflow was started"}
        if (str_contains($contentType, 'application/json')) {
            $decoded = json_decode($csv, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && isset($decoded['message'])) {
                Log::info('Webhook retornou resposta assíncrona, CSV virá via endpoint /api/data/receive/raf/csvfile', [
                    'resume_url' => $resumeUrl,
                    'message' => $decoded['message'],
                ]);
                
                return [
                    'success' => true,
                    'async' => true,
                    'message' => $decoded['message'] ?? 'Processamento iniciado. Aguarde o CSV via notificação.',
                ];
            }
        }

        $parsed = $this->csvParser->parse($csv);

        $filenameFromHeader = $this->extractFilenameFromResponse($response);
        $finalFilename = $this->normalizeCsvFilename($filenameFromHeader ?? 'resultado.csv');

        Log::info('Webhook confirmação sucesso', [
            'resume_url' => $resumeUrl,
            'filename' => $finalFilename,
        ]);

        return [
            'success' => true,
            'headers' => $parsed['headers'],
            'rows' => $parsed['rows'],
            'csv' => $csv,
            'filename' => $finalFilename,
        ];
    }

    /**
     * Extrai nome de arquivo da resposta do webhook.
     * Prioridade: header 'name' (API EFD), depois Content-Disposition (RFC 6266).
     */
    private function extractFilenameFromResponse(ClientResponse $response): ?string
    {
        // Prioridade: header 'name' da API EFD Contribuicoes
        $nameHeader = $response->header('name');
        if ($nameHeader && trim($nameHeader) !== '') {
            return trim($nameHeader);
        }

        // Fallback: Content-Disposition (RFC 6266)
        $disposition = $response->header('Content-Disposition');
        if (!$disposition) {
            return null;
        }

        // filename*=UTF-8''nome.csv
        if (preg_match("/filename\\*=UTF-8''([^;]+)/i", $disposition, $matches)) {
            $raw = trim($matches[1], " \t\n\r\0\x0B\"'");
            return urldecode($raw);
        }

        // filename="nome.csv" ou filename=nome.csv
        if (preg_match('/filename=\"?([^\";]+)\"?/i', $disposition, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Garante extensão .csv no nome final.
     */
    private function normalizeCsvFilename(string $name): string
    {
        $trimmed = trim($name);
        if ($trimmed === '') {
            return 'resultado.csv';
        }

        // Remove query/fragment se vier junto
        $base = preg_split('/[?#]/', $trimmed, 2)[0];

        // Se já termina com .csv (case-insensitive), mantém
        if (preg_match('/\\.csv$/i', $base)) {
            return $base;
        }

        // Caso contrário, troca extensão existente ou adiciona .csv
        $withoutExt = preg_replace('/\\.[^.]+$/', '', $base);
        return ($withoutExt ?: 'resultado') . '.csv';
    }

    /**
     * Retorna a URL do webhook baseado no tipo de SPED e modalidade.
     *
     * @throws \InvalidArgumentException Se a modalidade for inválida
     */
    private function getWebhookUrl(string $tipo, string $modalidade): string
    {
        // Validação de segurança - apenas modalidades permitidas
        if (!in_array($modalidade, ['gratuito', 'completa'], true)) {
            throw new \InvalidArgumentException('Modalidade inválida: ' . $modalidade);
        }

        // RAF Completo: CND + Regime Tributário (modalidade paga)
        if ($modalidade === 'completa') {
            return match ($tipo) {
                'EFD Fiscal' => config('services.webhook.sped_fiscal_completa_url')
                    ?: 'https://autowebhook.fiscaldock.com.br/webhook/raf-consulta-completa-sped-fiscal',
                'EFD Contribuições' => config('services.webhook.sped_contribuicoes_completa_url')
                    ?: 'https://autowebhook.fiscaldock.com.br/webhook/raf-consulta-completa-sped-contribuicoes',
                default => config('services.webhook.sped_contribuicoes_completa_url')
                    ?: 'https://autowebhook.fiscaldock.com.br/webhook/raf-consulta-completa-sped-contribuicoes',
            };
        }

        // RAF Gratuito: apenas Regime Tributário (modalidade gratuita)
        $url = match ($tipo) {
            'EFD Fiscal' => config('services.webhook.sped_fiscal_url')
                ?: 'https://autowebhook.fiscaldock.com.br/webhook/raf-consulta-gratuita-sped-fiscal',
            'EFD Contribuições' => config('services.webhook.sped_contribuicoes_url')
                ?: 'https://autowebhook.fiscaldock.com.br/webhook/raf-consulta-gratuita-sped-contribuicoes',
            default => config('services.webhook.sped_contribuicoes_url')
                ?: 'https://autowebhook.fiscaldock.com.br/webhook/raf-consulta-gratuita-sped-contribuicoes',
        };
        
        Log::debug('getWebhookUrl - RAF Gratuito', [
            'tipo' => $tipo,
            'modalidade' => $modalidade,
            'config_value' => $tipo === 'EFD Fiscal' ? config('services.webhook.sped_fiscal_url') : config('services.webhook.sped_contribuicoes_url'),
            'url_retornada' => $url,
        ]);
        
        return $url;
    }
}

