<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\EfdDivergencia;
use App\Models\EfdImportacao;
use App\Models\Participante;
use App\Services\EfdAuditoriaService;
use App\Services\EfdResumoBuilder;
use App\Services\SaldoService;
use App\Support\SystemCriticalError;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DataReceiverController extends Controller
{
    public function __construct(
        protected SaldoService $saldoService,
        protected SystemCriticalError $systemCriticalError
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

        // Endpoint público: expõe apenas liveness + se o token está configurado.
        // Nunca devolver prefixo/tamanho do token, versão do PHP ou ambiente —
        // são dados de reconhecimento para um atacante não autenticado.
        return response()->json([
            'status' => 'ok',
            'api_token_configured' => ! empty($sanitized),
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

        // Diagnóstico para log interno — NUNCA inclui material do token esperado
        // (prefixo/segredo não deve ser escrito em log). Só sinaliza presença/tamanho.
        $debug = [
            'received_length' => strlen($apiToken),
            'expected_configured' => ! empty($expectedToken),
        ];

        if (! $isValid) {
            if (empty($expectedToken)) {
                $debug['hint'] = "config('services.api.token') esta vazio — verifique API_TOKEN no .env e re-execute config:cache";
            } elseif (empty($apiToken)) {
                $debug['hint'] = 'Header X-API-Token nao enviado ou vazio';
            } else {
                $debug['hint'] = 'Token recebido difere do esperado';
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
     * Atualiza o updated_at da importação como "último sinal de vida".
     * Só toca linhas em 'processando' — não rejuvenesce concluido/erro.
     */
    private function tocarImportacao(string $modelClass, ?int $importacaoId, ?int $userId): void
    {
        if (empty($importacaoId) || empty($userId)) {
            return;
        }

        $modelClass::where('id', $importacaoId)
            ->where('user_id', $userId)
            ->where('status', 'processando')
            ->update(['updated_at' => now()]);
    }

    /**
     * Retorna resposta 401 com diagnostico de token.
     */
    private function unauthorizedResponse(Request $request): \Illuminate\Http\JsonResponse
    {
        // Valida (e loga server-side o diagnóstico); a resposta ao cliente NÃO
        // expõe prefixo/tamanho do token esperado — isso vazaria parte do segredo.
        $this->validateToken($request);

        return response()->json([
            'success' => false,
            'message' => 'Token de API inválido.',
        ], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Recebe progresso da importação/processamento de arquivo SPED (enviado pelo n8n).
     * Armazena em cache para o SSE ler e enviar ao frontend.
     * NÃO edita banco de dados - apenas cache.
     *
     * POST /api/importacao/efd/importacao-txt/progresso
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
     * Recebe progresso de extração de notas EFD por bloco (A, C, D).
     * Registra uma divergência detectada durante a extração EFD.
     *
     * Auditor do n8n compara input/output de cada subworkflow e reporta itens
     * descartados (duplicação, constraint, órfão, cancelada). Idempotente via
     * (importacao_id, bloco, chave_acesso, numero_item, motivo).
     *
     * POST /api/importacao/efd/divergencia
     * Headers: X-API-Token
     * Body: { user_id, importacao_id, bloco, motivo, severidade, chave_acesso?,
     *         numero_documento?, numero_item?, payload_descartado, mensagem? }
     */
    public function receiveEfdDivergencia(Request $request): JsonResponse
    {
        if (! $this->isTokenValid($request)) {
            return response()->json(['error' => 'Unauthorized', 'message' => 'Token inválido'], 401);
        }

        $data = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'importacao_id' => 'required|integer|exists:efd_importacoes,id',
            'bloco' => 'required|string|max:8',
            'motivo' => 'required|in:'.implode(',', [
                EfdDivergencia::MOTIVO_CANCELADA_DESCARTADA,
                EfdDivergencia::MOTIVO_COMPLEMENTAR_DESCARTADA,
                EfdDivergencia::MOTIVO_REGULARIZACAO_DESCARTADA,
                EfdDivergencia::MOTIVO_DUPLICADA_PROCESSAMENTO,
                EfdDivergencia::MOTIVO_CONSTRAINT_VIOLADA,
                EfdDivergencia::MOTIVO_PAI_NAO_ENCONTRADO,
                EfdDivergencia::MOTIVO_PARSE_INCONSISTENTE,
                EfdDivergencia::MOTIVO_VALOR_DIVERGENTE,
            ]),
            'severidade' => 'required|in:info,aviso,erro',
            'chave_acesso' => 'nullable|string|max:44',
            'numero_documento' => 'nullable|integer',
            'numero_item' => 'nullable|integer',
            'payload_descartado' => 'required|array',
            'mensagem' => 'nullable|string|max:1000',
        ]);

        $div = EfdDivergencia::updateOrCreate(
            [
                'importacao_id' => $data['importacao_id'],
                'bloco' => $data['bloco'],
                'motivo' => $data['motivo'],
                'chave_acesso' => $data['chave_acesso'] ?? null,
                'numero_item' => $data['numero_item'] ?? null,
            ],
            [
                'user_id' => $data['user_id'],
                'severidade' => $data['severidade'],
                'numero_documento' => $data['numero_documento'] ?? null,
                'payload_descartado' => $data['payload_descartado'],
                'mensagem' => $data['mensagem'] ?? null,
                'detectado_em' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'divergencia_id' => $div->id,
            'created' => $div->wasRecentlyCreated,
        ]);
    }

    /**
     * Finaliza importação EFD: n8n chama no fim do orquestrador, Laravel constrói
     * o resumo_final a partir do banco (single source of truth), persiste, e
     * atualiza o cache do SSE pra UI fechar.
     *
     * POST /api/importacao/efd/finalizar
     * Headers: X-API-Token
     * Body: { user_id, tab_id, importacao_id, tipo_efd? }
     */
    public function finalizarImportacaoEfd(Request $request, EfdResumoBuilder $builder, EfdAuditoriaService $auditoria): JsonResponse
    {
        if (! $this->isTokenValid($request)) {
            return response()->json(['error' => 'Unauthorized', 'message' => 'Token inválido'], 401);
        }

        $data = $request->validate([
            'user_id' => 'required|integer',
            'tab_id' => 'required|string|max:36',
            'importacao_id' => 'required|integer',
            'tipo_efd' => 'nullable|string|max:50',
        ]);

        $imp = EfdImportacao::where('id', $data['importacao_id'])
            ->where('user_id', $data['user_id'])
            ->first();

        if (! $imp) {
            return response()->json([
                'error' => 'NotFound',
                'message' => 'Importação não encontrada para este usuário.',
            ], 404);
        }

        $cacheKey = "progresso:{$data['user_id']}:{$data['tab_id']}";

        // Idempotência: já concluída → devolve o resumo persistido, não reconstrói
        if ($imp->status === 'concluido' && ! empty($imp->resumo_final)) {
            return response()->json([
                'status' => 'ok',
                'importacao_id' => $imp->id,
                'status_final' => 'concluido',
                'resumo_final' => $imp->resumo_final,
                'concluido_em' => optional($imp->concluido_em)->toIso8601String(),
                'tempo_processamento_segundos' => $imp->tempo_processamento_segundos,
                'idempotent' => true,
            ]);
        }

        $resumo = $builder->build($imp);
        $tempoSegundos = $imp->iniciado_em
            ? (int) $imp->iniciado_em->diffInSeconds(now())
            : null;

        // Guardrail universal: toda importação se autoverifica contra o SPED bruto. Se o
        // pipeline dropou notas (ex.: Merge C100↔0150 soltando NFC-e — bug UTIDA), a
        // importação NÃO fica "concluído" mudo: grava o veredito em resumo_final.integridade
        // e loga. Barato (só conta chaves), degrada seguro sem arquivo retido.
        $integridade = $auditoria->integridade($imp);
        $resumo['integridade'] = $integridade;
        if (! $integridade['ok']) {
            Log::warning('EFD import: notas do SPED ausentes no banco (pipeline dropou)', [
                'importacao_id' => $imp->id,
                'user_id' => $imp->user_id,
                'tipo_efd' => $imp->tipo_efd,
                'esperadas' => $integridade['esperadas'],
                'faltando' => $integridade['faltando'],
                'amostra' => $integridade['amostra_faltando'],
            ]);
        }

        $est = $resumo['estatisticas'] ?? [];
        $imp->update([
            'status' => 'concluido',
            'resumo_final' => $resumo,
            'concluido_em' => now(),
            'tempo_processamento_segundos' => $tempoSegundos,
            'total_participantes' => $est['total_participantes_processados'] ?? 0,
            'total_cnpjs_unicos' => $est['total_cnpjs_unicos'] ?? 0,
            'total_cpfs_unicos' => $est['total_cpfs_unicos'] ?? 0,
            'novos' => $est['participantes_novos'] ?? 0,
            'duplicados' => $est['participantes_repetidos'] ?? 0,
            'total_notas' => $est['total_notas_processadas'] ?? 0,
            'notas_extraidas' => $est['notas_novas'] ?? 0,
            'participante_ids' => $resumo['participante_ids'] ?? [],
        ]);

        $existing = Cache::get($cacheKey, []);
        Cache::put($cacheKey, array_merge($existing, [
            'user_id' => $data['user_id'],
            'tab_id' => $data['tab_id'],
            'importacao_id' => $imp->id,
            'status' => 'concluido',
            'progresso' => 100,
            'mensagem' => $resumo['mensagem'] ?? 'Importação concluída.',
            'resumo_final' => $resumo,
            'updated_at' => now()->toIso8601String(),
        ]), 600);

        return response()->json([
            'status' => 'ok',
            'importacao_id' => $imp->id,
            'status_final' => 'concluido',
            'resumo_final' => $resumo,
            'concluido_em' => $imp->concluido_em?->toIso8601String(),
            'tempo_processamento_segundos' => $tempoSegundos,
        ]);
    }

    /**
     * n8n envia fase/status e contadores por bloco; Laravel armazena em cache para SSE.
     *
     * POST /api/importacao/efd/notas/progresso
     * Headers: X-API-Token
     * Body: { user_id, tab_id, status, bloco, progresso, mensagem? }
     */
    public function receiveNotasEfdProgress(Request $request): JsonResponse
    {
        if (! $this->isTokenValid($request)) {
            return response()->json(['error' => 'Unauthorized', 'message' => 'Token inválido'], 401);
        }

        $data = $request->validate([
            'user_id' => 'required|integer',
            'tab_id' => 'required|string|max:36',
            'status' => 'required|in:inicio,processando,concluido,skip,erro',
            'bloco' => 'nullable|in:participantes,notas_servicos,notas_mercadorias,notas_transportes,catalogo,apuracao_icms,retencoes_fonte,apuracao_pis_cofins',
            'progresso' => 'nullable|integer|min:0|max:100',
            'mensagem' => 'nullable|string|max:255',
            'importacao_id' => 'nullable|integer',
            'error_code' => 'nullable|string|max:50',
            'error_message' => 'nullable|string|max:500',
            'resumo_final' => 'nullable|array',
            'notas_blocos' => 'nullable|array',
            'blocos' => 'nullable|array',
            'estatisticas' => 'nullable|array',
            'totais' => 'nullable|array',
            'participantes_resumo' => 'nullable|array',
            'dados' => 'nullable',
        ]);

        // Default progresso=0 quando não enviado (ex: payloads de erro)
        $data['progresso'] = $data['progresso'] ?? 0;

        // Tratar status de erro: cachear para SSE e persistir no banco
        if ($data['status'] === 'erro') {
            $mainKey = "progresso:{$data['user_id']}:{$data['tab_id']}";
            $existing = Cache::get($mainKey, []);
            $importacaoId = $data['importacao_id'] ?? ($existing['importacao_id'] ?? null);
            $uiError = $this->systemCriticalError->forAsyncFailure(
                $data['error_message'] ?? $data['mensagem'] ?? null,
                $data['error_code'] ?? null,
                [
                    'context' => 'importacao-efd',
                    'reference' => $importacaoId ? 'Importação #'.$importacaoId : null,
                ]
            );

            $cachePayload = array_merge($existing, [
                'user_id' => $data['user_id'],
                'tab_id' => $data['tab_id'],
                'status' => 'erro',
                'progresso' => $data['progresso'],
                'mensagem' => $uiError['message'],
                'error_code' => $data['error_code'] ?? null,
                'error_message' => $uiError['message'],
                'ui_error' => $uiError,
                'updated_at' => now()->toIso8601String(),
            ]);
            if (! empty($importacaoId)) {
                $cachePayload['importacao_id'] = $importacaoId;

                EfdImportacao::where('id', $importacaoId)
                    ->where('user_id', $data['user_id'])
                    ->update([
                        'status' => 'erro',
                    ]);
            }
            Cache::put($mainKey, $cachePayload, 600);

            return response()->json(['status' => 'ok', 'received' => 'erro']);
        }

        // Se os 4 campos chegaram separados, montar resumo_final internamente.
        // Mantém compatibilidade: se resumo_final já vier pronto, usa direto.
        if (empty($data['resumo_final'])
            && (! empty($data['blocos']) || ! empty($data['estatisticas']) || ! empty($data['totais']))) {
            $data['resumo_final'] = [
                'blocos' => $data['blocos'] ?? [],
                'estatisticas' => $data['estatisticas'] ?? [],
                'totais' => $data['totais'] ?? [],
                'participantes_resumo' => $data['participantes_resumo'] ?? [],
            ];
        }

        // Ler cache existente antes de qualquer operação (para fallback de importacao_id)
        $mainKey = "progresso:{$data['user_id']}:{$data['tab_id']}";
        $existing = Cache::get($mainKey, []);

        // Usar importacao_id do payload OU do cache existente
        $importacaoId = $data['importacao_id'] ?? ($existing['importacao_id'] ?? null);

        // Heartbeat: marca a linha da importação como viva (updated_at = agora),
        // só se estiver em 'processando'. Lido pelo importacao:expirar-travadas.
        $this->tocarImportacao(EfdImportacao::class, $importacaoId ? (int) $importacaoId : null, (int) $data['user_id']);

        // Persiste resumo_final no banco se presente, junto com colunas de stats
        if (! empty($data['resumo_final']) && ! empty($importacaoId)) {
            $rfUpdate = ['resumo_final' => $data['resumo_final']];

            if ($data['status'] === 'concluido') {
                $rfUpdate['status'] = 'concluido';
                $rfUpdate['concluido_em'] = now();

                $imp = EfdImportacao::find($importacaoId);
                if ($imp && $imp->iniciado_em) {
                    $rfUpdate['tempo_processamento_segundos'] = (int) $imp->iniciado_em->diffInSeconds(now());
                }
            }

            $est = $data['resumo_final']['estatisticas'] ?? [];
            if (! empty($est['total_participantes_processados'])) {
                $rfUpdate['total_participantes'] = (int) $est['total_participantes_processados'];
            }
            if (isset($est['participantes_novos'])) {
                $rfUpdate['novos'] = (int) $est['participantes_novos'];
            }
            if (isset($est['participantes_repetidos'])) {
                $rfUpdate['duplicados'] = (int) $est['participantes_repetidos'];
            }
            if (! empty($est['total_cnpjs_unicos'])) {
                $rfUpdate['total_cnpjs_unicos'] = (int) $est['total_cnpjs_unicos'];
            }
            if (! empty($est['total_cpfs_unicos'])) {
                $rfUpdate['total_cpfs_unicos'] = (int) $est['total_cpfs_unicos'];
            }

            EfdImportacao::where('id', $importacaoId)
                ->where('user_id', $data['user_id'])
                ->update($rfUpdate);
        }

        // Recalcular alertas após importação concluída
        if ($data['status'] === 'concluido' && ! empty($importacaoId)) {
            dispatch(function () use ($data) {
                app(\App\Services\AlertaCentralService::class)->recalcular((int) $data['user_id']);
            })->afterResponse();
        }

        // Atualiza cache principal (lido pelo SSE) com o progresso atual.
        // Não rebaixar status de 'concluido' para 'processando' — blocos extras
        // (catálogo, apuração) podem chegar após o payload final do n8n.
        $existingStatus = $existing['status'] ?? null;
        $incomingStatus = $data['status'];
        $statusFinal = ($existingStatus === 'concluido' && $incomingStatus === 'processando')
            ? 'concluido'
            : $incomingStatus;

        $cachePayload = array_merge($existing, [
            'user_id' => $data['user_id'],
            'tab_id' => $data['tab_id'],
            'status' => $statusFinal,
            'progresso' => $existingStatus === 'concluido' ? ($existing['progresso'] ?? 100) : $data['progresso'],
            'mensagem' => $existingStatus === 'concluido' ? ($existing['mensagem'] ?? $data['mensagem']) : ($data['mensagem'] ?? null),
            'bloco' => $data['bloco'] ?? null,
            'updated_at' => now()->toIso8601String(),
        ]);
        // Preservar importacao_id no cache (do payload ou do existente)
        if (! empty($importacaoId)) {
            $cachePayload['importacao_id'] = $importacaoId;
        }
        if (! empty($data['resumo_final'])) {
            $cachePayload['resumo_final'] = $data['resumo_final'];
        }
        if (! empty($data['notas_blocos'])) {
            $cachePayload['notas_blocos'] = $data['notas_blocos'];
        }
        $dadosRaw = $data['dados'] ?? null;
        if (is_string($dadosRaw) && ! empty($dadosRaw)) {
            $dadosParsed = json_decode($dadosRaw, true) ?? [];
        } elseif (is_array($dadosRaw)) {
            $dadosParsed = $dadosRaw;
        } else {
            $dadosParsed = [];
        }
        if (! empty($dadosParsed)) {
            $cachePayload['dados'] = $dadosParsed;
        }
        Cache::put($mainKey, $cachePayload, 600);

        // Cache por bloco (lido pelo SSE para montar notas_blocos)
        if (! empty($data['bloco'])) {
            $blocoKey = "efd_notas_progress:{$data['user_id']}:{$data['tab_id']}:{$data['bloco']}";
            Cache::put($blocoKey, [
                'bloco' => $data['bloco'],
                'status' => $data['status'],
                'progresso' => $data['progresso'],
                'mensagem' => $data['mensagem'] ?? null,
                'updated_at' => now()->toIso8601String(),
            ], 600);

            // Marcar blocos anteriores como concluídos se ainda estiverem processando
            if (in_array($data['status'], ['inicio', 'processando'])) {
                $ordemBlocos = ['participantes', 'notas_servicos', 'notas_mercadorias', 'notas_transportes', 'catalogo', 'apuracao_icms', 'retencoes_fonte', 'apuracao_pis_cofins'];
                $currentIdx = array_search($data['bloco'], $ordemBlocos);

                for ($i = 0; $i < $currentIdx; $i++) {
                    $priorKey = "efd_notas_progress:{$data['user_id']}:{$data['tab_id']}:{$ordemBlocos[$i]}";
                    $priorData = Cache::get($priorKey);
                    if ($priorData && ! in_array($priorData['status'], ['concluido', 'skip'])) {
                        $priorData['status'] = 'concluido';
                        $priorData['progresso'] = 100;
                        Cache::put($priorKey, $priorData, 600);
                    }
                }
            }
        }

        return response()->json(['success' => true]);
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
            'error_code' => 'nullable|string|max:50',
            'error_message' => 'nullable|string|max:500',
            'dados' => 'nullable',
            'cliente_id' => 'nullable|integer',
            'importacao_id' => 'nullable|integer',
            'resumo_final' => 'nullable|array',
            'notas_blocos' => 'nullable|array',
            'participante_ids' => 'nullable|array',
        ]);

        $cacheKey = "progresso:{$validated['user_id']}:{$validated['tab_id']}";

        // Ler cache existente antes de qualquer operação (para merge e fallback de importacao_id)
        $existing = Cache::get($cacheKey, []);

        // Idempotência: não grava no cache se progresso/mensagem/status são idênticos.
        // Bypassar se resumo_final presente — payloads finais devem sempre ser processados.
        if ($existing &&
            ($existing['progresso'] ?? null) === ($validated['progresso'] ?? 0) &&
            ($existing['mensagem'] ?? null) === ($validated['mensagem'] ?? null) &&
            ($existing['status'] ?? null) === $validated['status'] &&
            empty($validated['error_code']) &&
            empty($validated['dados']) &&
            empty($validated['resumo_final'])
        ) {
            return response()->json([
                'success' => true,
                'message' => 'Progresso sem alteração (idempotente).',
                'progresso' => $validated['progresso'],
            ], Response::HTTP_OK);
        }

        // Merge com cache existente para preservar importacao_id e outros campos anteriores
        $cacheData = array_merge($existing, [
            'user_id' => $validated['user_id'],
            'tab_id' => $validated['tab_id'],
            'progresso' => $validated['progresso'] ?? 0,
            'mensagem' => $validated['mensagem'] ?? null,
            'status' => $validated['status'],
            'updated_at' => now()->toIso8601String(),
        ]);

        // Se a fase de notas está ativa (bloco presente no cache), não sobrescrever
        // o progresso com o valor do endpoint principal — o endpoint de notas controla.
        if (isset($existing['bloco']) && $existing['bloco'] !== '' && $validated['status'] !== 'concluido' && $validated['status'] !== 'erro') {
            $cacheData['progresso'] = $existing['progresso'] ?? $cacheData['progresso'];
            $cacheData['mensagem'] = $existing['mensagem'] ?? $cacheData['mensagem'];
        }

        // Preservar importacao_id do cache inicial se n8n não reenviar
        $importacaoIdTop = $validated['importacao_id'] ?? ($existing['importacao_id'] ?? null);
        if (! empty($importacaoIdTop)) {
            $cacheData['importacao_id'] = $importacaoIdTop;
        }

        // Heartbeat: marca a linha da importação como viva (updated_at = agora),
        // só se estiver em 'processando'. Lido pelo importacao:expirar-travadas.
        $this->tocarImportacao(EfdImportacao::class, $importacaoIdTop ? (int) $importacaoIdTop : null, (int) $validated['user_id']);

        // Propagar resumo_final e notas_blocos ao cache
        if (! empty($validated['resumo_final'])) {
            $resumoFinal = $validated['resumo_final'];

            // Enriquecer resumo_final com apurações do DB para os Resumos Inteligentes
            $importacaoId = $validated['importacao_id'] ?? ($existing['importacao_id'] ?? null);
            if ($importacaoId) {
                // Buscamos a importacao e as obrigações que foram extraídas na fase anterior do workflow
                $importacao = \App\Models\EfdImportacao::with(['apuracaoIcms', 'apuracaoContribuicao', 'retencoesFonte'])
                    ->find($importacaoId);

                if ($importacao) {
                    if (! isset($resumoFinal['blocos'])) {
                        $resumoFinal['blocos'] = [];
                    }

                    if ($importacao->apuracaoIcms) {
                        $resumoFinal['blocos']['apuracao_icms'] = [
                            'total_notas' => 1,
                            'valor_total' => $importacao->apuracaoIcms->icms_a_recolher + ($importacao->apuracaoIcms->tem_st ? $importacao->apuracaoIcms->st_icms_recolher : 0),
                            'label_count' => 'apuração',
                        ];
                    }
                    if ($importacao->apuracaoContribuicao) {
                        $resumoFinal['blocos']['apuracao_pis_cofins'] = [
                            'total_notas' => 1,
                            'valor_total' => $importacao->apuracaoContribuicao->pis_total_recolher + $importacao->apuracaoContribuicao->cofins_total_recolher,
                            'label_count' => 'apuração',
                        ];
                    }
                    if ($importacao->retencoesFonte && $importacao->retencoesFonte->isNotEmpty()) {
                        $totalRet = $importacao->retencoesFonte->count();
                        $valorRet = $importacao->retencoesFonte->sum('valor_pis') + $importacao->retencoesFonte->sum('valor_cofins');
                        $resumoFinal['blocos']['retencoes_fonte'] = [
                            'total_notas' => $totalRet,
                            'valor_total' => $valorRet,
                            'label_count' => $totalRet > 1 ? 'retenções' : 'retenção',
                        ];
                    }

                    // Atualiza o registro no BD caso ele não propague de outra forma
                    $importacao->update(['resumo_final' => $resumoFinal]);
                }
            }

            $cacheData['resumo_final'] = $resumoFinal;
        }
        if (! empty($validated['notas_blocos'])) {
            $cacheData['notas_blocos'] = $validated['notas_blocos'];
        }

        // Adicionar campos de erro se fornecidos
        if (! empty($validated['error_code'])) {
            $cacheData['error_code'] = $validated['error_code'];
        }
        if (! empty($validated['error_message'])) {
            $cacheData['error_message'] = $validated['error_message'];
        }

        // n8n pode enviar dados como string JSON (via JSON.stringify) — fazer parse aqui
        $dadosRaw = $validated['dados'] ?? null;
        if (is_string($dadosRaw) && ! empty($dadosRaw)) {
            $dadosParsed = json_decode($dadosRaw, true) ?? [];
        } elseif (is_array($dadosRaw)) {
            $dadosParsed = $dadosRaw;
        } else {
            $dadosParsed = [];
        }
        $cacheData['dados'] = $dadosParsed;

        // Enriquecer dados com informações do cliente se fornecido
        if (! empty($validated['cliente_id'])) {
            $cliente = Cliente::find($validated['cliente_id']);
            if ($cliente) {
                $dados = $cacheData['dados'];
                if (is_array($dados)) {
                    $dados['cliente_id'] = $validated['cliente_id'];
                    $dados['cliente_nome'] = $cliente->razao_social ?: $cliente->nome;
                    $dados['cliente_documento'] = $cliente->documento_formatado ?? $cliente->documento;
                    $dados['cliente_tipo_pessoa'] = $cliente->tipo_pessoa;
                    $cacheData['dados'] = $dados;
                }
            }
        }

        // Atualizar DB antes do cache para evitar race condition com SSE
        if ($validated['status'] === 'concluido') {
            $this->updateEfdImportacaoFromProgress($validated, $importacaoIdTop);
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
            'has_resumo_final' => ! empty($validated['resumo_final']),
            'importacao_id' => $importacaoIdTop,
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
     * Atualiza EfdImportacao e vincula participantes ao cliente quando n8n envia status final.
     */
    private function updateEfdImportacaoFromProgress(array $validated, ?int $importacaoIdTop = null): void
    {
        $dadosRaw = $validated['dados'] ?? [];
        if (is_string($dadosRaw)) {
            $dados = json_decode($dadosRaw, true) ?? [];
        } else {
            $dados = is_array($dadosRaw) ? $dadosRaw : [];
        }
        // Aceita importacao_id top-level (preferido) ou dentro de dados
        $importacaoId = $importacaoIdTop
            ?? $dados['importacao_id']
            ?? $dados['importacoes_efd_id']
            ?? null;

        if (! $importacaoId) {
            return;
        }

        try {
            $importacao = EfdImportacao::where('id', $importacaoId)
                ->where('user_id', $validated['user_id'])
                ->first();

            if (! $importacao) {
                return;
            }

            $updateData = [
                'status' => 'concluido',
                'concluido_em' => now(),
            ];

            if ($importacao->iniciado_em) {
                $updateData['tempo_processamento_segundos'] = (int) $importacao->iniciado_em->diffInSeconds(now());
            }

            if (! empty($dados['total_processados'])) {
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

            // participante_ids: preferir array top-level (n8n via Execute Query), fallback para string CSV
            $ids = [];
            if (! empty($validated['participante_ids']) && is_array($validated['participante_ids'])) {
                $ids = array_values(array_filter(array_map('intval', $validated['participante_ids'])));
                $updateData['participante_ids'] = $ids;
            } else {
                // Fallback: aceitar "lita" (typo n8n) ou "lista" como string CSV
                $idsStr = $dados['participante_lita_geral_ids']
                    ?? $dados['participante_lista_geral_ids']
                    ?? '';
                if (! empty($idsStr)) {
                    $ids = array_values(array_filter(array_map('intval', explode(',', $idsStr))));
                    $updateData['participante_ids'] = $ids;
                }
            }

            // Persistir resumo_final: prioridade para o campo top-level, fallback para dados
            if (! empty($validated['resumo_final'])) {
                $updateData['resumo_final'] = $validated['resumo_final'];
            } elseif (! empty($dados['estatisticas']) || ! empty($dados['blocos'])) {
                $updateData['resumo_final'] = $dados;
            }
            // Enriquecer contadores a partir de estatisticas (se não vieram nos campos legados)
            if (! empty($dados['estatisticas'])) {
                $est = $dados['estatisticas'];
                if (empty($updateData['total_participantes']) && ! empty($est['total_participantes_processados'])) {
                    $updateData['total_participantes'] = (int) $est['total_participantes_processados'];
                }
                if (! isset($updateData['novos']) && isset($est['participantes_novos'])) {
                    $updateData['novos'] = (int) $est['participantes_novos'];
                }
                if (! isset($updateData['duplicados']) && isset($est['participantes_repetidos'])) {
                    $updateData['duplicados'] = (int) $est['participantes_repetidos'];
                }
            }

            $importacao->update($updateData);

            // Vincular participantes ao cliente
            if ($clienteId && ! empty($ids)) {
                Participante::whereIn('id', $ids)
                    ->where('user_id', $validated['user_id'])
                    ->whereNull('cliente_id')
                    ->update(['cliente_id' => $clienteId]);
            }

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar EfdImportacao do progresso', [
                'importacao_id' => $importacaoId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
