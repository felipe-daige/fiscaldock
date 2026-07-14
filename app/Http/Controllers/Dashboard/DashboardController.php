<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Concerns\RespondeAjax;
use App\Http\Controllers\Controller;
use App\Models\Alerta;
use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\EfdImportacao;
use App\Models\Participante;
use App\Models\SaldoTransacao;
use App\Services\AlertaCentralService;
use App\Services\Consultas\Fiscal\TopMovimentacaoQuery;
use App\Services\Consultas\ResultadoDetalhePresenter;
use App\Services\Dashboard\DashboardDataService;
use App\Services\NotaFiscalService;
use App\Services\PricingCatalogService;
use App\Support\ClienteOrigem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class DashboardController extends Controller
{
    use RespondeAjax;

    public function __construct(
        protected DashboardDataService $dashboardDataService,
        protected NotaFiscalService $notaFiscalService,
        protected AlertaCentralService $alertaCentralService,
        protected PricingCatalogService $pricingCatalogService,
        protected ResultadoDetalhePresenter $detalhePresenter,
        protected \App\Services\GuiaAlertaService $guiaAlertaService,
    ) {}

    /** Rótulo curto por fonte de certidão p/ os badges compactos na listagem de clientes. */
    private const FONTE_CURTA = [
        'cnd_federal' => 'Federal',
        'cnd_estadual' => 'Estadual',
        'cnd_municipal' => 'Municipal',
        'crf_fgts' => 'FGTS',
        'cndt' => 'CNDT',
        'sintegra' => 'Sintegra',
    ];

    private const AUTH_VIEW_PREFIX = 'autenticado.';

    private const AUTH_LAYOUT_VIEW = 'autenticado.layouts.app';

    /** Catálogo canônico de atalhos do cockpit (slug => rota). Fonte da whitelist de prefs. */
    public const ATALHOS_CATALOGO = [
        'consulta_nova' => '/app/consulta/nova',
        'importar_efd' => '/app/importacao/efd',
        'importar_xml' => '/app/importacao/xml',
        'verificar_notas' => '/app/clearance/notas',
        'bi_dashboard' => '/app/bi/dashboard',
        'resumo_fiscal' => '/app/resumo-fiscal',
        'clientes' => '/app/clientes',
        'score_fiscal' => '/app/score-fiscal',
    ];

    public function dashboard(Request $request)
    {
        $dashboardView = self::AUTH_VIEW_PREFIX.'dashboard.index';

        if (! view()->exists($dashboardView)) {
            abort(404);
        }

        if (! Auth::check()) {
            if ($this->isAjaxRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não está logado',
                    'redirect' => '/login',
                ]);
            }

            return redirect('/login');
        }

        $user = Auth::user();
        $userId = $user->id;

        // O cockpit (KPIs/triagem/tendência) vem do assembler único; aqui só sobram
        // os dados auxiliares que a view ainda usa (atividade recente, onboarding, trial).
        $data = [
            'atividadeRecente' => $this->dashboardDataService->getAtividadeRecente($userId),
            'isUsuarioNovo' => $this->dashboardDataService->isUsuarioNovo($userId),
            'trialResumo' => $this->buildTrialResumo($user),
            'cockpit' => $this->dashboardDataService->cockpit($userId, $user, null, 6),
            'dashboardPrefs' => $user->dashboardPrefs(),
            'atalhosCatalogo' => self::ATALHOS_CATALOGO,
            'clientesOpcoes' => Cliente::where('user_id', $userId)
                ->where(function ($q) {
                    $q->where('is_empresa_propria', false)->orWhereNull('is_empresa_propria');
                })
                ->orderByRaw("COALESCE(razao_social, nome, '') asc")
                ->get(['id', 'razao_social', 'nome'])
                ->map(fn ($c) => ['id' => $c->id, 'label' => $c->razao_social ?: ($c->nome ?: ('Cliente #'.$c->id))])
                ->all(),
        ];

        if ($this->isAjaxRequest($request)) {
            return view($dashboardView, $data);
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $dashboardView,
        ], $data));
    }

    /** JSON do cockpit (KPIs + triagem + tendência), filtrável por cliente/período. */
    public function dados(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['success' => false, 'redirect' => '/login'], 401);
        }

        $user = Auth::user();
        $userId = (int) $user->id;

        $clienteId = (int) $request->integer('cliente');
        if ($clienteId > 0) {
            $existe = Cliente::where('id', $clienteId)->where('user_id', $userId)->exists();
            $clienteId = $existe ? $clienteId : null;
        } else {
            $clienteId = null;
        }

        $periodo = (int) $request->integer('periodo', 6);

        return response()->json(
            $this->dashboardDataService->cockpit($userId, $user, $clienteId, $periodo)
        );
    }

    /** Persiste prefs do cockpit — só chaves da whitelist; nunca confia no front. */
    public function salvarPrefs(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['success' => false, 'redirect' => '/login'], 401);
        }

        $cardsValidos = array_keys(\App\Models\User::DASHBOARD_PREFS_DEFAULT['cards']);
        $atalhosValidos = array_keys(self::ATALHOS_CATALOGO);

        $validated = $request->validate([
            'cards' => ['sometimes', 'array'],
            'cards.*' => ['array'],
            'cards.*.visivel' => ['sometimes', 'boolean'],
            'cards.*.ordem' => ['sometimes', 'integer', 'min:0', 'max:50'],
            'atalhos_fixos' => ['sometimes', 'array'],
            'atalhos_fixos.*' => ['string', Rule::in($atalhosValidos)],
            'atalhos_ordem' => ['sometimes', 'array'],
            'atalhos_ordem.*' => ['string', Rule::in($atalhosValidos)],
        ]);

        // Rejeita chave de card fora da whitelist (a regra acima valida o valor, não a chave).
        foreach (array_keys($validated['cards'] ?? []) as $chave) {
            if (! in_array($chave, $cardsValidos, true)) {
                return response()->json(['success' => false, 'message' => "Card inválido: {$chave}"], 422);
            }
        }

        $user = Auth::user();
        $atual = $user->dashboard_prefs ?? [];
        $user->dashboard_prefs = array_replace_recursive($atual, $validated);
        $user->save();

        return response()->json(['success' => true, 'prefs' => $user->dashboardPrefs()]);
    }

    /**
     * Verifica se a requisição é AJAX de forma compatível com Laravel 11 e 12
     */
    private function renderAutenticado(Request $request, string $viewName)
    {
        $autenticadoView = self::AUTH_VIEW_PREFIX.$viewName;

        if (! view()->exists($autenticadoView)) {
            abort(404);
        }

        if (! Auth::check()) {
            if ($this->isAjaxRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não está logado',
                    'redirect' => '/login',
                ]);
            }

            return redirect('/login');
        }

        if ($this->isAjaxRequest($request)) {
            return view($autenticadoView);
        }

        return view(self::AUTH_LAYOUT_VIEW, [
            'initialView' => $autenticadoView,
        ]);
    }

    public function novoCliente(Request $request)
    {
        return $this->renderAutenticado($request, 'clientes.novo');
    }

    public function clientes(Request $request)
    {
        $autenticadoView = self::AUTH_VIEW_PREFIX.'clientes.index';

        if (! view()->exists($autenticadoView)) {
            abort(404);
        }

        if (! Auth::check()) {
            if ($this->isAjaxRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não está logado',
                    'redirect' => '/login',
                ]);
            }

            return redirect('/login');
        }

        $userId = (int) Auth::id();
        $status = $request->string('status')->toString();
        $tipo = $request->string('tipo')->toString();
        $busca = trim($request->string('busca')->toString());
        $regime = trim($request->string('regime')->toString());
        $situacao = trim($request->string('situacao')->toString());
        $uf = trim($request->string('uf')->toString());
        $importacao = trim($request->string('importacao')->toString());

        // Regularidade / status de consulta — derivados da última consulta do
        // participante de mesmo documento (whitelist; valor inválido é ignorado).
        $regValida = ['regular', 'irregular', 'indeterminada', 'nao_consultado'];
        $regularidade = in_array($request->string('regularidade')->toString(), $regValida, true)
            ? $request->string('regularidade')->toString() : null;
        $stValida = ['nunca', 'desatualizada', 'recente'];
        $statusConsulta = in_array($request->string('status_consulta')->toString(), $stValida, true)
            ? $request->string('status_consulta')->toString() : null;
        $ordem = in_array($request->get('ordem'), ['movimentacao', 'nome', 'recentes'], true)
            ? $request->get('ordem') : 'movimentacao';

        // Mapa documento(normalizado) → regularidade / última consulta, para os dois
        // filtros acima. Só monta quando algum deles é usado.
        $resumoService = app(\App\Services\Consultas\ParticipanteFiscalResumoService::class);
        $mapaRegularidade = ($regularidade !== null || $statusConsulta !== null)
            ? $resumoService->mapaRegularidadeCliente($userId)
            : ['consultados' => [], 'porRegularidade' => [], 'ultimaPorDoc' => []];

        $baseQuery = Cliente::where('user_id', $userId)
            ->when($importacao !== '', fn ($query) => $query->whereIn(
                'id',
                EfdImportacao::where('user_id', $userId)->where('id', $importacao)->select('cliente_id')
            ));

        $clientes = (clone $baseQuery)
            ->withCount('participantes')
            ->when($status !== '', function ($query) use ($status) {
                if ($status === 'ativos') {
                    $query->where('ativo', true);
                } elseif ($status === 'inativos') {
                    $query->where('ativo', false);
                }
            })
            ->when($tipo !== '', fn ($query) => $query->where('tipo_pessoa', strtoupper($tipo)))
            ->when($busca !== '', function ($query) use ($busca) {
                $query->where(function ($sub) use ($busca) {
                    $sub->where('documento', 'like', '%'.preg_replace('/\D/', '', $busca).'%')
                        ->orWhere('razao_social', 'ilike', "%{$busca}%")
                        ->orWhere('nome', 'ilike', "%{$busca}%");
                });
            })
            ->when($regime !== '', fn ($query) => $query->where('regime_tributario', 'ilike', $regime))
            ->when($situacao !== '', fn ($query) => $query->where('situacao_cadastral', 'ilike', $situacao))
            ->when($uf !== '', fn ($query) => $query->where('uf', strtoupper($uf)))
            ->when($regularidade !== null || $statusConsulta !== null, fn ($query) => $resumoService
                ->aplicarFiltroRegularidadeCliente($query, $regularidade, $statusConsulta, $mapaRegularidade));

        // Notas unificadas EFD+XML por empresa, DEDUPLICADAS por chave de acesso: a mesma
        // nota costuma existir nas duas origens (importada por XML e depois via SPED, ou
        // vice-versa) — em prod 10/10 notas XML eram duplicatas do EFD. EFD (fiscal, não
        // cancelada) vence; XML só entra quando a chave não está no EFD do usuário.
        $notasUnificadas = fn () => \Illuminate\Support\Facades\DB::table('efd_notas')
            ->where('user_id', $userId)
            ->where('origem_arquivo', 'fiscal')
            ->where('cancelada', false)
            ->selectRaw('cliente_id, tipo_operacao, valor_total, data_emissao')
            ->unionAll(
                \Illuminate\Support\Facades\DB::table('xml_notas as x')
                    ->where('x.user_id', $userId)
                    ->whereNotExists(fn ($sub) => $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                        ->from('efd_notas as e')
                        ->whereColumn('e.chave_acesso', 'x.chave_acesso')
                        ->where('e.user_id', $userId)
                        ->where('e.origem_arquivo', 'fiscal')
                        ->where('e.cancelada', false))
                    ->selectRaw("x.cliente_id, CASE WHEN x.tipo_nota = 0 THEN 'entrada' ELSE 'saida' END as tipo_operacao, x.valor_total, x.data_emissao")
            );

        // Ordenação: default por volume movimentado desc (empresa própria e clientes mais
        // relevantes primeiro), como em /app/participantes. Agregado via joinSub (1 scan).
        match ($ordem) {
            'nome' => $clientes = $clientes->orderByRaw("COALESCE(razao_social, nome, '') asc"),
            'recentes' => $clientes = $clientes->orderBy('created_at', 'desc'),
            default => $clientes = $clientes
                ->leftJoinSub(
                    \Illuminate\Support\Facades\DB::query()->fromSub($notasUnificadas(), 'n')
                        ->groupBy('cliente_id')
                        ->selectRaw('cliente_id, SUM(valor_total) as valor'),
                    'mov', 'mov.cliente_id', '=', 'clientes.id'
                )
                // select() zera as colunas do withCount lá de cima — re-aplica depois.
                ->select('clientes.*')
                ->withCount('participantes')
                ->orderByRaw('COALESCE(mov.valor, 0) desc')
                ->orderByRaw("COALESCE(razao_social, nome, '') asc"),
        };

        $clientes = $clientes->paginate(20)->withQueryString();

        // Movimentação por empresa (entradas × saídas + última emissão) — só das
        // empresas da página (1 query agregada sobre o union deduplicado).
        $movPorCliente = \Illuminate\Support\Facades\DB::query()->fromSub($notasUnificadas(), 'n')
            ->whereIn('cliente_id', $clientes->getCollection()->pluck('id'))
            ->groupBy('cliente_id', 'tipo_operacao')
            ->selectRaw('cliente_id, tipo_operacao, COUNT(*) as qtd, SUM(valor_total) as valor, MAX(data_emissao) as ultima')
            ->get()
            ->groupBy('cliente_id');

        $agora = Carbon::now();
        $documentosClientes = $clientes->getCollection()
            ->pluck('documento')
            ->filter()
            ->map(fn ($documento) => preg_replace('/\D/', '', (string) $documento))
            ->filter()
            ->unique()
            ->values();

        $participantesPorDocumento = Participante::query()
            ->where('user_id', $userId)
            ->whereIn('documento', $documentosClientes)
            ->get(['id', 'documento'])
            ->keyBy('documento');

        $ultimosResultadosClientes = ConsultaResultado::query()
            ->whereIn('participante_id', $participantesPorDocumento->pluck('id'))
            ->where('status', ConsultaResultado::STATUS_SUCESSO)
            ->orderBy('consultado_em', 'desc')
            ->get()
            ->unique('participante_id')
            ->keyBy('participante_id');

        // Consultas com ESCOPO CLIENTE gravam cliente_id direto no resultado (sem passar por
        // participante). Sem este caminho, cliente consultado aparecia "Não Consultado"/sem
        // certidões na lista quando não existia participante consultado de mesmo documento.
        $ultimosResultadosPorClienteId = ConsultaResultado::query()
            ->whereIn('cliente_id', $clientes->getCollection()->pluck('id'))
            ->where('status', ConsultaResultado::STATUS_SUCESSO)
            ->orderBy('consultado_em', 'desc')
            ->get()
            ->unique('cliente_id')
            ->keyBy('cliente_id');

        $clientes->getCollection()->transform(function (Cliente $cliente) use ($agora, $participantesPorDocumento, $ultimosResultadosClientes, $ultimosResultadosPorClienteId, $movPorCliente) {
            $mov = $movPorCliente->get($cliente->id, collect());
            $entrada = $mov->firstWhere('tipo_operacao', 'entrada');
            $saida = $mov->firstWhere('tipo_operacao', 'saida');
            $ultimaNota = $mov->max('ultima');
            $cliente->setAttribute('mov_valor', (float) ($entrada->valor ?? 0) + (float) ($saida->valor ?? 0));
            $cliente->setAttribute('mov_qtd', (int) ($entrada->qtd ?? 0) + (int) ($saida->qtd ?? 0));
            $cliente->setAttribute('mov_entradas', (float) ($entrada->valor ?? 0));
            $cliente->setAttribute('mov_saidas', (float) ($saida->valor ?? 0));
            $cliente->setAttribute('mov_ultima_nota', $ultimaNota ? Carbon::parse($ultimaNota)->format('m/Y') : null);

            $documento = preg_replace('/\D/', '', (string) $cliente->documento);
            $participante = $participantesPorDocumento->get($documento);
            $viaParticipante = $participante ? $ultimosResultadosClientes->get($participante->id) : null;
            $viaCliente = $ultimosResultadosPorClienteId->get($cliente->id);
            // Dois caminhos possíveis (consulta do participante de mesmo documento OU consulta
            // com escopo cliente) — vence a mais recente.
            $ultimoResultado = collect([$viaParticipante, $viaCliente])
                ->filter()
                ->sortByDesc('consultado_em')
                ->first();
            $ultimaConsulta = $ultimoResultado?->consultado_em;

            $consultaStatusLabel = 'Não Consultado';
            $consultaStatusHex = '#9ca3af';
            $consultaStatusMeta = 'Sem consulta realizada';

            if ($ultimaConsulta) {
                $diasSemConsulta = $ultimaConsulta->diffInDays($agora);

                if ($diasSemConsulta > 30) {
                    $consultaStatusLabel = 'Consulta desatualizada';
                    $consultaStatusHex = '#b45309';
                } else {
                    $consultaStatusLabel = 'Consultado recentemente';
                    $consultaStatusHex = '#047857';
                }

                $consultaStatusMeta = 'Última atualização em '.$ultimaConsulta->format('d/m/Y H:i');
            }

            // Badge compacto de TODAS as fontes que a consulta trouxe (CND Federal/Estadual/
            // Municipal, FGTS, CNDT, SINTEGRA). A cor reflete a regularidade, classificada
            // pela fonte única (CertidaoBadge via ResultadoDetalhePresenter).
            $certidoesBadges = [];
            if ($ultimoResultado) {
                foreach ($this->detalhePresenter->blocos($ultimoResultado) as $bloco) {
                    $chave = $bloco['chave'] ?? '';
                    if ($chave === 'cadastro' || empty($bloco['badge'])) {
                        continue;
                    }
                    $certidoesBadges[] = [
                        'fonte' => $chave,
                        'curto' => self::FONTE_CURTA[$chave] ?? ($bloco['titulo'] ?? $chave),
                        'titulo' => $bloco['titulo'] ?? $chave,
                        'label' => $bloco['badge']['label'] ?? '—',
                        'hex' => $bloco['badge']['hex'] ?? '#9ca3af',
                    ];
                }
            }

            $cliente->setAttribute('consulta_status_label', $consultaStatusLabel);
            $cliente->setAttribute('consulta_status_hex', $consultaStatusHex);
            $cliente->setAttribute('consulta_status_meta', $consultaStatusMeta);
            $cliente->setAttribute('certidoes_badges', $certidoesBadges);

            return $cliente;
        });

        // Estatísticas
        $totalAtivos = (clone $baseQuery)->where('ativo', true)->count();
        $totalInativos = (clone $baseQuery)->where('ativo', false)->count();
        $totalPJ = (clone $baseQuery)->where('tipo_pessoa', 'PJ')->count();
        $totalPF = (clone $baseQuery)->where('tipo_pessoa', 'PF')->count();
        $ufs = (clone $baseQuery)
            ->whereNotNull('uf')
            ->where('uf', '!=', '')
            ->distinct()
            ->orderBy('uf')
            ->pluck('uf');

        $importacoes = EfdImportacao::where('user_id', $userId)
            ->where('status', 'concluido')
            ->orderByDesc('created_at')
            ->get(['id', 'filename', 'tipo_efd', 'created_at']);

        // Dropdown do modal de dossiê em lote: lista leve, mesmo shape do filtro do BI.
        $clientesDossie = Cliente::where('user_id', $userId)
            ->where('ativo', true)
            ->select('id', 'nome', 'documento', 'is_empresa_propria')
            ->orderByDesc('is_empresa_propria')
            ->orderBy('nome')
            ->get();

        $data = [
            'clientes' => $clientes,
            'clientesDossie' => $clientesDossie,
            'totalAtivos' => $totalAtivos,
            'totalInativos' => $totalInativos,
            'totalPJ' => $totalPJ,
            'totalPF' => $totalPF,
            'ufs' => $ufs,
            'importacoes' => $importacoes,
            'filtros' => [
                'status' => $status,
                'tipo' => strtoupper($tipo),
                'busca' => $busca,
                'regime' => $regime,
                'situacao' => $situacao,
                'uf' => strtoupper($uf),
                'importacao' => $importacao,
                'regularidade' => $regularidade,
                'status_consulta' => $statusConsulta,
                'ordem' => $ordem,
            ],
        ];

        if ($this->isAjaxRequest($request)) {
            return view($autenticadoView, $data);
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $autenticadoView,
        ], $data));
    }

    public function clienteParticipantes(Request $request, int $id)
    {
        if (! Auth::check()) {
            return response('Nao autenticado', 401);
        }

        $userId = (int) Auth::id();
        $tipoDocumento = strtoupper(trim($request->string('tipo_documento')->toString()));
        $cliente = Cliente::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $participantes = Participante::where('user_id', $userId)
            ->where('cliente_id', $cliente->id)
            ->when($tipoDocumento === 'CPF', fn ($query) => $query->somenteCpf())
            ->when($tipoDocumento === 'CNPJ', fn ($query) => $query->somenteCnpj())
            ->withCount('efdNotas')
            ->orderByRaw("COALESCE(razao_social, nome_fantasia, documento, '') asc")
            ->paginate(5)
            ->withQueryString();

        return view('autenticado.partials.relacionados-participantes', [
            'participantes' => $participantes,
            'titulo' => 'Participantes vinculados',
            'emptyMessage' => 'Nenhum participante vinculado a este cliente.',
            'scope' => 'cliente',
            'entityId' => $cliente->id,
            'ajaxBaseUrl' => "/app/cliente/{$cliente->id}/participantes",
            'filtros' => [
                'tipo_documento' => $tipoDocumento,
            ],
        ]);
    }

    public function clienteDetalhes(Request $request, int $id)
    {
        if (! Auth::check()) {
            if ($this->isAjaxRequest($request)) {
                return response()->json(['success' => false, 'message' => 'Nao autenticado'], 401);
            }

            return redirect('/login');
        }

        $cliente = Cliente::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (! $cliente) {
            if ($this->isAjaxRequest($request)) {
                return response()->json(['success' => false, 'message' => 'Cliente nao encontrado'], 404);
            }
            abort(404);
        }

        // Empresa própria: redirect to /app/minha-empresa
        if ($cliente->is_empresa_propria) {
            if ($this->isAjaxRequest($request)) {
                return response()->json(['redirect' => '/app/minha-empresa']);
            }

            return redirect('/app/minha-empresa');
        }

        $totalParticipantes = Participante::where('user_id', Auth::id())
            ->where('cliente_id', $cliente->id)
            ->count();

        $notasFiscais = $this->notaFiscalService->listarUnificadas(
            (int) Auth::id(),
            ['cliente_id' => $cliente->id],
            5,
            1,
            "/app/cliente/{$id}/notas"
        );
        $totalNotas = $notasFiscais->total();

        $showView = self::AUTH_VIEW_PREFIX.'clientes.show';

        $viewData = [
            'cliente' => $cliente,
            'origemCliente' => ClienteOrigem::dados($cliente),
            'totalParticipantes' => $totalParticipantes,
            'totalNotas' => $totalNotas,
            'notasFiscais' => $notasFiscais,
            'totalNotasFiscais' => $totalNotas,
            'notasAjaxUrl' => "/app/cliente/{$id}/notas",
            'notasContexto' => 'cliente',
            'notasEntityId' => $cliente->id,
        ];

        $topMov = app(TopMovimentacaoQuery::class);
        $viewData['top_produtos'] = $topMov->produtos((int) Auth::id(), 'cliente_id', [$cliente->id], 10)[$cliente->id] ?? [];
        $viewData['top_cfops'] = $topMov->cfops((int) Auth::id(), 'cliente_id', [$cliente->id], 10)[$cliente->id] ?? [];

        // Movimentação & análise fiscal do cliente (empresa gerida do contador).
        $movSvc = app(\App\Services\Clientes\ClienteMovimentacaoService::class);
        $viewData['movimentacao'] = $movSvc->kpisEResumoParaPreview($cliente);

        $viewData['planos'] = \App\Models\MonitoramentoPlano::ativos();

        $ultimaConsultaCliente = \App\Models\ConsultaResultado::where('cliente_id', $cliente->id)
            ->where('status', \App\Models\ConsultaResultado::STATUS_SUCESSO)
            ->orderByDesc('consultado_em')
            ->first();
        $scoreCliente = $ultimaConsultaCliente?->calcularScore();
        $viewData['score'] = $scoreCliente;
        $viewData['score_detalhamento'] = $scoreCliente
            ? app(\App\Services\RiskScoreService::class)->detalhar($scoreCliente['scores'])
            : [];

        if ($this->isAjaxRequest($request)) {
            // Modal requests send Accept: application/json — return JSON for the modal to populate
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'cliente' => [
                        'id' => $cliente->id,
                        'nome' => $cliente->nome,
                        'razao_social' => $cliente->razao_social,
                        'documento_formatado' => $cliente->documento_formatado,
                        'tipo_pessoa' => $cliente->tipo_pessoa,
                        'email' => $cliente->email,
                        'telefone' => $cliente->telefone,
                        'ativo' => $cliente->ativo,
                        'is_empresa_propria' => $cliente->is_empresa_propria,
                        'uf' => $cliente->uf,
                        'cep' => $cliente->cep,
                        'municipio' => $cliente->municipio,
                        'created_at' => $cliente->created_at?->format('d/m/Y H:i'),
                    ],
                    'stats' => [
                        'total_participantes' => $totalParticipantes,
                        'total_notas' => $totalNotas,
                    ],
                ]);
            }

            // SPA navigation sends Accept: text/html — return HTML view
            return view($showView, $viewData);
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $showView,
        ], $viewData));
    }

    /**
     * Gera e baixa o dossiê completo do cliente em PDF.
     */
    public function clienteDossiePdf(Request $request, int $id)
    {
        if (! Auth::check()) {
            return redirect('/login');
        }

        $cliente = Cliente::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $arquivo = 'dossie_'.preg_replace('/\D/', '', (string) $cliente->documento).'.pdf';

        // ?top=10|20|50 → inclui os top N participantes por volume EFD junto no PDF
        // (via pipeline do dossiê em lote com 1 cliente). Sem/0 → só o cliente.
        $top = (int) $request->query('top', 0);
        if (in_array($top, \App\Services\Clientes\DossieLoteBuilder::TOPS_VALIDOS, true)) {
            $dados = app(\App\Services\Clientes\DossieLoteBuilder::class)
                ->montar((int) Auth::id(), [$cliente->id], $top);
            $dados['gerado_em'] = now()->format('d/m/Y H:i');

            // Dossiês multiplicam páginas/tabelas no dompdf — mesmos tetos do lote/BI.
            ini_set('memory_limit', '1024M');
            set_time_limit(240);

            return \App\Support\PdfReport::render('reports.dossie.lote', $dados, 'portrait')
                ->download($arquivo);
        }

        $dados = app(\App\Services\Clientes\DossieClienteBuilder::class)->montar($cliente);

        // ?formato=xlsx → planilha no modelo de design aprovado (mesma fonte do PDF)
        if ($request->query('formato') === 'xlsx') {
            return app(\App\Services\Dossie\DossieXlsxBuilder::class)
                ->download($dados, $cliente, str_replace('.pdf', '.xlsx', $arquivo));
        }

        return \App\Support\PdfReport::render('reports.dossie.cliente', $dados, 'portrait')
            ->download($arquivo);
    }

    /**
     * Notas fiscais unificadas do cliente (AJAX pagination).
     */
    public function clienteNotas(Request $request, int $id)
    {
        if (! Auth::check()) {
            return response('Nao autenticado', 401);
        }

        $userId = (int) Auth::id();
        $cliente = Cliente::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $page = max(1, (int) $request->get('page', 1));
        $notas = $this->notaFiscalService->listarUnificadas(
            $userId,
            ['cliente_id' => $cliente->id],
            5,
            $page,
            "/app/cliente/{$id}/notas"
        );

        return view('autenticado.partials.notas-fiscais-card', [
            'notas' => $notas,
            'totalNotas' => $notas->total(),
            'ajaxUrl' => "/app/cliente/{$id}/notas",
            'contexto' => 'cliente',
            'entityId' => $cliente->id,
        ]);
    }

    public function perfil(Request $request)
    {
        $perfilView = self::AUTH_VIEW_PREFIX.'usuario.perfil';

        if (! view()->exists($perfilView)) {
            abort(404);
        }

        if (! Auth::check()) {
            if ($this->isAjaxRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não está logado',
                    'redirect' => '/login',
                ]);
            }

            return redirect('/login');
        }

        $user = Auth::user();

        $saldoReais = $this->pricingCatalogService->creditsToCurrency((float) ($user->credits ?? 0));

        $dadosPerfil = [
            'user' => $user,
            'saldoReais' => $saldoReais,
            'trialAtivo' => $user->hasActiveTrial(),
            'trialExpiraEm' => $user->trial_expires_at,
            'trialCreditosRestantes' => $user->trial_credits_remaining,
        ];

        if ($this->isAjaxRequest($request)) {
            return view($perfilView, $dadosPerfil);
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $perfilView,
        ], $dadosPerfil));
    }

    public function atualizarPerfil(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['success' => false, 'redirect' => '/login'], 401);
        }

        $dados = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sobrenome' => ['nullable', 'string', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],
        ]);

        $user = Auth::user();
        $user->name = $dados['name'];
        if ($request->has('sobrenome')) {
            $user->sobrenome = $dados['sobrenome'];
        }
        if ($request->has('telefone')) {
            $user->telefone = $dados['telefone'];
        }
        $user->save();

        if ($this->isAjaxRequest($request)) {
            return response()->json([
                'success' => true,
                'user' => [
                    'name' => $user->name,
                    'sobrenome' => $user->sobrenome,
                    'telefone' => $user->telefone,
                ],
            ]);
        }

        return redirect('/app/perfil')->with('status', 'Perfil atualizado.');
    }

    public function atualizarSenha(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['success' => false, 'redirect' => '/login'], 401);
        }

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = Auth::user();
        $user->password = $request->input('password');
        $user->save();

        if ($this->isAjaxRequest($request)) {
            return response()->json(['success' => true]);
        }

        return redirect('/app/perfil')->with('status', 'Senha alterada.');
    }

    public function alertas(Request $request)
    {
        if (! Auth::check()) {
            if ($this->isAjaxRequest($request)) {
                return response()->json(['success' => false, 'redirect' => '/login']);
            }

            return redirect('/login');
        }

        $userId = Auth::id();
        $clientes = Cliente::where('user_id', $userId)
            ->select('id', 'razao_social')
            ->orderBy('razao_social')
            ->get();

        $resumo = $this->alertaCentralService->obterResumo($userId);

        $data = ['clientes' => $clientes, 'resumo' => $resumo];

        if ($this->isAjaxRequest($request)) {
            return view('autenticado.alertas.central', $data);
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => 'autenticado.alertas.central',
        ], $data));
    }

    public function alertasDados(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['success' => false, 'redirect' => '/login']);
        }

        $userId = Auth::id();
        $filtros = [
            'status' => $request->input('status', 'ativo'),
            'severidade' => $request->input('severidade'),
            'categoria' => $request->input('categoria'),
            'cliente_id' => $request->input('cliente_id'),
            'busca' => $request->input('busca'),
            'ordem' => in_array($request->input('ordem'), ['risco', 'prazo'], true) ? $request->input('ordem') : null,
        ];

        $alertas = $this->alertaCentralService->obterAlertas($userId, $filtros);

        return response()->json($alertas);
    }

    public function alertasResumo(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['success' => false, 'redirect' => '/login']);
        }

        return response()->json($this->alertaCentralService->obterResumo(Auth::id()));
    }

    public function alertasEvolucao(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['success' => false, 'redirect' => '/login']);
        }

        return response()->json($this->alertaCentralService->obterEvolucao(Auth::id()));
    }

    public function alertasMarcarStatus(Request $request, int $id)
    {
        if (! Auth::check()) {
            return response()->json(['success' => false, 'redirect' => '/login']);
        }

        $request->validate([
            'status' => 'required|in:ativo,visto,resolvido,ignorado',
            'notas' => 'nullable|string|max:1000',
        ]);

        $alerta = $this->alertaCentralService->marcarStatus(
            $id,
            Auth::id(),
            $request->input('status'),
            $request->input('notas')
        );

        return response()->json(['success' => true, 'alerta' => $alerta]);
    }

    public function alertasMarcarStatusLote(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['success' => false, 'redirect' => '/login']);
        }

        $request->validate([
            'ids' => 'required|array|max:1000',
            'ids.*' => 'integer',
            'status' => 'required|in:ativo,visto,resolvido,ignorado',
            'notas' => 'nullable|string|max:1000',
        ]);

        $total = $this->alertaCentralService->marcarStatusEmLote(
            $request->input('ids'),
            Auth::id(),
            $request->input('status'),
            $request->input('notas')
        );

        return response()->json([
            'success' => true,
            'total' => $total,
            'resumo' => $this->alertaCentralService->obterResumo(Auth::id()),
        ]);
    }

    public function alertasRecalcular(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['success' => false, 'redirect' => '/login']);
        }

        $resultado = $this->alertaCentralService->recalcular(Auth::id());
        $resumo = $this->alertaCentralService->obterResumo(Auth::id());

        return response()->json([
            'success' => true,
            'resultado' => $resultado,
            'resumo' => $resumo,
        ]);
    }

    /**
     * Gera o PDF da Central de Alertas (padrão de export do design system:
     * download nativo via <x-download-button>). Escopo por cliente opcional (`cliente_id`).
     */
    public function alertasExportarPdf(Request $request)
    {
        if (! Auth::check()) {
            return redirect('/login');
        }

        $clienteParam = $request->query('cliente_id');
        $clienteId = ($clienteParam !== null && ctype_digit((string) $clienteParam)) ? (int) $clienteParam : null;

        $grupos = $this->alertaCentralService->alertasAtivosAgrupados(Auth::id(), null, $clienteId);

        $total = array_sum(array_map(fn ($g) => $g['alertas']->count(), $grupos));

        abort_if($total === 0, 404, 'Nenhum alerta ativo para exportar.');

        // Materialidade do recorte exportado (soma o valor em risco dos alertas incluídos).
        $valorRiscoTotal = array_sum(array_map(
            fn ($g) => (float) $g['alertas']->sum('valor_risco'),
            $grupos
        ));

        $arquivo = 'alertas-'.now()->format('Y-m-d').'.pdf';

        $pdf = \App\Support\PdfReport::render('reports.alertas', [
            'grupos' => $grupos,
            'total' => $total,
            'valorRiscoTotal' => round($valorRiscoTotal, 2),
            'geradoEm' => now(),
            'hashDoc' => \App\Support\PdfReport::hashDocumento(Auth::id(), 'alertas', $total),
        ], 'portrait');

        // Cookie de download p/ o spinner do <x-download-button> saber que o arquivo chegou.
        return $this->comTokenDownload($pdf->download($arquivo), $request);
    }

    /**
     * Resolve o `cliente_id` opcional e devolve os grupos de alertas ativos do recorte,
     * abortando 404 quando não há nada para exportar. Base comum de CSV/XLSX/PDF.
     *
     * @return array<int,array{key:string,label:string,cor:string,alertas:\Illuminate\Support\Collection}>
     */
    private function alertasGruposParaExport(Request $request): array
    {
        $clienteParam = $request->query('cliente_id');
        $clienteId = ($clienteParam !== null && ctype_digit((string) $clienteParam)) ? (int) $clienteParam : null;

        $grupos = $this->alertaCentralService->alertasAtivosAgrupados(Auth::id(), null, $clienteId);

        $total = array_sum(array_map(fn ($g) => $g['alertas']->count(), $grupos));
        abort_if($total === 0, 404, 'Nenhum alerta ativo para exportar.');

        return $grupos;
    }

    /**
     * Central de Alertas em CSV (padrão canônico CsvExport: BOM + delimitador ";").
     * Uma linha por alerta ativo; escopo por cliente opcional (`cliente_id`).
     */
    public function alertasExportarCsv(Request $request)
    {
        if (! Auth::check()) {
            return redirect('/login');
        }

        $grupos = $this->alertasGruposParaExport($request);

        $sevLabel = ['alta' => 'Alta', 'media' => 'Média', 'baixa' => 'Baixa'];
        $fmtRs = fn ($v) => number_format((float) $v, 2, ',', '.');

        $linhas = [];
        foreach ($grupos as $g) {
            foreach ($g['alertas'] as $a) {
                $sev = (string) $a->severidade;
                $linhas[] = [
                    $g['label'],
                    $sevLabel[$sev] ?? ucfirst($sev),
                    (string) $a->titulo,
                    (string) $a->descricao,
                    $a->cliente?->razao_social ?? '',
                    $a->participante?->razao_social ?? '',
                    $a->participante?->documento ?? '',
                    (int) $a->total_afetados,
                    $fmtRs($a->valor_risco),
                    $a->vence_em?->format('d/m/Y') ?? '',
                    $a->created_at?->format('d/m/Y') ?? '',
                ];
            }
        }

        $colunas = [
            'Classe', 'Severidade', 'Alerta', 'Descrição', 'Cliente',
            'Participante', 'Documento', 'Afetados', 'Em risco (R$)', 'Vence em', 'Criado em',
        ];

        $arquivo = 'alertas-'.now()->format('Y-m-d').'.csv';

        return $this->comTokenDownload(
            \App\Support\CsvExport::download($arquivo, $colunas, $linhas),
            $request
        );
    }

    /**
     * Central de Alertas em XLSX (Resumo por classe + Alertas). Escopo por cliente
     * opcional (`cliente_id`). Espelha as colunas do PDF, com valor em risco numérico.
     */
    public function alertasExportarXlsx(Request $request)
    {
        if (! Auth::check()) {
            return redirect('/login');
        }

        if (! \App\Support\Reports\XlsxReport::disponivel()) {
            abort(503, 'Exportação XLSX indisponível.');
        }

        $grupos = $this->alertasGruposParaExport($request);

        $arquivo = 'alertas-'.now()->format('Y-m-d').'.xlsx';

        return $this->comTokenDownload(
            (new \App\Services\Alertas\Export\AlertaXlsxBuilder)->download($grupos, $arquivo),
            $request
        );
    }

    /**
     * Anexa o cookie `bi_download=<token>` à resposta de download quando o request traz
     * `download_token` — o spinner do <x-download-button> faz poll da PRESENÇA do cookie
     * pra saber que o arquivo chegou (mesmo mecanismo do BI). httpOnly=false: o JS lê.
     */
    private function comTokenDownload($response, Request $request)
    {
        $token = $request->get('download_token');
        if ($token) {
            $response->headers->setCookie(cookie('bi_download', (string) $token, 1, '/', null, null, false));
        }

        return $response;
    }

    /**
     * Histórico/auditoria de ações sobre alertas (quem, quando, de→para, nota).
     * Escopado ao usuário (via alertas.user_id). Filtros: ação, cliente, período.
     */
    public function alertasHistorico(Request $request)
    {
        if (! Auth::check()) {
            if ($this->isAjaxRequest($request)) {
                return response()->json(['success' => false, 'redirect' => '/login']);
            }

            return redirect('/login');
        }

        $userId = Auth::id();

        $acaoValida = ['criado', 'resolvido', 'ignorado', 'visto', 'reaberto', 'auto_resolvido', 'reativado'];
        $acao = in_array($request->query('acao'), $acaoValida, true) ? $request->query('acao') : null;
        $clienteParam = $request->query('cliente_id');
        $clienteId = ($clienteParam !== null && ctype_digit((string) $clienteParam)) ? (int) $clienteParam : null;
        $periodo = in_array($request->query('periodo'), ['30', '90', '365'], true) ? (int) $request->query('periodo') : null;

        $query = \App\Models\AlertaAuditoria::query()
            ->whereHas('alerta', fn ($q) => $q->where('user_id', $userId))
            ->with(['alerta:id,tipo,titulo,categoria,cliente_id', 'alerta.cliente:id,razao_social'])
            ->when($acao, fn ($q) => $q->where('acao', $acao))
            ->when($periodo, fn ($q) => $q->where('created_at', '>=', now()->subDays($periodo)))
            ->when($clienteId, fn ($q) => $q->whereHas('alerta', fn ($a) => $a->where('cliente_id', $clienteId)))
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        $eventos = $query->paginate(30)->withQueryString();

        $clientes = Cliente::where('user_id', $userId)
            ->select('id', 'razao_social')
            ->orderBy('razao_social')
            ->get();

        $data = [
            'eventos' => $eventos,
            'clientes' => $clientes,
            'filtroAcao' => $acao,
            'filtroCliente' => $clienteId,
            'filtroPeriodo' => $periodo,
        ];
        $viewName = self::AUTH_VIEW_PREFIX.'alertas.historico';

        if ($this->isAjaxRequest($request)) {
            return view($viewName, $data);
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge(['initialView' => $viewName], $data));
    }

    public function alertaDetalhes(Request $request, int $id)
    {
        if (! Auth::check()) {
            if ($this->isAjaxRequest($request)) {
                return response()->json(['success' => false, 'redirect' => '/login']);
            }

            return redirect('/login');
        }

        $userId = Auth::id();

        $alerta = Alerta::where('id', $id)
            ->where('user_id', $userId)
            ->with(['cliente', 'participante'])
            ->firstOrFail();

        $data = [
            'alerta' => $alerta,
            'guia' => $this->guiaAlertaService->para($alerta),
        ];
        $viewName = self::AUTH_VIEW_PREFIX.'alertas.show';

        if ($this->isAjaxRequest($request)) {
            return view($viewName, $data);
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $viewName,
        ], $data));
    }

    // ==================== USUÁRIO ====================

    public function configuracoes(Request $request)
    {
        $configuracoesView = self::AUTH_VIEW_PREFIX.'usuario.configuracoes';

        if (! view()->exists($configuracoesView)) {
            abort(404);
        }

        if (! Auth::check()) {
            if ($this->isAjaxRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não está logado',
                    'redirect' => '/login',
                ]);
            }

            return redirect('/login');
        }

        $user = Auth::user();

        // Só o que a view consome. (Antes montava 'recursos'/'preferencias'/'resumo' e
        // rodava a query `empresaPropria()` — nada disso é renderizado.)
        $data = [
            'user' => $user,
            'configuracoes' => [
                'notificacoes' => [
                    'email_ativo' => ! empty($user->email),
                    'alertas_operacionais' => (bool) $user->alertas_operacionais,
                    'alertas_monitoramento' => (bool) $user->alertas_monitoramento,
                    'resumo_periodico' => (bool) $user->resumo_periodico,
                    'alertas_severidade_minima' => $user->alertas_severidade_minima ?? 'media',
                    'resumo_frequencia' => $user->resumo_frequencia ?? 'semanal',
                ],
            ],
        ];

        if ($this->isAjaxRequest($request)) {
            return view($configuracoesView, $data);
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $configuracoesView,
        ], $data));
    }

    public function atualizarNotificacaoConfiguracao(Request $request)
    {
        // Campos booleanos (on/off) e de frequência (enum) num único endpoint. Cada
        // enum valida contra seus valores permitidos; o resto é boolean.
        $enums = [
            'alertas_severidade_minima' => ['media', 'alta'],
            'resumo_frequencia' => ['semanal', 'mensal'],
        ];

        // `campo` PRECISA ser string antes de virar chave: um array no payload fazia o
        // cast `(string)` emitir "Array to string conversion" → ErrorException → 500.
        // Input malformado tem que dar 422, não derrubar o endpoint.
        $bruto = $request->input('campo');
        $campo = is_string($bruto) ? $bruto : '';

        if (isset($enums[$campo])) {
            $payload = $request->validate([
                'campo' => ['required', 'string'],
                'valor' => ['required', 'string', Rule::in($enums[$campo])],
            ]);
            $valor = $payload['valor'];
        } else {
            $payload = $request->validate([
                'campo' => ['required', 'string', Rule::in([
                    'alertas_operacionais',
                    'alertas_monitoramento',
                    'resumo_periodico',
                ])],
                'valor' => ['required', 'boolean'],
            ]);
            $valor = (bool) $payload['valor'];
        }

        $user = Auth::user();
        $user->{$payload['campo']} = $valor;
        $user->save();

        return response()->json([
            'success' => true,
            'campo' => $payload['campo'],
            'valor' => $user->{$payload['campo']},
        ]);
    }

    public function meuPlano(Request $request)
    {
        if (! Auth::check()) {
            if ($this->isAjaxRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Voce nao esta logado',
                    'redirect' => '/login',
                ]);
            }

            return redirect('/login');
        }

        $user = Auth::user();
        $now = now();
        $mesInicio = $now->copy()->startOfMonth();
        $mesFim = $now->copy()->endOfMonth();
        $pricing = $this->pricingCatalogService->getCommercialSummaryForUser($user);

        // KPI 1: Saldo atual
        $saldoAtual = (float) $user->credits;

        // KPI 2: Creditos usados no mes
        $creditosUsadosMes = ConsultaLote::where('user_id', $user->id)
            ->whereIn('status', ConsultaLote::successfulStatuses())
            ->whereBetween('created_at', [$mesInicio, $mesFim])
            ->sum('creditos_cobrados');

        // KPI 3: Consultas no mes
        $consultasMes = ConsultaLote::where('user_id', $user->id)
            ->whereBetween('created_at', [$mesInicio, $mesFim])
            ->count();

        // KPI 4: Media creditos/consulta
        $totalConsultas = ConsultaLote::where('user_id', $user->id)
            ->whereIn('status', ConsultaLote::successfulStatuses())
            ->count();
        $totalCreditosHistorico = ConsultaLote::where('user_id', $user->id)
            ->whereIn('status', ConsultaLote::successfulStatuses())
            ->sum('creditos_cobrados');
        $mediaCreditos = $totalConsultas > 0 ? round($totalCreditosHistorico / $totalConsultas, 1) : 0;

        // Ultimas 20 transacoes (consulta_lotes como fallback)
        $ultimasTransacoes = ConsultaLote::where('user_id', $user->id)
            ->with('plano:id,nome,codigo')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Consumo mensal ultimos 6 meses
        $consumoMensal = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = $now->copy()->subMonths($i);
            $consumo = ConsultaLote::where('user_id', $user->id)
                ->whereIn('status', ConsultaLote::successfulStatuses())
                ->whereYear('created_at', $mes->year)
                ->whereMonth('created_at', $mes->month)
                ->sum('creditos_cobrados');
            $consumoMensal[] = [
                'label' => $mes->translatedFormat('M/y'),
                'valor' => (int) $consumo,
            ];
        }

        $maxConsumo = max(array_column($consumoMensal, 'valor') ?: [1]);

        $planoView = self::AUTH_VIEW_PREFIX.'plano.index';

        $data = [
            'saldoAtual' => $saldoAtual,
            'creditosUsadosMes' => (int) $creditosUsadosMes,
            'consultasMes' => $consultasMes,
            'mediaCreditos' => $mediaCreditos,
            'ultimasTransacoes' => $ultimasTransacoes,
            'consumoMensal' => $consumoMensal,
            'maxConsumo' => $maxConsumo,
            'pricing' => $pricing,
            'trialResumo' => $this->buildTrialResumo($user),
        ];

        if ($this->isAjaxRequest($request)) {
            return view($planoView, $data);
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $planoView,
        ], $data));
    }

    public function saldo(Request $request)
    {
        if (! Auth::check()) {
            if ($this->isAjaxRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Voce nao esta logado',
                    'redirect' => '/login',
                ]);
            }

            return redirect('/login');
        }

        $user = Auth::user();
        $pricing = $this->pricingCatalogService->getCommercialSummaryForUser($user);

        $saldoAtual = (float) $user->credits;

        $totalRecebido = (int) SaldoTransacao::where('user_id', $user->id)
            ->where('amount', '>', 0)
            ->sum('amount');

        $totalConsumido = (int) abs(SaldoTransacao::where('user_id', $user->id)
            ->where('amount', '<', 0)
            ->sum('amount'));

        $ultimaEntrada = SaldoTransacao::where('user_id', $user->id)
            ->where('amount', '>', 0)
            ->orderBy('created_at', 'desc')
            ->first();

        $historicoSaldo = SaldoTransacao::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(30)
            ->get();

        $saldoView = self::AUTH_VIEW_PREFIX.'saldo.index';

        $data = [
            'saldoAtual' => $saldoAtual,
            'totalRecebido' => $totalRecebido,
            'totalConsumido' => $totalConsumido,
            'ultimaEntrada' => $ultimaEntrada,
            'historicoSaldo' => $historicoSaldo,
            'pacotes' => $this->pricingCatalogService->getPackages(),
            'pricing' => $pricing,
            'trialResumo' => $this->buildTrialResumo($user),
            'mpPublicKey' => (string) config('services.mercadopago.public_key'),
            'recargaAtual' => \App\Models\RecargaAutomatica::where('user_id', $user->id)->first(),
        ];

        if ($this->isAjaxRequest($request)) {
            return view($saldoView, $data);
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $saldoView,
        ], $data));
    }

    /**
     * Planos de assinatura (tiers). Página informativa — lê do `subscription_plans`
     * seedado (fonte: CFO design). "Assinar" fica em breve até a Fase 4 (preapproval).
     */
    public function planos(Request $request)
    {
        if (! Auth::check()) {
            return $this->isAjaxRequest($request)
                ? response()->json(['success' => false, 'message' => 'Voce nao esta logado', 'redirect' => '/login'])
                : redirect('/login');
        }

        $user = Auth::user();
        $planos = \App\Models\SubscriptionPlan::allActive();
        $planoAtual = (new \App\Services\Entitlements\EntitlementService)->planFor($user);
        $assinatura = $user->subscription()->with('plan')->first();

        $planosView = self::AUTH_VIEW_PREFIX.'planos.index';
        $data = [
            'planos' => $planos,
            'planoAtualCodigo' => $planoAtual->codigo,
            'planoAtualOrdem' => (int) $planoAtual->ordem,
            'assinaturaAtual' => $assinatura,
            'creditUnitPrice' => $this->pricingCatalogService->creditUnitPrice(),
            'mpPublicKey' => (string) config('services.mercadopago.public_key'),
            'mpTetoCentavos' => (int) config('services.mercadopago.preapproval_teto_centavos', 400000),
            'whatsappUrl' => (string) config('support.whatsapp_url'),
        ];

        if ($this->isAjaxRequest($request)) {
            return view($planosView, $data);
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge(['initialView' => $planosView], $data));
    }

    public function checkout(Request $request, string $pacote)
    {
        $dados = $this->pricingCatalogService->resolveCheckoutSelection($pacote, $request->query('amount'));

        if (! $dados) {
            return redirect()
                ->route('app.saldo')
                ->withErrors([
                    'amount' => 'Informe um valor válido a partir de R$ '.number_format($this->pricingCatalogService->getMinimumDeposit(), 0, ',', '.').'.',
                ]);
        }

        $checkoutView = self::AUTH_VIEW_PREFIX.'plano.checkout';

        if ($this->isAjaxRequest($request)) {
            return view($checkoutView, [
                'pacote' => $dados,
                'pricing' => $this->pricingCatalogService->getCommercialSummaryForUser(Auth::user()),
            ]);
        }

        return view(self::AUTH_LAYOUT_VIEW, [
            'initialView' => $checkoutView,
            'pacote' => $dados,
            'pricing' => $this->pricingCatalogService->getCommercialSummaryForUser(Auth::user()),
        ]);
    }

    private function buildTrialResumo($user): array
    {
        $expiresAt = $user->trial_expires_at;
        $hasTrial = (bool) $user->trial_used;
        $isActive = $hasTrial && $expiresAt && now()->lt($expiresAt);
        $isExpired = $hasTrial && $expiresAt && now()->gte($expiresAt);

        return [
            'has_trial' => $hasTrial,
            'is_active' => $isActive,
            'is_expired' => $isExpired,
            'started_at' => $user->trial_started_at,
            'expires_at' => $expiresAt,
            'days_remaining' => $isActive && $expiresAt ? max(0, now()->diffInDays($expiresAt, false)) : 0,
            'granted' => (float) $user->trial_credits_granted,
            'remaining' => (float) $user->trial_credits_remaining,
            'expired' => (float) $user->trial_credits_expired,
        ];
    }
}
