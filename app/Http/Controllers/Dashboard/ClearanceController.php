<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Concerns\RespondeAjax;
use App\Http\Controllers\Concerns\SetsDownloadToken;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessarConsultaJob;
use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\EfdNota;
use App\Models\Participante;
use App\Models\XmlImportacao;
use App\Models\XmlNota;
use App\Services\Bi\CruzamentosConsultasClearanceService;
use App\Services\Catalogo\ReconciliacaoXmlEfdService;
use App\Services\Clearance\ClearanceLoteService;
use App\Services\Clearance\DivergenciaService;
use App\Services\Clearance\RelatorioExecutivoService;
use App\Services\Consultas\FecharLoteService;
use App\Services\PricingCatalogService;
use App\Services\SaldoService;
use App\Services\NotaFiscalService;
use App\Services\ValidacaoContabilService;
use App\Support\PdfReport;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ClearanceController extends Controller
{
    use RespondeAjax;
    use SetsDownloadToken;

    public const CLEARANCE_NFE_AVULSA_CUSTO = 14;

    private const BUSCA_AVULSA_CACHE_TTL_MINUTES = 120;

    private const AUTH_VIEW_PREFIX = 'autenticado.clearance.';

    private const AUTH_LAYOUT_VIEW = 'autenticado.layouts.app';

    public function __construct(
        protected ValidacaoContabilService $validacaoService,
        protected SaldoService $saldoService,
        protected NotaFiscalService $notaFiscalService,
        protected ReconciliacaoXmlEfdService $reconciliacaoService,
        protected PricingCatalogService $pricingCatalogService
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
            'saldoCreditos' => $this->saldoService->getBalance($user),
            // Verificação em lote (na Listagem de Notas): básico/completo por documento.
            // Verificação avulsa por chave (botão "Verificar nota"): custo próprio.
            'custosTiers' => [
                'basico' => ValidacaoContabilService::custoUnitarioPorTier('basico'),
                'full' => ValidacaoContabilService::custoUnitarioPorTier('full'),
            ],
            'custoConsultaUnitaria' => self::CLEARANCE_NFE_AVULSA_CUSTO,
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
            'modelo' => 'modelo',
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

            $badge = $this->modeloBadge($row->modelo ?? null);
            $row->modelo_label = $badge['label'];
            $row->modelo_hex = $badge['hex'];

            return $row;
        });

        $this->enriquecerDetalhesListagem($notas->getCollection(), $userId);

        $clientes = \App\Models\Cliente::where('user_id', $userId)
            ->orderBy('razao_social')
            ->get(['id', 'razao_social', 'documento']);

        $data = [
            'notas' => $notas,
            'clientes' => $clientes,
            'filtros' => $filtros,
            'escopoNotas' => $this->buildEscopoNotasResumo($userId),
            'saldoAtual' => $this->saldoService->getBalance(Auth::user()),
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
            $xml->whereRaw('NOT '.$this->snapshotExistsSql('xml_notas'));
        }
        $xmlIds = $xml->pluck('id')->map(fn ($v) => (int) $v)->values();

        $efdIds = collect();
        if (! in_array($status, ['validadas', 'com_alertas'], true)) {
            $efd = $this->efdSubquery($userId, $filtros);
            if ($status === 'sem_situacao_receita') {
                $efd->whereRaw('NOT '.$this->snapshotExistsSql('efd_notas'));
            }
            $efd->whereNotExists(function ($q) use ($userId) {
                $q->select(DB::raw(1))
                    ->from('xml_notas')
                    ->whereColumn('xml_notas.chave_acesso', 'efd_notas.chave_acesso')
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
            'saldoAtual' => $this->saldoService->getBalance(Auth::user()),
            'custoEstimadoCreditos' => self::CLEARANCE_NFE_AVULSA_CUSTO,
            'clientes' => Cliente::where('user_id', Auth::id())
                ->orderByDesc('is_empresa_propria')
                ->orderBy('razao_social')
                ->get(['id', 'razao_social', 'documento', 'is_empresa_propria']),
            'defaultClienteId' => Auth::user()->empresaPropria()?->id,
            'ultimasConsultasDfe' => $this->listarUltimasConsultasDfe(Auth::id(), 3),
        ];

        return $this->render($request, 'buscar', $data);
    }

    public function historico(Request $request)
    {
        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $userId = Auth::id();
        $filtros = $this->filtrosHistoricoConsultasDfe($request);

        $query = $this->notaFiscalService->consultaDfeHistoricoQuery($userId);
        $this->aplicarFiltrosHistoricoConsultasDfe($query, $filtros);

        $consultas = $query
            ->orderByRaw('COALESCE(consultado_em, created_at) DESC')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $consultas->getCollection()->transform(
            fn ($consulta) => $this->notaFiscalService->formatarHistoricoConsultaDfe($consulta, $userId)
        );

        return $this->render($request, 'historico', [
            'consultas' => $consultas,
            'filtros' => $filtros,
            'filtrosAtivos' => collect($filtros)->filter(fn ($value) => $value !== null && $value !== '')->count(),
            'statusOptions' => $this->statusOptionsHistoricoDfe($userId),
        ]);
    }

    /**
     * Precheck da busca avulsa: informa se a chave já está no acervo (xml/efd) e/ou já tem
     * snapshot SEFAZ. A UI intercepta o submit com essa resposta e oferece o clearance da
     * própria nota (tier básico, mais barato) em vez da consulta avulsa. Não cobra nem cria lote.
     */
    public function buscarPrecheck(Request $request)
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (! config('clearance.busca_avulsa.habilitada')) {
            return response()->json([
                'success' => false,
                'error' => 'Busca avulsa em desenvolvimento. Em breve.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $validated = $request->validate([
            'chave_acesso' => 'required|string',
        ]);

        $chave = preg_replace('/\D/', '', $validated['chave_acesso']);

        if (strlen($chave) !== 44) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => ['chave_acesso' => ['A chave deve ter 44 dígitos numéricos.']],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $userId = Auth::id();
        $acervo = $this->localizarNotasNoAcervo($userId, collect([$chave]));
        $snapshot = $this->snapshotResumoDfe($userId, $chave);

        if (! isset($acervo[$chave])) {
            return response()->json([
                'success' => true,
                'no_acervo' => false,
                'snapshot' => $snapshot,
                'custo_avulsa' => self::CLEARANCE_NFE_AVULSA_CUSTO,
            ]);
        }

        $origem = $acervo[$chave]['origem'];
        $nota = $acervo[$chave]['nota'];
        $resumo = $this->formatarResultadoAcervoExistente($nota, $origem, 1);

        return response()->json([
            'success' => true,
            'no_acervo' => true,
            'origem' => $origem,
            'nota_id' => $nota->id,
            'detalhe_url' => $resumo->detalhe_url,
            'listagem_url' => route('app.notas.index', ['busca' => $chave]),
            'nota' => [
                'tipo_documento' => $resumo->tipo_documento,
                'numero' => $resumo->numero,
                'serie' => $resumo->serie,
                'valor_total_label' => $resumo->valor_total_label,
                'data_emissao_label' => $resumo->data_emissao_label,
                'emit_nome' => $resumo->emit_nome,
                'dest_nome' => $resumo->dest_nome,
                'origem_acervo_label' => $resumo->origem_acervo_label,
                'origem_acervo_hex' => $resumo->origem_acervo_hex,
            ],
            'snapshot' => $snapshot,
            'custo_clearance' => ValidacaoContabilService::custoUnitarioPorTier('basico'),
            'custo_avulsa' => self::CLEARANCE_NFE_AVULSA_CUSTO,
        ]);
    }

    /**
     * Dados pro bloco "quem é seu cliente?" da tela de resultado da busca avulsa: só aparece
     * quando NENHUM dos CNPJs do documento (emitente/destinatário) já é cliente do usuário —
     * aí ele escolhe qual lado vira Cliente; o outro fica como Participante (contraparte).
     */
    private function montarClassificacaoPartes(int $userId, object $nota): ?array
    {
        $fmtCnpj = fn (string $d) => preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $d);
        $resolver = app(\App\Services\Clearance\CnpjMascaradoResolver::class);

        $lados = [];
        $emit = preg_replace('/\D/', '', (string) ($nota->emit_cnpj ?? ''));
        $dest = preg_replace('/\D/', '', (string) ($nota->dest_cnpj ?? ''));

        if (strlen($emit) === 14) {
            $lados[] = ['lado' => 'emit', 'papel' => 'Emitente', 'cnpj' => $emit, 'cnpj_fmt' => $fmtCnpj($emit), 'nome' => $nota->emit_nome ?: '—'];
        }
        if (strlen($dest) === 14 && $dest !== $emit) {
            $lados[] = ['lado' => 'dest', 'papel' => 'Destinatário', 'cnpj' => $dest, 'cnpj_fmt' => $fmtCnpj($dest), 'nome' => $nota->dest_nome ?: '—'];
        }

        // Lado mascarado pela SEFAZ (consulta sem certificado): tenta identificar o cadastro
        // real por sufixo do CNPJ + prefixo do nome. Cliente identificado encerra o bloco;
        // participante identificado exibe os dados reais; não identificado fica inelegível
        // (não dá pra cadastrar cliente com CNPJ incompleto).
        foreach ($lados as &$lado) {
            if (! $resolver->estaMascarado($lado['cnpj'])) {
                continue;
            }

            if ($resolver->identificarCliente($userId, $lado['cnpj'], $lado['nome'])) {
                return null;
            }

            if ($p = $resolver->identificarParticipante($userId, $lado['cnpj'], $lado['nome'])) {
                $lado['cnpj'] = $p->documento;
                $lado['cnpj_fmt'] = $fmtCnpj($p->documento);
                $lado['nome'] = $p->razao_social ?: $lado['nome'];
                $lado['identificado_acervo'] = true;
            } else {
                $lado['mascarado'] = true;
            }
        }
        unset($lado);

        if ($lados === []) {
            return null;
        }

        $jaEhCliente = Cliente::where('user_id', $userId)
            ->whereIn('documento', array_column($lados, 'cnpj'))
            ->exists();

        if ($jaEhCliente) {
            return null;
        }

        return [
            'chave_acesso' => $nota->chave_acesso,
            'lados' => $lados,
        ];
    }

    /**
     * Classifica as partes de um snapshot consultado: o lado escolhido ('emit'|'dest') vira
     * Cliente do usuário (criado a partir dos dados da SEFAZ), o snapshot é reassociado a ele
     * e o participante auto-criado com esse CNPJ é removido. O outro lado permanece participante.
     */
    public function classificarPartesBusca(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['success' => false, 'error' => 'Usuário não autenticado.'], Response::HTTP_UNAUTHORIZED);
        }

        $validated = $request->validate([
            'chave_acesso' => 'required|string',
            'lado' => 'required|in:emit,dest',
        ]);

        $chave = preg_replace('/\D/', '', $validated['chave_acesso']);
        $userId = Auth::id();

        $isCte = strlen($chave) === 44 && substr($chave, 20, 2) === '57';
        $snap = $isCte
            ? \App\Models\CteConsulta::where('user_id', $userId)->where('chave_acesso', $chave)->first()
            : \App\Models\NfeConsulta::where('user_id', $userId)->where('chave_acesso', $chave)->first();

        if (! $snap) {
            return response()->json(['success' => false, 'error' => 'Consulta não encontrada para esta chave.'], Response::HTTP_NOT_FOUND);
        }

        $lado = $validated['lado'];
        $cnpj = preg_replace('/\D/', '', (string) ($lado === 'emit' ? $snap->emit_cnpj : $snap->dest_cnpj));
        $nome = $lado === 'emit' ? $snap->emit_nome : $snap->dest_nome;

        if (strlen($cnpj) !== 14) {
            return response()->json([
                'success' => false,
                'error' => 'Este lado do documento não tem um CNPJ válido para virar cliente.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // CNPJ mascarado pela SEFAZ (consulta sem certificado): só vira cliente se o
        // sufixo + nome identificarem com segurança um participante já cadastrado —
        // aí usamos o documento real dele. Sem identificação, não cadastramos lixo.
        $resolver = app(\App\Services\Clearance\CnpjMascaradoResolver::class);
        if ($resolver->estaMascarado($cnpj)) {
            $identificado = $resolver->identificarParticipante($userId, $cnpj, $nome);

            if (! $identificado) {
                return response()->json([
                    'success' => false,
                    'error' => 'O CNPJ deste lado veio mascarado pela SEFAZ (consulta sem certificado) e não foi identificado no seu acervo. Cadastre o cliente manualmente com o CNPJ completo.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $cnpj = $identificado->documento;
            $nome = $identificado->razao_social ?: $nome;
        }

        $cliente = Cliente::where('user_id', $userId)->where('documento', $cnpj)->first();

        if (! $cliente) {
            $cliente = Cliente::create([
                'user_id' => $userId,
                'tipo_pessoa' => 'PJ',
                'documento' => $cnpj,
                'razao_social' => $nome ?: null,
                'uf' => $lado === 'emit' ? ($snap->emit_uf ?? null) : ($snap->dest_uf ?? null),
                'municipio' => $lado === 'emit' ? ($snap->emit_municipio ?? null) : ($snap->dest_municipio ?? null),
                'inscricao_estadual' => $lado === 'emit' ? ($snap->emit_ie ?? null) : null,
            ]);
        }

        // Reassocia o snapshot ao cliente correto e remove o participante auto-criado
        // com esse CNPJ (agora ele é cliente, não contraparte). Só apaga o que este
        // fluxo criou (origem_ref.fonte) — participante manual/EFD não é tocado.
        $snap->update(['cliente_id' => $cliente->id]);
        Participante::where('user_id', $userId)
            ->where('documento', $cnpj)
            ->where('origem_ref->fonte', 'clearance_snapshot')
            ->delete();

        // Garante o outro lado como participante, agora vinculado ao cliente novo.
        app(\App\Services\Clearance\ParticipanteAutoCadastroService::class)->criarDesdeColunas(
            $snap->getAttributes(),
            $isCte ? 'CTE' : 'NFE',
            $chave,
            $userId,
            $cliente->id
        );

        return response()->json([
            'success' => true,
            'cliente_id' => $cliente->id,
            'cliente_nome' => $cliente->razao_social ?: $cliente->documento,
        ]);
    }

    /**
     * Resumo do snapshot SEFAZ (nfe_consultas/cte_consultas) de uma chave, se existir.
     */
    private function snapshotResumoDfe(int $userId, string $chave): ?array
    {
        $snap = \App\Models\NfeConsulta::where('user_id', $userId)->where('chave_acesso', $chave)->first()
            ?? \App\Models\CteConsulta::where('user_id', $userId)->where('chave_acesso', $chave)->first();

        if (! $snap) {
            return null;
        }

        return [
            'status' => $snap->status,
            'consultado_em_label' => optional($snap->consultado_em)->format('d/m/Y H:i'),
        ];
    }

    /**
     * Busca avulsa de DF-e por chave (atrás da feature flag clearance.busca_avulsa.habilitada;
     * desabilitada → 503). Valida input, deduplica contra acervo/snapshot, debita saldo e
     * processa no Laravel (InfoSimples). Sem n8n — despacho desligado no cutover de 2026-06-07.
     */
    public function consultarNfe(Request $request)
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (! config('clearance.busca_avulsa.habilitada')) {
            return response()->json([
                'success' => false,
                'error' => 'Busca avulsa em desenvolvimento. Em breve.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $user = Auth::user();

        $validated = $request->validate([
            'tipo_documento' => 'nullable|string|in:nfe,cte,nfse',
            'chave_acesso' => 'nullable|string',
            'cliente_id' => 'required|integer',
            'tab_id' => 'required|string|max:36',
            'blocos' => 'nullable|array|min:1',
            'blocos.*.tipo_documento' => 'required|string|in:nfe,cte,nfse',
            'blocos.*.chaves_acesso' => 'required|string',
        ], [
            'cliente_id.required' => 'Selecione o cliente associado antes de consultar.',
            'cliente_id.integer' => 'Cliente inválido.',
        ]);
        $blocos = $this->normalizarBlocosBusca($validated);

        if ($blocos->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => ['blocos' => ['Adicione ao menos um bloco com tipo e chaves de acesso.']],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $duplicadas = [];
        $chavesVistas = [];
        $notasPreparadas = collect();

        foreach ($blocos as $indiceBloco => $bloco) {
            $tipoDocumentoBloco = strtolower((string) ($bloco['tipo_documento'] ?? ''));

            if ($tipoDocumentoBloco === 'nfse') {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação.',
                    'errors' => ['blocos' => ['NFS-e ainda não é suportada. Em breve.']],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $chaves = $this->extrairChavesAcesso($bloco['chaves_acesso'] ?? null, deduplicar: false);

            if ($chaves === []) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação.',
                    'errors' => ['blocos' => ['Cada bloco precisa ter ao menos uma chave de acesso válida.']],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $modelosPermitidos = match ($tipoDocumentoBloco) {
                'nfe' => ['55', '65'],
                'cte' => ['57'],
                default => [],
            };

            foreach ($chaves as $indiceChave => $chave) {
                if (strlen($chave) !== 44) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erro de validação.',
                        'errors' => ['blocos' => ['A chave #'.($indiceChave + 1).' do bloco '.($indiceBloco + 1).' deve ter 44 dígitos numéricos.']],
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                if (! $this->validarDigitoVerificadorDfe($chave)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erro de validação.',
                        'errors' => ['blocos' => ['A chave #'.($indiceChave + 1).' do bloco '.($indiceBloco + 1).' possui dígito verificador inválido.']],
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $modeloChave = substr($chave, 20, 2);

                if (! in_array($modeloChave, $modelosPermitidos, true)) {
                    $labelTipo = strtoupper($tipoDocumentoBloco);
                    $labelModelos = implode(', ', $modelosPermitidos);

                    return response()->json([
                        'success' => false,
                        'message' => 'Erro de validação.',
                        'errors' => ['blocos' => ["A chave {$chave} usa modelo {$modeloChave} incompatível com {$labelTipo}. Modelos aceitos: {$labelModelos}."]],
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                if (isset($chavesVistas[$chave])) {
                    $duplicadas[] = $chave;

                    continue;
                }

                $chavesVistas[$chave] = true;

                $notasPreparadas->push([
                    'ordem_lote' => $notasPreparadas->count() + 1,
                    'chave_acesso' => $chave,
                    'tipo_documento' => match ($modeloChave) {
                        '55' => 'NFE',
                        '65' => 'NFCE',
                        '57' => 'CTE',
                        default => strtoupper($tipoDocumentoBloco),
                    },
                    'tipo_documento_bloco' => $tipoDocumentoBloco,
                ]);
            }
        }

        if ($duplicadas !== []) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => ['blocos' => ['Existem chaves repetidas no envio: '.implode(', ', array_values(array_unique($duplicadas))).'.']],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $clienteId = (int) $validated['cliente_id'];
        $cliente = Cliente::where('id', $clienteId)
            ->where('user_id', $user->id)
            ->first();

        if (! $cliente) {
            return response()->json([
                'success' => false,
                'error' => 'Cliente não encontrado ou não pertence a este usuário.',
            ], Response::HTTP_FORBIDDEN);
        }

        $clienteCnpj = preg_replace('/\D/', '', (string) $cliente->documento);

        $acervoPorChave = $this->localizarNotasNoAcervo($user->id, $notasPreparadas->pluck('chave_acesso'));
        $reconsultar = $request->boolean('reconsultar');
        $snapshotPorChave = $reconsultar
            ? []
            : $this->chavesComSnapshot($user->id, $notasPreparadas->pluck('chave_acesso'));
        $notasExistentes = collect();
        $notasParaConsultar = collect();

        foreach ($notasPreparadas as $notaPreparada) {
            $chave = $notaPreparada['chave_acesso'];
            $ordem = (int) $notaPreparada['ordem_lote'];

            if (isset($acervoPorChave[$chave])) {
                $notasExistentes->push(
                    $this->formatarResultadoAcervoExistente(
                        $acervoPorChave[$chave]['nota'],
                        $acervoPorChave[$chave]['origem'],
                        $ordem
                    )
                );

                continue;
            }

            // Idempotência: já consultada antes (snapshot) e não é reconsulta → não recobra.
            if (! $reconsultar && isset($snapshotPorChave[$chave])) {
                continue;
            }

            $notasParaConsultar->push(array_merge($notaPreparada, [
                'id' => null,
                'origem' => 'avulsa',
                'cliente_id' => $clienteId,
            ]));
        }

        $quantidadeNotas = $notasParaConsultar->count();
        $quantidadeItens = $notasPreparadas->count();
        $quantidadeExistentes = $notasExistentes->count();
        $custo = self::CLEARANCE_NFE_AVULSA_CUSTO * $quantidadeNotas;
        $tiposNoEnvio = $notasPreparadas->pluck('tipo_documento')->unique()->values();
        $labelTipoDoc = $tiposNoEnvio->contains('CTE') && $tiposNoEnvio->count() > 1
            ? 'DF-e'
            : ($tiposNoEnvio->contains('CTE') ? 'CT-e' : 'NF-e');
        $transactionType = 'clearance_busca_avulsa';
        $refundType = 'clearance_busca_avulsa_refund';

        if ($quantidadeItens === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => ['blocos' => ['Nenhuma chave válida foi encontrada no envio.']],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($quantidadeNotas === 0) {
            $token = $this->storeBuscaResultadoLocal($user->id, [
                'cliente_id' => $clienteId,
                'cliente_nome' => Cliente::query()->where('id', $clienteId)->value('razao_social'),
                'resultados' => $notasExistentes->sortBy('ordem_lote')->values()->all(),
                'resumo' => $this->resumirResultadosClearance($notasExistentes),
                'total_itens' => $quantidadeItens,
                'total_existentes' => $quantidadeExistentes,
            ]);

            return response()->json([
                'success' => true,
                'resultado_url' => route('app.clearance.buscar.resultado-local', ['token' => $token]),
                'mensagem' => 'Todas as chaves já estavam no acervo.',
                'novo_saldo' => $this->saldoService->getBalance($user),
                'total_itens' => $quantidadeItens,
                'total_existentes' => $quantidadeExistentes,
                'total_consultadas' => 0,
            ]);
        }

        // Busca avulsa roda no MESMO motor assíncrono do lote (Bus::batch + ProcessarClearanceJob),
        // associando o snapshot ao cliente. Substitui o despacho ao webhook n8n (removido no cutover).
        $itensParaConsultar = $notasParaConsultar->map(fn (array $n) => [
            'chave' => preg_replace('/\D/', '', (string) $n['chave_acesso']),
            'tipo' => ($n['tipo_documento'] ?? '') === 'CTE' ? 'cte' : 'nfe',
            'cliente_id' => $clienteId,
        ])->values();

        $resultado = app(ClearanceLoteService::class)->iniciarComItens(
            $itensParaConsultar,
            self::CLEARANCE_NFE_AVULSA_CUSTO,
            $user->id,
            $validated['tab_id'],
            "Clearance {$labelTipoDoc} avulsa · {$quantidadeNotas} documento(s)",
            'app.clearance.buscar.resultado'
        );

        if (($resultado['success'] ?? false) && ! empty($resultado['consulta_lote_id'])) {
            // Guarda os itens já no acervo p/ a tela de resultado mesclar declarado × consultado.
            $this->storeBuscaAcervoPrecheck($user->id, $resultado['consulta_lote_id'], [
                'resultados' => $notasExistentes->values()->all(),
                'ordem_por_chave' => $notasPreparadas
                    ->mapWithKeys(fn (array $nota) => [$nota['chave_acesso'] => (int) $nota['ordem_lote']])
                    ->all(),
            ]);

            $resultado['total_itens'] = $quantidadeItens;
            $resultado['total_existentes'] = $quantidadeExistentes;
            $resultado['total_consultadas'] = $quantidadeNotas;
        }

        return response()->json($resultado, $resultado['http_status'] ?? Response::HTTP_OK);
    }

    private function normalizarBlocosBusca(array $validated): Collection
    {
        if (! empty($validated['blocos']) && is_array($validated['blocos'])) {
            return collect($validated['blocos'])
                ->map(fn ($bloco) => [
                    'tipo_documento' => strtolower((string) ($bloco['tipo_documento'] ?? '')),
                    'chaves_acesso' => (string) ($bloco['chaves_acesso'] ?? ''),
                ])
                ->filter(fn (array $bloco) => $bloco['tipo_documento'] !== '' || trim($bloco['chaves_acesso']) !== '')
                ->values();
        }

        if (! empty($validated['tipo_documento']) || ! empty($validated['chave_acesso'])) {
            return collect([[
                'tipo_documento' => strtolower((string) ($validated['tipo_documento'] ?? '')),
                'chaves_acesso' => (string) ($validated['chave_acesso'] ?? ''),
            ]]);
        }

        return collect();
    }

    private function extrairChavesAcesso(?string $conteudo, bool $deduplicar = true): array
    {
        $linhas = preg_split('/[\r\n,;]+/', (string) $conteudo) ?: [];

        $chaves = collect($linhas)
            ->map(fn ($linha) => preg_replace('/\D/', '', (string) $linha))
            ->filter()
            ->values();

        if ($deduplicar) {
            $chaves = $chaves->unique()->values();
        }

        return $chaves->all();
    }

    /**
     * Chaves do usuário que já têm snapshot persistido (nfe_consultas/cte_consultas).
     * Idempotência da busca avulsa: não recobrar o que já foi consultado (salvo reconsultar).
     *
     * @param  Collection<int,string>  $chaves
     * @return array<string,bool>
     */
    private function chavesComSnapshot(int $userId, Collection $chaves): array
    {
        $chaves = $chaves->filter()->unique()->values();
        if ($chaves->isEmpty()) {
            return [];
        }

        $nfe = \App\Models\NfeConsulta::where('user_id', $userId)->whereIn('chave_acesso', $chaves)->pluck('chave_acesso');
        $cte = \App\Models\CteConsulta::where('user_id', $userId)->whereIn('chave_acesso', $chaves)->pluck('chave_acesso');

        return $nfe->concat($cte)->mapWithKeys(fn ($c) => [$c => true])->all();
    }

    private function localizarNotasNoAcervo(int $userId, Collection $chaves): array
    {
        $chaves = $chaves->filter()->unique()->values();

        if ($chaves->isEmpty()) {
            return [];
        }

        $xml = XmlNota::query()
            ->with('cliente')
            ->where('user_id', $userId)
            ->whereIn('chave_acesso', $chaves)
            ->get()
            ->keyBy('chave_acesso');

        $chavesRestantes = $chaves->reject(fn (string $chave) => $xml->has($chave))->values();

        $efd = EfdNota::query()
            ->with(['cliente', 'participante'])
            ->where('user_id', $userId)
            ->whereIn('chave_acesso', $chavesRestantes)
            ->get()
            ->keyBy('chave_acesso');

        $encontradas = [];

        foreach ($chaves as $chave) {
            if ($xml->has($chave)) {
                $encontradas[$chave] = [
                    'origem' => 'xml',
                    'nota' => $xml->get($chave),
                ];

                continue;
            }

            if ($efd->has($chave)) {
                $encontradas[$chave] = [
                    'origem' => 'efd',
                    'nota' => $efd->get($chave),
                ];
            }
        }

        return $encontradas;
    }

    private function formatarResultadoAcervoExistente(object $nota, string $origem, int $ordem): object
    {
        if ($origem === 'xml' && $nota instanceof XmlNota) {
            return (object) [
                'id' => 'xml-'.$nota->id,
                'consulta_lote_id' => null,
                'chave_acesso' => $nota->chave_acesso,
                'tipo_documento' => strtoupper((string) ($nota->tipo_documento ?: 'NFE')),
                'modelo' => $this->inferirModeloDocumento($nota->tipo_documento, $nota->chave_acesso),
                'numero' => $nota->numero_documento,
                'serie' => $nota->serie,
                'status' => 'JA_NO_ACERVO',
                'status_label' => 'JA_NO_ACERVO',
                'status_hex' => $this->statusHexConsultaDfe('JA_NO_ACERVO'),
                'valor_total' => $nota->valor_total,
                'valor_total_label' => $nota->valor_total !== null ? 'R$ '.number_format((float) $nota->valor_total, 2, ',', '.') : '—',
                'data_emissao' => $nota->data_emissao,
                'data_emissao_label' => optional($nota->data_emissao)->format('d/m/Y H:i'),
                'emit_nome' => $nota->emit_razao_social,
                'emit_cnpj' => $nota->emit_documento,
                'dest_nome' => $nota->dest_razao_social,
                'dest_cnpj' => $nota->dest_documento,
                'tomador_nome' => null,
                'tomador_cnpj' => null,
                'participante_label' => $nota->dest_razao_social ?: $nota->dest_documento ?: 'Não informado',
                'consultado_em' => null,
                'consultado_em_label' => 'Já no acervo',
                'detalhe_url' => route('app.notas.detalhes', ['origem' => 'xml', 'id' => $nota->id]),
                'origem_acervo_label' => 'XML',
                'origem_acervo_hex' => '#0f766e',
                'ordem_lote' => $ordem,
            ];
        }

        /** @var EfdNota $nota */
        $tipoDocumento = match ((string) $nota->modelo) {
            '57' => 'CTE',
            '65' => 'NFCE',
            default => 'NFE',
        };
        $clienteNome = $nota->cliente?->razao_social;
        $participanteNome = $nota->participante?->razao_social;
        $emitente = $nota->tipo_operacao === 'entrada' ? ($participanteNome ?: 'Participante EFD') : ($clienteNome ?: 'Empresa');
        $destinatario = $nota->tipo_operacao === 'entrada' ? ($clienteNome ?: 'Empresa') : ($participanteNome ?: 'Participante EFD');

        return (object) [
            'id' => 'efd-'.$nota->id,
            'consulta_lote_id' => null,
            'chave_acesso' => $nota->chave_acesso,
            'tipo_documento' => $tipoDocumento,
            'modelo' => (string) ($nota->modelo ?: $this->inferirModeloDocumento($tipoDocumento, $nota->chave_acesso)),
            'numero' => $nota->numero,
            'serie' => $nota->serie,
            'status' => 'JA_NO_ACERVO',
            'status_label' => 'JA_NO_ACERVO',
            'status_hex' => $this->statusHexConsultaDfe('JA_NO_ACERVO'),
            'valor_total' => $nota->valor_total,
            'valor_total_label' => $nota->valor_total !== null ? 'R$ '.number_format((float) $nota->valor_total, 2, ',', '.') : '—',
            'data_emissao' => $nota->data_emissao,
            'data_emissao_label' => optional($nota->data_emissao)->format('d/m/Y'),
            'emit_nome' => $emitente,
            'emit_cnpj' => null,
            'dest_nome' => $destinatario,
            'dest_cnpj' => null,
            'tomador_nome' => null,
            'tomador_cnpj' => null,
            'participante_label' => $destinatario ?: 'Não informado',
            'consultado_em' => null,
            'consultado_em_label' => 'Já no acervo',
            'detalhe_url' => route('app.notas.detalhes', ['origem' => 'efd', 'id' => $nota->id]),
            'origem_acervo_label' => 'EFD',
            'origem_acervo_hex' => '#4338ca',
            'ordem_lote' => $ordem,
        ];
    }

    private function storeBuscaAcervoPrecheck(int $userId, int $consultaLoteId, array $payload): void
    {
        Cache::put(
            $this->buscarAcervoPrecheckCacheKey($userId, $consultaLoteId),
            $payload,
            now()->addMinutes(self::BUSCA_AVULSA_CACHE_TTL_MINUTES)
        );
    }

    private function getBuscaAcervoPrecheck(int $userId, int $consultaLoteId): array
    {
        return Cache::get($this->buscarAcervoPrecheckCacheKey($userId, $consultaLoteId), []);
    }

    private function buscarAcervoPrecheckCacheKey(int $userId, int $consultaLoteId): string
    {
        return "clearance:buscar:precheck:user:{$userId}:lote:{$consultaLoteId}";
    }

    private function storeBuscaResultadoLocal(int $userId, array $payload): string
    {
        $token = Str::random(40);

        Cache::put(
            $this->buscarResultadoLocalCacheKey($userId, $token),
            $payload,
            now()->addMinutes(self::BUSCA_AVULSA_CACHE_TTL_MINUTES)
        );

        return $token;
    }

    private function getBuscaResultadoLocal(int $userId, string $token): ?array
    {
        return Cache::get($this->buscarResultadoLocalCacheKey($userId, $token));
    }

    private function buscarResultadoLocalCacheKey(int $userId, string $token): string
    {
        return "clearance:buscar:local:user:{$userId}:token:{$token}";
    }

    private function inferirModeloDocumento(?string $tipoDocumento, ?string $chaveAcesso): string
    {
        $chave = preg_replace('/\D/', '', (string) $chaveAcesso);

        if (strlen($chave) === 44) {
            return substr($chave, 20, 2);
        }

        return match (strtoupper((string) $tipoDocumento)) {
            'CTE' => '57',
            'NFCE' => '65',
            default => '55',
        };
    }

    /**
     * Retorna a consulta canônica persistida pelo fluxo de clearance avulso.
     *
     * Chamado pelo frontend depois do SSE sinalizar status=finalizado.
     */
    public function resultadoUltimaConsulta(Request $request, int $consultaLoteId)
    {
        if (! Auth::check()) {
            if ($this->isAjaxRequest($request)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Usuário não autenticado.',
                ], Response::HTTP_UNAUTHORIZED);
            }

            return $this->redirectToLogin($request);
        }

        $userId = Auth::id();
        $chaveConsultada = preg_replace('/\D/', '', (string) $request->query('chave_acesso', ''));
        $tipoDocumento = strtoupper((string) $request->query('tipo_documento', 'NFE'));

        $lote = ConsultaLote::where('id', $consultaLoteId)
            ->where('user_id', $userId)
            ->first();

        if (! $lote) {
            if (! $this->isAjaxRequest($request)) {
                abort(404);
            }

            return response()->json([
                'success' => false,
                'error' => 'Lote não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        $nota = $this->buscarConsultaDfePorLote($userId, $lote->id);
        $notaAcervo = null;

        if (! $nota && strlen($chaveConsultada) === 44) {
            $notaAcervo = $this->buscarNotaAcervoPorChave($userId, $chaveConsultada);
        }

        $notaResultado = $nota
            ? $this->formatarResultadoConsultaDfe($nota, $userId)
            : ($notaAcervo ? $this->formatarResultadoXmlAcervo($notaAcervo) : null);

        if (! $this->isAjaxRequest($request)) {
            return $this->render($request, 'buscar-resultado', [
                'lote' => $lote,
                'statusMeta' => $this->statusMetaLote($lote->status),
                'notaResultado' => $notaResultado,
                'tipoDocumento' => $tipoDocumento,
                'chaveConsultada' => strlen($chaveConsultada) === 44
                    ? $chaveConsultada
                    : ($notaResultado['chave_acesso'] ?? $notaResultado['nfe_id'] ?? null),
                'aguardaPersistencia' => $lote->isFinalizado() && ! $notaResultado,
                'progressSnapshot' => $this->getClearanceProgressSnapshot($lote),
                'classificacaoPartes' => ($nota && $lote->isFinalizado())
                    ? $this->montarClassificacaoPartes($userId, $nota)
                    : null,
            ]);
        }

        if (! $notaResultado) {
            return response()->json([
                'success' => false,
                'error' => 'Consulta ainda não persistida nas tabelas canônicas do clearance.',
                'status_lote' => ConsultaLote::normalizeStatus($lote->status),
                'resultado_pronto' => false,
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'status_lote' => ConsultaLote::normalizeStatus($lote->status),
            'resultado_pronto' => true,
            'nota' => $notaResultado,
        ]);
    }

    public function resultadoBuscaLocal(Request $request, string $token)
    {
        if (! Auth::check()) {
            if ($this->isAjaxRequest($request)) {
                return response()->json(['success' => false, 'error' => 'Usuário não autenticado.'], Response::HTTP_UNAUTHORIZED);
            }

            return $this->redirectToLogin($request);
        }

        $userId = Auth::id();
        $payload = $this->getBuscaResultadoLocal($userId, $token);

        if (! $payload) {
            if ($this->isAjaxRequest($request)) {
                return response()->json(['success' => false, 'error' => 'Resultado local não encontrado.'], Response::HTTP_NOT_FOUND);
            }

            abort(404);
        }

        $resultados = collect($payload['resultados'] ?? [])->map(fn ($resultado) => (object) $resultado);
        $resumo = $payload['resumo'] ?? $this->resumirResultadosClearance($resultados);

        return $this->render($request, 'buscar-resultado-local', [
            'resultados' => $resultados,
            'resumo' => $resumo,
            'clienteNome' => $payload['cliente_nome'] ?? 'Cliente não informado',
            'totalItens' => (int) ($payload['total_itens'] ?? $resultados->count()),
            'totalExistentes' => (int) ($payload['total_existentes'] ?? $resultados->count()),
        ]);
    }

    /**
     * Monta os dados de export do resultado da busca avulsa (1 documento) — mesma fonte
     * da tela (formatarResultadoConsultaDfe). Ownership: lote do usuário (404); o snapshot
     * precisa estar persistido (404). Gates de plano ficam nas rotas (RequiresEntitlement).
     */
    private function montarExportBuscaAvulsa(int $consultaLoteId): array
    {
        $userId = Auth::id();

        $lote = ConsultaLote::where('id', $consultaLoteId)->where('user_id', $userId)->first();
        abort_if($lote === null, 404);

        $nota = $this->buscarConsultaDfePorLote($userId, $lote->id);
        abort_if($nota === null, 404, 'Resultado ainda não disponível para exportação.');

        return [$lote, $this->formatarResultadoConsultaDfe($nota, $userId)];
    }

    /** PDF do resultado da busca avulsa. Gate: `:export` (PDF universal — Free sai com marca d'água). */
    public function buscaResultadoPdf(Request $request, int $consultaLoteId)
    {
        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        [$lote, $nota] = $this->montarExportBuscaAvulsa($consultaLoteId);

        $pdf = PdfReport::render('reports.clearance-busca-avulsa', ['nota' => $nota, 'lote' => $lote]);

        return $this->comTokenDownload($pdf->download('clearance-documento-'.($nota['nfe_id'] ?: $lote->id).'.pdf'), $request);
    }

    /** XLSX do resultado da busca avulsa. Gate: `:export,excel`. */
    public function buscaResultadoXlsx(Request $request, int $consultaLoteId)
    {
        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        if (! \App\Support\Reports\XlsxReport::disponivel()) {
            abort(503, 'Exportação XLSX indisponível.');
        }

        [$lote, $nota] = $this->montarExportBuscaAvulsa($consultaLoteId);

        return $this->comTokenDownload(
            app(\App\Services\Clearance\Export\BuscaAvulsaXlsxBuilder::class)
                ->download($nota, 'clearance-documento-'.($nota['nfe_id'] ?: $lote->id).'.xlsx'),
            $request
        );
    }

    /** CSV do resultado da busca avulsa (seções empilhadas: documento, eventos, itens). Gate: `:export,csv`. */
    public function buscaResultadoCsv(Request $request, int $consultaLoteId)
    {
        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        [$lote, $nota] = $this->montarExportBuscaAvulsa($consultaLoteId);
        $d = $nota['detalhes'] ?? [];
        $isCte = ($nota['tipo_documento'] ?? '') === 'CTE';

        $linhas = [
            ['Chave de acesso', $nota['nfe_id'] ?? ''],
            ['Tipo', ($nota['tipo_documento'] ?? 'NFE').(! empty($nota['modelo']) ? ' (modelo '.$nota['modelo'].')' : '')],
            ['Situação', $nota['situacao'] ?? ''],
            ['Número / Série', ($nota['numero'] ?? '').' / '.($nota['serie'] ?? '')],
            ['Emissão', $nota['data_emissao'] ?? ''],
            ['Valor total', $nota['valor_total_label'] ?? ''],
            ['Natureza da operação', $d['natureza_operacao'] ?? ''],
            ['Consultado na SEFAZ em', $nota['consultado_em'] ?? ''],
            ['Abrangência', ($d['consulta_sem_certificado'] ?? false) ? 'Consulta pública (sem certificado)' : 'Consulta completa'],
            ['Cliente associado', $nota['cliente_nome'] ?? ''],
            ['Emitente', trim(($d['emit']['nome'] ?? '').' '.($d['emit']['documento'] ?? ''))],
        ];

        if ($isCte) {
            foreach ($d['partes'] ?? [] as $parte) {
                $linhas[] = [$parte['papel'], trim(($parte['nome'] ?? '').' '.($parte['documento'] ?? ''))];
            }
        } else {
            $linhas[] = ['Destinatário', trim(($d['dest']['nome'] ?? '').' '.($d['dest']['documento'] ?? ''))];
        }

        if (! empty($d['eventos_timeline'])) {
            $linhas[] = [];
            $linhas[] = ['Eventos na SEFAZ'];
            $linhas[] = ['Situação', 'Data', 'Evento', 'Protocolo'];
            foreach ($d['eventos_timeline'] as $ev) {
                $linhas[] = [$ev['label'] ?? '', $ev['data_label'] ?? '', $ev['descricao'] ?? '', $ev['protocolo'] ?? ''];
            }
        }

        if (! $isCte && ! empty($d['totais'])) {
            $linhas[] = [];
            $linhas[] = ['Totais informados pela SEFAZ'];
            foreach ($d['totais'] as $t) {
                $linhas[] = [$t['label'], $t['valor']];
            }
        }

        if ($isCte && ! empty($d['componentes'])) {
            $linhas[] = [];
            $linhas[] = ['Componentes da prestação'];
            $linhas[] = ['Componente', 'Valor'];
            foreach ($d['componentes'] as $c) {
                $linhas[] = [$c['nome'] ?? '', $c['valor'] ?? ''];
            }
        }

        if (! $isCte && ! empty($d['produtos'])) {
            $linhas[] = [];
            $linhas[] = ['Produtos'];
            $linhas[] = ['Descrição', 'NCM', 'CFOP', 'Quantidade', 'Valor'];
            foreach ($d['produtos'] as $p) {
                $linhas[] = [$p['descricao'] ?? '', $p['ncm'] ?? '', $p['cfop'] ?? '', $p['quantidade'] ?? '', $p['valor'] ?? ''];
            }
        }

        return $this->comTokenDownload(
            \App\Support\CsvExport::download('clearance-documento-'.($nota['nfe_id'] ?: $lote->id).'.csv', ['Campo', 'Valor'], $linhas),
            $request
        );
    }

    public function resultadoNotas(Request $request, int $consultaLoteId)
    {
        if (! Auth::check()) {
            if ($this->isAjaxRequest($request)) {
                return response()->json(['success' => false, 'error' => 'Usuário não autenticado.'], Response::HTTP_UNAUTHORIZED);
            }

            return $this->redirectToLogin($request);
        }

        $userId = Auth::id();

        $lote = ConsultaLote::where('id', $consultaLoteId)
            ->where('user_id', $userId)
            ->first();

        if (! $lote) {
            if ($this->isAjaxRequest($request)) {
                return response()->json(['success' => false, 'error' => 'Lote não encontrado.'], Response::HTTP_NOT_FOUND);
            }

            abort(404);
        }

        $resultados = $this->listarConsultasDfePorLote($userId, $lote->id);
        $resumo = $this->resumirResultadosClearance($resultados);

        $analiseDivergencia = (new DivergenciaService)->analisar(
            $resultados,
            $userId,
            (int) ($lote->creditos_cobrados ?? 0)
        );

        if ($this->isAjaxRequest($request)) {
            $resultadoPronto = $lote->isFinalizado() && $resultados->isNotEmpty();

            return response()->json([
                'success' => true,
                'status_lote' => ConsultaLote::normalizeStatus($lote->status),
                'total_resultados' => $resultados->count(),
                'resultado_pronto' => $resultadoPronto,
                'resumo' => $resumo,
                'veredito' => $analiseDivergencia['veredito'],
                'kpis' => $analiseDivergencia['kpis'],
            ]);
        }

        return $this->render($request, 'notas-resultado', [
            'lote' => $lote,
            'statusMeta' => $this->statusMetaLote($lote->status),
            'resultados' => $resultados,
            'resumo' => $resumo,
            'divergencia' => $analiseDivergencia,
            'tipoValidacao' => strtolower((string) $request->query('tipo_validacao', '')),
            'aguardaPersistencia' => $lote->isFinalizado() && $resultados->isEmpty(),
            'progressSnapshot' => $this->getClearanceProgressSnapshot($lote),
        ]);
    }

    /**
     * PDF executivo do resultado do lote (entregável pro cliente final do escritório).
     * Reusa o mesmo caminho de resultadoNotas e delega a montagem ao RelatorioExecutivoService.
     * Gate: entitlement `export` (rota). Ownership: lote precisa ser do usuário (404).
     */
    public function resultadoPdf(Request $request, int $consultaLoteId)
    {
        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        [$lote, $relatorio] = $this->montarRelatorioExecutivo($request, $consultaLoteId, 'pdf');

        $pdf = PdfReport::render('autenticado.clearance.pdf.relatorio', ['r' => $relatorio], 'portrait');

        return $this->comTokenDownload($pdf->download("clearance-lote-{$lote->id}.pdf"), $request);
    }

    /**
     * XLSX executivo do resultado do lote — mesma fonte do PDF (RelatorioExecutivoService),
     * planilha no modelo de design aprovado (docs/bi/export-planilhas.md).
     * Gate: entitlement `export` (rota). Ownership: lote precisa ser do usuário (404).
     */
    public function resultadoXlsx(Request $request, int $consultaLoteId)
    {
        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        [$lote, $relatorio] = $this->montarRelatorioExecutivo($request, $consultaLoteId, 'xlsx');

        return $this->comTokenDownload(
            app(\App\Services\Clearance\Export\ClearanceXlsxBuilder::class)
                ->download($relatorio, "clearance-lote-{$lote->id}.xlsx"),
            $request
        );
    }

    /**
     * PDF do Panorama de Clearance (dashboard) — valor R$ por status, exposição bloqueante
     * e cobertura por cliente sobre o acervo inteiro. Fonte única: ClearanceDashboardReportBuilder.
     * Gate: entitlement `export` (rota).
     */
    public function exportarDashboardPdf(Request $request, \App\Services\Clearance\Export\ClearanceDashboardReportBuilder $builder)
    {
        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $relatorio = $builder->montar(Auth::id());

        return $this->comTokenDownload(
            PdfReport::render('reports.clearance-dashboard', ['relatorio' => $relatorio])
                ->download('panorama-clearance.pdf'),
            $request
        );
    }

    /** XLSX do Panorama de Clearance — mesma fonte do PDF (abas por seção + Resumo). */
    public function exportarDashboardXlsx(Request $request, \App\Services\Clearance\Export\ClearanceDashboardReportBuilder $builder, \App\Services\Clearance\Export\ClearanceDashboardXlsxBuilder $xlsx)
    {
        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        if (! \App\Support\Reports\XlsxReport::disponivel()) {
            abort(503, 'Exportação XLSX indisponível.');
        }

        $relatorio = $builder->montar(Auth::id());

        return $this->comTokenDownload($xlsx->download($relatorio, 'panorama-clearance.xlsx'), $request);
    }

    /** CSV/ZIP do Panorama de Clearance — 1 CSV por seção (mesma fonte do PDF e do XLSX). */
    public function exportarDashboardCsvZip(Request $request, \App\Services\Clearance\Export\ClearanceDashboardReportBuilder $builder, \App\Services\Clearance\Export\ClearanceDashboardCsvZipBuilder $zip)
    {
        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $relatorio = $builder->montar(Auth::id());

        return $this->comTokenDownload($zip->download($relatorio, 'panorama-clearance-csv.zip'), $request);
    }

    /** Custo interno de uma consulta SINTEGRA (fonte paga). */
    private function custoSintegraUnitario(): int
    {
        return (int) config('consultas.fontes.sintegra', 2);
    }

    /**
     * Resolve os participantes SEM Inscrição Estadual alvos do enriquecimento SINTEGRA.
     * Aceita ids explícitos (botão por documento) e/ou um lote inteiro (botão do topo —
     * varre as contrapartes das notas do lote). Filtra por dono, CNPJ e IE ausente.
     */
    private function resolverParticipantesSemIe(int $userId, array $ids, ?int $loteId): Collection
    {
        $idsAlvo = $ids;

        if ($loteId !== null) {
            $lote = ConsultaLote::where('id', $loteId)->where('user_id', $userId)->first();
            if ($lote) {
                $resultados = $this->listarConsultasDfePorLote($userId, $lote->id);
                $chaves = $resultados->pluck('chave_acesso')->filter()->unique()->values()->all();
                $declarado = app(DivergenciaService::class)->buscarDeclaradoPorChave($userId, $chaves);
                foreach ($declarado as $d) {
                    if (empty($d['contraparte_ie']) && ! empty($d['contraparte_participante_id'])) {
                        $idsAlvo[] = $d['contraparte_participante_id'];
                    }
                }
            }
        }

        $idsAlvo = array_values(array_unique(array_filter(array_map('intval', $idsAlvo))));
        if ($idsAlvo === []) {
            return collect();
        }

        return Participante::where('user_id', $userId)
            ->somenteCnpj()
            ->whereIn('id', $idsAlvo)
            ->where(fn ($q) => $q->whereNull('inscricao_estadual')->orWhere('inscricao_estadual', ''))
            ->get(['id', 'documento', 'razao_social', 'uf', 'crt']);
    }

    /**
     * Preview do custo do enriquecimento SINTEGRA (para o modal de confirmação).
     * Não cobra nada — só resolve os alvos e devolve custo × saldo.
     */
    public function sintegraPreview(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['success' => false, 'error' => 'Não autenticado.'], Response::HTTP_UNAUTHORIZED);
        }

        $validated = $request->validate([
            'participante_ids' => 'nullable|array|max:1000',
            'participante_ids.*' => 'integer',
            'lote_id' => 'nullable|integer',
        ]);

        $userId = (int) Auth::id();
        $participantes = $this->resolverParticipantesSemIe(
            $userId,
            $validated['participante_ids'] ?? [],
            $validated['lote_id'] ?? null
        );

        $total = $participantes->count();
        $custo = $total * $this->custoSintegraUnitario();
        $saldo = $this->saldoService->getBalance(Auth::user());

        return response()->json([
            'success' => true,
            'total' => $total,
            'participante_ids' => $participantes->pluck('id')->values()->all(),
            'valor_reais' => $this->pricingCatalogService->creditsToCurrency($custo),
            'saldo_reais' => $this->pricingCatalogService->creditsToCurrency($saldo),
            'suficiente' => $saldo >= $custo,
        ]);
    }

    /**
     * Executa o enriquecimento SINTEGRA: debita, cria lote sintegra-only (plano_id null) e
     * dispara ProcessarConsultaJob com somenteFontes=['sintegra']. O estorno em falha fatal é
     * automático (FecharLoteService, por fonte). Persiste em participantes.inscricao_estadual.
     */
    public function sintegraExecutar(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['success' => false, 'error' => 'Não autenticado.'], Response::HTTP_UNAUTHORIZED);
        }

        $validated = $request->validate([
            'participante_ids' => 'nullable|array|max:1000',
            'participante_ids.*' => 'integer',
            'lote_id' => 'nullable|integer',
            'tab_id' => 'nullable|string|max:36',
        ]);

        $user = Auth::user();
        $userId = (int) $user->id;

        if (! (bool) config('consultas.infosimples_ativo', false)) {
            return response()->json(['success' => false, 'error' => 'Consulta SINTEGRA indisponível no momento.'], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $participantes = $this->resolverParticipantesSemIe(
            $userId,
            $validated['participante_ids'] ?? [],
            $validated['lote_id'] ?? null
        );

        if ($participantes->isEmpty()) {
            return response()->json(['success' => false, 'error' => 'Nenhum participante elegível (sem IE) para consultar.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $total = $participantes->count();
        $custoTotal = $total * $this->custoSintegraUnitario();

        if (! $this->saldoService->hasEnough($user, $custoTotal)) {
            return response()->json([
                'success' => false,
                'error' => 'Saldo insuficiente.',
                'creditos_necessarios' => $custoTotal,
                'creditos_disponiveis' => $this->saldoService->getBalance($user),
            ], Response::HTTP_PAYMENT_REQUIRED);
        }

        $tabId = $validated['tab_id'] ?: (string) Str::uuid();

        try {
            if (! $this->saldoService->deduct($user, $custoTotal, 'consulta_lote', "SINTEGRA IE — {$total} participante(s)")) {
                return response()->json(['success' => false, 'error' => 'Falha ao debitar o saldo.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $lote = ConsultaLote::create([
                'user_id' => $userId,
                'plano_id' => null,
                'status' => ConsultaLote::STATUS_PROCESSANDO,
                'total_participantes' => $total,
                'creditos_cobrados' => $custoTotal,
                'tab_id' => $tabId,
            ]);
            $lote->participantes()->attach($participantes->pluck('id')->all());

            $jobs = $participantes->values()->map(fn ($p, $i) => new ProcessarConsultaJob(
                loteId: $lote->id,
                alvoTipo: 'participante',
                alvoId: (int) $p->id,
                userId: $userId,
                tabId: $tabId,
                consultasIncluidas: ['sintegra'],
                alvo: [
                    'cnpj' => preg_replace('/\D/', '', (string) $p->documento),
                    'uf' => $p->uf,
                    'crt' => $p->crt,
                ],
                etapas: [],
                alvoIndice: $i + 1,
                totalAlvos: $total,
                somenteFontes: ['sintegra'],
            ))->all();

            Bus::batch($jobs)
                ->name("sintegra-ie-{$lote->id}")
                ->then(fn () => app(FecharLoteService::class)->fechar($lote->id, resumo: ['engine' => 'laravel', 'origem' => 'clearance_ie']))
                ->dispatch();

            return response()->json([
                'success' => true,
                'lote_id' => $lote->id,
                'total' => $total,
                'participante_ids' => $participantes->pluck('id')->values()->all(),
                'valor_reais' => $this->pricingCatalogService->creditsToCurrency($custoTotal),
                'novo_saldo_reais' => $this->pricingCatalogService->creditsToCurrency($this->saldoService->getBalance($user)),
            ]);
        } catch (\Throwable $e) {
            if (isset($lote)) {
                $lote->update(['status' => ConsultaLote::STATUS_ERRO, 'error_code' => 'INTERNAL_ERROR', 'error_message' => $e->getMessage()]);
                $this->saldoService->add($user, $custoTotal, 'consulta_refund', "Estorno SINTEGRA IE lote #{$lote->id}");
            }
            Log::error('Clearance SINTEGRA IE: exceção', ['user_id' => $userId, 'error' => $e->getMessage()]);

            return response()->json(['success' => false, 'error' => 'Erro interno ao iniciar consulta.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Poll do enriquecimento: quantos dos participantes pedidos já têm IE preenchida.
     * O frontend recarrega a página quando pendentes chega a 0.
     */
    public function sintegraStatus(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['success' => false, 'error' => 'Não autenticado.'], Response::HTTP_UNAUTHORIZED);
        }

        $validated = $request->validate([
            'participante_ids' => 'required|array|max:1000',
            'participante_ids.*' => 'integer',
        ]);

        $userId = (int) Auth::id();
        $ids = array_values(array_unique(array_map('intval', $validated['participante_ids'])));

        $prontos = Participante::where('user_id', $userId)
            ->whereIn('id', $ids)
            ->whereNotNull('inscricao_estadual')
            ->where('inscricao_estadual', '!=', '')
            ->count();

        return response()->json([
            'success' => true,
            'total' => count($ids),
            'prontos' => $prontos,
            'pendentes' => max(0, count($ids) - $prontos),
        ]);
    }

    /**
     * Monta o relatório executivo do lote (fonte única do PDF e do XLSX).
     *
     * @return array{0: ConsultaLote, 1: array}
     */
    private function montarRelatorioExecutivo(Request $request, int $consultaLoteId, string $formato): array
    {
        $userId = Auth::id();

        $lote = ConsultaLote::where('id', $consultaLoteId)
            ->where('user_id', $userId)
            ->first();

        if (! $lote) {
            abort(404);
        }

        $resultados = $this->listarConsultasDfePorLote($userId, $lote->id);

        if ($resultados->isEmpty()) {
            abort(404, 'Lote sem resultados de clearance para exportar.');
        }

        $divergencia = (new DivergenciaService)->analisar(
            $resultados,
            $userId,
            (int) ($lote->creditos_cobrados ?? 0)
        );

        $relatorio = (new RelatorioExecutivoService)->montar($lote, $resultados, $divergencia);

        Log::info('clearance.relatorio_executivo.download', [
            'user_id' => $userId,
            'lote_id' => $lote->id,
            'formato' => $formato,
            'ip' => $request->ip(),
            'hash' => $relatorio['hash'],
        ]);

        return [$lote, $relatorio];
    }

    /**
     * Valida dígito verificador (módulo 11) de uma chave de acesso NF-e de 44 dígitos.
     */
    private function validarDigitoVerificadorDfe(string $chave): bool
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
            'modelo' => $request->input('modelo'),
            'busca' => trim((string) $request->input('busca')) ?: null,
            'status_validacao' => $request->input('status_validacao', 'todos'),
            'situacao_receita' => $request->input('situacao_receita'),
        ];
    }

    /**
     * Mapeia a escolha canônica de modelo (55/65/57) para os valores reais
     * gravados em cada acervo. XML guarda string ('NFE'), EFD guarda código ('55').
     */
    private const MODELO_MAP_XML = [
        '55' => ['NFE', 'NF-E', '55'],
        '65' => ['NFCE', 'NFC-E', '65'],
        '57' => ['CTE', 'CT-E', '57', '67'],
    ];

    private const MODELO_MAP_EFD = [
        '55' => ['55'],
        '65' => ['65'],
        '57' => ['57', '67'],
    ];

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
            $xml->whereRaw('NOT '.$this->snapshotExistsSql('xml_notas'));
        }

        $efd = $this->efdSubquery($userId, $f);

        if ($status === 'sem_situacao_receita') {
            $efd->whereRaw('NOT '.$this->snapshotExistsSql('efd_notas'));
        }

        $efd->whereNotExists(function ($q) use ($userId) {
            $q->select(DB::raw(1))
                ->from('xml_notas')
                ->whereColumn('xml_notas.chave_acesso', 'efd_notas.chave_acesso')
                ->where('xml_notas.user_id', $userId);
        });

        return DB::query()->fromSub($xml->unionAll($efd), 'u');
    }

    private function xmlSubquery(int $userId, array $f): \Illuminate\Database\Query\Builder
    {
        $q = DB::table('xml_notas')
            ->leftJoin('nfe_consultas as nfe_status', function ($join) {
                $join->on('nfe_status.user_id', '=', 'xml_notas.user_id')
                    ->on('nfe_status.chave_acesso', '=', 'xml_notas.chave_acesso');
            })
            ->leftJoin('cte_consultas as cte_status', function ($join) {
                $join->on('cte_status.user_id', '=', 'xml_notas.user_id')
                    ->on('cte_status.chave_acesso', '=', 'xml_notas.chave_acesso');
            })
            ->selectRaw("
                'xml'::text                                   as origem,
                xml_notas.id                                   as id,
                xml_notas.chave_acesso                         as chave,
                xml_notas.numero_documento                     as numero,
                xml_notas.serie::text                          as serie,
                xml_notas.tipo_documento                       as modelo,
                xml_notas.data_emissao                         as data_emissao,
                xml_notas.valor_total                          as valor_total,
                CASE xml_notas.tipo_nota WHEN 0 THEN 'entrada' ELSE 'saida' END as tipo_nota,
                xml_notas.emit_razao_social                    as emit_razao_social,
                xml_notas.dest_razao_social                    as dest_razao_social,
                CASE xml_notas.tipo_nota
                    WHEN 0 THEN xml_notas.emit_razao_social
                    ELSE xml_notas.dest_razao_social
                END                                             as participante_nome,
                CASE xml_notas.tipo_nota
                    WHEN 0 THEN xml_notas.emit_documento
                    ELSE xml_notas.dest_documento
                END                                             as participante_cnpj,
                CASE xml_notas.tipo_nota
                    WHEN 0 THEN xml_notas.emit_participante_id
                    ELSE xml_notas.dest_participante_id
                END                                             as participante_id,
                CASE xml_notas.tipo_nota
                    WHEN 0 THEN xml_notas.dest_razao_social
                    ELSE xml_notas.emit_razao_social
                END                                             as cliente_nome,
                CASE xml_notas.tipo_nota
                    WHEN 0 THEN xml_notas.dest_documento
                    ELSE xml_notas.emit_documento
                END                                             as cliente_documento,
                COALESCE(
                    CASE xml_notas.tipo_nota
                        WHEN 0 THEN xml_notas.dest_cliente_id
                        ELSE xml_notas.emit_cliente_id
                    END,
                    xml_notas.cliente_id
                )                                               as cliente_id,
                xml_notas.icms_valor                           as icms_valor,
                xml_notas.pis_valor                            as pis_valor,
                xml_notas.cofins_valor                         as cofins_valor,
                xml_notas.ipi_valor                            as ipi_valor,
                xml_notas.tributos_total                       as tributos_total,
                NULL::text                                     as situacao_cadastral,
                xml_notas.validacao::text                      as validacao_json,
                COALESCE(nfe_status.status, cte_status.status)::text as status_consulta,
                COALESCE(nfe_status.consultado_em, cte_status.consultado_em) as consultado_em
            ")
            ->where('xml_notas.user_id', $userId)
            ->whereRaw("UPPER(COALESCE(xml_notas.tipo_documento, '')) NOT IN ('NFSE', 'NFS-E')");

        $this->applyCommonFiltersXml($q, $f);

        return $q;
    }

    private function efdSubquery(int $userId, array $f): \Illuminate\Database\Query\Builder
    {
        $q = DB::table('efd_notas')
            ->leftJoin('participantes', 'participantes.id', '=', 'efd_notas.participante_id')
            ->leftJoin('clientes', 'clientes.id', '=', 'efd_notas.cliente_id')
            ->leftJoin('nfe_consultas as nfe_status', function ($join) {
                $join->on('nfe_status.user_id', '=', 'efd_notas.user_id')
                    ->on('nfe_status.chave_acesso', '=', 'efd_notas.chave_acesso');
            })
            ->leftJoin('cte_consultas as cte_status', function ($join) {
                $join->on('cte_status.user_id', '=', 'efd_notas.user_id')
                    ->on('cte_status.chave_acesso', '=', 'efd_notas.chave_acesso');
            })
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
                participantes.razao_social                     as participante_nome,
                participantes.documento                        as participante_cnpj,
                participantes.id                               as participante_id,
                clientes.razao_social                          as cliente_nome,
                clientes.documento                             as cliente_documento,
                efd_notas.cliente_id                           as cliente_id,
                NULL::numeric                                  as icms_valor,
                NULL::numeric                                  as pis_valor,
                NULL::numeric                                  as cofins_valor,
                NULL::numeric                                  as ipi_valor,
                NULL::numeric                                  as tributos_total,
                participantes.situacao_cadastral               as situacao_cadastral,
                NULL::text                                     as validacao_json,
                COALESCE(nfe_status.status, cte_status.status)::text as status_consulta,
                COALESCE(nfe_status.consultado_em, cte_status.consultado_em) as consultado_em
            ")
            ->where('efd_notas.user_id', $userId)
            ->where('efd_notas.cancelada', false) // P4: cancelada não é selecionável/cobrável
            ->whereRaw("UPPER(COALESCE(efd_notas.modelo, '')) NOT IN ('00', 'NFSE', 'NFS-E')")
            // P1: a MESMA NF-e está em 'fiscal' e 'contribuicoes'. Sem dedup a lista mostra a
            // nota 2× (e o custo cobraria 2×); manter só a fiscal garante que a validação caia
            // numa origem canônica única (base do dedup das KPIs de status).
            ->whereRaw("(efd_notas.origem_arquivo = 'fiscal' OR NOT EXISTS (SELECT 1 FROM efd_notas f WHERE f.user_id = efd_notas.user_id AND f.origem_arquivo = 'fiscal' AND f.chave_acesso IS NOT NULL AND f.chave_acesso = efd_notas.chave_acesso))");

        $this->applyCommonFiltersEfd($q, $f);

        return $q;
    }

    /**
     * Anexa ao recorte paginado os mesmos sinais usados no resultado do clearance e os
     * perfis das duas partes. Toda a carga e a auditoria são feitas em lote para não criar
     * N+1 ao abrir uma página com até 50 documentos.
     */
    private function enriquecerDetalhesListagem(Collection $notas, int $userId): void
    {
        if ($notas->isEmpty()) {
            return;
        }

        $clientes = Cliente::query()
            ->where('user_id', $userId)
            ->whereIn('id', $notas->pluck('cliente_id')->filter()->unique())
            ->get()
            ->keyBy('id');

        $participanteIds = $notas->pluck('participante_id')->filter()->unique()->values();
        $participanteDocumentos = $notas->pluck('participante_cnpj')
            ->map(fn ($documento) => preg_replace('/\D/', '', (string) $documento))
            ->filter()
            ->unique()
            ->values();

        $participantes = ($participanteIds->isEmpty() && $participanteDocumentos->isEmpty())
            ? collect()
            : Participante::query()
                ->where('user_id', $userId)
                ->where(function ($query) use ($participanteIds, $participanteDocumentos) {
                    if ($participanteIds->isNotEmpty()) {
                        $query->whereIn('id', $participanteIds);
                    }

                    if ($participanteDocumentos->isNotEmpty()) {
                        $method = $participanteIds->isNotEmpty() ? 'orWhereIn' : 'whereIn';
                        $query->{$method}('documento', $participanteDocumentos);
                    }
                })
                ->get();

        $participantesPorId = $participantes->keyBy('id');
        $participantesPorDocumento = $participantes->keyBy(
            fn (Participante $participante) => preg_replace('/\D/', '', (string) $participante->documento)
        );

        $chaves = $notas->pluck('chave')->filter()->unique()->values();
        $snapshots = collect();

        if ($chaves->isNotEmpty()) {
            $snapshots = \App\Models\NfeConsulta::query()
                ->where('user_id', $userId)
                ->whereIn('chave_acesso', $chaves)
                ->get()
                ->map(fn ($snapshot) => $this->formatarSnapshotDetalheListagem($snapshot, 'nfe'))
                ->concat(
                    \App\Models\CteConsulta::query()
                        ->where('user_id', $userId)
                        ->whereIn('chave_acesso', $chaves)
                        ->get()
                        ->map(fn ($snapshot) => $this->formatarSnapshotDetalheListagem($snapshot, 'cte'))
                )
                ->keyBy('chave_acesso');
        }

        $auditorias = collect();
        if ($snapshots->isNotEmpty()) {
            $analise = app(DivergenciaService::class)->analisar($snapshots->values(), $userId, 0);
            $auditorias = $analise['divergencias']
                ->concat($analise['sem_divergencia'])
                ->concat($analise['ruido'])
                ->keyBy('chave_acesso');
        }

        $notas->each(function ($nota) use (
            $clientes,
            $participantesPorId,
            $participantesPorDocumento,
            $snapshots,
            $auditorias
        ) {
            $documento = preg_replace('/\D/', '', (string) ($nota->participante_cnpj ?? ''));
            $nota->cliente_perfil = $clientes->get((int) ($nota->cliente_id ?? 0));
            $nota->participante_perfil = $participantesPorId->get((int) ($nota->participante_id ?? 0))
                ?? $participantesPorDocumento->get($documento);
            $nota->clearance_resultado = $auditorias->get($nota->chave)
                ?? $snapshots->get($nota->chave);
        });
    }

    /**
     * Normaliza NF-e e CT-e para o contrato consumido por DivergenciaService e pela Blade.
     */
    private function formatarSnapshotDetalheListagem(object $snapshot, string $tipo): object
    {
        $isCte = $tipo === 'cte';
        $payload = is_array($snapshot->payload ?? null) ? $snapshot->payload : [];
        $eventos = is_array($snapshot->eventos ?? null) ? $snapshot->eventos : [];
        $status = strtoupper((string) ($snapshot->status ?? 'INDETERMINADO'));
        $valor = $isCte ? ($snapshot->valor_prestacao ?? null) : ($snapshot->valor_total ?? null);
        $tipoDocumento = $isCte ? 'CTE' : strtoupper((string) ($snapshot->tipo_documento ?? 'NFE'));
        $tipoRota = $isCte ? 'cte' : 'nfe';
        $comprovanteLocal = null;

        if (\Illuminate\Support\Facades\Route::has('app.clearance.comprovante')
            && method_exists($this, 'arquivoLocalDfeUrl')) {
            $comprovanteLocal = $this->arquivoLocalDfeUrl($tipoRota, (int) $snapshot->id, 'html', $payload)
                ?: $this->arquivoLocalDfeUrl($tipoRota, (int) $snapshot->id, 'site_receipt', $payload);
        }

        return (object) [
            'id' => $snapshot->id,
            'chave_acesso' => $snapshot->chave_acesso,
            'tipo_documento' => $tipoDocumento,
            'modelo' => $snapshot->modelo ?? ($isCte ? '57' : '55'),
            'numero' => $snapshot->numero ?? null,
            'serie' => $snapshot->serie ?? null,
            'status' => $status,
            'status_label' => $status,
            'status_hex' => $this->statusHexConsultaDfe($status),
            'valor_total' => $valor,
            'valor_total_label' => $valor !== null
                ? 'R$ '.number_format((float) $valor, 2, ',', '.')
                : '—',
            'data_emissao' => $snapshot->data_emissao ?? null,
            'emit_nome' => $snapshot->emit_nome ?? null,
            'emit_cnpj' => $snapshot->emit_cnpj ?? null,
            'emit_uf' => $snapshot->emit_uf ?? null,
            'emit_ie' => $snapshot->emit_ie ?? null,
            'dest_nome' => $snapshot->dest_nome ?? null,
            'dest_cnpj' => $snapshot->dest_cnpj ?? null,
            'dest_uf' => $snapshot->dest_uf ?? null,
            'tomador_nome' => $snapshot->tomador_nome ?? null,
            'tomador_cnpj' => $snapshot->tomador_cnpj ?? null,
            'natureza_operacao' => $snapshot->natureza_operacao ?? null,
            'situacao_ambiente' => data_get(
                $payload,
                ($isCte ? 'cte_clearance' : 'nfe_clearance').'.situacao_ambiente'
            ),
            'consultado_em_label' => $this->formatarDataConsulta(
                $snapshot->consultado_em ?? $snapshot->created_at ?? null
            ),
            'eventos_chips' => collect($eventos)
                ->map(fn ($evento) => [
                    'label' => $this->rotuloEventoDfe((string) ($evento['evento'] ?? '')),
                    'hex' => $this->hexEventoDfe((string) ($evento['evento'] ?? '')),
                    'protocolo' => $evento['protocolo'] ?? null,
                    'data' => $evento['data_autorizacao'] ?? ($evento['data_inclusao'] ?? null),
                ])
                ->filter(fn ($evento) => $evento['label'] !== '')
                ->values()
                ->all(),
            'comprovante_url' => $comprovanteLocal
                ?: ($snapshot->url_html ?? ($snapshot->url_site_receipt ?? null)),
        ];
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
                $sub->where('xml_notas.emit_documento', $cnpj)->orWhere('xml_notas.dest_documento', $cnpj);
            });
        }

        if (($f['tipo_nota'] ?? null) === 'entrada') {
            $q->where('xml_notas.tipo_nota', XmlNota::TIPO_ENTRADA);
        } elseif (($f['tipo_nota'] ?? null) === 'saida') {
            $q->where('xml_notas.tipo_nota', XmlNota::TIPO_SAIDA);
        }

        if (! empty($f['situacao_receita'])) {
            $q->whereRaw($this->snapshotStatusExistsSql('xml_notas'), [$f['situacao_receita'], $f['situacao_receita']]);
        }

        if (! empty($f['modelo'])) {
            $vals = self::MODELO_MAP_XML[$f['modelo']] ?? [$f['modelo']];
            $q->whereIn(DB::raw("UPPER(COALESCE(xml_notas.tipo_documento, ''))"), array_map('strtoupper', $vals));
        }

        if (! empty($f['busca'])) {
            $b = '%'.$f['busca'].'%';
            $q->where(function ($sub) use ($b) {
                $sub->whereRaw('xml_notas.numero_documento::text ILIKE ?', [$b])
                    ->orWhere('xml_notas.chave_acesso', 'ILIKE', $b);
            });
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
            $q->whereRaw($this->snapshotStatusExistsSql('efd_notas'), [$f['situacao_receita'], $f['situacao_receita']]);
        }

        if (! empty($f['modelo'])) {
            $vals = self::MODELO_MAP_EFD[$f['modelo']] ?? [$f['modelo']];
            $q->whereIn('efd_notas.modelo', $vals);
        }

        if (! empty($f['busca'])) {
            $b = '%'.$f['busca'].'%';
            $q->where(function ($sub) use ($b) {
                $sub->whereRaw('efd_notas.numero::text ILIKE ?', [$b])
                    ->orWhere('efd_notas.chave_acesso', 'ILIKE', $b);
            });
        }
    }

    /**
     * Fragmento EXISTS contra o snapshot SEFAZ (nfe_consultas/cte_consultas) por chave.
     * O campo validacao->>'situacao' nunca é escrito — a situação real vive nas snapshots.
     * $notaTable é constante interna ('xml_notas'|'efd_notas'), sem risco de injeção.
     */
    private function snapshotExistsSql(string $notaTable): string
    {
        return "EXISTS (SELECT 1 FROM nfe_consultas s WHERE s.user_id = {$notaTable}.user_id AND s.chave_acesso = {$notaTable}.chave_acesso
            UNION ALL SELECT 1 FROM cte_consultas s WHERE s.user_id = {$notaTable}.user_id AND s.chave_acesso = {$notaTable}.chave_acesso)";
    }

    /** Idem, filtrando por status específico (2 binds: nfe e cte). */
    private function snapshotStatusExistsSql(string $notaTable): string
    {
        return "EXISTS (SELECT 1 FROM nfe_consultas s WHERE s.user_id = {$notaTable}.user_id AND s.chave_acesso = {$notaTable}.chave_acesso AND s.status = ?
            UNION ALL SELECT 1 FROM cte_consultas s WHERE s.user_id = {$notaTable}.user_id AND s.chave_acesso = {$notaTable}.chave_acesso AND s.status = ?)";
    }

    private function modeloBadge(?string $codigo): array
    {
        $codigo = $codigo !== null ? strtoupper(trim($codigo)) : null;

        return match ($codigo) {
            '55' => ['label' => 'NF-e', 'hex' => '#2563eb'],
            '65' => ['label' => 'NFC-e', 'hex' => '#0891b2'],
            '57' => ['label' => 'CT-e', 'hex' => '#7c3aed'],
            '67' => ['label' => 'CT-e OS', 'hex' => '#7c3aed'],
            '00', 'NFSE', 'NFS-E' => ['label' => 'NFS-e', 'hex' => '#047857'],
            '01' => ['label' => 'Modelo 1', 'hex' => '#6b7280'],
            '1B' => ['label' => 'NF Avulsa', 'hex' => '#6b7280'],
            '04' => ['label' => 'NF Produtor', 'hex' => '#6b7280'],
            null, '' => ['label' => 'N/D', 'hex' => '#9ca3af'],
            default => ['label' => $codigo, 'hex' => '#6b7280'],
        };
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
        $saldoAtual = $this->saldoService->getBalance(Auth::user());

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

        // Validação contábil local (enriquecimento; popula *.validacao p/ dashboard, sem cobrança).
        $this->validacaoService->validarNotas($notaIds, $origens, $userId, $tipo);

        // Clearance SEFAZ executado no Laravel (camada de consultas) — dono do débito e do estorno
        // por documento. Substitui o despacho ao webhook n8n, desligado no cutover de 2026-06-07.
        $resultado = app(ClearanceLoteService::class)->iniciar($notaIds, $origens, $tipo, $userId, $tabId);

        return response()->json($resultado, $resultado['http_status'] ?? Response::HTTP_OK);
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

        // Verificar se a importacao pertence ao usuario
        XmlImportacao::where('id', $id)
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
            'tab_id' => 'nullable|string|max:36',
        ]);
        $tipo = $this->normalizarTier($request->input('tipo'));
        $tabId = $request->input('tab_id');
        $origens = array_fill_keys($notaIds, 'xml');

        // Validação contábil local (enriquecimento; popula *.validacao p/ dashboard, sem cobrança).
        $this->validacaoService->validarImportacao($id, $userId, $tipo);

        // Clearance SEFAZ executado no Laravel — dono do débito e do estorno por documento.
        $resultado = app(ClearanceLoteService::class)->iniciar($notaIds, $origens, $tipo, $userId, $tabId);

        return response()->json($resultado, $resultado['http_status'] ?? Response::HTTP_OK);
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

            return redirect('/app/notas?busca='.$efdNota->chave_acesso);
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
            'cruzamentos' => (new CruzamentosConsultasClearanceService)->resumo($userId),
            'catalogoDocumentos' => $this->reconciliacaoService->resumoAlertas($userId),
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
                'numero' => $nota->numero_documento,
                'emitente' => $nota->emitente->razao_social ?? $nota->emit_documento,
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
    private function getClearanceProgressSnapshot(ConsultaLote $lote): ?array
    {
        if (empty($lote->tab_id)) {
            return null;
        }

        $cached = Cache::get("progresso:{$lote->user_id}:{$lote->tab_id}");

        if (! is_array($cached)) {
            return null;
        }

        $cacheStatus = $cached['status'] ?? null;
        $loteStatus = ConsultaLote::normalizeStatus($lote->status);
        $loteAberto = in_array($loteStatus, [ConsultaLote::STATUS_PROCESSANDO, ConsultaLote::STATUS_PENDENTE], true);
        $cacheIntermediario = in_array($cacheStatus, [
            ConsultaLote::STATUS_PROCESSANDO,
            ConsultaLote::STATUS_PENDENTE,
            ConsultaLote::STATUS_CONCLUIDO,
        ], true);
        $cacheTerminalCompativel = $cacheStatus === ConsultaLote::STATUS_ERRO
            ? $loteStatus === ConsultaLote::STATUS_ERRO
            : $cacheStatus === ConsultaLote::STATUS_FINALIZADO && $lote->isFinalizado();

        if (! (($cacheIntermediario && $loteAberto) || $cacheTerminalCompativel)) {
            return null;
        }

        return [
            'status' => $cached['status'] ?? $loteStatus,
            'progresso' => (int) ($cached['progresso'] ?? 0),
            'mensagem' => $cached['mensagem'] ?? null,
            'etapa' => $cached['etapa'] ?? null,
            'total_etapas' => $cached['total_etapas'] ?? null,
            'etapa_label' => $cached['etapa_label'] ?? null,
            'etapas_puladas' => $cached['etapas_puladas'] ?? [],
            'trilha_etapas' => $cached['trilha_etapas'] ?? null,
            'ultima_etapa_concluida' => $cached['ultima_etapa_concluida'] ?? null,
            'consulta_lote_id' => $cached['consulta_lote_id'] ?? $lote->id,
            'updated_at' => $cached['updated_at'] ?? null,
        ];
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

    private function listarUltimasConsultasDfe(int $userId, int $limite = 10)
    {
        return $this->consultaDfeHistoricoQuery($userId)
            ->where('fluxo_origem', 'avulsa')
            ->orderByRaw('COALESCE(consultado_em, created_at) DESC')
            ->orderByDesc('id')
            ->limit($limite)
            ->get()
            ->map(function ($consulta) {
                $consulta->momento_consulta = $this->formatarDataConsulta($consulta->consultado_em ?: $consulta->created_at);

                return $consulta;
            });
    }

    private function consultaDfeHistoricoQuery(int $userId): Builder
    {
        return $this->notaFiscalService->consultaDfeHistoricoQuery($userId);
    }

    private function buscarConsultaDfePorLote(int $userId, int $consultaLoteId): ?object
    {
        return $this->consultaDfeHistoricoQuery($userId)
            ->where('consulta_lote_id', $consultaLoteId)
            ->orderByRaw('COALESCE(consultado_em, created_at) DESC')
            ->orderByDesc('id')
            ->first();
    }

    private function filtrosHistoricoConsultasDfe(Request $request): array
    {
        $tipoDocumento = strtolower((string) $request->input('tipo_documento', ''));
        $status = strtoupper(trim((string) $request->input('status', '')));
        $origemFluxo = strtolower((string) $request->input('origem_fluxo', ''));

        return [
            'busca' => trim((string) $request->input('busca', '')),
            'tipo_documento' => in_array($tipoDocumento, ['nfe', 'nfce', 'cte'], true) ? $tipoDocumento : '',
            'status' => $status,
            'origem_fluxo' => in_array($origemFluxo, ['avulsa', 'lote'], true) ? $origemFluxo : '',
        ];
    }

    private function aplicarFiltrosHistoricoConsultasDfe(Builder $query, array $filtros): void
    {
        if (($filtros['busca'] ?? '') !== '') {
            $busca = '%'.$filtros['busca'].'%';
            $query->where(function ($sub) use ($busca) {
                $sub->where('chave_acesso', 'ILIKE', $busca)
                    ->orWhere('numero', 'ILIKE', $busca)
                    ->orWhere('cliente_nome', 'ILIKE', $busca)
                    ->orWhere('emit_nome', 'ILIKE', $busca)
                    ->orWhere('emit_cnpj', 'ILIKE', $busca)
                    ->orWhere('dest_nome', 'ILIKE', $busca)
                    ->orWhere('dest_cnpj', 'ILIKE', $busca)
                    ->orWhere('tomador_nome', 'ILIKE', $busca)
                    ->orWhere('tomador_cnpj', 'ILIKE', $busca);
            });
        }

        if (($filtros['tipo_documento'] ?? '') !== '') {
            $query->where('tipo_documento', strtoupper($filtros['tipo_documento']));
        }

        if (($filtros['status'] ?? '') !== '') {
            $query->whereRaw('UPPER(status) = ?', [$filtros['status']]);
        }

        if (($filtros['origem_fluxo'] ?? '') !== '') {
            $query->where('fluxo_origem', $filtros['origem_fluxo']);
        }
    }

    private function statusOptionsHistoricoDfe(int $userId): Collection
    {
        return $this->consultaDfeHistoricoQuery($userId)
            ->selectRaw('UPPER(status) as status')
            ->whereNotNull('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');
    }

    private function listarConsultasDfePorLote(int $userId, int $consultaLoteId): Collection
    {
        $nfe = DB::table('nfe_consultas as consulta')
            ->leftJoin('clientes as cliente', 'cliente.id', '=', 'consulta.cliente_id')
            ->selectRaw("
                consulta.id,
                consulta.consulta_lote_id,
                consulta.chave_acesso,
                UPPER(COALESCE(consulta.tipo_documento, 'NFE')) as tipo_documento,
                COALESCE(consulta.modelo, '55') as modelo,
                consulta.numero,
                consulta.serie,
                consulta.status,
                consulta.valor_total,
                consulta.data_emissao,
                consulta.emit_nome,
                consulta.emit_cnpj,
                consulta.emit_uf,
                consulta.emit_ie,
                consulta.dest_nome,
                consulta.dest_cnpj,
                consulta.dest_uf,
                NULL::varchar as tomador_nome,
                NULL::varchar as tomador_cnpj,
                consulta.natureza_operacao,
                consulta.eventos,
                consulta.url_html,
                consulta.url_site_receipt,
                consulta.payload->'nfe_clearance'->>'situacao_ambiente' as situacao_ambiente,
                cliente.razao_social as cliente_nome,
                consulta.consultado_em,
                consulta.created_at
            ")
            ->where('consulta.user_id', $userId)
            ->where('consulta.consulta_lote_id', $consultaLoteId);

        $cte = DB::table('cte_consultas as consulta')
            ->leftJoin('clientes as cliente', 'cliente.id', '=', 'consulta.cliente_id')
            ->selectRaw("
                consulta.id,
                consulta.consulta_lote_id,
                consulta.chave_acesso,
                UPPER(COALESCE(consulta.tipo_documento, 'CTE')) as tipo_documento,
                COALESCE(consulta.modelo, '57') as modelo,
                consulta.numero,
                consulta.serie,
                consulta.status,
                consulta.valor_prestacao as valor_total,
                consulta.data_emissao,
                consulta.emit_nome,
                consulta.emit_cnpj,
                consulta.emit_uf,
                consulta.emit_ie,
                consulta.dest_nome,
                consulta.dest_cnpj,
                consulta.dest_uf,
                consulta.tomador_nome,
                consulta.tomador_cnpj,
                consulta.natureza_operacao,
                consulta.eventos,
                consulta.url_html,
                consulta.url_site_receipt,
                consulta.payload->'cte_clearance'->>'situacao_ambiente' as situacao_ambiente,
                cliente.razao_social as cliente_nome,
                consulta.consultado_em,
                consulta.created_at
            ")
            ->where('consulta.user_id', $userId)
            ->where('consulta.consulta_lote_id', $consultaLoteId);

        $resultados = DB::query()
            ->fromSub($nfe->unionAll($cte), 'consultas')
            ->orderByRaw('COALESCE(consultado_em, created_at) DESC')
            ->orderByDesc('id')
            ->get();

        $chaves = $resultados->pluck('chave_acesso')->filter()->unique()->values();
        $xmlByChave = XmlNota::query()
            ->where('user_id', $userId)
            ->whereIn('chave_acesso', $chaves)
            ->pluck('id', 'chave_acesso')
            ->all();
        $efdByChave = EfdNota::query()
            ->where('user_id', $userId)
            ->whereIn('chave_acesso', $chaves)
            ->pluck('id', 'chave_acesso')
            ->all();

        $resultadosPersistidos = $resultados->map(function ($resultado) use ($xmlByChave, $efdByChave) {
            $status = strtoupper((string) ($resultado->status ?? 'INDETERMINADO'));
            $resultado->status_label = $status;
            $resultado->status_hex = $this->statusHexConsultaDfe($status);
            $resultado->valor_total_label = $resultado->valor_total !== null
                ? 'R$ '.number_format((float) $resultado->valor_total, 2, ',', '.')
                : '—';
            $resultado->data_emissao_label = $this->formatarDataCurta($resultado->data_emissao);
            $resultado->consultado_em_label = $this->formatarDataConsulta($resultado->consultado_em ?: $resultado->created_at);
            $resultado->participante_label = $resultado->dest_nome
                ?: $resultado->tomador_nome
                ?: $resultado->dest_cnpj
                ?: $resultado->tomador_cnpj
                ?: 'Não informado';
            $chave = trim((string) $resultado->chave_acesso);
            $resultado->detalhe_url = match (true) {
                $chave !== '' && isset($xmlByChave[$chave]) => route('app.notas.detalhes', ['origem' => 'xml', 'id' => $xmlByChave[$chave]]),
                $chave !== '' && isset($efdByChave[$chave]) => route('app.notas.detalhes', ['origem' => 'efd', 'id' => $efdByChave[$chave]]),
                default => null,
            };
            $resultado->eventos = is_string($resultado->eventos ?? null)
                ? (json_decode($resultado->eventos, true) ?: [])
                : (array) ($resultado->eventos ?? []);
            $resultado->eventos_chips = collect($resultado->eventos)
                ->map(fn ($e) => [
                    'label' => $this->rotuloEventoDfe((string) ($e['evento'] ?? '')),
                    'hex' => $this->hexEventoDfe((string) ($e['evento'] ?? '')),
                    'protocolo' => $e['protocolo'] ?? null,
                    'data' => $e['data_autorizacao'] ?? ($e['data_inclusao'] ?? null),
                ])
                ->filter(fn ($c) => $c['label'] !== '')
                ->values()
                ->all();
            $resultado->comprovante_url = $resultado->url_html ?: ($resultado->url_site_receipt ?: null);
            $resultado->ambiente_homologacao = str_contains(
                mb_strtoupper((string) ($resultado->situacao_ambiente ?? '')), 'HOMOLOGA'
            );

            $resultado->origem_acervo_label = null;
            $resultado->origem_acervo_hex = null;
            $resultado->ordem_lote = null;

            return $resultado;
        });

        $precheck = $this->getBuscaAcervoPrecheck($userId, $consultaLoteId);
        $resultadosAcervo = collect($precheck['resultados'] ?? [])
            ->map(fn ($resultado) => (object) $resultado);
        $ordemPorChave = collect($precheck['ordem_por_chave'] ?? []);

        return $resultadosPersistidos
            ->concat($resultadosAcervo)
            ->sortBy(function ($resultado) use ($ordemPorChave) {
                $chave = trim((string) ($resultado->chave_acesso ?? ''));

                return $ordemPorChave->get($chave, PHP_INT_MAX);
            })
            ->values();
    }

    private function buscarNotaAcervoPorChave(int $userId, string $chaveAcesso): ?XmlNota
    {
        return XmlNota::query()
            ->where('user_id', $userId)
            ->where('chave_acesso', $chaveAcesso)
            ->first();
    }

    private function formatarResultadoConsultaDfe(object $nota, int $userId): array
    {
        // Contraparte mascarada (consulta sem certificado) identificada no acervo:
        // exibe a razão social real do participante em vez de 'RAIZ***'.
        $destIdentificado = app(\App\Services\Clearance\CnpjMascaradoResolver::class)
            ->identificarParticipante($userId, $nota->dest_cnpj ?? null, $nota->dest_nome ?? null);

        return [
            'id' => $nota->id,
            'consulta_lote_id' => $nota->consulta_lote_id,
            'tipo_documento' => strtoupper((string) ($nota->tipo_documento ?? 'NFE')),
            'modelo' => $nota->modelo ?? null,
            'nfe_id' => $nota->chave_acesso,
            'numero_nota' => $nota->numero,
            'numero' => $nota->numero,
            'serie' => $nota->serie,
            'valor_total' => $nota->valor_total,
            'valor_total_label' => $nota->valor_total !== null
                ? 'R$ '.number_format((float) $nota->valor_total, 2, ',', '.')
                : '—',
            'data_emissao' => $this->formatarDataCurta($nota->data_emissao),
            'emit' => $nota->emit_nome ?: $nota->emit_cnpj,
            'emit_cnpj' => $nota->emit_cnpj ?? null,
            'dest' => $destIdentificado?->razao_social
                ?: ($nota->dest_nome ?: $nota->tomador_nome ?: $nota->dest_cnpj ?: $nota->tomador_cnpj),
            'dest_identificado_acervo' => $destIdentificado !== null,
            'dest_cnpj' => $destIdentificado?->documento ?? ($nota->dest_cnpj ?? null),
            'tomador_nome' => $nota->tomador_nome ?? null,
            'tomador_cnpj' => $nota->tomador_cnpj ?? null,
            'cliente_nome' => $nota->cliente_nome,
            'situacao' => strtoupper((string) ($nota->status ?? 'INDETERMINADO')),
            'situacao_hex' => $this->statusHexConsultaDfe($nota->status ?? null),
            'consultado_em' => $this->formatarDataConsulta($nota->consultado_em),
            'detalhe_url' => $this->resolverDetalheNotaUrl($userId, $nota->chave_acesso),
            'detalhes' => $this->detalhesSnapshotDfe($nota),
        ];
    }

    /**
     * Detalhes ricos do snapshot (nfe_consultas/cte_consultas) pra tela de resultado da busca
     * avulsa: partes completas, eventos, totais, produtos/componentes e links do comprovante.
     * O histórico unificado (consultaDfeHistoricoQuery) só traz colunas magras — recarrega a linha.
     */
    private function detalhesSnapshotDfe(object $nota): ?array
    {
        $isCte = strtoupper((string) ($nota->tipo_documento ?? '')) === 'CTE';
        $snap = $isCte
            ? \App\Models\CteConsulta::find($nota->id)
            : \App\Models\NfeConsulta::find($nota->id);

        if (! $snap) {
            return null;
        }

        $eventos = is_array($snap->eventos) ? $snap->eventos : (json_decode((string) $snap->eventos, true) ?: []);

        // Datas da SEFAZ vêm como '13/08/2025 às 08:36:57-04:00' — normaliza pra ordenar
        // a linha do tempo e exibir 'd/m/Y H:i' (hora local da SEFAZ, offset descartado).
        $parseDataEvento = function (?string $raw): ?\Carbon\Carbon {
            if (! $raw) {
                return null;
            }
            $s = trim((string) preg_replace('/[+-]\d{2}:\d{2}$/', '', (string) preg_replace('/\s*às\s*/u', ' ', trim($raw))));

            try {
                return \Carbon\Carbon::createFromFormat('d/m/Y H:i:s', $s);
            } catch (\Throwable) {
                return null;
            }
        };

        $eventosTimeline = collect($eventos)
            ->map(function ($e) use ($parseDataEvento) {
                $dt = $parseDataEvento($e['data_autorizacao'] ?? null) ?? $parseDataEvento($e['data_inclusao'] ?? null);

                return [
                    'label' => $this->rotuloEventoDfe((string) ($e['evento'] ?? '')),
                    'descricao' => trim((string) ($e['evento'] ?? '')) ?: null,
                    'hex' => $this->hexEventoDfe((string) ($e['evento'] ?? '')),
                    'protocolo' => $e['protocolo'] ?? null,
                    'data_label' => $dt?->format('d/m/Y H:i') ?? ($e['data_autorizacao'] ?? ($e['data_inclusao'] ?? null)),
                    'ordem' => $dt?->getTimestamp(),
                ];
            })
            ->filter(fn ($c) => $c['label'] !== '')
            ->sortBy(fn ($c) => $c['ordem'] ?? PHP_INT_MAX)
            ->values()
            ->all();

        // Primeiro ponto da linha do tempo: a emissão do documento (quando conhecida).
        if ($snap->data_emissao) {
            array_unshift($eventosTimeline, [
                'label' => 'Emissão',
                'descricao' => null,
                'hex' => '#6b7280',
                'protocolo' => null,
                'data_label' => $this->formatarDataCurta($snap->data_emissao),
                'ordem' => null,
            ]);
        }

        $fmtDoc = function (?string $doc): ?string {
            $d = preg_replace('/\D/', '', (string) $doc);

            return match (strlen($d)) {
                14 => preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $d),
                11 => preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $d),
                default => $doc ?: null,
            };
        };
        $local = fn (?string $municipio, ?string $uf) => trim(implode('/', array_filter([$municipio, $uf]))) ?: null;
        $brl = fn ($v) => $v !== null ? 'R$ '.number_format((float) $v, 2, ',', '.') : null;

        // Parte mascarada (consulta sem certificado) identificada no acervo do usuário.
        $resolver = app(\App\Services\Clearance\CnpjMascaradoResolver::class);
        $identificar = fn (?string $doc, ?string $nome) => $resolver->identificarParticipante((int) $snap->user_id, $doc, $nome);

        // Consulta pública: SEFAZ mascara contraparte, descrição de itens, totais e tributos.
        // O banner da view explica e oferece o atalho pro cadastro do certificado A1 — a menos
        // que o usuário já tenha um válido (aí o copy muda: consumo do cert ainda não existe).
        $semCertificado = (bool) ($snap->consulta_sem_certificado ?? false);
        $certificadoCadastrado = $semCertificado && \App\Models\CertificadoDigital::query()
            ->where('user_id', $snap->user_id)
            ->whereDate('validade', '>=', now()->toDateString())
            ->exists();

        $detalhes = [
            'natureza_operacao' => $snap->natureza_operacao,
            'consulta_sem_certificado' => $semCertificado,
            'certificado_cadastrado' => $certificadoCadastrado,
            'versao_xml' => $snap->versao_xml,
            'comprovante_url' => $snap->url_html ?: ($snap->url_site_receipt ?: null),
            'url_xml' => $snap->url_xml,
            'eventos_timeline' => $eventosTimeline,
            'emit' => [
                'nome' => $snap->emit_nome,
                'documento' => $fmtDoc($snap->emit_cnpj),
                'ie' => $snap->emit_ie,
                'local' => $local($snap->emit_municipio, $snap->emit_uf),
            ],
        ];

        if ($isCte) {
            $componentes = is_array($snap->componentes) ? $snap->componentes : (json_decode((string) $snap->componentes, true) ?: []);

            return array_merge($detalhes, [
                'tipo_servico' => $snap->tipo_servico,
                'cfop' => $snap->cfop,
                'modal' => $snap->modal,
                'trajeto' => trim(implode(' → ', array_filter([$snap->uf_inicio, $snap->uf_fim]))) ?: null,
                'valor_carga_label' => $brl($snap->valor_carga),
                'valor_prestacao_label' => $brl($snap->valor_prestacao),
                'nfes_referenciadas_count' => (int) ($snap->nfes_referenciadas_count ?? 0),
                'componentes' => collect($componentes)
                    ->map(fn ($c) => ['nome' => $c['nome'] ?? '—', 'valor' => $c['valor'] ?? null])
                    ->values()
                    ->all(),
                'partes' => collect([
                    ['papel' => 'Tomador', 'cnpj' => $snap->tomador_cnpj, 'cpf' => $snap->tomador_cpf, 'nome' => $snap->tomador_nome, 'local' => $local($snap->tomador_municipio, $snap->tomador_uf)],
                    ['papel' => 'Remetente', 'cnpj' => $snap->remet_cnpj, 'cpf' => $snap->remet_cpf, 'nome' => $snap->remet_nome, 'local' => $local(null, $snap->remet_uf)],
                    ['papel' => 'Destinatário', 'cnpj' => $snap->dest_cnpj, 'cpf' => $snap->dest_cpf, 'nome' => $snap->dest_nome, 'local' => $local(null, $snap->dest_uf)],
                    ['papel' => 'Expedidor', 'cnpj' => $snap->expedidor_cnpj, 'cpf' => null, 'nome' => null, 'local' => null],
                    ['papel' => 'Recebedor', 'cnpj' => $snap->recebedor_cnpj, 'cpf' => null, 'nome' => null, 'local' => null],
                ])
                    ->map(function ($p) use ($identificar, $fmtDoc) {
                        $ident = $identificar($p['cnpj'], $p['nome']);

                        return [
                            'papel' => $p['papel'],
                            'nome' => $ident?->razao_social ?: $p['nome'],
                            'documento' => $fmtDoc($ident?->documento ?: ($p['cnpj'] ?: $p['cpf'])),
                            'local' => $p['local'],
                            'identificado_acervo' => $ident !== null,
                        ];
                    })
                    ->filter(fn ($p) => $p['nome'] || $p['documento'])
                    ->values()
                    ->all(),
            ]);
        }

        $totais = is_array($snap->totais) ? $snap->totais : (json_decode((string) $snap->totais, true) ?: []);
        $produtos = is_array($snap->produtos) ? $snap->produtos : (json_decode((string) $snap->produtos, true) ?: []);

        $destIdentificado = $identificar($snap->dest_cnpj, $snap->dest_nome);

        return array_merge($detalhes, [
            'tipo_operacao' => $snap->tipo_operacao,
            'dest' => [
                'nome' => $destIdentificado?->razao_social ?: $snap->dest_nome,
                'documento' => $fmtDoc($destIdentificado?->documento ?: ($snap->dest_cnpj ?: $snap->dest_cpf)),
                'local' => $local($snap->dest_municipio, $snap->dest_uf),
                'identificado_acervo' => $destIdentificado !== null,
            ],
            'totais' => collect($totais)
                ->filter(fn ($v) => is_scalar($v) && $v !== '' && $v !== null)
                ->map(function ($v, $k) {
                    $label = ucfirst(str_replace('_', ' ', preg_replace('/^normalizado_/', '', (string) $k)));

                    return ['label' => $label, 'valor' => is_numeric($v) ? 'R$ '.number_format((float) $v, 2, ',', '.') : (string) $v];
                })
                ->values()
                ->all(),
            'produtos' => collect($produtos)
                ->map(fn ($p) => [
                    'descricao' => $p['descricao'] ?? ($p['nome'] ?? '—'),
                    'ncm' => $p['ncm'] ?? null,
                    'cfop' => $p['cfop'] ?? null,
                    'quantidade' => $p['quantidade'] ?? ($p['qtd'] ?? null),
                    'valor' => $p['valor'] ?? ($p['valor_total'] ?? null),
                ])
                ->values()
                ->all(),
        ]);
    }

    private function formatarResultadoXmlAcervo(XmlNota $nota): array
    {
        $situacao = strtoupper((string) data_get($nota->validacao, 'situacao', 'SALVA_NO_ACERVO'));
        $chave = (string) $nota->chave_acesso;
        $modeloDerivado = strlen($chave) === 44 ? substr($chave, 20, 2) : null;

        return [
            'id' => $nota->id,
            'consulta_lote_id' => null,
            'tipo_documento' => strtoupper((string) ($nota->tipo_documento ?: 'NFE')),
            'modelo' => $modeloDerivado,
            'nfe_id' => $nota->chave_acesso,
            'numero_nota' => $nota->numero_documento,
            'numero' => $nota->numero_documento,
            'serie' => $nota->serie,
            'valor_total' => $nota->valor_total,
            'valor_total_label' => $nota->valor_total !== null
                ? 'R$ '.number_format((float) $nota->valor_total, 2, ',', '.')
                : '—',
            'data_emissao' => optional($nota->data_emissao)->format('d/m/Y H:i'),
            'emit' => $nota->emit_razao_social ?: $nota->emit_documento,
            'emit_cnpj' => $nota->emit_documento,
            'dest' => $nota->dest_razao_social ?: $nota->dest_documento,
            'dest_cnpj' => $nota->dest_documento,
            'tomador_nome' => null,
            'tomador_cnpj' => null,
            'cliente_nome' => $nota->cliente?->razao_social,
            'situacao' => $situacao,
            'situacao_hex' => $this->statusHexConsultaDfe($situacao),
            'consultado_em' => $this->formatarDataConsulta($nota->updated_at ?: $nota->created_at),
            'detalhe_url' => route('app.notas.detalhes', ['origem' => 'xml', 'id' => $nota->id]),
        ];
    }

    private function resolverDetalheNotaUrl(int $userId, ?string $chaveAcesso): ?string
    {
        $chave = trim((string) $chaveAcesso);

        if ($chave === '') {
            return null;
        }

        $xmlNotaId = XmlNota::query()
            ->where('user_id', $userId)
            ->where('chave_acesso', $chave)
            ->value('id');

        if ($xmlNotaId) {
            return route('app.notas.detalhes', ['origem' => 'xml', 'id' => $xmlNotaId]);
        }

        $efdNotaId = EfdNota::query()
            ->where('user_id', $userId)
            ->where('chave_acesso', $chave)
            ->value('id');

        if ($efdNotaId) {
            return route('app.notas.detalhes', ['origem' => 'efd', 'id' => $efdNotaId]);
        }

        return null;
    }

    private function resumirResultadosClearance(Collection $resultados): array
    {
        return [
            'total' => $resultados->count(),
            'ja_no_acervo' => $resultados->filter(fn ($item) => ($item->status_label ?? '') === 'JA_NO_ACERVO')->count(),
            'autorizadas' => $resultados->filter(fn ($item) => in_array($item->status_label ?? '', ['AUTORIZADA', 'NEGATIVA'], true))->count(),
            'alertas' => $resultados->filter(fn ($item) => in_array($item->status_label ?? '', ['CANCELADA', 'DENEGADA', 'INUTILIZADA'], true))->count(),
            'indeterminadas' => $resultados->filter(fn ($item) => in_array($item->status_label ?? '', ['INDETERMINADO', 'NAO_ENCONTRADA'], true))->count(),
            'erros' => $resultados->filter(function ($item) {
                $status = strtoupper((string) ($item->status_label ?? ''));

                return str_starts_with($status, 'ERRO');
            })->count(),
        ];
    }

    private function statusMetaLote(?string $status): array
    {
        return match (ConsultaLote::normalizeStatus($status)) {
            ConsultaLote::STATUS_PROCESSANDO => ['label' => 'Processando', 'hex' => '#b45309'],
            ConsultaLote::STATUS_FINALIZADO => ['label' => 'Finalizado', 'hex' => '#047857'],
            ConsultaLote::STATUS_ERRO => ['label' => 'Erro', 'hex' => '#dc2626'],
            default => ['label' => 'Pendente', 'hex' => '#9ca3af'],
        };
    }

    private function statusHexConsultaDfe(?string $status): string
    {
        return match (strtoupper((string) $status)) {
            'JA_NO_ACERVO' => '#4338ca',
            'AUTORIZADA', 'NEGATIVA' => '#047857',
            'CANCELADA', 'DENEGADA', 'INUTILIZADA' => '#dc2626',
            'INDETERMINADO', 'NAO_ENCONTRADA' => '#b45309',
            'ERRO_PARAMETRO', 'ERRO_PROVEDOR' => '#6b7280',
            default => '#374151',
        };
    }

    /** Rótulo curto do evento DF-e p/ chip. '' = ignorar (evento não relevante). */
    private function rotuloEventoDfe(string $evento): string
    {
        $e = mb_strtoupper($evento);

        return match (true) {
            str_contains($e, 'CANCELAMENTO') => 'Cancelada',
            str_contains($e, 'CORRECAO') || str_contains($e, 'CORREÇÃO') || str_contains($e, 'CCE') => 'CC-e',
            str_contains($e, 'DENEGA') => 'Denegada',
            str_contains($e, 'AUTORIZA') => 'Autorizada',
            default => '',
        };
    }

    /** Cor do chip por tipo de evento (hex inline — Design System DANFE). */
    private function hexEventoDfe(string $evento): string
    {
        $e = mb_strtoupper($evento);

        return match (true) {
            str_contains($e, 'CANCELAMENTO') || str_contains($e, 'DENEGA') => '#b91c1c',
            str_contains($e, 'CORRECAO') || str_contains($e, 'CORREÇÃO') || str_contains($e, 'CCE') => '#b45309',
            default => '#15803d',
        };
    }

    private function formatarDataCurta($valor): ?string
    {
        if (empty($valor)) {
            return null;
        }

        if ($this->isInvalidDatePlaceholder($valor)) {
            return null;
        }

        try {
            return Carbon::parse($valor)->format('d/m/Y H:i');
        } catch (\Throwable) {
            return is_string($valor) && ! $this->isInvalidDatePlaceholder($valor) ? $valor : null;
        }
    }

    private function formatarDataConsulta($valor): ?string
    {
        if (empty($valor)) {
            return null;
        }

        if ($this->isInvalidDatePlaceholder($valor)) {
            return null;
        }

        try {
            return Carbon::parse($valor)->format('d/m/Y H:i');
        } catch (\Throwable) {
            return is_string($valor) && ! $this->isInvalidDatePlaceholder($valor) ? $valor : null;
        }
    }

    private function isInvalidDatePlaceholder($valor): bool
    {
        if (! is_string($valor)) {
            return false;
        }

        return in_array(strtolower(trim($valor)), [
            'invalid datetime',
            'invalid date',
            'invalid date time',
            'nan',
            'null',
            'undefined',
        ], true);
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
