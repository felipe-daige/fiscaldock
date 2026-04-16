<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\EfdNota;
use App\Models\XmlImportacao;
use App\Models\XmlNota;
use App\Services\CreditService;
use App\Services\ValidacaoContabilService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ValidacaoController extends Controller
{
    private const AUTH_VIEW_PREFIX = 'autenticado.validacao.';
    private const AUTH_LAYOUT_VIEW = 'autenticado.layouts.app';

    public function __construct(
        protected ValidacaoContabilService $validacaoService,
        protected CreditService $creditService
    ) {}

    /**
     * Dashboard de Validacao Contabil.
     */
    public function index(Request $request)
    {
        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $userId = Auth::id();

        // Estatisticas gerais
        $estatisticas = $this->validacaoService->getEstatisticas($userId);

        // Importacoes com notas para validar
        $importacoes = XmlImportacao::where('user_id', $userId)
            ->where('status', 'concluido')
            ->withCount(['notas', 'notas as notas_validadas_count' => function ($q) {
                $q->whereNotNull('validacao');
            }])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Notas com alertas bloqueantes (ultimas 10)
        $notasCriticas = XmlNota::where('user_id', $userId)
            ->whereNotNull('validacao')
            ->whereRaw("EXISTS (SELECT 1 FROM jsonb_array_elements(validacao->'alertas') AS a WHERE a->>'nivel' = 'bloqueante')")
            ->with(['emitente', 'destinatario'])
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        // Categorias de validacao
        $categorias = $this->validacaoService->getCategorias();

        $data = [
            'estatisticas' => $estatisticas,
            'importacoes' => $importacoes,
            'notasCriticas' => $notasCriticas,
            'categorias' => $categorias,
            'escopoNotas' => $this->buildEscopoNotasResumo($userId),
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

        $query = $this->queryListagem($userId, $filtros)
            ->with(['emitente:id,cnpj,razao_social', 'destinatario:id,cnpj,razao_social']);

        $notas = $query->orderByDesc('data_emissao')->orderByDesc('id')->paginate(50)->withQueryString();

        $clientes = \App\Models\Cliente::where('user_id', $userId)
            ->orderBy('razao_social')
            ->get(['id', 'razao_social', 'documento']);

        $data = [
            'notas' => $notas,
            'clientes' => $clientes,
            'filtros' => $filtros,
            'escopoNotas' => $this->buildEscopoNotasResumo($userId),
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

        $ids = $this->queryListagem($userId, $filtros)->pluck('id');

        return response()->json([
            'success' => true,
            'ids' => $ids,
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
            'custoEstimadoCreditos' => 14,
            'fornecedorMvp' => 'InfoSimples',
            'clientes' => \App\Models\Cliente::where('user_id', Auth::id())
                ->orderBy('razao_social')
                ->get(['id', 'razao_social', 'documento']),
            'ultimasConsultasDfe' => XmlNota::where('user_id', Auth::id())
                ->with('cliente:id,razao_social,documento')
                ->orderByDesc('updated_at')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(),
        ];

        return $this->render($request, 'buscar-nfe', $data);
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
        ];
    }

    private function queryListagem(int $userId, array $f)
    {
        $q = XmlNota::where('user_id', $userId);

        if (! empty($f['periodo_de']) && ! empty($f['periodo_ate'])) {
            $q->whereBetween('data_emissao', [$f['periodo_de'].' 00:00:00', $f['periodo_ate'].' 23:59:59']);
        } elseif (! empty($f['periodo_de'])) {
            $q->where('data_emissao', '>=', $f['periodo_de'].' 00:00:00');
        } elseif (! empty($f['periodo_ate'])) {
            $q->where('data_emissao', '<=', $f['periodo_ate'].' 23:59:59');
        }

        if (! empty($f['cliente_id'])) {
            $q->where(function ($sub) use ($f) {
                $sub->where('emit_cliente_id', $f['cliente_id'])
                    ->orWhere('dest_cliente_id', $f['cliente_id']);
            });
        }

        if (! empty($f['participante_cnpj'])) {
            $cnpj = preg_replace('/\D/', '', $f['participante_cnpj']);
            $q->where(function ($sub) use ($cnpj) {
                $sub->where('emit_cnpj', $cnpj)->orWhere('dest_cnpj', $cnpj);
            });
        }

        if ($f['tipo_nota'] === 'entrada') {
            $q->where('tipo_nota', XmlNota::TIPO_ENTRADA);
        } elseif ($f['tipo_nota'] === 'saida') {
            $q->where('tipo_nota', XmlNota::TIPO_SAIDA);
        }

        switch ($f['status_validacao']) {
            case 'validadas':
                $q->whereNotNull('validacao');
                break;
            case 'nao_validadas':
                $q->whereNull('validacao');
                break;
            case 'com_alertas':
                $q->whereNotNull('validacao')
                  ->whereRaw("EXISTS (SELECT 1 FROM jsonb_array_elements(validacao->'alertas') AS a WHERE a->>'nivel' = 'bloqueante')");
                break;
        }

        return $q;
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
            'importacao_id' => 'integer',
            'tipo' => 'in:completa,deep,local',
        ]);

        $userId = Auth::id();
        $tipo = $request->input('tipo', 'completa');

        // Obter nota_ids diretamente ou via importacao_id
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

        $custo = $this->validacaoService->calcularCusto($notaIds, $userId, $tipo);
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
            'tipo' => 'in:completa,deep,local',
        ]);

        $userId = Auth::id();
        $notaIds = $request->input('nota_ids');
        $tipo = $request->input('tipo', 'completa');
        $user = Auth::user();

        // Calcular custo
        $custo = $this->validacaoService->calcularCusto($notaIds, $userId, $tipo);

        // Validacao gratuita (regras locais) nao cobra
        // Camada paga so cobra se tipo != 'local'
        if ($tipo !== 'local' && $custo['custo_total'] > 0) {
            if (! $this->creditService->hasEnough($user, $custo['custo_total'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Creditos insuficientes',
                    'custo_necessario' => $custo['custo_total'],
                    'saldo_atual' => $this->creditService->getBalance($user),
                ], 402);
            }

            // Debitar creditos
            $this->creditService->deduct($user, $custo['custo_total']);
        }

        // Executar validacao
        $resultado = $this->validacaoService->validarNotas($notaIds, $userId, $tipo);

        return response()->json(array_merge($resultado, [
            'creditos_utilizados' => $custo['custo_total'],
        ]));
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

        $tipo = $request->input('tipo', 'completa');

        // Calcular e cobrar creditos
        $custo = $this->validacaoService->calcularCusto($notaIds, $userId, $tipo);

        if ($tipo !== 'local' && $custo['custo_total'] > 0) {
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

        $nota = XmlNota::where('id', $id)
            ->where('user_id', $userId)
            ->with(['emitente', 'destinatario', 'importacaoXml'])
            ->firstOrFail();

        // Se nao foi validada ainda, executar validacao preview (sem salvar)
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
        $view = self::AUTH_VIEW_PREFIX . $viewName;

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
