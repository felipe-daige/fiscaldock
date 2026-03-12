<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\SpedImportacao;
use App\Models\XmlImportacao;
use App\Models\MonitoramentoConsulta;
use App\Models\Participante;
use App\Models\User;
use App\Services\CreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DataReceiverController extends Controller
{
    public function __construct(
        protected CreditService $creditService
    ) {}

    /**
     * Health check endpoint - verifica estado do token sem autenticação.
     *
     * GET /api/health
     */
    public function health()
    {
        $token = config('services.api.token');
        $sanitized = $token ? trim(trim($token), '"\'') : '';

        return response()->json([
            'status' => 'ok',
            'api_token_configured' => ! empty($sanitized),
            'token_prefix' => $sanitized ? substr($sanitized, 0, 8) . '...' : '(vazio)',
            'token_length' => strlen($sanitized),
            'raw_length' => strlen($token ?? ''),
            'had_quotes_or_whitespace' => strlen($token ?? '') !== strlen($sanitized),
            'php_version' => PHP_VERSION,
            'laravel_env' => config('app.env'),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Verifica se o token API é válido.
     * Retorna array com 'valid' e 'debug' para diagnostico.
     */
    private function validateToken(Request $request): array
    {
        $apiToken = $request->header('X-API-Token') ?? $request->input('api_token');
        $expectedToken = config('services.api.token');

        // Sanitizar: remover whitespace e aspas literais
        $apiToken = $apiToken ? trim(trim($apiToken), '"\'') : '';
        $expectedToken = $expectedToken ? trim(trim($expectedToken), '"\'') : '';

        $isValid = ! empty($apiToken) && ! empty($expectedToken) && hash_equals($expectedToken, $apiToken);

        $debug = [
            'received_prefix' => $apiToken ? substr($apiToken, 0, 8) . '...' : '(vazio)',
            'expected_prefix' => $expectedToken ? substr($expectedToken, 0, 8) . '...' : '(vazio)',
            'received_length' => strlen($apiToken),
            'expected_length' => strlen($expectedToken),
        ];

        if (! $isValid) {
            if (empty($expectedToken)) {
                $debug['hint'] = "config('services.api.token') esta vazio — verifique API_TOKEN no .env e re-execute config:cache";
            } elseif (empty($apiToken)) {
                $debug['hint'] = 'Header X-API-Token nao enviado ou vazio';
            } else {
                $debug['hint'] = 'Token recebido difere do esperado — compare os prefixos acima';
            }

            Log::warning('Token validation failed', $debug);
        }

        return ['valid' => $isValid, 'debug' => $debug];
    }

    /**
     * Verifica se o token API é válido (wrapper booleano para compatibilidade).
     */
    private function isTokenValid(Request $request): bool
    {
        return $this->validateToken($request)['valid'];
    }

    /**
     * Retorna resposta 401 com diagnostico de token.
     */
    private function unauthorizedResponse(Request $request): \Illuminate\Http\JsonResponse
    {
        $validation = $this->validateToken($request);

        return response()->json([
            'success' => false,
            'message' => 'Token de API inválido.',
            'debug' => $validation['debug'],
        ], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Recebe progresso da importação/processamento de arquivo SPED (enviado pelo n8n).
     * Armazena em cache para o SSE ler e enviar ao frontend.
     * NÃO edita banco de dados - apenas cache.
     *
     * POST /api/importacao/sped/importacao-txt/progress
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

            // Verifica autenticação via token
            if (! $this->isTokenValid($request)) {
                Log::warning('Token inválido em receiveImportacaoTxtProgress');

                return $this->unauthorizedResponse($request);
            }

            // Detectar formato do payload (novo vs legado)
            // Novo formato: user_id + tab_id (progresso pode estar ausente em erros, default 0)
            $hasNewFormat = $request->has('user_id') && $request->has('tab_id');
            $hasLegacyFormat = $request->has('importacao_id') && ! $hasNewFormat;

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
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'tab_id' => 'required|string|max:36',
            'progresso' => 'nullable|integer|min:0|max:100',
            'mensagem' => 'nullable|string|max:255',
            'status' => 'required|in:iniciando,processando,concluido,erro',
            // Campos opcionais para erros
            'error_code' => 'nullable|string|max:50',
            'error_message' => 'nullable|string|max:500',
            // Campo flexível para dados agregados (nome empresa, totais, etc.)
            'dados' => 'nullable',
            'cliente_id' => 'nullable|integer',
        ]);

        // Chave do cache: progresso:{user_id}:{tab_id}
        $cacheKey = "progresso:{$validated['user_id']}:{$validated['tab_id']}";

        // Dados para cache (repassa exatamente o que n8n enviou)
        $cacheData = [
            'user_id' => $validated['user_id'],
            'tab_id' => $validated['tab_id'],
            'progresso' => $validated['progresso'] ?? 0,
            'mensagem' => $validated['mensagem'] ?? null,
            'status' => $validated['status'],
            'updated_at' => now()->toIso8601String(),
        ];

        // Adicionar campos de erro se fornecidos
        if (! empty($validated['error_code'])) {
            $cacheData['error_code'] = $validated['error_code'];
        }
        if (! empty($validated['error_message'])) {
            $cacheData['error_message'] = $validated['error_message'];
        }

        // Sempre incluir campo dados no cache (mesmo se vazio, para consistência)
        $cacheData['dados'] = $validated['dados'] ?? [];

        // Enriquecer dados com informações do cliente se fornecido
        if (! empty($validated['cliente_id'])) {
            $cliente = \App\Models\Cliente::find($validated['cliente_id']);
            if ($cliente) {
                $dados = $cacheData['dados'];
                if (is_array($dados)) {
                    $dados['cliente_id']          = $validated['cliente_id'];
                    $dados['cliente_nome']        = $cliente->razao_social ?: $cliente->nome;
                    $dados['cliente_documento']   = $cliente->documento_formatado ?? $cliente->documento;
                    $dados['cliente_tipo_pessoa'] = $cliente->tipo_pessoa;
                    $cacheData['dados']           = $dados;
                }
            }
        }

        // Idempotência: não grava no cache se progresso/mensagem/status são idênticos.
        // Evita que loops do n8n que reenviam o mesmo percentual causem múltiplos
        // eventos SSE no frontend (updated_at diferente mudava o hash de dedup).
        $existing = Cache::get($cacheKey);
        if ($existing &&
            ($existing['progresso'] ?? null) === $cacheData['progresso'] &&
            ($existing['mensagem'] ?? null) === $cacheData['mensagem'] &&
            ($existing['status'] ?? null) === $cacheData['status'] &&
            empty($validated['error_code']) &&
            empty($validated['dados'])
        ) {
            return response()->json([
                'success' => true,
                'message' => 'Progresso sem alteração (idempotente).',
                'progresso' => $validated['progresso'],
            ], Response::HTTP_OK);
        }

        // Atualizar DB antes do cache para evitar race condition com SSE
        if ($validated['status'] === 'concluido') {
            $this->updateSpedImportacaoFromProgress($validated);
        }

        // Armazena em cache (TTL 10 minutos)
        Cache::put($cacheKey, $cacheData, 600);

        Log::info('Progresso armazenado em cache (novo formato)', [
            'cache_key' => $cacheKey,
            'user_id' => $validated['user_id'],
            'tab_id' => $validated['tab_id'],
            'progresso' => $validated['progresso'],
            'status' => $validated['status'],
            'has_error' => ! empty($validated['error_code']),
            'has_dados' => ! empty($validated['dados']),
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
        if (! empty($validated['error_message'])) {
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
            if (! $this->isTokenValid($request)) {
                Log::warning('Token inválido em receiveMonitoramentoConsulta');

                return $this->unauthorizedResponse($request);
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

            if (! $consulta) {
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
            if (isset($validated['participante']) && ! empty($validated['participante'])) {
                $participante = Participante::find($consulta->participante_id);

                if ($participante) {
                    $participanteUpdate = array_filter([
                        'razao_social' => $validated['participante']['razao_social'] ?? null,
                        'situacao_cadastral' => $validated['participante']['situacao_cadastral'] ?? null,
                        'regime_tributario' => $validated['participante']['regime_tributario'] ?? null,
                        'ultima_consulta_em' => now(),
                    ]);

                    if (! empty($participanteUpdate)) {
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
     * Recebe progresso de importação de XMLs (enviado pelo n8n).
     *
     * POST /api/importacao/xml/progress
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
            if (! $this->isTokenValid($request)) {
                Log::warning('Token inválido em receiveXmlImportacaoProgress');

                return $this->unauthorizedResponse($request);
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
            if (! empty($validated['importacao_id'])) {
                $cacheData['importacao_id'] = $validated['importacao_id'];
            }

            // Adicionar campos de erro se fornecidos
            if (! empty($validated['error_code'])) {
                $cacheData['error_code'] = $validated['error_code'];
            }
            if (! empty($validated['error_message'])) {
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
                'has_error' => ! empty($validated['error_code']),
                'has_dados' => ! empty($validated['dados']),
            ]);

            // Quando status é final, atualizar registro XmlImportacao no banco
            if (in_array($validated['status'], ['concluido', 'erro'])) {
                $this->updateXmlImportacaoFromProgress($validated);
            }

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

    /**
     * Atualiza SpedImportacao e vincula participantes ao cliente quando n8n envia status final.
     */
    private function updateSpedImportacaoFromProgress(array $validated): void
    {
        $dados = $validated['dados'] ?? [];
        $importacaoId = $dados['importacao_id'] ?? null;

        if (!$importacaoId) return;

        try {
            $importacao = SpedImportacao::where('id', $importacaoId)
                ->where('user_id', $validated['user_id'])
                ->first();

            if (!$importacao) return;

            $updateData = [
                'status'       => 'concluido',
                'concluido_em' => now(),
            ];

            if (!empty($dados['total_processados'])) {
                $updateData['total_participantes'] = (int) $dados['total_processados'];
            }
            if (isset($dados['novos_salvos'])) {
                $updateData['novos'] = (int) $dados['novos_salvos'];
            }
            if (isset($dados['duplicados_identificados'])) {
                $updateData['duplicados'] = (int) $dados['duplicados_identificados'];
            }

            // cliente_id vem no topo do payload (não dentro de dados)
            $clienteId = $validated['cliente_id'] ?? null;
            if ($clienteId) {
                $updateData['cliente_id'] = $clienteId;
            }

            // participante_ids: aceitar "lita" (typo n8n) ou "lista"
            $idsStr = $dados['participante_lita_geral_ids']
                ?? $dados['participante_lista_geral_ids']
                ?? '';
            $ids = [];
            if (!empty($idsStr)) {
                $ids = array_values(array_filter(array_map('intval', explode(',', $idsStr))));
                $updateData['participante_ids'] = $ids;
            }

            $importacao->update($updateData);

            // Vincular participantes ao cliente
            if ($clienteId && !empty($ids)) {
                Participante::whereIn('id', $ids)
                    ->where('user_id', $validated['user_id'])
                    ->whereNull('cliente_id')
                    ->update(['cliente_id' => $clienteId]);
            }

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar SpedImportacao do progresso', [
                'importacao_id' => $importacaoId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Atualiza o registro XmlImportacao quando o n8n envia status final.
     */
    private function updateXmlImportacaoFromProgress(array $validated): void
    {
        if (empty($validated['importacao_id'])) {
            Log::warning('updateXmlImportacaoFromProgress: importacao_id não fornecido', [
                'user_id' => $validated['user_id'],
                'tab_id' => $validated['tab_id'],
                'status' => $validated['status'],
            ]);

            return;
        }

        try {
            $importacao = XmlImportacao::where('id', $validated['importacao_id'])
                ->where('user_id', $validated['user_id'])
                ->first();

            if (! $importacao) {
                Log::warning('updateXmlImportacaoFromProgress: importacao não encontrada', [
                    'importacao_id' => $validated['importacao_id'],
                    'user_id' => $validated['user_id'],
                ]);

                return;
            }

            $dados = $validated['dados'] ?? [];

            $updateData = [
                'status' => $validated['status'],
                'concluido_em' => now(),
            ];

            // Estatísticas de XMLs
            if (isset($dados['xmls_processados'])) {
                $updateData['xmls_processados'] = (int) $dados['xmls_processados'];
            }
            if (isset($dados['total_xmls'])) {
                $updateData['total_xmls'] = (int) $dados['total_xmls'];
            }
            if (isset($dados['xmls_novos'])) {
                $updateData['xmls_novos'] = (int) $dados['xmls_novos'];
            }
            if (isset($dados['xmls_duplicados_processados'])) {
                $updateData['xmls_duplicados_processados'] = (int) $dados['xmls_duplicados_processados'];
            }
            if (isset($dados['xmls_com_erro'])) {
                $updateData['xmls_com_erro'] = (int) $dados['xmls_com_erro'];
            }

            // Estatísticas de participantes
            if (isset($dados['participantes_novos'])) {
                $updateData['participantes_novos'] = (int) $dados['participantes_novos'];
            }
            if (isset($dados['participantes_atualizados'])) {
                $updateData['participantes_atualizados'] = (int) $dados['participantes_atualizados'];
            }
            if (isset($dados['participantes_ignorados'])) {
                $updateData['participantes_ignorados'] = (int) $dados['participantes_ignorados'];
            }

            // IDs dos participantes processados (crítico para getParticipantes)
            if (! empty($dados['participante_ids'])) {
                $updateData['participante_ids'] = array_values(array_unique(array_map('intval', $dados['participante_ids'])));
            }

            // Valor total das notas
            if (isset($dados['valor_total'])) {
                $updateData['valor_total'] = (float) $dados['valor_total'];
            }

            // Erros detalhados
            if (! empty($dados['erros'])) {
                $updateData['erros_detalhados'] = $dados['erros'];
            }

            // Mensagem de erro
            if ($validated['status'] === 'erro' && ! empty($validated['error_message'])) {
                $updateData['erro_mensagem'] = $validated['error_message'];
            }

            $importacao->update($updateData);

            Log::info('XmlImportacao atualizada com dados do progresso', [
                'importacao_id' => $importacao->id,
                'status' => $validated['status'],
                'participante_ids_count' => count($updateData['participante_ids'] ?? []),
                'xmls_processados' => $updateData['xmls_processados'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar XmlImportacao do progresso', [
                'importacao_id' => $validated['importacao_id'],
                'error' => $e->getMessage(),
            ]);
        }
    }


    /**
     * Recebe progresso de consulta em lote do n8n (endpoint canônico).
     *
     * POST /api/consulta/progress
     */
    public function receiveConsultaProgress(Request $request)
    {
        try {
            Log::info('Requisição recebida em receiveConsultaProgress', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'headers' => [
                    'x-api-token' => $request->hasHeader('X-API-Token') ? 'presente' : 'ausente',
                    'content-type' => $request->header('Content-Type'),
                ],
                'body' => $request->except(['dados']),
            ]);

            if (! $this->isTokenValid($request)) {
                Log::warning('Token inválido em receiveConsultaProgress');

                return $this->unauthorizedResponse($request);
            }

            $validated = $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'tab_id' => 'required|string|max:36',
                'consulta_lote_id' => 'required|integer|exists:consulta_lotes,id',
                'progresso' => 'required|integer|min:0|max:100',
                'mensagem' => 'nullable|string|max:255',
                'status' => 'required|in:iniciando,processando,concluido,erro',
                'error_code' => 'nullable|string|max:50',
                'error_message' => 'nullable|string|max:500',
                'dados' => 'nullable',
            ]);

            $cacheKey = "progresso:{$validated['user_id']}:{$validated['tab_id']}";

            $cacheData = [
                'user_id' => $validated['user_id'],
                'tab_id' => $validated['tab_id'],
                'consulta_lote_id' => $validated['consulta_lote_id'],
                'progresso' => $validated['progresso'],
                'mensagem' => $validated['mensagem'] ?? null,
                'status' => $validated['status'],
                'updated_at' => now()->toIso8601String(),
            ];

            if (! empty($validated['error_code'])) {
                $cacheData['error_code'] = $validated['error_code'];
            }
            if (! empty($validated['error_message'])) {
                $cacheData['error_message'] = $validated['error_message'];
            }

            $cacheData['dados'] = $validated['dados'] ?? [];

            Cache::put($cacheKey, $cacheData, 600);

            Log::info('Progresso consulta armazenado em cache (receiveConsultaProgress)', [
                'cache_key' => $cacheKey,
                'user_id' => $validated['user_id'],
                'tab_id' => $validated['tab_id'],
                'consulta_lote_id' => $validated['consulta_lote_id'],
                'progresso' => $validated['progresso'],
                'status' => $validated['status'],
            ]);

            if (in_array($validated['status'], ['concluido', 'erro'])) {
                $this->updateConsultaLoteFromProgress($validated);
            }

            return response()->json([
                'success' => true,
                'message' => 'Progresso atualizado.',
                'progresso' => $validated['progresso'],
            ], Response::HTTP_OK);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Erro de validação em receiveConsultaProgress', [
                'errors' => $e->errors(),
                'request_data' => $request->except(['dados']),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);

        } catch (\Exception $e) {
            Log::error('Erro inesperado em receiveConsultaProgress', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['dados']),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor. Tente novamente mais tarde.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Atualiza o registro ConsultaLote quando o n8n envia status final.
     */
    private function updateConsultaLoteFromProgress(array $validated): void
    {
        try {
            $lote = ConsultaLote::where('id', $validated['consulta_lote_id'])
                ->where('user_id', $validated['user_id'])
                ->first();

            if (! $lote) {
                Log::warning('updateConsultaLoteFromProgress: lote não encontrado', [
                    'consulta_lote_id' => $validated['consulta_lote_id'],
                    'user_id' => $validated['user_id'],
                ]);

                return;
            }

            $updateData = [
                'status' => $validated['status'],
                'processado_em' => now(),
            ];

            // Se concluído, salvar resultado
            if ($validated['status'] === 'concluido') {
                if (! empty($validated['resultado_resumo'])) {
                    $updateData['resultado_resumo'] = $validated['resultado_resumo'];
                }
                if (! empty($validated['report_csv_base64'])) {
                    $updateData['report_csv_base64'] = $validated['report_csv_base64'];
                }
                if (! empty($validated['filename'])) {
                    $updateData['filename'] = $validated['filename'];
                }
            }

            // Se erro, salvar detalhes do erro
            if ($validated['status'] === 'erro') {
                $updateData['error_code'] = $validated['error_code'] ?? 'UNKNOWN_ERROR';
                $updateData['error_message'] = $validated['error_message'] ?? 'Erro desconhecido';

                // Estornar créditos em caso de erro
                $this->refundConsultaLoteCredits($lote);
            }

            $lote->update($updateData);

            Log::info('ConsultaLote atualizado com dados do progresso', [
                'consulta_lote_id' => $lote->id,
                'status' => $validated['status'],
                'has_csv' => ! empty($validated['report_csv_base64']),
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar ConsultaLote do progresso', [
                'consulta_lote_id' => $validated['consulta_lote_id'],
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Estorna créditos de um lote de consulta em caso de erro.
     */
    private function refundConsultaLoteCredits(ConsultaLote $lote): void
    {
        if ($lote->creditos_cobrados <= 0) {
            return;
        }

        try {
            $user = User::find($lote->user_id);
            if ($user) {
                $this->creditService->add($user, $lote->creditos_cobrados);
                Log::info('Créditos estornados para consulta lote com erro', [
                    'consulta_lote_id' => $lote->id,
                    'user_id' => $lote->user_id,
                    'creditos_estornados' => $lote->creditos_cobrados,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao estornar créditos do consulta lote', [
                'consulta_lote_id' => $lote->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Recebe resultado individual de consulta por participante.
     *
     * POST /api/consultas/lote/resultado
     */
    public function receiveConsultaLoteResultado(Request $request)
    {
        try {
            Log::info('Requisição recebida em receiveConsultaLoteResultado', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'headers' => [
                    'x-api-token' => $request->hasHeader('X-API-Token') ? 'presente' : 'ausente',
                    'content-type' => $request->header('Content-Type'),
                ],
                'body' => array_diff_key($request->all(), ['resultado_dados' => '']),
            ]);

            // Verifica autenticação via token
            if (! $this->isTokenValid($request)) {
                Log::warning('Token inválido em receiveConsultaLoteResultado');

                return $this->unauthorizedResponse($request);
            }

            // Validar payload
            $validated = $request->validate([
                'consulta_lote_id' => 'required|integer|exists:consulta_lotes,id',
                'user_id' => 'required|integer|exists:users,id',
                'tab_id' => 'required|string|max:36',
                'participante_id' => 'required|integer|exists:participantes,id',
                'status' => 'required|in:sucesso,erro,timeout',
                'resultado_dados' => 'nullable|array',
                'error_message' => 'nullable|string|max:500',
            ]);

            // Verificar que o lote pertence ao usuário
            $lote = ConsultaLote::where('id', $validated['consulta_lote_id'])
                ->where('user_id', $validated['user_id'])
                ->first();

            if (! $lote) {
                Log::warning('receiveConsultaLoteResultado: lote não pertence ao usuário', [
                    'consulta_lote_id' => $validated['consulta_lote_id'],
                    'user_id' => $validated['user_id'],
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Lote não encontrado para este usuário.',
                ], Response::HTTP_NOT_FOUND);
            }

            // Verificar que o participante está no lote
            $participanteNoLote = $lote->participantes()
                ->where('participantes.id', $validated['participante_id'])
                ->exists();

            if (! $participanteNoLote) {
                Log::warning('receiveConsultaLoteResultado: participante não pertence ao lote', [
                    'consulta_lote_id' => $validated['consulta_lote_id'],
                    'participante_id' => $validated['participante_id'],
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Participante não pertence a este lote.',
                ], Response::HTTP_BAD_REQUEST);
            }

            // Criar ou atualizar resultado
            $resultado = ConsultaResultado::updateOrCreate(
                [
                    'consulta_lote_id' => $validated['consulta_lote_id'],
                    'participante_id' => $validated['participante_id'],
                ],
                [
                    'resultado_dados' => $validated['resultado_dados'] ?? null,
                    'status' => $validated['status'],
                    'error_message' => $validated['error_message'] ?? null,
                    'consultado_em' => now(),
                ]
            );

            Log::info('Resultado consulta armazenado', [
                'consulta_lote_id' => $validated['consulta_lote_id'],
                'participante_id' => $validated['participante_id'],
                'status' => $validated['status'],
                'resultado_id' => $resultado->id,
            ]);

            // Auto-completar lote quando todos os resultados chegarem
            $totalResultados = ConsultaResultado::where('consulta_lote_id', $lote->id)->count();
            if ($totalResultados >= $lote->total_participantes && $lote->status !== ConsultaLote::STATUS_CONCLUIDO) {
                $lote->update([
                    'status' => ConsultaLote::STATUS_CONCLUIDO,
                    'concluido_em' => now(),
                ]);

                Log::info('Auto-completando lote consulta', [
                    'consulta_lote_id' => $lote->id,
                    'total_resultados' => $totalResultados,
                    'total_participantes' => $lote->total_participantes,
                ]);

                // Escrever no cache para acionar o SSE
                if ($lote->tab_id) {
                    $cacheKey = "progresso:{$lote->user_id}:{$lote->tab_id}";
                    Cache::put($cacheKey, [
                        'user_id' => $lote->user_id,
                        'tab_id' => $lote->tab_id,
                        'consulta_lote_id' => $lote->id,
                        'progresso' => 100,
                        'mensagem' => 'Consulta concluída.',
                        'status' => 'concluido',
                        'updated_at' => now()->toIso8601String(),
                    ], 600);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Resultado armazenado.',
                'resultado_id' => $resultado->id,
            ], Response::HTTP_OK);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Erro de validação em receiveConsultaLoteResultado', [
                'errors' => $e->errors(),
                'request_data' => array_diff_key($request->all(), ['resultado_dados' => '']),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);

        } catch (\Exception $e) {
            Log::error('Erro inesperado em receiveConsultaLoteResultado', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => array_diff_key($request->all(), ['resultado_dados' => '']),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

