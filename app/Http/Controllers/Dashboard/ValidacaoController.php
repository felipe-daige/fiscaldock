<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\EfdNota;
use App\Models\XmlImportacao;
use App\Models\XmlNota;
use App\Services\CreditService;
use App\Services\ValidacaoContabilService;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValidacaoController extends Controller
{
    public const CLEARANCE_NFE_AVULSA_CUSTO = 14;

    private const AUTH_VIEW_PREFIX = 'autenticado.validacao.';

    private const AUTH_LAYOUT_VIEW = 'autenticado.layouts.app';

    public function __construct(
        protected ValidacaoContabilService $validacaoService,
        protected CreditService $creditService
    ) {}

    /**
     * Dashboard de Clearance DF-e — KPIs unificados XML+EFD por status Receita Federal.
     */
    public function index(Request $request)
    {
        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $userId = Auth::id();
        $user = Auth::user();

        $data = [
            'kpis' => $this->validacaoService->getKpisStatusReceita($userId),
            'notasBloqueantes' => $this->validacaoService->getNotasComSituacaoBloqueante($userId, 5),
            'ultimasVerificacoes' => $this->validacaoService->getUltimasVerificacoes($userId, 10),
            'saldoCreditos' => $this->creditService->getBalance($user),
            'custoConsultaUnitaria' => 14,
        ];

        return $this->render($request, 'index', $data);
    }

    /**
     * Listagem paginada de notas com filtros e bulk-select.
     */
    public function notas(Request $request)
    {
        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $userId = Auth::id();
        $filtros = $this->filtrosListagem($request);

        $sort = $request->input('sort', 'data_emissao');
        $dir = strtolower($request->input('dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $sortMap = [
            'origem' => 'origem',
            'numero' => 'numero',
            'data_emissao' => 'data_emissao',
            'emit_razao_social' => 'emit_razao_social',
            'dest_razao_social' => 'dest_razao_social',
            'valor_total' => 'valor_total',
            'tipo_nota' => 'tipo_nota',
            'status' => null,
        ];

        if (! array_key_exists($sort, $sortMap)) {
            $sort = 'data_emissao';
        }

        $query = $this->queryListagem($userId, $filtros);

        if ($sort === 'status') {
            $query->orderByRaw(
                "CASE
                    WHEN validacao_json IS NULL THEN 0
                    WHEN EXISTS (
                        SELECT 1 FROM jsonb_array_elements((validacao_json::jsonb)->'alertas') a
                        WHERE a->>'nivel' = 'bloqueante'
                    ) THEN 3
                    WHEN EXISTS (
                        SELECT 1 FROM jsonb_array_elements((validacao_json::jsonb)->'alertas') a
                        WHERE a->>'nivel' = 'atencao'
                    ) THEN 2
                    ELSE 1
                 END $dir"
            )->orderByDesc('data_emissao')->orderByDesc('id');
        } else {
            $query->orderBy($sortMap[$sort], $dir)->orderByDesc('id');
        }

        $notas = $query->paginate(50)->withQueryString();

        $notas->getCollection()->transform(function ($row) {
            $row->validacao = $row->validacao_json ? json_decode($row->validacao_json, true) : null;
            unset($row->validacao_json);

            return $row;
        });

        $clientes = \App\Models\Cliente::where('user_id', $userId)
            ->orderBy('razao_social')
            ->get(['id', 'razao_social', 'documento']);

        $data = [
            'notas' => $notas,
            'clientes' => $clientes,
            'filtros' => $filtros,
            'escopoNotas' => $this->buildEscopoNotasResumo($userId),
            'saldoAtual' => $this->creditService->getBalance(Auth::user()),
            'custosTiers' => [
                'basico' => ValidacaoContabilService::custoUnitarioPorTier('basico'),
                'full' => ValidacaoContabilService::custoUnitarioPorTier('full'),
            ],
            'sort' => $sort,
            'dir' => $dir,
        ];

        return $this->render($request, 'notas', $data);
    }

    /**
     * Retorna todos os IDs que batem com os filtros atuais (cross-page select).
     */
    public function todosIds(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userId = Auth::id();
        $filtros = $this->filtrosListagem($request);
        $status = $filtros['status_validacao'] ?? 'todos';

        $xml = $this->xmlSubquery($userId, $filtros);
        if ($status === 'validadas') {
            $xml->whereNotNull('xml_notas.validacao');
        } elseif ($status === 'com_alertas') {
            $xml->whereNotNull('xml_notas.validacao')
                ->whereRaw("EXISTS (SELECT 1 FROM jsonb_array_elements(xml_notas.validacao->'alertas') AS a WHERE a->>'nivel' = 'bloqueante')");
        } elseif ($status === 'nao_validadas') {
            $xml->whereNull('xml_notas.validacao');
        } elseif ($status === 'sem_situacao_receita') {
            $xml->whereRaw("(xml_notas.validacao IS NULL OR xml_notas.validacao->>'situacao' IS NULL)");
        }
        $xmlIds = $xml->pluck('id')->map(fn ($v) => (int) $v)->values();

        $efdIds = collect();
        if (! in_array($status, ['validadas', 'com_alertas'], true)) {
            $efd = $this->efdSubquery($userId, $filtros);
            if ($status === 'sem_situacao_receita') {
                $efd->whereRaw("(efd_notas.validacao IS NULL OR efd_notas.validacao->>'situacao' IS NULL)");
            }
            $efd->whereNotExists(function ($q) use ($userId) {
                $q->select(DB::raw(1))
                    ->from('xml_notas')
                    ->whereColumn('xml_notas.nfe_id', 'efd_notas.chave_acesso')
                    ->where('xml_notas.user_id', $userId);
            });
            $efdIds = $efd->pluck('id')->map(fn ($v) => (int) $v)->values();
        }

        $origens = [];
        foreach ($xmlIds as $id) {
            $origens[$id] = 'xml';
        }
        foreach ($efdIds as $id) {
            $origens[$id] = 'efd';
        }

        $ids = $xmlIds->concat($efdIds)->values();

        return response()->json([
            'success' => true,
            'ids' => $ids,
            'origens' => (object) $origens,
            'total' => $ids->count(),
        ]);
    }

    /**
     * Busca avulsa de NF-e por chave de acesso.
     */
    public function buscarNfe(Request $request)
    {
        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $data = [
            'saldoAtual' => $this->creditService->getBalance(Auth::user()),
            'custoEstimadoCreditos' => self::CLEARANCE_NFE_AVULSA_CUSTO,
            'clientes' => Cliente::where('user_id', Auth::id())
                ->orderBy('razao_social')
                ->get(['id', 'razao_social', 'documento']),
            'ultimasConsultasDfe' => XmlNota::where('user_id', Auth::id())
                ->with('cliente:id,razao_social,documento')
                ->orderByDesc('updated_at')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get(),
        ];

        return $this->render($request, 'buscar-nfe', $data);
    }

    /**
     * Dispara consulta avulsa de DF-e via n8n + InfoSimples.
     *
     * Valida input, debita créditos, cria ConsultaLote (plano_id=null)
     * e envia payload para o webhook n8n. Em falha, estorna créditos.
     */
    public function consultarNfe(Request $request)
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        $validated = $request->validate([
            'tipo_documento' => 'required|string|in:nfe,cte,nfse',
            'chave_acesso' => 'required|string',
            'cliente_id' => 'nullable|integer',
            'tab_id' => 'required|string|max:36',
        ]);

        if ($validated['tipo_documento'] !== 'nfe') {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => ['tipo_documento' => ['Apenas NF-e é suportada no MVP. CT-e e NFS-e estarão disponíveis em breve.']],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $chave = preg_replace('/\D/', '', $validated['chave_acesso']);

        if (strlen($chave) !== 44) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => ['chave_acesso' => ['A chave de acesso deve ter 44 dígitos numéricos.']],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! $this->validarDigitoVerificadorNfe($chave)) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => ['chave_acesso' => ['Dígito verificador da chave de acesso inválido.']],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $clienteId = null;
        if (! empty($validated['cliente_id'])) {
            $clienteId = (int) $validated['cliente_id'];
            $clienteOwned = Cliente::where('id', $clienteId)
                ->where('user_id', $user->id)
                ->exists();

            if (! $clienteOwned) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cliente não encontrado ou não pertence a este usuário.',
                ], Response::HTTP_FORBIDDEN);
            }
        }

        $custo = self::CLEARANCE_NFE_AVULSA_CUSTO;

        if (! $this->creditService->hasEnough($user, $custo)) {
            return response()->json([
                'success' => false,
                'error' => 'Créditos insuficientes.',
                'custo_necessario' => $custo,
                'saldo_atual' => $this->creditService->getBalance($user),
            ], Response::HTTP_PAYMENT_REQUIRED);
        }

        $webhookUrl = config('services.webhook.consultas_notas_url');

        if (empty($webhookUrl)) {
            Log::error('Clearance NF-e: webhook não configurado (WEBHOOK_CONSULTAS_NOTAS_URL)');

            return response()->json([
                'success' => false,
                'error' => 'Configuração de webhook ausente. Contate o suporte.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $debitado = $this->creditService->deduct(
            $user,
            $custo,
            'clearance_nfe_avulsa',
            'Clearance NF-e avulsa · chave …'.substr($chave, -4)
        );

        if (! $debitado) {
            return response()->json([
                'success' => false,
                'error' => 'Falha ao debitar créditos. Tente novamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $lote = null;

        try {
            $lote = ConsultaLote::create([
                'user_id' => $user->id,
                'cliente_id' => $clienteId,
                'plano_id' => null,
                'status' => ConsultaLote::STATUS_PROCESSANDO,
                'total_participantes' => 1,
                'creditos_cobrados' => $custo,
                'tab_id' => $validated['tab_id'],
            ]);

            $payload = [
                'user_id' => $user->id,
                'cliente_id' => $clienteId,
                'tipo_documento' => 'NFE',
                'chave_acesso' => $chave,
                'tab_id' => $validated['tab_id'],
                'consulta_lote_id' => $lote->id,
                'progress_url' => url('/api/consultas/progresso'),
            ];

            $response = Http::timeout(15)
                ->withHeaders([
                    'X-API-Token' => config('services.api.token'),
                    'Content-Type' => 'application/json',
                ])
                ->post($webhookUrl, $payload);

            if (! $response->successful()) {
                $this->creditService->add(
                    $user,
                    $custo,
                    'clearance_nfe_avulsa_refund',
                    'Estorno · webhook clearance indisponível'
                );

                $lote->update([
                    'status' => ConsultaLote::STATUS_ERRO,
                    'error_code' => 'WEBHOOK_ERROR',
                    'error_message' => 'Webhook n8n respondeu '.$response->status(),
                ]);

                Log::error('Clearance NF-e: webhook retornou erro', [
                    'consulta_lote_id' => $lote->id,
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'Erro ao iniciar processamento. Créditos foram estornados.',
                ], Response::HTTP_BAD_GATEWAY);
            }

            Log::info('Clearance NF-e: despachado para n8n', [
                'consulta_lote_id' => $lote->id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'consulta_lote_id' => $lote->id,
                'tab_id' => $validated['tab_id'],
                'progress_url' => url('/app/consulta/progresso/stream?tab_id='.$validated['tab_id']),
                'mensagem' => 'Consulta iniciada.',
                'novo_saldo' => $this->creditService->getBalance($user),
            ]);

        } catch (\Throwable $e) {
            if ($lote) {
                $lote->update([
                    'status' => ConsultaLote::STATUS_ERRO,
                    'error_code' => 'INTERNAL_ERROR',
                    'error_message' => $e->getMessage(),
                ]);
            }

            $this->creditService->add(
                $user,
                $custo,
                'clearance_nfe_avulsa_refund',
                'Estorno · exceção ao despachar clearance'
            );

            Log::error('Clearance NF-e: exceção ao despachar', [
                'user_id' => $user->id,
                'consulta_lote_id' => $lote?->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro interno ao processar consulta.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Retorna o XmlNota persistido pelo n8n após conclusão da consulta.
     *
     * Chamado pelo frontend depois do SSE sinalizar status=concluido.
     */
    public function resultadoUltimaConsulta(Request $request, int $consultaLoteId)
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $userId = Auth::id();

        $lote = ConsultaLote::where('id', $consultaLoteId)
            ->where('user_id', $userId)
            ->first();

        if (! $lote) {
            return response()->json([
                'success' => false,
                'error' => 'Lote não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        $nota = XmlNota::where('user_id', $userId)
            ->where(function ($q) use ($lote) {
                $q->where('updated_at', '>=', $lote->created_at)
                    ->orWhere('created_at', '>=', $lote->created_at);
            })
            ->with('cliente:id,razao_social,documento')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        if (! $nota) {
            return response()->json([
                'success' => false,
                'error' => 'Nota ainda não persistida pelo n8n.',
                'status_lote' => $lote->status,
            ], Response::HTTP_NOT_FOUND);
        }

        $validacao = is_array($nota->validacao) ? $nota->validacao : [];

        return response()->json([
            'success' => true,
            'nota' => [
                'id' => $nota->id,
                'tipo_documento' => $nota->tipo_documento,
                'nfe_id' => $nota->nfe_id,
                'numero_nota' => $nota->numero_nota,
                'serie' => $nota->serie,
                'valor_total' => $nota->valor_total,
                'data_emissao' => $nota->data_emissao?->format('d/m/Y'),
                'emit_razao_social' => $nota->emit_razao_social,
                'emit_cnpj' => $nota->emit_cnpj,
                'dest_razao_social' => $nota->dest_razao_social,
                'dest_cnpj' => $nota->dest_cnpj,
                'cliente_nome' => $nota->cliente?->razao_social,
                'situacao' => $validacao['situacao'] ?? null,
                'classificacao' => $validacao['classificacao'] ?? null,
                'motivo' => $validacao['motivo'] ?? null,
                'consultado_em' => $validacao['consultado_em'] ?? null,
                'detalhe_url' => '/app/validacao/nota/'.$nota->id,
            ],
        ]);
    }

    /**
     * Valida dígito verificador (módulo 11) de uma chave de acesso NF-e de 44 dígitos.
     */
    private function validarDigitoVerificadorNfe(string $chave): bool
    {
        if (strlen($chave) !== 44 || ! ctype_digit($chave)) {
            return false;
        }

        $base = substr($chave, 0, 43);
        $dvInformado = (int) substr($chave, -1);

        $peso = 2;
        $soma = 0;
        for ($i = strlen($base) - 1; $i >= 0; $i--) {
            $soma += ((int) $base[$i]) * $peso;
            $peso = $peso === 9 ? 2 : $peso + 1;
        }

        $resto = $soma % 11;
        $dvCalculado = ($resto === 0 || $resto === 1) ? 0 : 11 - $resto;

        return $dvCalculado === $dvInformado;
    }

    /**
     * Normaliza tipo legado (completa/deep/local) para os tiers atuais (basico/full).
     * Free tier removido — local vira basico.
     */
    private function normalizarTier(?string $tipo): string
    {
        return match ($tipo) {
            'full', 'deep' => 'full',
            default => 'basico',
        };
    }

    private function filtrosListagem(Request $request): array
    {
        return [
            'periodo_de' => $request->input('periodo_de'),
            'periodo_ate' => $request->input('periodo_ate'),
            'cliente_id' => $request->input('cliente_id'),
            'participante_cnpj' => $request->input('participante_cnpj'),
            'tipo_nota' => $request->input('tipo_nota'),
            'status_validacao' => $request->input('status_validacao', 'todos'),
            'situacao_receita' => $request->input('situacao_receita'),
        ];
    }

    private function queryListagem(int $userId, array $f): Builder
    {
        $status = $f['status_validacao'] ?? 'todos';

        $xml = $this->xmlSubquery($userId, $f);

        if ($status === 'validadas') {
            $xml->whereNotNull('xml_notas.validacao');

            return DB::query()->fromSub($xml, 'u');
        }

        if ($status === 'com_alertas') {
            $xml->whereNotNull('xml_notas.validacao')
                ->whereRaw("EXISTS (SELECT 1 FROM jsonb_array_elements(xml_notas.validacao->'alertas') AS a WHERE a->>'nivel' = 'bloqueante')");

            return DB::query()->fromSub($xml, 'u');
        }

        if ($status === 'nao_validadas') {
            $xml->whereNull('xml_notas.validacao');
        }

        if ($status === 'sem_situacao_receita') {
            $xml->whereRaw("(xml_notas.validacao IS NULL OR xml_notas.validacao->>'situacao' IS NULL)");
        }

        $efd = $this->efdSubquery($userId, $f);

        if ($status === 'sem_situacao_receita') {
            $efd->whereRaw("(efd_notas.validacao IS NULL OR efd_notas.validacao->>'situacao' IS NULL)");
        }

        $efd->whereNotExists(function ($q) use ($userId) {
            $q->select(DB::raw(1))
                ->from('xml_notas')
                ->whereColumn('xml_notas.nfe_id', 'efd_notas.chave_acesso')
                ->where('xml_notas.user_id', $userId);
        });

        return DB::query()->fromSub($xml->unionAll($efd), 'u');
    }

    private function xmlSubquery(int $userId, array $f): \Illuminate\Database\Query\Builder
    {
        $q = DB::table('xml_notas')
            ->selectRaw("
                'xml'::text                                   as origem,
                xml_notas.id                                   as id,
                xml_notas.nfe_id                               as chave,
                xml_notas.numero_nota                          as numero,
                xml_notas.serie::text                          as serie,
                xml_notas.tipo_documento                       as modelo,
                xml_notas.data_emissao                         as data_emissao,
                xml_notas.valor_total                          as valor_total,
                CASE xml_notas.tipo_nota WHEN 0 THEN 'entrada' ELSE 'saida' END as tipo_nota,
                xml_notas.emit_razao_social                    as emit_razao_social,
                xml_notas.dest_razao_social                    as dest_razao_social,
                COALESCE(xml_notas.emit_cnpj, xml_notas.dest_cnpj) as participante_cnpj,
                COALESCE(xml_notas.emit_cliente_id, xml_notas.dest_cliente_id) as cliente_id,
                xml_notas.validacao::text                      as validacao_json
            ")
            ->where('xml_notas.user_id', $userId);

        $this->applyCommonFiltersXml($q, $f);

        return $q;
    }

    private function efdSubquery(int $userId, array $f): \Illuminate\Database\Query\Builder
    {
        $q = DB::table('efd_notas')
            ->leftJoin('participantes', 'participantes.id', '=', 'efd_notas.participante_id')
            ->leftJoin('clientes', 'clientes.id', '=', 'efd_notas.cliente_id')
            ->selectRaw("
                'efd'::text                                   as origem,
                efd_notas.id                                   as id,
                efd_notas.chave_acesso                         as chave,
                efd_notas.numero                               as numero,
                efd_notas.serie                                as serie,
                efd_notas.modelo                               as modelo,
                efd_notas.data_emissao::timestamp              as data_emissao,
                efd_notas.valor_total                          as valor_total,
                efd_notas.tipo_operacao                        as tipo_nota,
                CASE WHEN efd_notas.tipo_operacao = 'entrada'
                     THEN participantes.razao_social
                     ELSE clientes.razao_social END            as emit_razao_social,
                CASE WHEN efd_notas.tipo_operacao = 'saida'
                     THEN participantes.razao_social
                     ELSE clientes.razao_social END            as dest_razao_social,
                participantes.documento                        as participante_cnpj,
                efd_notas.cliente_id                           as cliente_id,
                NULL::text                                     as validacao_json
            ")
            ->where('efd_notas.user_id', $userId);

        $this->applyCommonFiltersEfd($q, $f);

        return $q;
    }

    private function applyCommonFiltersXml(\Illuminate\Database\Query\Builder $q, array $f): void
    {
        if (! empty($f['periodo_de']) && ! empty($f['periodo_ate'])) {
            $q->whereBetween('xml_notas.data_emissao', [$f['periodo_de'].' 00:00:00', $f['periodo_ate'].' 23:59:59']);
        } elseif (! empty($f['periodo_de'])) {
            $q->where('xml_notas.data_emissao', '>=', $f['periodo_de'].' 00:00:00');
        } elseif (! empty($f['periodo_ate'])) {
            $q->where('xml_notas.data_emissao', '<=', $f['periodo_ate'].' 23:59:59');
        }

        if (! empty($f['cliente_id'])) {
            $q->where(function ($sub) use ($f) {
                $sub->where('xml_notas.emit_cliente_id', $f['cliente_id'])
                    ->orWhere('xml_notas.dest_cliente_id', $f['cliente_id']);
            });
        }

        if (! empty($f['participante_cnpj'])) {
            $cnpj = preg_replace('/\D/', '', $f['participante_cnpj']);
            $q->where(function ($sub) use ($cnpj) {
                $sub->where('xml_notas.emit_cnpj', $cnpj)->orWhere('xml_notas.dest_cnpj', $cnpj);
            });
        }

        if (($f['tipo_nota'] ?? null) === 'entrada') {
            $q->where('xml_notas.tipo_nota', XmlNota::TIPO_ENTRADA);
        } elseif (($f['tipo_nota'] ?? null) === 'saida') {
            $q->where('xml_notas.tipo_nota', XmlNota::TIPO_SAIDA);
        }

        if (! empty($f['situacao_receita'])) {
            $q->whereRaw("xml_notas.validacao->>'situacao' = ?", [$f['situacao_receita']]);
        }
    }

    private function applyCommonFiltersEfd(\Illuminate\Database\Query\Builder $q, array $f): void
    {
        if (! empty($f['periodo_de']) && ! empty($f['periodo_ate'])) {
            $q->whereBetween('efd_notas.data_emissao', [$f['periodo_de'], $f['periodo_ate']]);
        } elseif (! empty($f['periodo_de'])) {
            $q->where('efd_notas.data_emissao', '>=', $f['periodo_de']);
        } elseif (! empty($f['periodo_ate'])) {
            $q->where('efd_notas.data_emissao', '<=', $f['periodo_ate']);
        }

        if (! empty($f['cliente_id'])) {
            $q->where('efd_notas.cliente_id', $f['cliente_id']);
        }

        if (! empty($f['participante_cnpj'])) {
            $cnpj = preg_replace('/\D/', '', $f['participante_cnpj']);
            $q->where('participantes.documento', $cnpj);
        }

        if (($f['tipo_nota'] ?? null) === 'entrada') {
            $q->where('efd_notas.tipo_operacao', 'entrada');
        } elseif (($f['tipo_nota'] ?? null) === 'saida') {
            $q->where('efd_notas.tipo_operacao', 'saida');
        }

        if (! empty($f['situacao_receita'])) {
            $q->whereRaw("efd_notas.validacao->>'situacao' = ?", [$f['situacao_receita']]);
        }
    }

    private function buildEscopoNotasResumo(int $userId): array
    {
        $totalXml = XmlNota::where('user_id', $userId)->count();
        $totalEfd = EfdNota::where('user_id', $userId)->count();

        return [
            'total_xml' => $totalXml,
            'total_efd' => $totalEfd,
            'total_unificado' => $totalXml + $totalEfd,
            'possui_apenas_efd' => $totalXml === 0 && $totalEfd > 0,
        ];
    }

    /**
     * Calcula o custo de validacao.
     * Aceita nota_ids OU importacao_id.
     */
    public function calcularCusto(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'nota_ids' => 'array',
            'nota_ids.*' => 'integer',
            'origens' => 'array',
            'importacao_id' => 'integer',
            'tipo' => 'in:basico,full,completa,deep,local',
        ]);

        $userId = Auth::id();
        $tipo = $this->normalizarTier($request->input('tipo'));
        $origens = $request->input('origens', []);

        if ($request->has('nota_ids') && ! empty($request->input('nota_ids'))) {
            $notaIds = $request->input('nota_ids');
        } elseif ($request->has('importacao_id')) {
            $notaIds = XmlNota::where('importacao_xml_id', $request->input('importacao_id'))
                ->where('user_id', $userId)
                ->pluck('id')
                ->toArray();

            if (empty($notaIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhuma nota encontrada nesta importacao',
                ]);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Informe nota_ids ou importacao_id',
            ], 422);
        }

        $custo = $this->validacaoService->calcularCusto($notaIds, $origens, $userId, $tipo);
        $saldoAtual = $this->creditService->getBalance(Auth::user());

        return response()->json([
            'success' => true,
            'custo' => $custo,
            'saldo_atual' => $saldoAtual,
            'saldo_suficiente' => $saldoAtual >= $custo['custo_total'],
        ]);
    }

    /**
     * Executa validacao de notas especificas.
     */
    public function validarNotas(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'nota_ids' => 'required|array',
            'nota_ids.*' => 'integer',
            'origens' => 'array',
            'tipo' => 'in:basico,full,completa,deep,local',
            'tab_id' => 'nullable|string|max:36',
        ]);

        $userId = Auth::id();
        $notaIds = $request->input('nota_ids');
        $origens = $request->input('origens', []);
        $tipo = $this->normalizarTier($request->input('tipo'));
        $tabId = $request->input('tab_id');
        $user = Auth::user();

        $custo = $this->validacaoService->calcularCusto($notaIds, $origens, $userId, $tipo);

        if ($custo['custo_total'] > 0) {
            if (! $this->creditService->hasEnough($user, $custo['custo_total'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Creditos insuficientes',
                    'custo_necessario' => $custo['custo_total'],
                    'saldo_atual' => $this->creditService->getBalance($user),
                ], 402);
            }

            $this->creditService->deduct($user, $custo['custo_total']);
        }

        $resultado = $this->validacaoService->validarNotas($notaIds, $origens, $userId, $tipo);

        $responseBase = array_merge($resultado, [
            'creditos_utilizados' => $custo['custo_total'],
        ]);

        $webhookUrl = config('services.webhook.consultas_notas_url');

        if (empty($webhookUrl)) {
            Log::warning('Clearance bulk: webhook nao configurado (WEBHOOK_CONSULTAS_NOTAS_URL)');

            return response()->json(array_merge($responseBase, [
                'webhook_disparado' => false,
            ]));
        }

        $notasPayload = $this->montarPayloadNotasClearance($notaIds, $origens, $userId);

        if (empty($notasPayload)) {
            Log::warning('Clearance bulk: nenhuma nota com chave de acesso valida', [
                'user_id' => $userId,
                'nota_ids' => $notaIds,
            ]);

            return response()->json(array_merge($responseBase, [
                'webhook_disparado' => false,
                'motivo' => 'Nenhuma nota selecionada possui chave de acesso valida para clearance externo.',
            ]));
        }

        $lote = null;

        try {
            $lote = ConsultaLote::create([
                'user_id' => $userId,
                'cliente_id' => null,
                'plano_id' => null,
                'status' => ConsultaLote::STATUS_PROCESSANDO,
                'total_participantes' => count($notasPayload),
                'creditos_cobrados' => $custo['custo_total'],
                'tab_id' => $tabId,
            ]);

            $payload = [
                'user_id' => $userId,
                'consulta_lote_id' => $lote->id,
                'tab_id' => $tabId,
                'tipo_validacao' => $tipo,
                'total_notas' => count($notasPayload),
                'notas' => $notasPayload,
                'progress_url' => url('/api/consultas/progresso'),
            ];

            $response = Http::timeout(15)
                ->withHeaders([
                    'X-API-Token' => config('services.api.token'),
                    'Content-Type' => 'application/json',
                ])
                ->post($webhookUrl, $payload);

            if (! $response->successful()) {
                if ($custo['custo_total'] > 0) {
                    $this->creditService->add(
                        $user,
                        $custo['custo_total'],
                        'clearance_bulk_refund',
                        'Estorno - webhook clearance bulk indisponivel'
                    );
                }

                $lote->update([
                    'status' => ConsultaLote::STATUS_ERRO,
                    'error_code' => 'WEBHOOK_ERROR',
                    'error_message' => 'Webhook n8n respondeu '.$response->status(),
                ]);

                Log::error('Clearance bulk: webhook retornou erro', [
                    'consulta_lote_id' => $lote->id,
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'Erro ao iniciar clearance externo. Creditos foram estornados.',
                    'refund_aplicado' => true,
                    'novo_saldo' => $this->creditService->getBalance($user),
                ], Response::HTTP_BAD_GATEWAY);
            }

            Log::info('Clearance bulk: despachado para n8n', [
                'consulta_lote_id' => $lote->id,
                'user_id' => $userId,
                'total_notas' => count($notasPayload),
            ]);

            return response()->json(array_merge($responseBase, [
                'webhook_disparado' => true,
                'consulta_lote_id' => $lote->id,
                'tab_id' => $tabId,
                'progress_url' => $tabId ? url('/app/consulta/progresso/stream?tab_id='.$tabId) : null,
                'novo_saldo' => $this->creditService->getBalance($user),
            ]));
        } catch (\Throwable $e) {
            if ($lote) {
                $lote->update([
                    'status' => ConsultaLote::STATUS_ERRO,
                    'error_code' => 'INTERNAL_ERROR',
                    'error_message' => $e->getMessage(),
                ]);
            }

            if ($custo['custo_total'] > 0) {
                $this->creditService->add(
                    $user,
                    $custo['custo_total'],
                    'clearance_bulk_refund',
                    'Estorno - excecao ao despachar clearance bulk'
                );
            }

            Log::error('Clearance bulk: excecao ao despachar', [
                'user_id' => $userId,
                'consulta_lote_id' => $lote?->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro interno ao despachar clearance externo.',
                'refund_aplicado' => true,
                'novo_saldo' => $this->creditService->getBalance($user),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Monta o array de notas do payload enviado ao n8n.
     * Agrupa XmlNota + EfdNota por chave de acesso de 44 digitos.
     */
    private function montarPayloadNotasClearance(array $notaIds, array $origens, int $userId): array
    {
        $xmlIds = [];
        $efdIds = [];
        foreach ($notaIds as $id) {
            $origem = $origens[$id] ?? $origens[(string) $id] ?? 'xml';
            if ($origem === 'efd') {
                $efdIds[] = (int) $id;
            } else {
                $xmlIds[] = (int) $id;
            }
        }

        $payload = [];

        if (! empty($xmlIds)) {
            $xml = XmlNota::whereIn('id', $xmlIds)
                ->where('user_id', $userId)
                ->get(['id', 'nfe_id', 'tipo_documento', 'emit_cliente_id', 'dest_cliente_id']);

            foreach ($xml as $nota) {
                $chave = preg_replace('/\D/', '', (string) $nota->nfe_id);
                if (strlen($chave) !== 44) {
                    continue;
                }
                $payload[] = [
                    'id' => $nota->id,
                    'origem' => 'xml',
                    'chave_acesso' => $chave,
                    'tipo_documento' => strtoupper((string) ($nota->tipo_documento ?: 'NFE')),
                    'cliente_id' => $nota->emit_cliente_id ?: $nota->dest_cliente_id,
                ];
            }
        }

        if (! empty($efdIds)) {
            $efd = EfdNota::whereIn('id', $efdIds)
                ->where('user_id', $userId)
                ->get(['id', 'chave_acesso', 'modelo', 'cliente_id']);

            foreach ($efd as $nota) {
                $chave = preg_replace('/\D/', '', (string) $nota->chave_acesso);
                if (strlen($chave) !== 44) {
                    continue;
                }
                $payload[] = [
                    'id' => $nota->id,
                    'origem' => 'efd',
                    'chave_acesso' => $chave,
                    'tipo_documento' => match ((string) $nota->modelo) {
                        '55' => 'NFE',
                        '57' => 'CTE',
                        '65' => 'NFCE',
                        default => 'NFE',
                    },
                    'cliente_id' => $nota->cliente_id,
                ];
            }
        }

        return $payload;
    }

    /**
     * Executa validacao de todas as notas de uma importacao.
     */
    public function validarImportacao(Request $request, int $id)
    {
        if (! Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userId = Auth::id();
        $user = Auth::user();

        // Verificar se a importacao pertence ao usuario
        $importacao = XmlImportacao::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        // Obter IDs das notas
        $notaIds = XmlNota::where('importacao_xml_id', $id)
            ->where('user_id', $userId)
            ->pluck('id')
            ->toArray();

        if (empty($notaIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhuma nota encontrada nesta importacao',
            ], 404);
        }

        $request->validate([
            'tipo' => 'in:basico,full,completa,deep,local',
        ]);
        $tipo = $this->normalizarTier($request->input('tipo'));

        // Calcular e cobrar creditos
        $origens = array_fill_keys($notaIds, 'xml');
        $custo = $this->validacaoService->calcularCusto($notaIds, $origens, $userId, $tipo);

        if ($custo['custo_total'] > 0) {
            if (! $this->creditService->hasEnough($user, $custo['custo_total'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Creditos insuficientes',
                    'custo_necessario' => $custo['custo_total'],
                    'saldo_atual' => $this->creditService->getBalance($user),
                ], 402);
            }

            $this->creditService->deduct($user, $custo['custo_total']);
        }

        // Executar validacao
        $resultado = $this->validacaoService->validarImportacao($id, $userId, $tipo);

        return response()->json(array_merge($resultado, [
            'creditos_utilizados' => $custo['custo_total'],
        ]));
    }

    /**
     * Detalhes de validacao de uma nota especifica.
     */
    public function notaDetalhes(Request $request, int $id)
    {
        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $userId = Auth::id();

        if ($request->input('origem') === 'efd') {
            $efdNota = EfdNota::where('id', $id)
                ->where('user_id', $userId)
                ->firstOrFail();

            return redirect('/app/notas-fiscais?chave='.$efdNota->chave_acesso);
        }

        $nota = XmlNota::where('id', $id)
            ->where('user_id', $userId)
            ->with(['emitente', 'destinatario', 'importacaoXml'])
            ->firstOrFail();

        $validacao = $nota->validacao;
        if (! $validacao) {
            $validacao = $this->validacaoService->validarNota($nota);
            $validacao['preview'] = true;
        }

        $categorias = $this->validacaoService->getCategorias();

        $data = [
            'nota' => $nota,
            'validacao' => $validacao,
            'categorias' => $categorias,
        ];

        return $this->render($request, 'nota', $data);
    }

    /**
     * Lista de alertas do usuario.
     */
    public function alertas(Request $request)
    {
        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $userId = Auth::id();

        // Filtros
        $nivel = $request->input('nivel'); // bloqueante, atencao, info
        $categoria = $request->input('categoria');

        $query = XmlNota::where('user_id', $userId)
            ->whereNotNull('validacao')
            ->with(['emitente', 'destinatario']);

        // Filtrar por nivel
        if ($nivel) {
            $query->whereRaw("EXISTS (SELECT 1 FROM jsonb_array_elements(validacao->'alertas') AS a WHERE a->>'nivel' = ?)", [$nivel]);
        }

        // Filtrar por categoria
        if ($categoria) {
            $query->whereRaw("EXISTS (SELECT 1 FROM jsonb_array_elements(validacao->'alertas') AS a WHERE a->>'categoria' = ?)", [$categoria]);
        }

        $notas = $query->orderByDesc('updated_at')->paginate(20);

        // Contar alertas por nivel
        $contadores = XmlNota::where('user_id', $userId)
            ->whereNotNull('validacao')
            ->select(
                \DB::raw("(SELECT COUNT(*) FROM jsonb_array_elements(validacao->'alertas') AS a WHERE a->>'nivel' = 'bloqueante') as bloqueantes"),
                \DB::raw("(SELECT COUNT(*) FROM jsonb_array_elements(validacao->'alertas') AS a WHERE a->>'nivel' = 'atencao') as atencao"),
                \DB::raw("(SELECT COUNT(*) FROM jsonb_array_elements(validacao->'alertas') AS a WHERE a->>'nivel' = 'info') as info")
            )
            ->first();

        $data = [
            'notas' => $notas,
            'contadores' => [
                'bloqueante' => (int) ($contadores->bloqueantes ?? 0),
                'atencao' => (int) ($contadores->atencao ?? 0),
                'info' => (int) ($contadores->info ?? 0),
            ],
            'filtroNivel' => $nivel,
            'filtroCategoria' => $categoria,
            'categorias' => $this->validacaoService->getCategorias(),
        ];

        return $this->render($request, 'alertas', $data);
    }

    /**
     * Dashboard resumido (AJAX).
     */
    public function dashboard(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userId = Auth::id();

        $estatisticas = $this->validacaoService->getEstatisticas($userId);

        // Distribuicao por classificacao
        $distribuicao = [
            ['classificacao' => 'Conforme', 'quantidade' => $estatisticas['conforme'], 'cor' => '#22c55e'],
            ['classificacao' => 'Atencao', 'quantidade' => $estatisticas['atencao'], 'cor' => '#eab308'],
            ['classificacao' => 'Irregular', 'quantidade' => $estatisticas['irregular'], 'cor' => '#f97316'],
            ['classificacao' => 'Critico', 'quantidade' => $estatisticas['critico'], 'cor' => '#ef4444'],
        ];

        // Ultimas notas validadas
        $ultimasValidadas = XmlNota::where('user_id', $userId)
            ->whereNotNull('validacao')
            ->with(['emitente:id,cnpj,razao_social'])
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get()
            ->map(fn ($nota) => [
                'id' => $nota->id,
                'numero' => $nota->numero_nota,
                'emitente' => $nota->emitente->razao_social ?? $nota->emit_cnpj,
                'valor' => $nota->valor_formatado,
                'score' => $nota->validacao_score,
                'classificacao' => $nota->validacao_classificacao,
            ]);

        return response()->json([
            'estatisticas' => $estatisticas,
            'distribuicao' => $distribuicao,
            'ultimas_validadas' => $ultimasValidadas,
        ]);
    }

    /**
     * Verifica se e requisicao AJAX.
     */
    private function isAjaxRequest(Request $request): bool
    {
        if (method_exists($request, 'ajax') && $request->ajax()) {
            return true;
        }

        return $request->header('X-Requested-With') === 'XMLHttpRequest' ||
               $request->wantsJson() ||
               $request->expectsJson();
    }

    /**
     * Renderiza view com suporte a AJAX.
     */
    private function render(Request $request, string $viewName, array $data = [])
    {
        $view = self::AUTH_VIEW_PREFIX.$viewName;

        if (! view()->exists($view)) {
            abort(404);
        }

        if ($this->isAjaxRequest($request)) {
            return view($view, $data);
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $view,
        ], $data));
    }

    /**
     * Redireciona para login.
     */
    private function redirectToLogin(Request $request)
    {
        if ($this->isAjaxRequest($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Voce nao esta logado',
                'redirect' => '/login',
            ]);
        }

        return redirect('/login');
    }
}
