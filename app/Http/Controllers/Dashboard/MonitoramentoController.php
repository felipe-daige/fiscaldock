<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\MonitoramentoAssinatura;
use App\Models\MonitoramentoConsulta;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\ParticipanteGrupo;
use App\Models\SpedImportacao;
use App\Models\XmlNota;
use App\Services\CreditService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class MonitoramentoController extends Controller
{
    private const AUTH_VIEW_PREFIX = 'autenticado.monitoramento.';
    private const AUTH_LAYOUT_VIEW = 'autenticado.layouts.app';

    public function __construct(
        protected CreditService $creditService
    ) {}

    /**
     * Página instrucional com detalhes dos planos de consulta.
     */
    public function planos(Request $request)
    {
        $planosView = self::AUTH_VIEW_PREFIX . 'planos';

        if (!view()->exists($planosView)) {
            abort(404);
        }

        if (!Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $user = Auth::user();

        $data = [
            'credits' => $this->creditService->getBalance($user),
        ];

        if ($this->isAjaxRequest($request)) {
            $renderedView = view($planosView, $data)->render();
            return response($renderedView)->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $planosView
        ], $data));
    }

    /**
     * Dashboard principal do monitoramento - lista participantes e estatísticas.
     */
    public function index(Request $request)
    {
        $indexView = self::AUTH_VIEW_PREFIX . 'index';

        if (!view()->exists($indexView)) {
            abort(404);
        }

        if (!Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $user = Auth::user();
        $userId = (int) $user->id;

        // Estatísticas do mês atual
        $inicioMes = Carbon::now()->startOfMonth();
        $fimMes = Carbon::now()->endOfMonth();

        $stats = [
            'total_participantes' => Participante::where('user_id', $userId)->count(),
            'total_assinaturas' => MonitoramentoAssinatura::where('user_id', $userId)
                ->where('status', 'ativo')
                ->count(),
            'consultas_mes' => MonitoramentoConsulta::where('user_id', $userId)
                ->whereBetween('created_at', [$inicioMes, $fimMes])
                ->count(),
            'creditos_gastos_mes' => MonitoramentoConsulta::where('user_id', $userId)
                ->whereBetween('created_at', [$inicioMes, $fimMes])
                ->sum('creditos_cobrados'),
        ];

        // Estatísticas detalhadas dos participantes
        $participantesStats = [
            'total' => $stats['total_participantes'],
            'ativos' => Participante::where('user_id', $userId)
                ->where('situacao_cadastral', 'ATIVA')
                ->count(),
            'inaptos' => Participante::where('user_id', $userId)
                ->whereIn('situacao_cadastral', ['INAPTA', 'SUSPENSA', 'BAIXADA'])
                ->count(),
            'com_monitoramento' => MonitoramentoAssinatura::where('user_id', $userId)
                ->where('status', 'ativo')
                ->distinct('participante_id')
                ->count('participante_id'),
            'novos_mes' => Participante::where('user_id', $userId)
                ->whereBetween('created_at', [$inicioMes, $fimMes])
                ->count(),
        ];

        // Distribuição por regime tributário
        $porRegime = Participante::where('user_id', $userId)
            ->selectRaw("COALESCE(regime_tributario, 'nao_definido') as regime, COUNT(*) as total")
            ->groupBy('regime_tributario')
            ->pluck('total', 'regime')
            ->toArray();

        // Top 3 UFs
        $topUfs = Participante::where('user_id', $userId)
            ->whereNotNull('uf')
            ->where('uf', '!=', '')
            ->selectRaw('uf, COUNT(*) as total')
            ->groupBy('uf')
            ->orderByDesc('total')
            ->limit(3)
            ->pluck('total', 'uf')
            ->toArray();

        // Resumo da base para card visual
        $resumoBase = [
            'por_situacao' => [
                'ativas' => $participantesStats['ativos'],
                'inaptas' => $participantesStats['inaptos'],
                'outras' => $participantesStats['total'] - $participantesStats['ativos'] - $participantesStats['inaptos'],
            ],
            'por_regime' => $porRegime,
            'top_ufs' => $topUfs,
            'total' => $participantesStats['total'],
        ];

        // Filtro por grupo
        $grupoId = $request->get('grupo');

        // Buscar participantes com suas assinaturas, grupos e importação
        $participantes = Participante::where('user_id', $userId)
            ->when($grupoId, function ($q) use ($grupoId) {
                $q->whereHas('grupos', fn($q) => $q->where('participante_grupos.id', $grupoId));
            })
            ->with(['assinaturas.plano', 'grupos', 'importacaoSped', 'importacaoXml', 'consultas' => function ($query) {
                $query->latest('executado_em')->limit(1);
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Buscar grupos do usuário
        $grupos = ParticipanteGrupo::where('user_id', $userId)
            ->withCount('participantes')
            ->orderBy('nome')
            ->get();

        $data = [
            'stats' => $stats,
            'participantesStats' => $participantesStats,
            'resumoBase' => $resumoBase,
            'planos' => MonitoramentoPlano::ativos(),
            'participantes' => $participantes,
            'grupos' => $grupos,
            'grupoAtivo' => $grupoId,
            'coresPredefinidas' => ParticipanteGrupo::CORES_PREDEFINIDAS,
            'credits' => $this->creditService->getBalance($user),
        ];

        if ($this->isAjaxRequest($request)) {
            $renderedView = view($indexView, $data)->render();
            return response($renderedView)->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $indexView
        ], $data));
    }

    /**
     * Lista relatórios RAF para importação de participantes.
     */
    public function importarSped(Request $request)
    {
        $spedView = self::AUTH_VIEW_PREFIX . 'sped';

        if (!view()->exists($spedView)) {
            abort(404);
        }

        if (!Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $user = Auth::user();
        $userId = (int) $user->id;

        // Buscar clientes ativos do usuário para o select de associação
        $clientes = Cliente::where('user_id', $userId)
            ->where('ativo', true)
            ->orderBy('razao_social')
            ->get();

        // Buscar últimas importações SPED do usuário
        $importacoes = SpedImportacao::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $data = [
            'credits' => $this->creditService->getBalance($user),
            'clientes' => $clientes,
            'importacoes' => $importacoes,
        ];

        if ($this->isAjaxRequest($request)) {
            $renderedView = view($spedView, $data)->render();
            return response($renderedView)->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $spedView
        ], $data));
    }

    /**
     * Formulário de consulta avulsa.
     */
    public function consultaAvulsa(Request $request)
    {
        $avulsoView = self::AUTH_VIEW_PREFIX . 'avulso';

        if (!view()->exists($avulsoView)) {
            abort(404);
        }

        if (!Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $user = Auth::user();
        $userId = (int) $user->id;

        // Buscar participantes do usuário para lista
        $participantes = Participante::where('user_id', $userId)
            ->orderBy('updated_at', 'desc')
            ->limit(50)
            ->get();

        // Buscar clientes ativos do usuário para o select de associação
        $clientes = Cliente::where('user_id', $userId)
            ->where('ativo', true)
            ->orderBy('razao_social')
            ->get();

        $data = [
            'planos' => MonitoramentoPlano::ativos(),
            'credits' => $this->creditService->getBalance($user),
            'participantes' => $participantes,
            'clientes' => $clientes,
        ];

        if ($this->isAjaxRequest($request)) {
            $renderedView = view($avulsoView, $data)->render();
            return response($renderedView)->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $avulsoView
        ], $data));
    }

    /**
     * Histórico de consultas realizadas.
     */
    public function historico(Request $request)
    {
        $historicoView = self::AUTH_VIEW_PREFIX . 'historico';

        if (!view()->exists($historicoView)) {
            abort(404);
        }

        if (!Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $user = Auth::user();
        $userId = (int) $user->id;

        // Buscar consultas com relacionamentos
        $consultas = MonitoramentoConsulta::where('user_id', $userId)
            ->with(['participante', 'plano', 'assinatura'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $data = [
            'consultas' => $consultas,
            'planos' => MonitoramentoPlano::ativos(),
            'credits' => $this->creditService->getBalance($user),
        ];

        if ($this->isAjaxRequest($request)) {
            $renderedView = view($historicoView, $data)->render();
            return response($renderedView)->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $historicoView
        ], $data));
    }

    /**
     * Lista participantes importados com filtros.
     */
    public function listaParticipantes(Request $request)
    {
        $participantesView = self::AUTH_VIEW_PREFIX . 'participantes-importados';

        if (!view()->exists($participantesView)) {
            abort(404);
        }

        if (!Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $user = Auth::user();
        $userId = (int) $user->id;

        // Filtros
        $importacaoId = $request->get('importacao');
        $clienteId = $request->get('cliente');
        $origemTipo = $request->get('origem');
        $busca = $request->get('busca');

        // Query de participantes com filtros
        $participantesQuery = Participante::where('user_id', $userId)
            ->with(['cliente', 'importacaoSped'])
            ->when($importacaoId, fn($q) => $q->where('importacao_sped_id', $importacaoId))
            ->when($clienteId, fn($q) => $q->where('cliente_id', $clienteId))
            ->when($origemTipo, fn($q) => $q->where('origem_tipo', $origemTipo))
            ->when($busca, function ($q) use ($busca) {
                $q->where(function ($sub) use ($busca) {
                    $sub->where('cnpj', 'like', "%{$busca}%")
                        ->orWhere('razao_social', 'ilike', "%{$busca}%");
                });
            })
            ->orderBy('created_at', 'desc');

        $participantes = $participantesQuery->paginate(20)->withQueryString();

        // Buscar importações SPED para o filtro
        $importacoes = SpedImportacao::where('user_id', $userId)
            ->where('status', 'concluido')
            ->orderBy('created_at', 'desc')
            ->get();

        // Buscar clientes para o filtro
        $clientes = Cliente::where('user_id', $userId)
            ->where('ativo', true)
            ->orderBy('razao_social')
            ->get();

        // Tipos de origem disponíveis
        $origens = ['SPED_EFD_FISCAL', 'SPED_EFD_CONTRIB', 'NFE', 'NFSE', 'MANUAL'];

        $data = [
            'participantes' => $participantes,
            'importacoes' => $importacoes,
            'clientes' => $clientes,
            'origens' => $origens,
            'filtros' => [
                'importacao' => $importacaoId,
                'cliente' => $clienteId,
                'origem' => $origemTipo,
                'busca' => $busca,
            ],
            'credits' => $this->creditService->getBalance($user),
        ];

        if ($this->isAjaxRequest($request)) {
            $renderedView = view($participantesView, $data)->render();
            return response($renderedView)->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $participantesView
        ], $data));
    }

    /**
     * Monitoramento de clientes - visualiza status dos clientes monitorados.
     */
    public function clientes(Request $request)
    {
        $clientesView = self::AUTH_VIEW_PREFIX . 'clientes';

        if (!view()->exists($clientesView)) {
            abort(404);
        }

        if (!Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $user = Auth::user();

        $data = [
            'credits' => $this->creditService->getBalance($user),
        ];

        if ($this->isAjaxRequest($request)) {
            $renderedView = view($clientesView, $data)->render();
            return response($renderedView)->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $clientesView
        ], $data));
    }

    /**
     * Detalhes de um participante específico.
     */
    public function participante(Request $request, $id)
    {
        $participanteView = self::AUTH_VIEW_PREFIX . 'participante';

        if (!Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $user = Auth::user();
        $userId = (int) $user->id;

        $participante = Participante::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        // Carregar consultas do participante
        $consultas = MonitoramentoConsulta::where('participante_id', $participante->id)
            ->where('user_id', $userId)
            ->with('plano')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Buscar assinatura ativa ou pausada
        $assinaturaAtiva = MonitoramentoAssinatura::where('participante_id', $participante->id)
            ->where('user_id', $userId)
            ->whereIn('status', ['ativo', 'pausado'])
            ->with('plano')
            ->first();

        // Notas fiscais onde participante é emitente OU destinatário
        $notasFiscais = XmlNota::where('user_id', $userId)
            ->where(function ($query) use ($participante) {
                $query->where('emit_participante_id', $participante->id)
                      ->orWhere('dest_participante_id', $participante->id);
            })
            ->select([
                'id', 'nfe_id', 'tipo_documento', 'numero_nota', 'serie',
                'data_emissao', 'valor_total', 'natureza_operacao', 'tipo_nota', 'finalidade',
                'emit_participante_id', 'dest_participante_id',
                'emit_cnpj', 'emit_razao_social', 'dest_cnpj', 'dest_razao_social',
            ])
            ->orderBy('data_emissao', 'desc')
            ->paginate(10, ['*'], 'notas_page');

        // Campo 'papel' e contraparte
        $notasFiscais->getCollection()->transform(function ($nota) use ($participante) {
            $nota->papel = $nota->emit_participante_id === $participante->id ? 'emitente' : 'destinatario';
            $nota->contraparte_cnpj = $nota->papel === 'emitente' ? $nota->dest_cnpj : $nota->emit_cnpj;
            $nota->contraparte_razao = $nota->papel === 'emitente' ? $nota->dest_razao_social : $nota->emit_razao_social;
            $nota->contraparte_participante_id = $nota->papel === 'emitente' ? $nota->dest_participante_id : $nota->emit_participante_id;
            return $nota;
        });

        // Contador de notas fiscais (usado como totalXmlsProcessados também, já que são equivalentes)
        $totalNotasFiscais = XmlNota::where('user_id', $userId)
            ->where(fn($q) => $q->where('emit_participante_id', $participante->id)
                                ->orWhere('dest_participante_id', $participante->id))
            ->count();

        // Carregar planos disponíveis
        $planos = MonitoramentoPlano::ativos();

        // Estatísticas do participante - combinar ambos sistemas
        $monitoramentoTotal = MonitoramentoConsulta::where('participante_id', $participante->id)
            ->where('user_id', $userId)->count();
        $monitoramentoSucesso = MonitoramentoConsulta::where('participante_id', $participante->id)
            ->where('user_id', $userId)->where('status', 'sucesso')->count();
        $monitoramentoErro = MonitoramentoConsulta::where('participante_id', $participante->id)
            ->where('user_id', $userId)->where('status', 'erro')->count();
        $monitoramentoCreditos = MonitoramentoConsulta::where('participante_id', $participante->id)
            ->where('user_id', $userId)->sum('creditos_cobrados');

        // Consultas em lote (sistema novo)
        $consultaLoteTotal = ConsultaResultado::where('participante_id', $participante->id)
            ->whereHas('lote', fn ($q) => $q->where('user_id', $userId))->count();
        $consultaLoteSucesso = ConsultaResultado::where('participante_id', $participante->id)
            ->whereHas('lote', fn ($q) => $q->where('user_id', $userId))
            ->where('status', 'sucesso')->count();
        $consultaLoteErro = ConsultaResultado::where('participante_id', $participante->id)
            ->whereHas('lote', fn ($q) => $q->where('user_id', $userId))
            ->whereIn('status', ['erro', 'timeout'])->count();

        $estatisticas = [
            'total_consultas' => $monitoramentoTotal + $consultaLoteTotal,
            'consultas_sucesso' => $monitoramentoSucesso + $consultaLoteSucesso,
            'consultas_erro' => $monitoramentoErro + $consultaLoteErro,
            'creditos_utilizados' => $monitoramentoCreditos,
        ];

        // Buscar última consulta com sucesso para o participante (sistema de consultas em lote)
        $ultimaConsulta = ConsultaResultado::where('participante_id', $participante->id)
            ->where('status', ConsultaResultado::STATUS_SUCESSO)
            ->with(['lote:id,plano_id,created_at', 'lote.plano:id,nome,codigo'])
            ->orderBy('consultado_em', 'desc')
            ->first();

        // Buscar lotes que incluem este participante (para histórico de consultas em lote)
        $lotesDoParticipante = ConsultaLote::whereHas('resultados', function ($q) use ($participante) {
            $q->where('participante_id', $participante->id);
        })
            ->where('user_id', $userId)
            ->with('plano:id,nome,codigo')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Saldo de créditos do usuário
        $credits = $this->creditService->getBalance($user);

        $data = [
            'participante' => $participante,
            'consultas' => $consultas,
            'assinaturaAtiva' => $assinaturaAtiva,
            'planos' => $planos,
            'estatisticas' => $estatisticas,
            'credits' => $credits,
            'notasFiscais' => $notasFiscais,
            'totalNotasFiscais' => $totalNotasFiscais,
            'ultimaConsulta' => $ultimaConsulta,
            'lotesDoParticipante' => $lotesDoParticipante,
        ];

        if ($this->isAjaxRequest($request)) {
            $renderedView = view($participanteView, $data)->render();
            return response($renderedView)->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $participanteView
        ], $data));
    }

    /**
     * Detalhes de uma consulta específica (retorna JSON).
     */
    public function consultaDetalhes(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        $consulta = MonitoramentoConsulta::where('id', $id)
            ->where('user_id', $user->id)
            ->with(['participante', 'plano'])
            ->first();

        if (!$consulta) {
            return response()->json([
                'success' => false,
                'message' => 'Consulta não encontrada.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'id' => $consulta->id,
            'tipo' => $consulta->tipo,
            'status' => $consulta->status,
            'creditos_cobrados' => $consulta->creditos_cobrados,
            'executado_em' => $consulta->executado_em?->format('d/m/Y H:i'),
            'created_at' => $consulta->created_at->format('d/m/Y H:i'),
            'plano' => $consulta->plano ? [
                'nome' => $consulta->plano->nome,
                'codigo' => $consulta->plano->codigo,
            ] : null,
            'resultado' => $consulta->resultado ?? [
                'cnpj' => $consulta->participante?->cnpj,
                'razao_social' => $consulta->participante?->razao_social,
                'situacao_cadastral' => $consulta->participante?->situacao_cadastral,
                'regime_tributario' => $consulta->participante?->regime_tributario,
                'detalhes' => [],
            ],
            'error_message' => $consulta->error_message,
        ]);
    }

    /**
     * Detalhes de uma nota fiscal (retorna JSON).
     */
    public function notaFiscalDetalhes(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $userId = (int) Auth::id();

        $nota = XmlNota::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$nota) {
            return response()->json([
                'success' => false,
                'message' => 'Nota fiscal não encontrada.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'nfe_id' => $nota->nfe_id,
                'tipo_documento' => $nota->tipo_documento,
                'numero_nota' => $nota->numero_nota,
                'serie' => $nota->serie,
                'data_emissao' => $nota->data_emissao?->format('d/m/Y'),
                'natureza_operacao' => $nota->natureza_operacao,
                'valor_total' => number_format((float) $nota->valor_total, 2, ',', '.'),
                'tipo_nota' => $nota->tipo_nota_descricao,
                'finalidade' => $nota->finalidade_descricao,
                'emit_cnpj' => $nota->emit_cnpj_formatado,
                'emit_razao_social' => $nota->emit_razao_social,
                'emit_uf' => $nota->emit_uf,
                'dest_cnpj' => $nota->dest_cnpj_formatado,
                'dest_razao_social' => $nota->dest_razao_social,
                'dest_uf' => $nota->dest_uf,
                'icms_valor' => number_format((float) ($nota->icms_valor ?? 0), 2, ',', '.'),
                'icms_st_valor' => number_format((float) ($nota->icms_st_valor ?? 0), 2, ',', '.'),
                'pis_valor' => number_format((float) ($nota->pis_valor ?? 0), 2, ',', '.'),
                'cofins_valor' => number_format((float) ($nota->cofins_valor ?? 0), 2, ',', '.'),
                'ipi_valor' => number_format((float) ($nota->ipi_valor ?? 0), 2, ',', '.'),
                'tributos_total' => number_format((float) ($nota->tributos_total ?? 0), 2, ',', '.'),
            ]
        ]);
    }

    /**
     * Adiciona CNPJs avulsos para monitoramento.
     */
    public function adicionarCnpj(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();
        $cnpjsInput = $request->input('cnpjs', '');

        // Aceita string separada por vírgula, quebra de linha ou array
        if (is_string($cnpjsInput)) {
            $cnpjs = preg_split('/[,;\n\r]+/', $cnpjsInput);
        } else {
            $cnpjs = $cnpjsInput;
        }

        $cnpjs = array_filter(array_map(function ($cnpj) {
            return preg_replace('/[^0-9]/', '', trim($cnpj));
        }, $cnpjs), function ($cnpj) {
            return strlen($cnpj) === 14;
        });

        if (empty($cnpjs)) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum CNPJ válido informado.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $adicionados = 0;
        $duplicados = 0;

        try {
            DB::beginTransaction();

            foreach ($cnpjs as $cnpj) {
                // Verificar se já existe
                $existente = Participante::where('user_id', $user->id)
                    ->where('cnpj', $cnpj)
                    ->first();

                if ($existente) {
                    $duplicados++;
                    continue;
                }

                Participante::create([
                    'user_id' => $user->id,
                    'cnpj' => $cnpj,
                    'origem_tipo' => 'MANUAL',
                ]);

                $adicionados++;
            }

            DB::commit();

            Log::info('CNPJs avulsos adicionados', [
                'user_id' => $user->id,
                'adicionados' => $adicionados,
                'duplicados' => $duplicados,
            ]);

            $message = $adicionados . ' CNPJ(s) adicionado(s) com sucesso.';
            if ($duplicados > 0) {
                $message .= ' ' . $duplicados . ' já existiam e foram ignorados.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'adicionados' => $adicionados,
                'duplicados' => $duplicados,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao adicionar CNPJs', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao adicionar CNPJs. Tente novamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Cria assinatura de monitoramento para um ou mais participantes.
     */
    public function criarAssinatura(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        // Aceita participante_id (único) ou participantes (array)
        $participanteId = $request->input('participante_id');
        $participanteIds = $request->input('participantes', []);
        $planoId = $request->input('plano_id');
        $frequencia = $request->input('frequencia', 'quinzenal');

        // Se participante_id foi passado, converter para array
        if ($participanteId && empty($participanteIds)) {
            $participanteIds = [$participanteId];
        }

        if (empty($participanteIds) || empty($planoId)) {
            return response()->json([
                'success' => false,
                'error' => 'Dados incompletos. Selecione participantes e um plano.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validar frequência
        $frequenciasValidas = ['diario', 'semanal', 'quinzenal', 'mensal'];
        if (!in_array($frequencia, $frequenciasValidas)) {
            return response()->json([
                'success' => false,
                'error' => 'Frequência inválida.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Buscar plano
        $plano = MonitoramentoPlano::find($planoId);
        if (!$plano || !$plano->is_active) {
            return response()->json([
                'success' => false,
                'error' => 'Plano não encontrado ou inativo.',
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            DB::beginTransaction();

            $assinaturasCriadas = 0;
            $jaExistentes = 0;

            foreach ($participanteIds as $pId) {
                // Verificar se participante pertence ao usuário
                $participante = Participante::where('id', $pId)
                    ->where('user_id', $user->id)
                    ->first();

                if (!$participante) {
                    continue;
                }

                // Verificar se já existe assinatura ativa/pausada
                $assinaturaExistente = MonitoramentoAssinatura::where('participante_id', $participante->id)
                    ->where('user_id', $user->id)
                    ->whereIn('status', ['ativo', 'pausado'])
                    ->first();

                if ($assinaturaExistente) {
                    $jaExistentes++;
                    continue;
                }

                // Converter frequência para dias
                $frequenciaDias = $this->frequenciaParaDias($frequencia);
                $proximaExecucao = Carbon::now()->addDays($frequenciaDias)->setTime(8, 0, 0);

                // Criar assinatura
                MonitoramentoAssinatura::create([
                    'user_id' => $user->id,
                    'participante_id' => $participante->id,
                    'plano_id' => $plano->id,
                    'frequencia_dias' => $frequenciaDias,
                    'status' => 'ativo',
                    'proxima_execucao_em' => $proximaExecucao,
                ]);

                $assinaturasCriadas++;
            }

            DB::commit();

            Log::info('Assinatura(s) criada(s)', [
                'user_id' => $user->id,
                'criadas' => $assinaturasCriadas,
                'ja_existentes' => $jaExistentes,
                'plano_id' => $planoId,
                'frequencia' => $frequencia,
            ]);

            if ($assinaturasCriadas === 0 && $jaExistentes > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Já existe assinatura ativa para este(s) participante(s).',
                ], Response::HTTP_CONFLICT);
            }

            return response()->json([
                'success' => true,
                'message' => $assinaturasCriadas . ' assinatura(s) criada(s) com sucesso.',
                'criadas' => $assinaturasCriadas,
                'ja_existentes' => $jaExistentes,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar assinatura', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao criar assinatura. Tente novamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Pausa uma assinatura de monitoramento.
     */
    public function pausarAssinatura(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        $assinatura = MonitoramentoAssinatura::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$assinatura) {
            return response()->json([
                'success' => false,
                'error' => 'Assinatura não encontrada.',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($assinatura->status !== 'ativo') {
            return response()->json([
                'success' => false,
                'error' => 'Apenas assinaturas ativas podem ser pausadas.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $assinatura->update(['status' => 'pausado']);

        Log::info('Assinatura pausada', [
            'user_id' => $user->id,
            'assinatura_id' => $id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Assinatura pausada com sucesso.',
        ]);
    }

    /**
     * Reativa uma assinatura pausada.
     */
    public function reativarAssinatura(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        $assinatura = MonitoramentoAssinatura::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$assinatura) {
            return response()->json([
                'success' => false,
                'error' => 'Assinatura não encontrada.',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($assinatura->status !== 'pausado') {
            return response()->json([
                'success' => false,
                'error' => 'Apenas assinaturas pausadas podem ser reativadas.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Recalcular próxima execução baseado em frequencia_dias
        $proximaExecucao = Carbon::now()->addDays($assinatura->frequencia_dias)->setTime(8, 0, 0);

        $assinatura->update([
            'status' => 'ativo',
            'proxima_execucao_em' => $proximaExecucao,
        ]);

        Log::info('Assinatura reativada', [
            'user_id' => $user->id,
            'assinatura_id' => $id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Assinatura reativada com sucesso.',
        ]);
    }

    /**
     * Cancela uma assinatura de monitoramento.
     */
    public function cancelarAssinatura(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        $assinatura = MonitoramentoAssinatura::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$assinatura) {
            return response()->json([
                'success' => false,
                'error' => 'Assinatura não encontrada.',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($assinatura->status === 'cancelado') {
            return response()->json([
                'success' => false,
                'error' => 'Assinatura já foi cancelada.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $assinatura->update([
            'status' => 'cancelado',
        ]);

        Log::info('Assinatura cancelada', [
            'user_id' => $user->id,
            'assinatura_id' => $id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Assinatura cancelada com sucesso.',
        ]);
    }

    /**
     * Converte frequência textual para número de dias.
     */
    private function frequenciaParaDias(string $frequencia): int
    {
        return match ($frequencia) {
            'diario' => 1,
            'semanal' => 7,
            'quinzenal' => 15,
            'mensal' => 30,
            default => 15,
        };
    }

    /**
     * Executa consulta avulsa.
     */
    public function executarConsultaAvulsa(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();
        $cnpjsInput = $request->input('cnpjs', '');
        $planoId = $request->input('plano_id');
        $clienteId = $request->input('cliente_id');

        // Aceita string separada por vírgula, quebra de linha ou array
        if (is_string($cnpjsInput)) {
            $cnpjs = preg_split('/[,;\n\r]+/', $cnpjsInput);
        } else {
            $cnpjs = $cnpjsInput;
        }

        $cnpjs = array_filter(array_map(function ($cnpj) {
            return preg_replace('/[^0-9]/', '', trim($cnpj));
        }, $cnpjs), function ($cnpj) {
            return strlen($cnpj) === 14;
        });

        if (empty($cnpjs) || empty($planoId)) {
            return response()->json([
                'success' => false,
                'message' => 'Dados incompletos. Informe CNPJs válidos e selecione um plano.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Buscar plano no banco
        $plano = MonitoramentoPlano::find($planoId);

        if (!$plano || !$plano->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Plano não encontrado ou inativo.',
            ], Response::HTTP_NOT_FOUND);
        }

        $totalCreditos = count($cnpjs) * $plano->custo_creditos;
        $saldoAtual = $this->creditService->getBalance($user);

        if ($totalCreditos > 0 && !$this->creditService->hasEnough($user, $totalCreditos)) {
            return response()->json([
                'success' => false,
                'insufficient_credits' => true,
                'credits' => $saldoAtual,
                'required' => $totalCreditos,
                'message' => 'Créditos insuficientes. Você precisa de ' . $totalCreditos . ' créditos.',
            ], Response::HTTP_PAYMENT_REQUIRED);
        }

        // Verificar se webhook está configurado
        $webhookUrl = config('services.webhook.monitoramento_consulta_url');
        if (empty($webhookUrl)) {
            Log::warning('Webhook de monitoramento não configurado', [
                'user_id' => $user->id,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Serviço de consulta temporariamente indisponível. Tente novamente mais tarde.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        try {
            DB::beginTransaction();

            $consultasCriadas = [];
            $consultasParaN8n = [];

            foreach ($cnpjs as $cnpj) {
                // Criar ou buscar participante
                $participante = Participante::firstOrCreate(
                    ['user_id' => $user->id, 'cnpj' => $cnpj],
                    [
                        'origem_tipo' => 'MANUAL',
                        'cliente_id' => $clienteId,
                    ]
                );

                // Se já existe mas não tem cliente associado, atualizar
                if ($clienteId && !$participante->cliente_id) {
                    $participante->update(['cliente_id' => $clienteId]);
                }

                // Criar consulta
                $consulta = MonitoramentoConsulta::create([
                    'user_id' => $user->id,
                    'participante_id' => $participante->id,
                    'plano_id' => $plano->id,
                    'tipo' => 'avulso',
                    'status' => 'pendente',
                    'creditos_cobrados' => $plano->custo_creditos,
                ]);

                $consultasCriadas[] = $consulta->id;

                // Preparar dados para envio ao n8n
                $consultasParaN8n[] = [
                    'consulta_id' => $consulta->id,
                    'participante_id' => $participante->id,
                    'cnpj' => $cnpj,
                    'uf' => $participante->uf ?? null,
                ];
            }

            // Descontar créditos
            if ($totalCreditos > 0) {
                $this->creditService->deduct($user, $totalCreditos);
            }

            DB::commit();

            Log::info('Consulta avulsa criada', [
                'user_id' => $user->id,
                'total_cnpjs' => count($cnpjs),
                'plano_id' => $planoId,
                'total_creditos' => $totalCreditos,
                'consultas' => $consultasCriadas,
            ]);

            // Enviar para n8n webhook para processamento
            $this->enviarConsultasParaN8n($user, $plano, $consultasParaN8n);

            return response()->json([
                'success' => true,
                'message' => 'Consulta iniciada para ' . count($cnpjs) . ' CNPJ(s). Os resultados serão processados em breve.',
                'creditos_cobrados' => $totalCreditos,
                'saldo_restante' => $this->creditService->getBalance($user),
                'consultas_ids' => $consultasCriadas,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar consulta avulsa', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar consulta. Tente novamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Envia consultas para o n8n webhook para processamento.
     * n8n fará as consultas às APIs e atualizará o banco diretamente.
     */
    private function enviarConsultasParaN8n($user, MonitoramentoPlano $plano, array $consultas): void
    {
        $webhookUrl = config('services.webhook.monitoramento_consulta_url');

        if (empty($webhookUrl)) {
            Log::warning('Webhook de monitoramento não configurado');
            return;
        }

        try {
            $payload = [
                'user_id' => $user->id,
                'plano_codigo' => $plano->codigo,
                'plano_nome' => $plano->nome,
                'consultas_incluidas' => $plano->consultas_incluidas ?? [],
                'custo_creditos' => $plano->custo_creditos,
                'consultas' => $consultas,
                'callback_url' => url('/api/monitoramento/consulta/resultado'),
                'timestamp' => now()->toIso8601String(),
            ];

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-API-Token' => config('services.api.token'),
                ])
                ->post($webhookUrl, $payload);

            if ($response->successful()) {
                Log::info('Consultas enviadas para n8n com sucesso', [
                    'user_id' => $user->id,
                    'total_consultas' => count($consultas),
                    'plano' => $plano->codigo,
                ]);
            } else {
                Log::error('Erro ao enviar consultas para n8n', [
                    'user_id' => $user->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exceção ao enviar consultas para n8n', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Lista grupos de participantes.
     */
    public function grupos(Request $request)
    {
        $gruposView = self::AUTH_VIEW_PREFIX . 'grupos';

        if (!Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $user = Auth::user();
        $userId = (int) $user->id;

        // Buscar grupos do usuário com contagem de participantes
        $grupos = ParticipanteGrupo::where('user_id', $userId)
            ->withCount('participantes')
            ->orderBy('nome')
            ->get();

        $data = [
            'grupos' => $grupos,
            'coresPredefinidas' => ParticipanteGrupo::CORES_PREDEFINIDAS,
            'credits' => $this->creditService->getBalance($user),
        ];

        if ($this->isAjaxRequest($request)) {
            $renderedView = view($gruposView, $data)->render();
            return response($renderedView)->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $gruposView
        ], $data));
    }

    /**
     * Cria um novo grupo de participantes.
     */
    public function criarGrupo(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();
        $nome = trim($request->input('nome', ''));
        $cor = $request->input('cor', ParticipanteGrupo::CORES_PREDEFINIDAS[0]);
        $descricao = $request->input('descricao');

        if (empty($nome)) {
            return response()->json([
                'success' => false,
                'error' => 'Nome do grupo é obrigatório.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Verificar se já existe grupo com mesmo nome
        $existente = ParticipanteGrupo::where('user_id', $user->id)
            ->where('nome', $nome)
            ->exists();

        if ($existente) {
            return response()->json([
                'success' => false,
                'error' => 'Já existe um grupo com este nome.',
            ], Response::HTTP_CONFLICT);
        }

        try {
            $grupo = ParticipanteGrupo::create([
                'user_id' => $user->id,
                'nome' => $nome,
                'cor' => $cor,
                'descricao' => $descricao,
                'is_auto' => false,
            ]);

            Log::info('Grupo de participantes criado', [
                'user_id' => $user->id,
                'grupo_id' => $grupo->id,
                'nome' => $nome,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Grupo criado com sucesso.',
                'grupo' => [
                    'id' => $grupo->id,
                    'nome' => $grupo->nome,
                    'cor' => $grupo->cor,
                    'descricao' => $grupo->descricao,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao criar grupo', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao criar grupo. Tente novamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Edita um grupo de participantes.
     */
    public function editarGrupo(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        $grupo = ParticipanteGrupo::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$grupo) {
            return response()->json([
                'success' => false,
                'error' => 'Grupo não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        $nome = trim($request->input('nome', $grupo->nome));
        $cor = $request->input('cor', $grupo->cor);
        $descricao = $request->input('descricao', $grupo->descricao);

        if (empty($nome)) {
            return response()->json([
                'success' => false,
                'error' => 'Nome do grupo é obrigatório.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Verificar se já existe outro grupo com mesmo nome
        $existente = ParticipanteGrupo::where('user_id', $user->id)
            ->where('nome', $nome)
            ->where('id', '!=', $id)
            ->exists();

        if ($existente) {
            return response()->json([
                'success' => false,
                'error' => 'Já existe outro grupo com este nome.',
            ], Response::HTTP_CONFLICT);
        }

        try {
            $grupo->update([
                'nome' => $nome,
                'cor' => $cor,
                'descricao' => $descricao,
            ]);

            Log::info('Grupo de participantes editado', [
                'user_id' => $user->id,
                'grupo_id' => $grupo->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Grupo atualizado com sucesso.',
                'grupo' => [
                    'id' => $grupo->id,
                    'nome' => $grupo->nome,
                    'cor' => $grupo->cor,
                    'descricao' => $grupo->descricao,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao editar grupo', [
                'user_id' => $user->id,
                'grupo_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao atualizar grupo. Tente novamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Exclui um grupo de participantes.
     */
    public function excluirGrupo(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        $grupo = ParticipanteGrupo::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$grupo) {
            return response()->json([
                'success' => false,
                'error' => 'Grupo não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $nome = $grupo->nome;
            $grupo->delete();

            Log::info('Grupo de participantes excluído', [
                'user_id' => $user->id,
                'grupo_id' => $id,
                'nome' => $nome,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Grupo excluído com sucesso.',
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir grupo', [
                'user_id' => $user->id,
                'grupo_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao excluir grupo. Tente novamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Associa participantes a um grupo.
     */
    public function associarGrupo(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();
        $grupoId = $request->input('grupo_id');
        $participanteIds = $request->input('participantes', []);
        $acao = $request->input('acao', 'adicionar'); // adicionar ou remover

        if (empty($grupoId) || empty($participanteIds)) {
            return response()->json([
                'success' => false,
                'error' => 'Selecione um grupo e participantes.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $grupo = ParticipanteGrupo::where('id', $grupoId)
            ->where('user_id', $user->id)
            ->first();

        if (!$grupo) {
            return response()->json([
                'success' => false,
                'error' => 'Grupo não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Filtrar apenas participantes do usuário
        $participantesValidos = Participante::where('user_id', $user->id)
            ->whereIn('id', $participanteIds)
            ->pluck('id')
            ->toArray();

        if (empty($participantesValidos)) {
            return response()->json([
                'success' => false,
                'error' => 'Nenhum participante válido selecionado.',
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            if ($acao === 'remover') {
                $grupo->participantes()->detach($participantesValidos);
                $message = count($participantesValidos) . ' participante(s) removido(s) do grupo.';
            } else {
                $grupo->participantes()->syncWithoutDetaching($participantesValidos);
                $message = count($participantesValidos) . ' participante(s) adicionado(s) ao grupo.';
            }

            Log::info('Participantes associados ao grupo', [
                'user_id' => $user->id,
                'grupo_id' => $grupoId,
                'participantes' => count($participantesValidos),
                'acao' => $acao,
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao associar participantes ao grupo', [
                'user_id' => $user->id,
                'grupo_id' => $grupoId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao associar participantes. Tente novamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Exclui um participante e seus registros associados (cascades do DB).
     * Notas fiscais (xml_notas, sped_notas) ficam com participante_id = NULL.
     */
    public function excluirParticipante(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();
        $userId = (int) $user->id;

        $participante = Participante::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$participante) {
            return response()->json([
                'success' => false,
                'error' => 'Participante não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Contar registros associados antes de deletar
        $assinaturas = MonitoramentoAssinatura::where('participante_id', $participante->id)->count();
        $consultas = MonitoramentoConsulta::where('participante_id', $participante->id)->count();
        $scores = $participante->score()->exists() ? 1 : 0;
        $notasXml = XmlNota::where('user_id', $userId)
            ->where(fn($q) => $q->where('emit_participante_id', $participante->id)
                                ->orWhere('dest_participante_id', $participante->id))
            ->count();
        $consultaLoteResultados = ConsultaResultado::where('participante_id', $participante->id)->count();

        try {
            $razaoSocial = $participante->razao_social;
            $cnpj = $participante->cnpj;

            // DB cascades handle: assinaturas, consultas, scores, pivot grupos, consulta_lote_resultados
            // xml_notas/sped_notas: SET NULL on participante_id
            $participante->delete();

            Log::info('Participante excluído', [
                'user_id' => $userId,
                'participante_id' => $id,
                'cnpj' => $cnpj,
                'deletados' => compact('assinaturas', 'consultas', 'scores', 'consultaLoteResultados'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Participante excluído com sucesso.',
                'deletados' => [
                    'assinaturas' => $assinaturas,
                    'consultas' => $consultas,
                    'scores' => $scores,
                    'consulta_lote_resultados' => $consultaLoteResultados,
                    'notas_desvinculadas' => $notasXml,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir participante', [
                'user_id' => $userId,
                'participante_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao excluir participante. Tente novamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Recebe arquivo .txt e envia para n8n processar.
     * Laravel não valida/extrai CNPJs - apenas repassa o arquivo em base64.
     */
    public function importarTxt(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $request->validate([
            'arquivo' => 'required|file|max:10240', // Máximo 10MB
            'tipo_efd' => 'required|in:EFD Fiscal,EFD Contribuições',
            'cliente_id' => 'nullable|integer',
            'tab_id' => 'nullable|string|max:36',
        ]);

        $user = Auth::user();
        $arquivo = $request->file('arquivo');
        $tipoEfd = $request->tipo_efd;

        // Selecionar webhook baseado no tipo de EFD
        $webhookUrl = $tipoEfd === 'EFD Fiscal'
            ? config('services.webhook.monitoramento_importacao_fiscal_url')
            : config('services.webhook.monitoramento_importacao_contribuicoes_url');

        // Validar que o cliente pertence ao usuário (se fornecido)
        $clienteId = $request->input('cliente_id');
        if ($clienteId) {
            $cliente = Cliente::where('id', $clienteId)
                ->where('user_id', $user->id)
                ->first();
            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cliente não encontrado ou não pertence ao usuário.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        if (empty($webhookUrl)) {
            $configKey = $tipoEfd === 'EFD Fiscal'
                ? 'WEBHOOK_MONITORAMENTO_IMPORTACAO_FISCAL_URL'
                : 'WEBHOOK_MONITORAMENTO_IMPORTACAO_CONTRIBUICOES_URL';
            Log::error("Webhook URL para importação .txt não configurada ({$configKey})", [
                'tipo_efd' => $tipoEfd,
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Serviço de importação não configurado. Verifique as variáveis de ambiente.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        try {
            // Enviar arquivo em base64 para n8n (n8n faz toda validação e extração)
            $response = Http::timeout(30)->post($webhookUrl, [
                'user_id' => $user->id,
                'tipo_efd' => $request->tipo_efd,
                'filename' => $arquivo->getClientOriginalName(),
                'file_base64' => base64_encode(file_get_contents($arquivo->path())),
                'progress_url' => url('/api/monitoramento/sped/importacao-txt/progress'),
                'cliente_id' => $clienteId,
                'tab_id' => $request->input('tab_id'),
            ]);

            if (!$response->successful()) {
                Log::error('Erro ao enviar arquivo para n8n', [
                    'user_id' => $user->id,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return response()->json([
                    'success' => false,
                    'error' => $response->json('error') ?? 'Erro ao processar arquivo.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $importacaoId = $response->json('importacao_id');

            Log::info('Arquivo .txt enviado para n8n', [
                'user_id' => $user->id,
                'filename' => $arquivo->getClientOriginalName(),
                'importacao_id' => $importacaoId,
                'cliente_id' => $clienteId,
                'tab_id' => $request->input('tab_id'),
            ]);

            return response()->json([
                'success' => true,
                'importacao_id' => $importacaoId,
            ]);
        } catch (\Exception $e) {
            Log::error('Exceção ao enviar arquivo para n8n', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro de conexão com o serviço de importação.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }

    /**
     * SSE para acompanhar progresso da importação.
     * Lê dados do cache (enviados pelo n8n via API) - não consulta banco.
     */
    public function streamImportacao($id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return response()->stream(function () use ($id) {
            $cacheKey = "importacao_progresso_{$id}";
            $tentativas = 0;
            $maxTentativas = 300; // 5 minutos (300 segundos)

            while ($tentativas < $maxTentativas) {
                // Lê dados do cache (n8n envia via API)
                $dados = Cache::get($cacheKey);

                if (!$dados) {
                    // Ainda não começou ou não existe
                    echo "data: " . json_encode(['status' => 'aguardando']) . "\n\n";
                } else {
                    echo "data: " . json_encode($dados) . "\n\n";

                    if (in_array($dados['status'] ?? '', ['concluido', 'erro'])) {
                        Cache::forget($cacheKey); // Limpa cache
                        break;
                    }
                }

                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();

                sleep(1);
                $tentativas++;
            }

            // Se chegou no limite, encerra
            if ($tentativas >= $maxTentativas) {
                echo "data: " . json_encode(['status' => 'timeout', 'error' => 'Tempo limite atingido']) . "\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }

    /**
     * SSE para acompanhar resultado de consultas em tempo real.
     * Verifica o banco de dados para consultas que foram concluídas.
     *
     * GET /app/monitoramento/consulta/stream
     *
     * Query params:
     * - consultas: IDs das consultas separados por vírgula (ex: "1,2,3")
     */
    public function streamConsultas(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();
        $consultasIds = $request->query('consultas', '');

        // Parse IDs das consultas
        $ids = array_filter(array_map('intval', explode(',', $consultasIds)));

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'error' => 'Nenhuma consulta especificada.',
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->stream(function () use ($user, $ids) {
            $tentativas = 0;
            $maxTentativas = 300; // 5 minutos
            $consultasConcluidas = [];

            // Enviar comentário inicial
            echo ": SSE connection established for monitoramento consultas\n\n";
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();

            while ($tentativas < $maxTentativas) {
                try {
                    // Buscar consultas que ainda estão pendentes ou processando
                    $consultas = MonitoramentoConsulta::where('user_id', $user->id)
                        ->whereIn('id', $ids)
                        ->whereNotIn('id', $consultasConcluidas)
                        ->get();

                    foreach ($consultas as $consulta) {
                        // Se a consulta foi concluída (sucesso ou erro)
                        if (in_array($consulta->status, ['sucesso', 'erro'])) {
                            $data = [
                                'type' => $consulta->status === 'sucesso' ? 'consulta_sucesso' : 'consulta_erro',
                                'consulta_id' => $consulta->id,
                                'participante_id' => $consulta->participante_id,
                                'status' => $consulta->status,
                                'situacao_geral' => $consulta->situacao_geral,
                                'tem_pendencias' => $consulta->tem_pendencias,
                                'executado_em' => $consulta->executado_em?->toIso8601String(),
                            ];

                            if ($consulta->status === 'erro') {
                                $data['error_code'] = $consulta->error_code;
                                $data['error_message'] = $consulta->error_message;
                            }

                            echo "data: " . json_encode($data) . "\n\n";
                            $consultasConcluidas[] = $consulta->id;

                            Log::info('SSE: Consulta concluída notificada', [
                                'user_id' => $user->id,
                                'consulta_id' => $consulta->id,
                                'status' => $consulta->status,
                            ]);
                        }
                    }

                    // Se todas as consultas foram concluídas, encerrar
                    if (count($consultasConcluidas) >= count($ids)) {
                        echo "data: " . json_encode(['type' => 'complete', 'message' => 'Todas as consultas concluídas']) . "\n\n";
                        if (ob_get_level() > 0) {
                            ob_flush();
                        }
                        flush();
                        break;
                    }

                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();

                    sleep(2); // Verifica a cada 2 segundos
                    $tentativas++;

                    // Verificar se a conexão ainda está ativa
                    if (connection_aborted()) {
                        break;
                    }
                } catch (\Exception $e) {
                    Log::error('SSE: Erro no stream de consultas', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                    sleep(2);
                    $tentativas++;
                }
            }

            // Se chegou no limite, encerra
            if ($tentativas >= $maxTentativas) {
                echo "data: " . json_encode(['type' => 'timeout', 'error' => 'Tempo limite atingido']) . "\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }

    /**
     * SSE para acompanhar progresso de processamento SPED em tempo real.
     * Lê dados do cache (enviados pelo n8n via API) isolados por user_id + tab_id.
     *
     * GET /app/monitoramento/progresso/stream?tab_id=xxx
     *
     * Este endpoint é usado pelo frontend para acompanhar o progresso da
     * identificação de participantes em arquivos SPED. O n8n envia atualizações
     * de progresso para /api/monitoramento/sped/importacao-txt/progress com
     * user_id, tab_id, progresso (0-100), mensagem e status.
     */
    public function streamProgresso(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $userId = auth()->id();
        $tabId = $request->query('tab_id');

        if (!$tabId) {
            return response()->json([
                'success' => false,
                'error' => 'tab_id obrigatório.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Chave do cache: progresso:{user_id}:{tab_id}
        $cacheKey = "progresso:{$userId}:{$tabId}";

        Log::info('SSE streamProgresso iniciado', [
            'user_id' => $userId,
            'tab_id' => $tabId,
            'cache_key' => $cacheKey,
        ]);

        return response()->stream(function () use ($cacheKey, $userId, $tabId) {
            $tentativas = 0;
            $maxTentativas = 300; // 5 minutos (300 segundos)
            $lastDataHash = null; // Para evitar enviar dados repetidos

            // Enviar comentário inicial
            echo ": SSE connection established for progress stream (user:{$userId}, tab:{$tabId})\n\n";
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();

            while ($tentativas < $maxTentativas) {
                try {
                    // Lê dados do cache (n8n envia via API)
                    $data = Cache::get($cacheKey);

                    if ($data) {
                        // Calcular hash para detectar mudanças
                        $currentHash = md5(json_encode($data));

                        // Só enviar se os dados mudaram
                        if ($currentHash !== $lastDataHash) {
                            $lastDataHash = $currentHash;

                            // Enviar dados de progresso
                            echo "data: " . json_encode($data) . "\n\n";

                            if (ob_get_level() > 0) {
                                ob_flush();
                            }
                            flush();

                            // Se status é final, encerrar a conexão
                            if (in_array($data['status'] ?? '', ['concluido', 'erro'])) {
                                Log::info('SSE streamProgresso: status final recebido', [
                                    'user_id' => $userId,
                                    'tab_id' => $tabId,
                                    'status' => $data['status'],
                                ]);
                                // Limpar cache após status final
                                Cache::forget($cacheKey);
                                break;
                            }
                        }
                    }

                    // Verificar se a conexão ainda está ativa
                    if (connection_aborted()) {
                        Log::info('SSE streamProgresso: conexão abortada pelo cliente', [
                            'user_id' => $userId,
                            'tab_id' => $tabId,
                        ]);
                        break;
                    }

                    sleep(1);
                    $tentativas++;

                } catch (\Exception $e) {
                    Log::error('SSE streamProgresso: erro no loop', [
                        'user_id' => $userId,
                        'tab_id' => $tabId,
                        'error' => $e->getMessage(),
                    ]);
                    sleep(1);
                    $tentativas++;
                    if (connection_aborted()) {
                        break;
                    }
                }
            }

            // Se chegou no limite, encerrar
            if ($tentativas >= $maxTentativas) {
                echo "data: " . json_encode([
                    'status' => 'timeout',
                    'progresso' => 0,
                    'mensagem' => 'Tempo limite atingido. Tente novamente.',
                ]) . "\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
                Log::warning('SSE streamProgresso: timeout', [
                    'user_id' => $userId,
                    'tab_id' => $tabId,
                ]);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }

    /**
     * Retorna participantes por array de IDs (JSON para AJAX).
     * Usado quando n8n envia participante_ids no payload de conclusão.
     */
    public function participantesPorIds(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();
        $ids = $request->input('ids', []);

        if (empty($ids) || !is_array($ids)) {
            return response()->json([
                'success' => false,
                'error' => 'Nenhum ID de participante fornecido.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Converter strings para inteiros (n8n pode enviar ["295", "325"] como strings)
        $ids = array_map('intval', $ids);

        // Buscar participantes pelos IDs, garantindo que pertencem ao usuário
        $perPage = $request->input('per_page', 10);
        $participantes = Participante::whereIn('id', $ids)
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'participantes' => $participantes->map(function ($p) {
                return [
                    'id' => $p->id,
                    'cnpj' => $p->cnpj,
                    'razao_social' => $p->razao_social,
                    'situacao_cadastral' => $p->situacao_cadastral,
                    'regime_tributario' => $p->regime_tributario,
                    'uf' => $p->uf,
                ];
            }),
            'total' => $participantes->total(),
            'per_page' => $participantes->perPage(),
            'current_page' => $participantes->currentPage(),
            'last_page' => $participantes->lastPage(),
            'prev_page_url' => $participantes->previousPageUrl(),
            'next_page_url' => $participantes->nextPageUrl(),
        ]);
    }

    /**
     * Retorna participantes de uma importação específica (JSON para AJAX).
     */
    public function participantesPorImportacao(Request $request, $importacaoId)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        // Verificar se a importação pertence ao usuário
        $importacao = SpedImportacao::where('id', $importacaoId)
            ->where('user_id', $user->id)
            ->first();

        if (!$importacao) {
            return response()->json([
                'success' => false,
                'error' => 'Importação não encontrada.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Buscar participantes dessa importação
        $perPage = $request->input('per_page', 10);
        $participantes = Participante::where('user_id', $user->id)
            ->where('importacao_sped_id', $importacaoId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'importacao' => [
                'id' => $importacao->id,
                'filename' => $importacao->filename,
                'tipo_efd' => $importacao->tipo_efd,
                'total_participantes' => $importacao->total_participantes,
                'novos' => $importacao->novos,
                'duplicados' => $importacao->duplicados,
                'created_at' => $importacao->created_at->format('d/m/Y H:i'),
            ],
            'participantes' => $participantes->map(function ($p) {
                return [
                    'id' => $p->id,
                    'cnpj' => $p->cnpj,
                    'razao_social' => $p->razao_social,
                    'situacao_cadastral' => $p->situacao_cadastral,
                    'regime_tributario' => $p->regime_tributario,
                    'uf' => $p->uf,
                ];
            }),
            'total' => $participantes->total(),
            'per_page' => $participantes->perPage(),
            'current_page' => $participantes->currentPage(),
            'last_page' => $participantes->lastPage(),
            'prev_page_url' => $participantes->previousPageUrl(),
            'next_page_url' => $participantes->nextPageUrl(),
        ]);
    }

    /**
     * Redireciona para login.
     */
    private function redirectToLogin(Request $request)
    {
        if ($this->isAjaxRequest($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Você não está logado',
                'redirect' => '/login'
            ]);
        }
        return redirect('/login');
    }

    /**
     * Verifica se a requisição é AJAX.
     */
    private function isAjaxRequest(Request $request): bool
    {
        if (method_exists($request, 'ajax')) {
            return $request->ajax();
        }

        $xRequestedWith = $request->header('X-Requested-With');
        $wantsJson = $request->wantsJson();
        $expectsJson = $request->expectsJson();

        return $wantsJson
            || $expectsJson
            || $xRequestedWith === 'XMLHttpRequest';
    }
}
