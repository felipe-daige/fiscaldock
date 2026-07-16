<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Concerns\RespondeAjax;
use App\Http\Controllers\Concerns\SetsDownloadToken;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\ConsultaResultado;
use App\Models\Participante;
use App\Models\ParticipanteScore;
use App\Services\Consultas\ResultadoDetalhePresenter;
use App\Services\Reforma\CreditoRiscoReformaService;
use App\Services\Risk\Export\RiskScoreReportBuilder;
use App\Services\Risk\Export\RiskScoreXlsxBuilder;
use App\Services\Risk\RiscoCreditoCpfService;
use App\Services\RiskScoreService;
use App\Support\CsvExport;
use App\Support\ParticipanteOrigem;
use App\Support\PdfReport;
use App\Support\Reports\XlsxReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RiskScoreController extends Controller
{
    use RespondeAjax;
    use SetsDownloadToken;

    private const AUTH_VIEW_PREFIX = 'autenticado.risk.';

    private const AUTH_LAYOUT_VIEW = 'autenticado.layouts.app';

    public function __construct(
        protected RiskScoreService $riskScoreService,
        protected CreditoRiscoReformaService $creditoReforma,
        protected RiscoCreditoCpfService $riscoCreditoCpf,
    ) {}

    /**
     * Dashboard de Risk Score.
     */
    public function index(Request $request)
    {
        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $userId = Auth::id();

        $busca = trim((string) $request->input('busca', ''));
        $filtroClassificacao = $request->input('classificacao', 'todos');
        $filtroStatus = in_array($request->input('status'), ['consultados', 'nao_consultados'], true)
            ? $request->input('status')
            : 'todos';
        $filtroTipo = in_array($request->input('tipo'), ['cliente', 'participante'], true)
            ? $request->input('tipo')
            : 'todos';
        $filtroCredito = in_array($request->input('credito'), ['gera', 'parcial', 'nao_gera', 'indefinido'], true)
            ? $request->input('credito')
            : 'todos';
        $scoreMin = $this->scoreFiltro($request->input('score_min'));
        $scoreMax = $this->scoreFiltro($request->input('score_max'));
        if ($scoreMin !== null && $scoreMax !== null && $scoreMin > $scoreMax) {
            [$scoreMin, $scoreMax] = [$scoreMax, $scoreMin];
        }
        $filtrosSomenteConsultados = $filtroCredito !== 'todos'
            || $scoreMin !== null
            || $scoreMax !== null;

        // SÓ CNPJ (14 dígitos) — CPF fica de fora das listas.
        $cnpjRaw = "length(regexp_replace(coalesce(documento, ''), '[^0-9]', '', 'g')) = 14";

        // Filtro de visualização por cliente. Começa em "Todos os CNPJs" por padrão; só
        // restringe quando um cliente específico é escolhido. Escopo de um cliente = o próprio
        // CNPJ + os participantes daquele cliente.
        $clientes = Cliente::where('user_id', $userId)
            ->orderByDesc('is_empresa_propria')
            ->orderBy('razao_social')
            ->get();

        $clienteParam = $request->query('cliente_id');
        $clienteSelecionadoId = null;
        if ($clienteParam !== null && $clienteParam !== 'todos' && ctype_digit((string) $clienteParam)) {
            $clienteSelecionadoId = optional($clientes->firstWhere('id', (int) $clienteParam))->id;
        }
        $verTodos = $clienteSelecionadoId === null;

        // Escopo de cliente para ParticipanteScore (alvo = cliente OU participante daquele cliente).
        $escopoClienteScore = function ($query) use ($verTodos, $clienteSelecionadoId, $cnpjRaw) {
            $query->where(function ($alvo) use ($cnpjRaw) {
                $alvo->whereHas('participante', fn ($p) => $p->whereRaw($cnpjRaw))
                    ->orWhereHas('cliente', fn ($c) => $c->whereRaw($cnpjRaw));
            });

            if (! $verTodos && $clienteSelecionadoId) {
                $query->where(function ($q) use ($clienteSelecionadoId) {
                    $q->where('cliente_id', $clienteSelecionadoId)
                        ->orWhereHas('participante', fn ($p) => $p->where('cliente_id', $clienteSelecionadoId));
                });
            }
        };

        // Estatisticas (KPIs) — escopadas pelo cliente selecionado.
        $estatisticas = $this->riskScoreService->getEstatisticas($userId, $escopoClienteScore);

        // Score ligado a um participante "PROPRIO" (a própria empresa, materializada como
        // participante pela tela Minha Empresa) é duplicata do cliente empresa própria, que já
        // aparece nas listas pelo lado `cliente`. Mesma regra do DashboardDataService.
        $excluirParticipanteProprio = fn ($q) => $q->whereDoesntHave('participante', fn ($p) => $p->where('origem_tipo', 'PROPRIO'));

        // CONSULTADOS: têm score (participante OU cliente). Ordem por CLASSIFICAÇÃO primeiro
        // (crítico → baixo), depois pelo score numérico. A classificação leva o piso por certidão
        // positiva (RiskScoreService::pisoPorCertidoes) — sem ordenar por ela, um fornecedor com
        // certidão positiva ficaria "Alto Risco" porém afundado no fim (score ponderado baixo).
        $consultadosQuery = ParticipanteScore::where('user_id', $userId)
            ->with(['participante', 'cliente'])
            ->tap($excluirParticipanteProprio)
            ->orderByRaw("CASE classificacao WHEN 'critico' THEN 5 WHEN 'alto' THEN 4 WHEN 'medio' THEN 3 WHEN 'baixo' THEN 2 WHEN 'inconclusivo' THEN 1 ELSE 0 END DESC")
            ->orderByRaw('score_total desc nulls last')
            ->orderByDesc('ultima_consulta_em');

        $escopoClienteScore($consultadosQuery);

        if ($filtroClassificacao !== 'todos') {
            $consultadosQuery->where('classificacao', $filtroClassificacao);
        }
        if ($filtroStatus === 'nao_consultados') {
            $consultadosQuery->whereRaw('1 = 0');
        }
        if ($filtroTipo === 'cliente') {
            $consultadosQuery->whereNotNull('cliente_id')->whereNull('participante_id');
        } elseif ($filtroTipo === 'participante') {
            $consultadosQuery->whereNotNull('participante_id');
        }
        if ($scoreMin !== null) {
            $consultadosQuery->where('score_total', '>=', $scoreMin);
        }
        if ($scoreMax !== null) {
            $consultadosQuery->where('score_total', '<=', $scoreMax);
        }
        match ($filtroCredito) {
            'gera' => $consultadosQuery->where('score_credito_reforma', '<=', 0),
            'parcial' => $consultadosQuery->whereBetween('score_credito_reforma', [1, 99]),
            'nao_gera' => $consultadosQuery->where('score_credito_reforma', '>=', 100),
            'indefinido' => $consultadosQuery->whereNull('score_credito_reforma'),
            default => null,
        };

        if ($busca !== '') {
            $filtroAlvo = function ($q) use ($busca) {
                $q->where('razao_social', 'ilike', "%{$busca}%")->orWhere('documento', 'like', "%{$busca}%");
            };
            $consultadosQuery->where(function ($q) use ($filtroAlvo) {
                $q->whereHas('participante', $filtroAlvo)->orWhereHas('cliente', $filtroAlvo);
            });
        }

        $consultados = $consultadosQuery->paginate(20, ['*'], 'page')->withQueryString();

        // NÃO CONSULTADOS: participantes + clientes (só CNPJ) ainda sem score — lista de "a consultar".
        // Escopo por cliente: participantes pela coluna cliente_id; o próprio cliente pela coluna id.
        $buildNaoConsultados = function (string $model, string $tipo) use ($userId, $cnpjRaw, $busca, $verTodos, $clienteSelecionadoId, $filtroStatus, $filtroTipo, $filtrosSomenteConsultados) {
            $query = $model::where('user_id', $userId)
                ->whereRaw($cnpjRaw)
                ->whereDoesntHave('score')
                // O participante "PROPRIO" é a própria empresa duplicada; ela já aparece pelo
                // lado `cliente`. Não filtra o lado cliente (empresa própria deve permanecer).
                ->when($tipo === 'participante', fn ($q) => $q->where(fn ($w) => $w->where('origem_tipo', '!=', 'PROPRIO')->orWhereNull('origem_tipo')))
                ->when($filtroStatus === 'consultados', fn ($q) => $q->whereRaw('1 = 0'))
                ->when($filtrosSomenteConsultados, fn ($q) => $q->whereRaw('1 = 0'))
                ->when($filtroTipo !== 'todos' && $filtroTipo !== $tipo, fn ($q) => $q->whereRaw('1 = 0'))
                ->when($busca !== '', fn ($q) => $q->where(fn ($w) => $w->where('razao_social', 'ilike', "%{$busca}%")->orWhere('documento', 'like', "%{$busca}%")));

            if (! $verTodos && $clienteSelecionadoId) {
                $coluna = $tipo === 'cliente' ? 'id' : 'cliente_id';
                $query->where($coluna, $clienteSelecionadoId);
            }

            return $query->selectRaw("'{$tipo}' as tipo, id, razao_social, nome_fantasia, documento, uf");
        };

        $uniao = $buildNaoConsultados(Participante::class, 'participante')
            ->unionAll($buildNaoConsultados(Cliente::class, 'cliente'));

        $naoConsultados = \Illuminate\Support\Facades\DB::query()
            ->fromSub($uniao, 'nc')
            ->orderBy('razao_social')
            ->paginate(20, ['*'], 'nc')
            ->withQueryString();

        // CPF não entra em Consulta CNPJ nem em participante_scores. Ele aparece numa seção
        // própria como risco de crédito não avaliado, sem ser misturado aos CNPJs pendentes.
        // Filtros de score/consulta escondem a seção porque ainda não há fonte de crédito PF.
        $mostrarCpfs = $filtroStatus === 'todos'
            && $filtroClassificacao === 'todos'
            && ! $filtrosSomenteConsultados
            && $filtroTipo !== 'cliente';
        $cpfsQuery = Participante::where('user_id', $userId)
            ->somenteCpf()
            ->excludingEmpresaPropria()
            ->when(! $mostrarCpfs, fn ($q) => $q->whereRaw('1 = 0'))
            ->when($busca !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('razao_social', 'ilike', "%{$busca}%")
                ->orWhere('documento', 'like', "%{$busca}%")))
            ->when(! $verTodos && $clienteSelecionadoId, fn ($q) => $q->where('cliente_id', $clienteSelecionadoId))
            ->orderBy('razao_social');
        $cpfsSemAvaliacao = $cpfsQuery->paginate(20, ['id', 'razao_social', 'nome_fantasia', 'documento', 'uf'], 'cpf')
            ->withQueryString();

        // Papel comercial dos PARTICIPANTES exibidos, pelas notas EFD: entrada = nós compramos
        // dele (Fornecedor); saida = nós vendemos pra ele (Comprador); os dois = Ambos.
        $idsParticipantes = collect();
        foreach ($consultados as $sc) {
            if ($sc->participante_id) {
                $idsParticipantes->push($sc->participante_id);
            }
        }
        foreach ($naoConsultados as $item) {
            if (($item->tipo ?? null) === 'participante') {
                $idsParticipantes->push($item->id);
            }
        }
        foreach ($cpfsSemAvaliacao as $cpf) {
            $idsParticipantes->push($cpf->id);
        }

        $papeisParticipante = [];
        if ($idsParticipantes->isNotEmpty()) {
            $linhas = \Illuminate\Support\Facades\DB::table('efd_notas')
                ->select('participante_id', 'tipo_operacao')
                ->where('user_id', $userId)
                ->whereIn('participante_id', $idsParticipantes->unique()->values()->all())
                ->groupBy('participante_id', 'tipo_operacao')
                ->get();
            foreach ($linhas as $linha) {
                $papeisParticipante[$linha->participante_id][$linha->tipo_operacao] = true;
            }
        }

        // Em risco critico (para alerta) — participante ou cliente, escopado pelo cliente
        $emRiscoCriticoQuery = ParticipanteScore::where('user_id', $userId)
            ->where('classificacao', 'critico')
            ->with(['participante', 'cliente'])
            ->tap($excluirParticipanteProprio)
            ->limit(5);
        $escopoClienteScore($emRiscoCriticoQuery);
        $emRiscoCritico = $emRiscoCriticoQuery->get();

        $data = [
            'estatisticas' => $estatisticas,
            'consultados' => $consultados,
            'naoConsultados' => $naoConsultados,
            'cpfsSemAvaliacao' => $cpfsSemAvaliacao,
            'papeisParticipante' => $papeisParticipante,
            'emRiscoCritico' => $emRiscoCritico,
            'filtroClassificacao' => $filtroClassificacao,
            'filtroBusca' => $busca,
            'clientes' => $clientes,
            'clienteSelecionadoId' => $clienteSelecionadoId,
            'verTodosCnpjs' => $verTodos,
            'filtroStatus' => $filtroStatus,
            'filtroTipo' => $filtroTipo,
            'filtroCredito' => $filtroCredito,
            'filtroScoreMin' => $scoreMin,
            'filtroScoreMax' => $scoreMax,
        ];

        return $this->render($request, 'index', $data);
    }

    /**
     * Detalhes do score de um participante.
     */
    public function show(Request $request, int $id)
    {
        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $userId = Auth::id();

        $participante = Participante::where('user_id', $userId)
            ->where('id', $id)
            ->with(['score', 'importacaoEfd', 'importacaoXml'])
            ->firstOrFail();
        $isCpf = $participante->is_cpf;

        // Volume movimentado no acervo EFD auditado (efd_notas tem só participante_id — sem
        // split emitente/destinatário, que é exclusivo do acervo XML). Base CANÔNICA do BI
        // (P1 participante-scoped + sem canceladas): converge com a ficha `/app/participante` e
        // com o dossiê. O SUM cru da relação dobrava a NF-e escriturada nas duas EFD e somava
        // canceladas — inflava o volume e o crédito da reforma calculado a partir dele.
        $volumeAgg = \App\Models\EfdNota::query()
            ->where('user_id', $participante->user_id)
            ->where('participante_id', $participante->id)
            ->where('cancelada', false)
            ->whereRaw(\App\Services\BiService::dedupParticipanteSql('efd_notas'))
            ->selectRaw('COUNT(*) as total_notas, COALESCE(SUM(valor_total), 0) as volume, MIN(data_emissao) as primeira, MAX(data_emissao) as ultima')
            ->first();

        $volumeEfd = (float) $volumeAgg->volume;
        // Período coberto pelo volume (contextualiza o crédito em risco: é o acumulado
        // dessas emissões, não uma taxa anual).
        $volumePeriodo = $volumeAgg->primeira !== null
            ? [
                'inicio' => \Carbon\Carbon::parse($volumeAgg->primeira)->format('m/Y'),
                'fim' => \Carbon\Carbon::parse($volumeAgg->ultima)->format('m/Y'),
            ]
            : null;

        // participante_scores contém exclusivamente o Score Fiscal de CNPJ. Mesmo que exista
        // linha legada para um CPF, ela não pode ser apresentada como risco de crédito.
        $scoreModel = $isCpf ? null : $participante->score;
        $detalhamento = $scoreModel
            ? $this->riskScoreService->detalhar([
                'cadastral' => $scoreModel->score_cadastral,
                'cnd_federal' => $scoreModel->score_cnd_federal,
                'cnd_estadual' => $scoreModel->score_cnd_estadual,
                'fgts' => $scoreModel->score_fgts,
            ])
            : [];

        $data = [
            'participante' => $participante,
            'isCpf' => $isCpf,
            'score' => $scoreModel,
            'pesos' => $this->riskScoreService->getPesos(),
            'detalhamento' => $detalhamento,
            'volumeEfd' => $volumeEfd,
            'creditoReforma' => $isCpf
                ? null
                : $this->creditoReforma->creditoParticipante($participante, $volumeEfd, $participante->score?->score_credito_reforma),
            'riscoCpf' => $isCpf
                ? $this->riscoCreditoCpf->avaliar($participante, [
                    'total_notas' => (int) ($volumeAgg->total_notas ?? 0),
                    'valor_movimentado' => $volumeEfd,
                    'periodo_inicio' => $volumePeriodo['inicio'] ?? null,
                    'periodo_fim' => $volumePeriodo['fim'] ?? null,
                ])
                : null,
            // Certidões/blocos da última consulta renderizados server-side (mesmo partial do
            // "Ver detalhes" da Consulta CNPJ) — substitui o dump JSON de dados_consultados.
            'detalheConsultaHtml' => $this->htmlDetalheUltimaConsulta($participante),
            'origemLabel' => ParticipanteOrigem::dados($participante)['label'],
            'volumePeriodo' => $volumePeriodo,
            'papel' => $this->papelParticipante($participante),
            'metodologia' => $this->riskScoreService->metodologia(),
        ];

        return $this->render($request, 'show', $data);
    }

    /**
     * Papel do participante pelo fluxo das notas EFD (mesma regra da listagem/export:
     * RiskScoreReportBuilder::papeisParticipante): entrada = Fornecedor, saída = Comprador.
     */
    private function papelParticipante(Participante $participante): ?string
    {
        $tipos = \App\Models\EfdNota::where('user_id', $participante->user_id)
            ->where('participante_id', $participante->id)
            ->distinct()
            ->pluck('tipo_operacao');

        $entrada = $tipos->contains('entrada');
        $saida = $tipos->contains('saida');

        return match (true) {
            $entrada && $saida => 'Fornecedor e Comprador',
            $entrada => 'Fornecedor',
            $saida => 'Comprador',
            default => null,
        };
    }

    /**
     * HTML dos blocos de certidões da última consulta bem-sucedida do participante,
     * via ResultadoDetalhePresenter + partial detalhe-blocos (fonte única com a
     * Consulta CNPJ e com o detalhe AJAX da listagem). Null = sem consulta.
     */
    private function htmlDetalheUltimaConsulta(Participante $participante): ?string
    {
        // Fonte única com a Consulta CNPJ e o Clearance completo.
        $detalhe = app(ResultadoDetalhePresenter::class)->detalheDoParticipante($participante);

        if (! $detalhe) {
            return null;
        }

        return view('autenticado.consulta.partials.detalhe-blocos', $detalhe)->render();
    }

    /**
     * Detalhe expansível da última consulta de um participante (certidões/blocos) — mesmo
     * conteúdo do "Ver detalhes" da Consulta de CNPJ, carregado sob demanda (AJAX) na listagem
     * do Score Fiscal. Reusa o partial autenticado.consulta.partials.detalhe-blocos.
     */
    public function detalheParticipante(Request $request, int $id)
    {
        if (! Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $participante = Participante::where('user_id', Auth::id())->whereKey($id)->firstOrFail();

        $ultima = ConsultaResultado::where('participante_id', $participante->id)
            ->where('status', ConsultaResultado::STATUS_SUCESSO)
            ->with('lote.plano')
            ->orderByDesc('consultado_em')
            ->first();

        if (! $ultima) {
            return response()->json(['html' => '<div class="text-xs text-gray-500 py-3">Sem consulta de certidões para este CNPJ. <a href="/app/consulta/painel" data-link class="text-blue-600 hover:underline">Consultar agora</a>.</div>']);
        }

        // Certidões que o PLANO desta consulta realmente incluiu → fonte pedida mas sem retorno
        // aparece como erro; fonte fora do plano não vira erro falso (mesmo critério do dossiê).
        $presenter = app(ResultadoDetalhePresenter::class);
        $esperadas = $presenter->esperadasDoResultado($ultima);

        $html = view('autenticado.consulta.partials.detalhe-blocos', [
            'blocos' => $presenter->blocos($ultima, $esperadas),
            'resumo' => $presenter->resumoTextual($ultima),
            'certidoes' => $presenter->certidoes($ultima, $esperadas),
            'cabecalho' => [
                'razao' => $participante->razao_social,
                'documento' => $participante->cnpj_formatado ?? $participante->documento,
                'uf' => $participante->uf,
                'situacao' => $participante->situacao_cadastral ?? ($ultima->resultado_dados['situacao_cadastral'] ?? null),
            ],
        ])->render();

        return response()->json(['html' => $html]);
    }

    /**
     * Detalhe expansível da última consulta de um cliente no Score Fiscal.
     */
    public function detalheCliente(Request $request, int $id)
    {
        if (! Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $cliente = Cliente::where('user_id', Auth::id())->whereKey($id)->firstOrFail();

        $ultima = ConsultaResultado::where('cliente_id', $cliente->id)
            ->where('status', ConsultaResultado::STATUS_SUCESSO)
            ->with('lote.plano')
            ->orderByDesc('consultado_em')
            ->first();

        if ($ultima) {
            $ultima->resultado_dados = array_replace($this->dadosCadastraisCliente($cliente), (array) $ultima->resultado_dados);
        } else {
            $ultima = new ConsultaResultado;
            $ultima->resultado_dados = $this->dadosCadastraisCliente($cliente);
            $ultima->setRelation('cliente', $cliente);
        }

        $presenter = app(ResultadoDetalhePresenter::class);
        $esperadas = $presenter->esperadasDoResultado($ultima);

        $html = view('autenticado.consulta.partials.detalhe-blocos', [
            'blocos' => $presenter->blocos($ultima, $esperadas),
            'resumo' => $presenter->resumoTextual($ultima),
            'certidoes' => $presenter->certidoes($ultima, $esperadas),
            'cabecalho' => [
                'razao' => $cliente->razao_social,
                'documento' => $cliente->documento_formatado ?? $cliente->documento,
                'uf' => $cliente->uf,
                'situacao' => $cliente->situacao_cadastral ?? ($ultima->resultado_dados['situacao_cadastral'] ?? null),
            ],
        ])->render();

        return response()->json(['html' => $html]);
    }

    /**
     * Dados cadastrais salvos no cliente, usados como fallback visual quando ainda não existe
     * ConsultaResultado direto para o CNPJ do cliente. Não persiste nada.
     *
     * @return array<string, mixed>
     */
    private function dadosCadastraisCliente(Cliente $cliente): array
    {
        $cnaes = [];
        if ($cliente->cnae_principal || $cliente->cnae_principal_descricao) {
            $cnaes[] = [
                'codigo' => $cliente->cnae_principal,
                'descricao' => $cliente->cnae_principal_descricao,
                'principal' => true,
            ];
        }
        foreach (($cliente->cnaes_secundarios ?? []) as $cnae) {
            if (is_array($cnae)) {
                $cnaes[] = $cnae;
            }
        }

        $dados = [
            'razao_social' => $cliente->razao_social ?: $cliente->nome,
            'nome_fantasia' => $cliente->nome_fantasia,
            'situacao_cadastral' => $cliente->situacao_cadastral,
            'regime_tributario' => $cliente->regime_tributario,
            'regime_tributario_nota' => $cliente->regime_tributario_nota,
            'regime_tributario_origem' => $cliente->regime_tributario_origem,
            'natureza_juridica' => $cliente->natureza_juridica,
            'porte' => $cliente->porte,
            'capital_social' => $cliente->capital_social,
            'data_inicio_atividade' => $cliente->data_inicio_atividade?->format('Y-m-d'),
            'endereco' => [
                'logradouro' => $cliente->endereco,
                'numero' => $cliente->numero,
                'bairro' => $cliente->bairro,
                'municipio' => $cliente->municipio,
                'uf' => $cliente->uf,
                'cep' => $cliente->cep,
            ],
            'telefone_1' => $cliente->telefone,
            'cnaes' => $cnaes,
            'qsa' => $cliente->qsa ?? [],
        ];

        return array_filter($dados, fn ($valor) => ! ($valor === null || $valor === '' || $valor === []));
    }

    public function exportarPdf(Request $request, RiskScoreReportBuilder $builder)
    {
        $relatorio = $builder->montar((int) Auth::id(), $this->filtrosExportacao($request));

        return $this->comTokenDownload(
            PdfReport::render('reports.risk-score', ['relatorio' => $relatorio], 'portrait')
                ->download($this->nomeExportacao().'.pdf'),
            $request
        );
    }

    public function exportarXlsx(Request $request, RiskScoreReportBuilder $builder, RiskScoreXlsxBuilder $xlsx)
    {
        if (! XlsxReport::disponivel()) {
            abort(503, 'Exportação XLSX indisponível.');
        }

        $relatorio = $builder->montar((int) Auth::id(), $this->filtrosExportacao($request));

        return $this->comTokenDownload(
            $xlsx->download($relatorio, $this->nomeExportacao().'.xlsx'),
            $request
        );
    }

    public function exportarCsv(Request $request, RiskScoreReportBuilder $builder)
    {
        $relatorio = $builder->montar((int) Auth::id(), $this->filtrosExportacao($request));
        $linhas = array_map(
            fn (array $registro) => RiskScoreReportBuilder::linha($registro),
            $relatorio['registros']
        );

        return $this->comTokenDownload(
            CsvExport::download($this->nomeExportacao().'.csv', $relatorio['colunas'], $linhas),
            $request
        );
    }

    private function filtrosExportacao(Request $request): array
    {
        return $request->only(['cliente_id', 'classificacao', 'busca', 'status', 'tipo', 'credito', 'score_min', 'score_max']);
    }

    private function scoreFiltro(mixed $valor): ?int
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        if (! is_numeric($valor)) {
            return null;
        }

        return max(0, min(100, (int) $valor));
    }

    private function nomeExportacao(): string
    {
        return 'score-fiscal-'.now()->format('Ymd');
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
