<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Concerns\RespondeAjax;
use App\Http\Controllers\Concerns\SetsDownloadToken;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\MonitoramentoAssinatura;
use App\Models\MonitoramentoConsulta;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\ParticipanteScore;
use App\Models\XmlNota;
use App\Services\Consultas\PerfilConsultaHistoricoService;
use App\Services\Consultas\ResultadoDetalhePresenter;
use App\Services\NotaFiscalService;
use App\Services\ParecerFiscalService;
use App\Services\Risk\RiscoCreditoCpfService;
use App\Services\SaldoService;
use App\Support\ParticipanteOrigem;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ParticipanteController extends Controller
{
    use RespondeAjax;
    use SetsDownloadToken;

    private const AUTH_VIEW_PREFIX = 'autenticado.monitoramento.';

    private const AUTH_LAYOUT_VIEW = 'autenticado.layouts.app';

    /** Rótulo curto por fonte p/ os badges compactos da listagem (espelha /app/clientes). */
    private const FONTE_CURTA = [
        'cnd_federal' => 'Federal',
        'cnd_estadual' => 'Estadual',
        'cnd_municipal' => 'Municipal',
        'crf_fgts' => 'FGTS',
        'sintegra' => 'Sintegra',
    ];

    public function __construct(
        protected SaldoService $saldoService,
        protected NotaFiscalService $notaFiscalService,
        protected ResultadoDetalhePresenter $detalhePresenter,
        protected PerfilConsultaHistoricoService $perfilConsultaHistorico,
    ) {}

    /**
     * Formulário de cadastro manual de participante.
     */
    public function create(Request $request)
    {
        $viewName = self::AUTH_VIEW_PREFIX.'novo-participante';

        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $user = Auth::user();
        $userId = (int) $user->id;

        $clientes = Cliente::where('user_id', $userId)
            ->where('ativo', true)
            ->orderBy('razao_social')
            ->get();

        $data = [
            'clientes' => $clientes,
            'credits' => $this->saldoService->getBalance($user),
        ];

        if ($this->isAjaxRequest($request)) {
            $renderedView = view($viewName, $data)->render();

            return response($renderedView)->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $viewName,
        ], $data));
    }

    /**
     * Salva um novo participante cadastrado manualmente.
     */
    public function store(Request $request)
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        $tipDoc = $request->input('tipo_documento', 'PJ');
        $isPF = $tipDoc === 'PF';
        $docLabel = $isPF ? 'CPF' : 'CNPJ';

        $validated = $request->validate([
            'tipo_documento' => 'required|in:PF,PJ',
            'cnpj' => 'required|string|max:18',
            'razao_social' => $isPF ? 'nullable|string|max:255' : 'required|string|max:255',
            'nome_fantasia' => $isPF ? 'required|string|max:255' : 'nullable|string|max:255',
            'inscricao_estadual' => 'nullable|string|max:20',
            'crt' => 'nullable|in:1,2,3',
            'telefone' => 'nullable|string|max:20',
            'cliente_id' => 'nullable|integer',
            'cep' => 'nullable|string|max:9',
            'endereco' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:100',
            'bairro' => 'nullable|string|max:100',
            'municipio' => 'nullable|string|max:100',
            'uf' => 'nullable|string|size:2',
        ], [
            'razao_social.required' => 'Razão social é obrigatória para Pessoa Jurídica.',
            'nome_fantasia.required' => 'Nome completo é obrigatório para Pessoa Física.',
        ]);

        // Limpar documento (CNPJ ou CPF)
        $doc = preg_replace('/[^0-9]/', '', $validated['cnpj']);
        $expectedLen = $isPF ? 11 : 14;

        if (strlen($doc) !== $expectedLen) {
            return response()->json([
                'success' => false,
                'errors' => ['cnpj' => ["{$docLabel} deve conter {$expectedLen} dígitos."]],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Verificar unicidade (user_id, cnpj)
        $existente = Participante::where('user_id', $user->id)
            ->where('documento', $doc)
            ->first();

        if ($existente) {
            return response()->json([
                'success' => false,
                'errors' => ['cnpj' => ["Este {$docLabel} já está cadastrado na sua base."]],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Validar que cliente_id pertence ao usuário
        $clienteId = $validated['cliente_id'] ?? null;
        if ($clienteId) {
            $cliente = Cliente::where('id', $clienteId)
                ->where('user_id', $user->id)
                ->first();
            if (! $cliente) {
                return response()->json([
                    'success' => false,
                    'errors' => ['cliente_id' => ['Cliente não encontrado.']],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        // Limpar CEP
        $cep = isset($validated['cep']) ? preg_replace('/[^0-9]/', '', $validated['cep']) : null;

        // Para PF: copiar nome_fantasia para razao_social se vazio (garante listagens)
        $razaoSocial = $validated['razao_social'] ?? null;
        if ($isPF && empty($razaoSocial)) {
            $razaoSocial = $validated['nome_fantasia'];
        }

        try {
            $participante = Participante::create([
                'user_id' => $user->id,
                'documento' => $doc,
                'tipo_documento' => $tipDoc,
                'razao_social' => $razaoSocial,
                'nome_fantasia' => $validated['nome_fantasia'] ?? null,
                'inscricao_estadual' => $isPF ? null : ($validated['inscricao_estadual'] ?? null),
                'crt' => $isPF ? null : ($validated['crt'] ?? null),
                'telefone' => $validated['telefone'] ?? null,
                'cliente_id' => $clienteId,
                'cep' => $cep,
                'endereco' => $validated['endereco'] ?? null,
                'numero' => $validated['numero'] ?? null,
                'complemento' => $validated['complemento'] ?? null,
                'bairro' => $validated['bairro'] ?? null,
                'municipio' => $validated['municipio'] ?? null,
                'uf' => $validated['uf'] ?? null,
                'origem_tipo' => 'MANUAL',
            ]);

            Log::info('Participante criado manualmente', [
                'user_id' => $user->id,
                'participante_id' => $participante->id,
                'tipo_documento' => $tipDoc,
                'documento' => $doc,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Participante cadastrado com sucesso!',
                'participante_id' => $participante->id,
                'redirect' => '/app/participantes',
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao criar participante manualmente', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao cadastrar participante. Tente novamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Formulário de edição de participante (reutiliza view novo-participante).
     */
    public function edit(Request $request, $id)
    {
        $viewName = self::AUTH_VIEW_PREFIX.'novo-participante';

        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $user = Auth::user();
        $userId = (int) $user->id;

        $participante = Participante::where('user_id', $userId)->findOrFail($id);

        $clientes = Cliente::where('user_id', $userId)
            ->where('ativo', true)
            ->orderBy('razao_social')
            ->get();

        $data = [
            'participante' => $participante,
            'clientes' => $clientes,
            'credits' => $this->saldoService->getBalance($user),
        ];

        if ($this->isAjaxRequest($request)) {
            $renderedView = view($viewName, $data)->render();

            return response($renderedView)->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $viewName,
        ], $data));
    }

    /**
     * Atualiza um participante existente.
     */
    public function update(Request $request, $id)
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        $participante = Participante::where('user_id', $user->id)->findOrFail($id);

        $isPF = $participante->tipo_documento === 'PF';

        $validated = $request->validate([
            'razao_social' => $isPF ? 'nullable|string|max:255' : 'required|string|max:255',
            'nome_fantasia' => $isPF ? 'required|string|max:255' : 'nullable|string|max:255',
            'inscricao_estadual' => 'nullable|string|max:20',
            'crt' => 'nullable|in:1,2,3',
            'telefone' => 'nullable|string|max:20',
            'cliente_id' => 'nullable|integer',
            'cep' => 'nullable|string|max:9',
            'endereco' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:100',
            'bairro' => 'nullable|string|max:100',
            'municipio' => 'nullable|string|max:100',
            'uf' => 'nullable|string|size:2',
        ], [
            'razao_social.required' => 'Razão social é obrigatória para Pessoa Jurídica.',
            'nome_fantasia.required' => 'Nome completo é obrigatório para Pessoa Física.',
        ]);

        // Validar que cliente_id pertence ao usuário
        $clienteId = $validated['cliente_id'] ?? null;
        if ($clienteId) {
            $cliente = Cliente::where('id', $clienteId)
                ->where('user_id', $user->id)
                ->first();
            if (! $cliente) {
                return response()->json([
                    'success' => false,
                    'errors' => ['cliente_id' => ['Cliente não encontrado.']],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        // Limpar CEP
        $cep = isset($validated['cep']) ? preg_replace('/[^0-9]/', '', $validated['cep']) : null;

        // Para PF: copiar nome_fantasia para razao_social se vazio
        $razaoSocial = $validated['razao_social'] ?? null;
        if ($isPF && empty($razaoSocial)) {
            $razaoSocial = $validated['nome_fantasia'];
        }

        try {
            $participante->update([
                'razao_social' => $razaoSocial,
                'nome_fantasia' => $validated['nome_fantasia'] ?? null,
                'inscricao_estadual' => $isPF ? null : ($validated['inscricao_estadual'] ?? null),
                'crt' => $isPF ? null : ($validated['crt'] ?? null),
                'telefone' => $validated['telefone'] ?? null,
                'cliente_id' => $clienteId,
                'cep' => $cep,
                'endereco' => $validated['endereco'] ?? null,
                'numero' => $validated['numero'] ?? null,
                'complemento' => $validated['complemento'] ?? null,
                'bairro' => $validated['bairro'] ?? null,
                'municipio' => $validated['municipio'] ?? null,
                'uf' => $validated['uf'] ?? null,
            ]);

            Log::info('Participante atualizado', [
                'user_id' => $user->id,
                'participante_id' => $participante->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Participante atualizado com sucesso!',
                'participante_id' => $participante->id,
                'redirect' => '/app/participante/'.$participante->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar participante', [
                'user_id' => $user->id,
                'participante_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao atualizar participante. Tente novamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Lista participantes importados com filtros.
     */
    public function index(Request $request)
    {
        $participantesView = self::AUTH_VIEW_PREFIX.'participantes-importados';

        if (! view()->exists($participantesView)) {
            abort(404);
        }

        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $user = Auth::user();
        $userId = (int) $user->id;

        // Filtros
        $importacaoId = $request->get('importacao');
        $clienteId = $request->get('cliente');
        // Origem é DERIVADA (importacao_efd_id/origem_tipo) — o n8n não preenche origem_tipo,
        // então filtrar pelo valor cru nunca casava nada. Whitelist de grupos derivados.
        $origemTipo = in_array($request->get('origem'), ['efd', 'xml', 'manual'], true) ? $request->get('origem') : null;
        $busca = $request->get('busca');
        $regimeTributario = $request->get('regime');
        $situacaoCadastral = $request->get('situacao');
        $uf = $request->get('uf');
        $tipoDocumento = strtoupper((string) $request->get('tipo_documento', ''));

        // Relação fiscal (fornecedor/cliente/ambos/sem_movimentacao) — derivada das
        // notas EFD. Whitelist: valor fora do conjunto é ignorado (form-submit, sem 422).
        $relacaoValida = ['fornecedor', 'cliente', 'ambos', 'sem_movimentacao'];
        $relacao = in_array($request->get('relacao'), $relacaoValida, true) ? $request->get('relacao') : null;

        // Filtros de consulta/risco (whitelist) — alinhados com /app/clientes.
        $stValida = ['nunca', 'desatualizada', 'recente'];
        $statusConsulta = in_array($request->get('status_consulta'), $stValida, true) ? $request->get('status_consulta') : null;
        $regValida = ['regular', 'irregular', 'indeterminada', 'nao_consultado'];
        $regularidade = in_array($request->get('regularidade'), $regValida, true) ? $request->get('regularidade') : null;
        $monitorado = in_array($request->get('monitorado'), ['sim', 'nao'], true) ? $request->get('monitorado') : null;
        $ordem = in_array($request->get('ordem'), ['movimentacao', 'recentes', 'nome'], true) ? $request->get('ordem') : 'movimentacao';

        $resumoService = app(\App\Services\Consultas\ParticipanteFiscalResumoService::class);

        // Papel + valor + qtd de cada participante com movimentação (1 query) — serve o
        // filtro de relação, o badge de papel E a coluna de movimentação por linha.
        $resumoMov = $resumoService->resumoMovimentacao($userId);
        $papeis = array_map(fn (array $r) => $r['papel'], $resumoMov);

        // Regularidade por participante (só quando o filtro é usado).
        $regularidadeMap = $regularidade !== null ? $resumoService->regularidadePorParticipante($userId) : [];

        // Query de participantes com filtros
        $participantesQuery = Participante::where('user_id', $userId)
            ->excludingEmpresaPropria()
            ->with([
                'cliente',
                'importacaoEfd',
                'assinaturas' => fn ($q) => $q
                    ->whereIn('status', ['ativo', 'pausado'])
                    ->orderByRaw("CASE WHEN status = 'ativo' THEN 0 ELSE 1 END")
                    ->orderBy('updated_at', 'desc'),
            ])
            ->when($importacaoId, fn ($q) => $q->where('importacao_efd_id', $importacaoId))
            ->when($clienteId, fn ($q) => $q->where('cliente_id', $clienteId))
            ->when($origemTipo, fn ($q) => $this->aplicarFiltroOrigem($q, $origemTipo))
            ->when($busca, function ($q) use ($busca) {
                $q->where(function ($sub) use ($busca) {
                    $sub->where('documento', 'like', "%{$busca}%")
                        ->orWhere('razao_social', 'ilike', "%{$busca}%");
                });
            })
            ->when($regimeTributario, fn ($q) => $q->where('regime_tributario', 'ilike', $regimeTributario))
            ->when($situacaoCadastral, fn ($q) => $q->where('situacao_cadastral', $situacaoCadastral))
            ->when($uf, fn ($q) => $q->where('uf', $uf))
            ->when($tipoDocumento === 'CPF', fn ($q) => $q->somenteCpf())
            ->when($tipoDocumento === 'CNPJ', fn ($q) => $q->somenteCnpj())
            ->when($relacao, function ($q) use ($relacao, $papeis) {
                if ($relacao === 'sem_movimentacao') {
                    // whereNotIn [] vira "1=1" (todos) — correto: ninguém tem movimentação.
                    $q->whereNotIn('id', array_keys($papeis));
                } else {
                    $alvo = match ($relacao) {
                        'fornecedor' => ['fornecedor', 'ambos'],
                        'cliente' => ['cliente', 'ambos'],
                        default => ['ambos'],
                    };
                    // whereIn [] vira "0=1" (nenhum) — correto quando não há match.
                    $q->whereIn('id', array_keys(array_filter(
                        $papeis,
                        fn (string $papel) => in_array($papel, $alvo, true)
                    )));
                }
            })
            ->when($statusConsulta, function ($q) use ($statusConsulta) {
                $corte = Carbon::now()->subDays(30);
                match ($statusConsulta) {
                    'nunca' => $q->whereNull('ultima_consulta_em'),
                    'recente' => $q->where('ultima_consulta_em', '>=', $corte),
                    'desatualizada' => $q->where('ultima_consulta_em', '<', $corte),
                    default => null,
                };
            })
            ->when($regularidade, function ($q) use ($regularidade, $regularidadeMap) {
                if ($regularidade === 'nao_consultado') {
                    $q->whereNotIn('id', array_keys($regularidadeMap));
                } else {
                    $q->whereIn('id', array_keys(array_filter(
                        $regularidadeMap,
                        fn (string $c) => $c === $regularidade
                    )));
                }
            })
            ->when($monitorado, function ($q) use ($monitorado) {
                $ativa = fn ($sub) => $sub->whereIn('status', ['ativo', 'pausado']);
                $monitorado === 'sim' ? $q->whereHas('assinaturas', $ativa) : $q->whereDoesntHave('assinaturas', $ativa);
            });

        // Ordenação: default por volume movimentado (mesmos filtros fiscais do resumo),
        // pra trazer os participantes mais relevantes primeiro. Agregado via joinSub (1 scan
        // de efd_notas) — subquery correlacionada no ORDER BY custava ~2s com base real.
        match ($ordem) {
            'recentes' => $participantesQuery->orderBy('created_at', 'desc'),
            'nome' => $participantesQuery->orderByRaw('razao_social asc nulls last'),
            default => $participantesQuery
                ->leftJoinSub(
                    DB::table('efd_notas')
                        ->where('user_id', $userId)
                        ->where('origem_arquivo', 'fiscal')
                        ->where('cancelada', false)
                        ->whereNotNull('participante_id')
                        ->groupBy('participante_id')
                        ->selectRaw('participante_id, SUM(valor_total) as valor'),
                    'mov', 'mov.participante_id', '=', 'participantes.id'
                )
                ->select('participantes.*')
                ->orderByRaw('COALESCE(mov.valor, 0) desc')
                ->orderBy('participantes.created_at', 'desc'),
        };

        $participantes = $participantesQuery->paginate(20)->withQueryString();
        $agora = Carbon::now();
        $participanteIds = $participantes->getCollection()->pluck('id')->all();
        $ultimosResultados = ConsultaResultado::query()
            ->whereIn('participante_id', $participanteIds)
            ->where('status', ConsultaResultado::STATUS_SUCESSO)
            ->orderBy('consultado_em', 'desc')
            ->get()
            ->unique('participante_id')
            ->keyBy('participante_id');

        $participantes->getCollection()->transform(function (Participante $participante) use ($agora, $ultimosResultados, $resumoMov) {
            $papel = $resumoMov[$participante->id]['papel'] ?? null;
            $papelBadge = match ($papel) {
                'fornecedor' => ['label' => 'Fornecedor', 'hex' => '#2563eb'],
                'cliente' => ['label' => 'Cliente', 'hex' => '#7c3aed'],
                'ambos' => ['label' => 'Fornecedor e cliente', 'hex' => '#0f766e'],
                default => null,
            };
            $participante->setAttribute('papel_badge_label', $papelBadge['label'] ?? null);
            $participante->setAttribute('papel_badge_hex', $papelBadge['hex'] ?? null);
            $participante->setAttribute('mov_valor', (float) ($resumoMov[$participante->id]['valor'] ?? 0));
            $participante->setAttribute('mov_qtd', (int) ($resumoMov[$participante->id]['qtd'] ?? 0));

            $origem = $this->origemBadge($participante);
            $participante->setAttribute('origem_label', $origem['label']);
            $participante->setAttribute('origem_hex', $origem['hex']);

            $assinatura = $participante->assinaturas->first();
            $ultimaConsulta = $participante->ultima_consulta_em;
            $ultimoResultado = $ultimosResultados->get($participante->id);
            $cndFederal = $ultimoResultado?->getCndFederal() ?? [];

            $consultaStatus = 'nunca_consultado';
            $consultaStatusLabel = 'Nunca consultado';
            $consultaStatusHex = '#9ca3af';
            $consultaStatusMeta = 'Sem consulta realizada';

            if ($ultimaConsulta) {
                $diasSemConsulta = $ultimaConsulta->diffInDays($agora);

                if ($diasSemConsulta > 30) {
                    $consultaStatus = 'desatualizada';
                    $consultaStatusLabel = 'Consulta desatualizada';
                    $consultaStatusHex = '#b45309';
                    $consultaStatusMeta = 'Última atualização em '.$ultimaConsulta->format('d/m/Y H:i');
                } else {
                    $consultaStatus = 'consultado_recente';
                    $consultaStatusLabel = 'Consultado recentemente';
                    $consultaStatusHex = '#047857';
                    $consultaStatusMeta = 'Última atualização em '.$ultimaConsulta->format('d/m/Y H:i');
                }
            }

            $cndStatusLabel = 'Não consultada';
            $cndStatusHex = '#9ca3af';
            $cndMeta = 'Sem CND consultada';
            $cndStatus = strtoupper((string) ($cndFederal['status'] ?? ''));
            $cndValidade = $cndFederal['data_validade'] ?? null;

            if ($cndStatus !== '') {
                if (in_array($cndStatus, ['NEGATIVA', 'REGULAR', 'REGULARIDADE'])) {
                    $cndStatusLabel = 'Negativa';
                    $cndStatusHex = '#047857';
                } elseif (str_contains($cndStatus, 'POSITIVA COM EFEITO') || str_contains($cndStatus, 'EFEITO DE NEGATIVA')) {
                    $cndStatusLabel = 'Positiva c/ efeito';
                    $cndStatusHex = '#b45309';
                } elseif (in_array($cndStatus, ['POSITIVA', 'IRREGULAR', 'IRREGULARIDADE'])) {
                    $cndStatusLabel = 'Positiva';
                    $cndStatusHex = '#dc2626';
                } else {
                    $cndStatusLabel = $cndStatus;
                    $cndStatusHex = '#374151';
                }

                $cndMeta = 'Validade não informada';

                if ($cndValidade) {
                    try {
                        $dataValidade = Carbon::parse($cndValidade);
                        $diasRestantes = $agora->copy()->startOfDay()
                            ->diffInDays($dataValidade->copy()->startOfDay(), false);

                        if ($agora->greaterThan($dataValidade->copy()->endOfDay())) {
                            $cndMeta = 'Vencida em '.$dataValidade->format('d/m/Y');
                        } elseif ((int) $diasRestantes === 0) {
                            $cndMeta = 'Vence hoje';
                        } elseif ($diasRestantes <= 7) {
                            $cndMeta = 'Vence em '.(int) $diasRestantes.' dias';
                        } else {
                            $cndMeta = 'Validade: '.$dataValidade->format('d/m/Y');
                        }
                    } catch (\Exception $e) {
                        $cndMeta = 'Validade: '.(string) $cndValidade;
                    }
                }
            }

            $assinaturaLabel = null;
            $assinaturaHex = null;

            if ($assinatura) {
                $assinaturaLabel = $assinatura->status === 'ativo' ? 'Monitoramento ativo' : 'Monitoramento pausado';
                $assinaturaHex = $assinatura->status === 'ativo' ? '#1f2937' : '#6b7280';
            }

            // Badge compacto de TODAS as fontes que a última consulta trouxe (CND Federal/
            // Estadual/Municipal, FGTS, SINTEGRA). A cor reflete a regularidade
            // classificada pela fonte única (CertidaoBadge via presenter).
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
            $participante->setAttribute('certidoes_badges', $certidoesBadges);

            $participante->setAttribute('consulta_status', $consultaStatus);
            $participante->setAttribute('consulta_status_label', $consultaStatusLabel);
            $participante->setAttribute('consulta_status_hex', $consultaStatusHex);
            $participante->setAttribute('consulta_status_meta', $consultaStatusMeta);
            $participante->setAttribute('cnd_federal_status_label', $cndStatusLabel);
            $participante->setAttribute('cnd_federal_status_hex', $cndStatusHex);
            $participante->setAttribute('cnd_federal_meta', $cndMeta);
            $participante->setAttribute('assinatura_label', $assinaturaLabel);
            $participante->setAttribute('assinatura_hex', $assinaturaHex);

            return $participante;
        });

        // Contagens para KPI cards
        $baseQuery = Participante::where('user_id', $userId)->excludingEmpresaPropria();
        $totalParticipantes = (clone $baseQuery)->count();
        $totalAtiva = (clone $baseQuery)->where('situacao_cadastral', 'ATIVA')->count();
        $totalIrregular = (clone $baseQuery)->whereIn('situacao_cadastral', ['BAIXADA', 'SUSPENSA', 'INAPTA'])->count();
        $totalSemConsulta = (clone $baseQuery)->whereNull('ultima_consulta_em')->count();

        // Buscar importações SPED para o filtro
        $importacoes = EfdImportacao::where('user_id', $userId)
            ->where('status', 'concluido')
            ->orderBy('created_at', 'desc')
            ->get();

        // Buscar clientes para o filtro (excluindo empresa própria)
        $clientes = Cliente::where('user_id', $userId)
            ->where('ativo', true)
            ->where('is_empresa_propria', false)
            ->orderBy('razao_social')
            ->get();

        // Grupos de origem derivados (origem_tipo cru não é confiável — n8n não preenche)
        $origens = [
            'efd' => 'Importação EFD',
            'xml' => 'Importação XML',
            'manual' => 'Cadastro manual',
        ];

        // UFs distintas para o filtro
        $ufs = Participante::where('user_id', $userId)
            ->excludingEmpresaPropria()
            ->whereNotNull('uf')
            ->where('uf', '!=', '')
            ->distinct()
            ->orderBy('uf')
            ->pluck('uf');

        $data = [
            'participantes' => $participantes,
            'importacoes' => $importacoes,
            'clientes' => $clientes,
            'origens' => $origens,
            'ufs' => $ufs,
            'currentListUrl' => $request->getRequestUri(),
            'totalParticipantes' => $totalParticipantes,
            'totalAtiva' => $totalAtiva,
            'totalIrregular' => $totalIrregular,
            'totalSemConsulta' => $totalSemConsulta,
            'filtros' => [
                'importacao' => $importacaoId,
                'cliente' => $clienteId,
                'origem' => $origemTipo,
                'busca' => $busca,
                'regime' => $regimeTributario,
                'situacao' => $situacaoCadastral,
                'uf' => $uf,
                'tipo_documento' => $tipoDocumento,
                'relacao' => $relacao,
                'status_consulta' => $statusConsulta,
                'regularidade' => $regularidade,
                'monitorado' => $monitorado,
                'ordem' => $ordem,
            ],
            'credits' => $this->saldoService->getBalance($user),
        ];

        if ($this->isAjaxRequest($request)) {
            $renderedView = view($participantesView, $data)->render();

            return response($renderedView)->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $participantesView,
        ], $data));
    }

    /**
     * Retorna todos os IDs de participantes matching os filtros atuais (para "Selecionar todos").
     */
    public function todosIds(Request $request): JsonResponse
    {
        $user = Auth::user();
        $userId = (int) $user->id;

        $ids = Participante::where('user_id', $userId)
            ->excludingEmpresaPropria()
            ->somenteCnpj()
            ->when($request->importacao, fn ($q, $v) => $q->where('importacao_efd_id', $v))
            ->when($request->cliente, fn ($q, $v) => $q->where('cliente_id', $v))
            ->when(in_array($request->origem, ['efd', 'xml', 'manual'], true), fn ($q) => $this->aplicarFiltroOrigem($q, $request->origem))
            ->when($request->busca, fn ($q, $v) => $q->where(function ($sub) use ($v) {
                $sub->where('documento', 'like', "%{$v}%")
                    ->orWhere('razao_social', 'ilike', "%{$v}%");
            }))
            ->when($request->regime, fn ($q, $v) => $q->where('regime_tributario', 'ilike', $v))
            ->when($request->situacao, fn ($q, $v) => $q->where('situacao_cadastral', $v))
            ->when($request->uf, fn ($q, $v) => $q->where('uf', $v))
            ->when(strtoupper((string) $request->tipo_documento) === 'CPF', fn ($q) => $q->somenteCpf())
            ->when(strtoupper((string) $request->tipo_documento) === 'CNPJ', fn ($q) => $q->somenteCnpj())
            ->when(in_array($request->relacao, ['fornecedor', 'cliente', 'ambos', 'sem_movimentacao'], true), function ($q) use ($request, $userId) {
                $papeis = app(\App\Services\Consultas\ParticipanteFiscalResumoService::class)
                    ->papelPorParticipante($userId);
                if ($request->relacao === 'sem_movimentacao') {
                    $q->whereNotIn('id', array_keys($papeis));
                } else {
                    $alvo = match ($request->relacao) {
                        'fornecedor' => ['fornecedor', 'ambos'],
                        'cliente' => ['cliente', 'ambos'],
                        default => ['ambos'],
                    };
                    $q->whereIn('id', array_keys(array_filter(
                        $papeis,
                        fn (string $papel) => in_array($papel, $alvo, true)
                    )));
                }
            })
            ->when(in_array($request->status_consulta, ['nunca', 'desatualizada', 'recente'], true), function ($q) use ($request) {
                $corte = Carbon::now()->subDays(30);
                match ($request->status_consulta) {
                    'nunca' => $q->whereNull('ultima_consulta_em'),
                    'recente' => $q->where('ultima_consulta_em', '>=', $corte),
                    'desatualizada' => $q->where('ultima_consulta_em', '<', $corte),
                    default => null,
                };
            })
            ->when(in_array($request->regularidade, ['regular', 'irregular', 'indeterminada', 'nao_consultado'], true), function ($q) use ($request, $userId) {
                $map = app(\App\Services\Consultas\ParticipanteFiscalResumoService::class)
                    ->regularidadePorParticipante($userId);
                if ($request->regularidade === 'nao_consultado') {
                    $q->whereNotIn('id', array_keys($map));
                } else {
                    $q->whereIn('id', array_keys(array_filter($map, fn (string $c) => $c === $request->regularidade)));
                }
            })
            ->when(in_array($request->monitorado, ['sim', 'nao'], true), function ($q) use ($request) {
                $ativa = fn ($sub) => $sub->whereIn('status', ['ativo', 'pausado']);
                $request->monitorado === 'sim' ? $q->whereHas('assinaturas', $ativa) : $q->whereDoesntHave('assinaturas', $ativa);
            })
            ->pluck('id');

        return response()->json(['success' => true, 'ids' => $ids, 'total' => $ids->count()]);
    }

    /**
     * Detalhes de um participante específico.
     */
    public function show(Request $request, $id)
    {
        $participanteView = self::AUTH_VIEW_PREFIX.'participante';

        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $user = Auth::user();
        $userId = (int) $user->id;

        $participante = Participante::where('id', $id)
            ->where('user_id', $userId)
            ->with(['importacaoEfd', 'importacaoXml'])
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

        // Notas fiscais unificadas (EFD + XML) do participante
        $notasFiscais = $this->notaFiscalService->listarUnificadas(
            $userId,
            ['participante_id' => $participante->id],
            5,
            1,
            "/app/participante/{$id}/notas"
        );
        $totalNotasFiscais = $notasFiscais->total();

        // Carregar planos disponíveis
        $planos = MonitoramentoPlano::ativos();

        // Estatísticas do participante - combinar ambos sistemas
        $monitoramentoTotal = MonitoramentoConsulta::where('participante_id', $participante->id)
            ->where('user_id', $userId)->count();
        $monitoramentoSucesso = MonitoramentoConsulta::where('participante_id', $participante->id)
            ->where('user_id', $userId)->where('status', 'sucesso')->count();
        $monitoramentoErro = MonitoramentoConsulta::where('participante_id', $participante->id)
            ->where('user_id', $userId)->where('status', 'erro')->count();
        $monitoramentoSaldoUnidades = MonitoramentoConsulta::where('participante_id', $participante->id)
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

        // Valor gasto no sistema novo (lotes). Cada lote cobra por N participantes, então
        // atribui a fração deste participante conforme o total persistido do lote.
        // Pós-cutover (2026-06-07) toda consulta de CNPJ roda em lote; sem isto o "Valor gasto"
        // ficava preso só no MonitoramentoConsulta legado (vazio) e nunca atualizava.
        $loteSaldoUnidades = ConsultaLote::whereHas('participantes', fn ($q) => $q->where('participantes.id', $participante->id))
            ->where('user_id', $userId)
            ->get(['creditos_cobrados', 'total_participantes'])
            ->sum(fn ($l) => ($l->creditos_cobrados) / max(1, (int) $l->total_participantes));

        $estatisticas = [
            'total_consultas' => $monitoramentoTotal + $consultaLoteTotal,
            'consultas_sucesso' => $monitoramentoSucesso + $consultaLoteSucesso,
            'consultas_erro' => $monitoramentoErro + $consultaLoteErro,
            'valor_utilizado_reais' => ($monitoramentoSaldoUnidades + $loteSaldoUnidades),
        ];

        // Buscar última consulta com sucesso para o participante (sistema de consultas em lote)
        $ultimaConsulta = ConsultaResultado::where('participante_id', $participante->id)
            ->where('status', ConsultaResultado::STATUS_SUCESSO)
            ->with(['lote:id,plano_id,created_at', 'lote.plano:id,nome,codigo'])
            ->orderBy('consultado_em', 'desc')
            ->first();

        // A consulta mais recente pode ser de um plano parcial (ex.: Gratuito, só cadastro).
        // A ficha deve mostrar o snapshot mais recente POR FONTE, já consolidado na projeção
        // canônica do score, sem esconder certidões trazidas por um lote anterior mais amplo.
        // A mutação é apenas em memória; o resultado bruto e seu vínculo com o lote permanecem
        // intactos no histórico.
        $dadosConsolidados = ParticipanteScore::where('user_id', $userId)
            ->where('participante_id', $participante->id)
            ->first(['dados_consultados'])
            ?->dados_consultados;

        if ($ultimaConsulta && is_array($dadosConsolidados) && $dadosConsolidados !== []) {
            $dadosAtuais = (array) $ultimaConsulta->resultado_dados;
            $consultasRealizadas = array_values(array_unique(array_merge(
                (array) ($dadosConsolidados['consultas_realizadas'] ?? []),
                (array) ($dadosAtuais['consultas_realizadas'] ?? []),
                array_values(array_intersect(
                    ['cnd_federal', 'cnd_estadual', 'cnd_municipal', 'crf_fgts', 'sintegra', 'qsa'],
                    array_keys($dadosConsolidados)
                )),
            )));

            $dadosMesclados = array_merge($dadosConsolidados, $dadosAtuais);
            if ($consultasRealizadas !== []) {
                $dadosMesclados['consultas_realizadas'] = $consultasRealizadas;
            }
            $ultimaConsulta->resultado_dados = $dadosMesclados;
        }

        // Reusa o presenter canônico das Consultas para exibir todas as seis fontes no mesmo
        // padrão visual: cinco certidões + SINTEGRA, incluindo mensagem e comprovante.
        $fontesConsulta = $ultimaConsulta
            ? array_values(array_filter(
                $this->detalhePresenter->blocos($ultimaConsulta),
                fn (array $bloco) => ($bloco['chave'] ?? null) !== 'cadastro'
            ))
            : [];
        $certidoesConsulta = $ultimaConsulta
            ? $this->detalhePresenter->certidoes($ultimaConsulta)
            : [];

        // Se participante nao tem CEP salvo, tentar pegar da ultima consulta
        if (empty($participante->cep) && $ultimaConsulta) {
            $cepDados = $ultimaConsulta->resultado_dados['endereco']['cep'] ?? null;
            if ($cepDados) {
                $participante->update(['cep' => preg_replace('/\D/', '', $cepDados)]);
            }
        }

        // Geocoding (salva no DB para evitar chamadas repetidas)
        if (is_null($participante->latitude)) {
            $lat = null;
            $lng = null;

            // Tentativa 1: Brasil API via CEP
            if (! empty($participante->cep)) {
                $cep = preg_replace('/\D/', '', $participante->cep);
                $response = Http::timeout(5)
                    ->get("https://brasilapi.com.br/api/cep/v2/{$cep}");
                if ($response->successful()) {
                    $data = $response->json();
                    $lat = $data['location']['coordinates']['latitude'] ?? null;
                    $lng = $data['location']['coordinates']['longitude'] ?? null;
                }
            }

            // Tentativa 2: Nominatim via municipio/UF (quando Brasil API nao tem coordenadas)
            if (! $lat || ! $lng) {
                $municipio = $ultimaConsulta->resultado_dados['endereco']['municipio'] ?? ($participante->municipio ?? null);
                $uf = $ultimaConsulta->resultado_dados['endereco']['uf'] ?? ($participante->uf ?? null);
                if ($municipio && $uf) {
                    $query = urlencode("{$municipio},{$uf},Brasil");
                    $response = Http::timeout(5)
                        ->withHeaders(['User-Agent' => 'FiscalDock/1.0'])
                        ->get("https://nominatim.openstreetmap.org/search?q={$query}&format=json&limit=1");
                    if ($response->successful()) {
                        $results = $response->json();
                        $lat = $results[0]['lat'] ?? null;
                        $lng = $results[0]['lon'] ?? null;
                    }
                }
            }

            if ($lat && $lng) {
                $participante->update(['latitude' => $lat, 'longitude' => $lng]);
            }
        }

        $returnToUrl = $this->resolveReturnToUrl($request, (string) $request->query('return_to', ''));

        $data = [
            'participante' => $participante,
            'consultas' => $consultas,
            'assinaturaAtiva' => $assinaturaAtiva,
            'planos' => $planos,
            'estatisticas' => $estatisticas,
            'notasFiscais' => $notasFiscais,
            'totalNotasFiscais' => $totalNotasFiscais,
            'notasAjaxUrl' => "/app/participante/{$id}/notas",
            'notasContexto' => 'participante',
            'notasEntityId' => $participante->id,
            'returnToUrl' => $returnToUrl,
            'ultimaConsulta' => $ultimaConsulta,
            'fontesConsulta' => $fontesConsulta,
            'certidoesConsulta' => $certidoesConsulta,
            'historicoConsultasPerfil' => $this->perfilConsultaHistorico->paraParticipante($participante),
            'parecerFiscal' => $ultimaConsulta
                ? app(ParecerFiscalService::class)->gerar($ultimaConsulta->getParecerFiscalPayload())
                : [],
            'origemParticipante' => ParticipanteOrigem::dados($participante),
        ];

        $movimentacao = app(\App\Services\Participantes\ParticipanteMovimentacaoService::class)->kpisEResumoParaPreview($participante);
        $data['movimentacao'] = $movimentacao;

        $topMov = app(\App\Services\Consultas\Fiscal\TopMovimentacaoQuery::class);
        $data['top_produtos'] = $topMov->produtos($participante->user_id, 'participante_id', [$participante->id], 10)[$participante->id] ?? [];
        $data['top_cfops'] = $topMov->cfops($participante->user_id, 'participante_id', [$participante->id], 10)[$participante->id] ?? [];

        $resumoFiscal = app(\App\Services\Consultas\ParticipanteFiscalResumoService::class)
            ->paraParticipantes($participante->user_id, [$participante->id], comCfops: true);
        $data['negociantes'] = $resumoFiscal[$participante->id]['relacionamentos'] ?? [];
        $data['negociantesModo'] = 'participante';

        $scoreCalc = $ultimaConsulta?->calcularScore();
        $data['score'] = $participante->is_cpf
            ? app(RiscoCreditoCpfService::class)->avaliar($participante, $movimentacao['kpis'])
            : $scoreCalc;
        $data['score_detalhamento'] = $scoreCalc
            && ! $participante->is_cpf
            ? app(\App\Services\RiskScoreService::class)->detalhar($scoreCalc['scores'])
            : [];

        if ($this->isAjaxRequest($request)) {
            $renderedView = view($participanteView, $data)->render();

            return response($renderedView)->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $participanteView,
        ], $data));
    }

    /**
     * Notas fiscais unificadas do participante (AJAX pagination).
     */
    public function notas(Request $request, int $id)
    {
        if (! Auth::check()) {
            return response('Nao autenticado', 401);
        }

        $userId = (int) Auth::id();
        $participante = Participante::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $page = max(1, (int) $request->get('page', 1));
        $notas = $this->notaFiscalService->listarUnificadas(
            $userId,
            ['participante_id' => $participante->id],
            5,
            $page,
            "/app/participante/{$id}/notas"
        );

        return view('autenticado.partials.notas-fiscais-card', [
            'notas' => $notas,
            'totalNotas' => $notas->total(),
            'ajaxUrl' => "/app/participante/{$id}/notas",
            'contexto' => 'participante',
            'entityId' => $participante->id,
        ]);
    }

    /**
     * Detalhes de uma nota fiscal (retorna JSON).
     */
    public function notaFiscalDetalhes(Request $request, $id)
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $userId = (int) Auth::id();

        $nota = XmlNota::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (! $nota) {
            return response()->json([
                'success' => false,
                'message' => 'Nota fiscal não encontrada.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'nfe_id' => $nota->chave_acesso,
                'tipo_documento' => $nota->tipo_documento,
                'numero_nota' => $nota->numero_documento,
                'serie' => $nota->serie,
                'data_emissao' => $nota->data_emissao?->format('d/m/Y'),
                'natureza_operacao' => $nota->natureza_operacao,
                'valor_total' => number_format((float) $nota->valor_total, 2, ',', '.'),
                'tipo_nota' => $nota->tipo_nota_descricao,
                'finalidade' => $nota->finalidade_descricao,
                'emit_cnpj' => $nota->emit_documento_formatado,
                'emit_razao_social' => $nota->emit_razao_social,
                'emit_uf' => $nota->emit_uf,
                'dest_cnpj' => $nota->dest_documento_formatado,
                'dest_razao_social' => $nota->dest_razao_social,
                'dest_uf' => $nota->dest_uf,
                'icms_valor' => number_format((float) ($nota->icms_valor ?? 0), 2, ',', '.'),
                'icms_st_valor' => number_format((float) ($nota->icms_st_valor ?? 0), 2, ',', '.'),
                'pis_valor' => number_format((float) ($nota->pis_valor ?? 0), 2, ',', '.'),
                'cofins_valor' => number_format((float) ($nota->cofins_valor ?? 0), 2, ',', '.'),
                'ipi_valor' => number_format((float) ($nota->ipi_valor ?? 0), 2, ',', '.'),
                'tributos_total' => number_format((float) ($nota->tributos_total ?? 0), 2, ',', '.'),
            ],
        ]);
    }

    /**
     * Exclui um participante e seus registros associados (cascades do DB).
     * Notas fiscais (xml_notas, efd_notas) ficam com participante_id = NULL.
     */
    public function destroy(Request $request, $id)
    {
        if (! Auth::check()) {
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

        if (! $participante) {
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
            ->where(fn ($q) => $q->where('emit_participante_id', $participante->id)
                ->orWhere('dest_participante_id', $participante->id))
            ->count();
        $consultaLoteResultados = ConsultaResultado::where('participante_id', $participante->id)->count();

        try {
            $razaoSocial = $participante->razao_social;
            $cnpj = $participante->documento;

            // DB cascades handle: assinaturas, consultas, scores, pivot grupos, consulta_lote_resultados
            // xml_notas/efd_notas: SET NULL on participante_id
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
     * Bulk delete participantes.
     * DB cascades handle: assinaturas, consultas, scores, pivot grupos, consulta_lote_resultados.
     * xml_notas/efd_notas: SET NULL on participante_id.
     */
    public function bulkExcluir(Request $request)
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuario nao autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();
        $userId = (int) $user->id;

        $validated = $request->validate([
            'ids' => 'required|array|min:1|max:500',
            'ids.*' => 'integer',
        ]);

        $cpfSelecionados = Participante::where('user_id', $userId)
            ->whereIn('id', $validated['ids'])
            ->somenteCpf()
            ->count();

        if ($cpfSelecionados > 0) {
            return response()->json([
                'success' => false,
                'error' => 'CPFs não podem ser selecionados para ações em lote.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $count = Participante::where('user_id', $userId)
                ->whereIn('id', $validated['ids'])
                ->delete();

            Log::info('Participantes excluidos em lote', [
                'user_id' => $userId,
                'count' => $count,
                'ids' => $validated['ids'],
            ]);

            return response()->json([
                'success' => true,
                'message' => $count.' participante(s) excluido(s) com sucesso.',
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir participantes em lote', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao excluir participantes. Tente novamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Retorna participantes por array de IDs (JSON para AJAX).
     * Usado quando n8n envia participante_ids no payload de conclusão.
     */
    public function porIds(Request $request)
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();
        $ids = $request->input('ids', []);

        if (empty($ids) || ! is_array($ids)) {
            return response()->json([
                'success' => false,
                'error' => 'Nenhum ID de participante fornecido.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Converter strings para inteiros (n8n pode enviar ["295", "325"] como strings)
        $ids = array_map('intval', $ids);

        // Buscar participantes pelos IDs, garantindo que pertencem ao usuário
        $perPage = $request->input('per_page', 10);
        $query = Participante::whereIn('id', $ids)
            ->where('user_id', $user->id);

        $importacaoId = $request->input('importacao_id');
        if ($importacaoId) {
            $query->orderByDesc(
                EfdNota::selectRaw('COALESCE(SUM(valor_total), 0)')
                    ->whereColumn('participante_id', 'participantes.id')
                    ->where('importacao_id', $importacaoId)
            );
        } else {
            $query->orderByDesc(
                EfdNota::selectRaw('COALESCE(SUM(valor_total), 0)')
                    ->whereColumn('participante_id', 'participantes.id')
            )->orderBy('created_at', 'desc');
        }

        $participantes = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'participantes' => $participantes->map(function ($p) {
                return [
                    'id' => $p->id,
                    'cnpj' => $p->documento,
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
    public function porImportacao(Request $request, $importacaoId)
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        // Verificar se a importação pertence ao usuário
        $importacao = EfdImportacao::where('id', $importacaoId)
            ->where('user_id', $user->id)
            ->first();

        if (! $importacao) {
            return response()->json([
                'success' => false,
                'error' => 'Importação não encontrada.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Buscar participantes dessa importação
        $perPage = $request->input('per_page', 10);
        $query = Participante::where('user_id', $user->id);

        // Prioriza participante_ids salvo na EfdImportacao (caminho atual do n8n)
        // Fallback para importacao_efd_id (campo legado)
        if (! empty($importacao->participante_ids)) {
            $query->whereIn('id', $importacao->participante_ids);
        } else {
            $query->where('importacao_efd_id', $importacaoId);
        }

        $participantes = $query->orderByDesc(
            EfdNota::selectRaw('COALESCE(SUM(valor_total), 0)')
                ->whereColumn('participante_id', 'participantes.id')
                ->where('importacao_id', $importacaoId)
        )->paginate($perPage);

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
                    'cnpj' => $p->documento,
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
     * Gera e baixa o dossiê completo do participante em PDF.
     */
    public function dossiePdf(\Illuminate\Http\Request $request, $id)
    {
        if (! \Illuminate\Support\Facades\Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $participante = \App\Models\Participante::where('id', $id)
            ->where('user_id', (int) \Illuminate\Support\Facades\Auth::id())
            ->firstOrFail();

        $dados = app(\App\Services\Participantes\DossieParticipanteBuilder::class)->montar($participante);

        $arquivo = 'dossie_'.preg_replace('/\D/', '', (string) $participante->documento);

        // ?formato=xlsx → planilha no modelo de design aprovado (mesma fonte do PDF)
        if ($request->query('formato') === 'xlsx') {
            return app(\App\Services\Dossie\DossieXlsxBuilder::class)
                ->download($dados, $participante, $arquivo.'.xlsx');
        }

        return \App\Support\PdfReport::render('reports.dossie.participante', $dados, 'portrait')
            ->download($arquivo.'.pdf');
    }

    /**
     * Aplica o filtro de origem por grupo DERIVADO (efd|xml|manual). origem_tipo cru não é
     * confiável (o n8n não preenche na importação EFD), então EFD é inferido pelo vínculo
     * com a importação; valores legados SPED_* continuam aceitos.
     */
    private function aplicarFiltroOrigem($query, string $origem): void
    {
        match ($origem) {
            'efd' => $query->where(fn ($s) => $s
                ->whereNotNull('importacao_efd_id')
                ->orWhere('origem_tipo', 'ilike', 'SPED%')),
            'xml' => $query->whereNull('importacao_efd_id')
                ->whereIn(DB::raw('upper(origem_tipo)'), ['XML', 'NFE', 'NFSE', 'CTE']),
            'manual' => $query->where('origem_tipo', 'MANUAL'),
            default => null,
        };
    }

    /**
     * Badge de origem derivado dos dados reais (importação vinculada > origem_tipo legado).
     *
     * @return array{label: string, hex: string}
     */
    private function origemBadge(Participante $participante): array
    {
        return ParticipanteOrigem::dados($participante);
    }

    /**
     * Gera um PDF único com o dossiê de cada participante selecionado.
     * Espelha ClienteController::dossieLote (mesmo teto, mesmo racional de render síncrono).
     */
    public function dossieLote(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect('/login');
        }

        $validated = $request->validate([
            'ids' => 'required|array|min:1|max:500',
            'ids.*' => 'integer',
        ]);

        $teto = \App\Services\Clientes\DossieLoteBuilder::TETO_ITENS;

        // Ordena por volume EFD desc — mesmo critério da listagem, e é o que decide
        // quem entra quando a seleção passa do teto.
        $participantes = Participante::where('user_id', (int) $user->id)
            ->whereIn('id', $validated['ids'])
            ->orderByDesc(
                EfdNota::selectRaw('COALESCE(SUM(valor_total), 0)')
                    ->whereColumn('participante_id', 'participantes.id')
                    ->where('user_id', (int) $user->id)
                    ->where('origem_arquivo', 'fiscal')
                    ->where('cancelada', false)
            )
            ->limit($teto + 1)
            ->get();

        if ($participantes->isEmpty()) {
            return redirect()
                ->route('app.participantes')
                ->with('export_erro', 'Nenhum participante válido na seleção para gerar o dossiê.');
        }

        $truncado = $participantes->count() > $teto;
        $builder = app(\App\Services\Participantes\DossieParticipanteBuilder::class);
        $dossies = $participantes->take($teto)
            ->map(fn (Participante $p) => $builder->montar($p))
            ->values()
            ->all();

        // Dossiê multiplica páginas/tabelas no dompdf e o render é síncrono — mesmos
        // limites do dossiê em lote de clientes.
        ini_set('memory_limit', '1024M');
        set_time_limit(240);

        return \App\Support\PdfReport::render('reports.dossie.participantes-lote', [
            'dossies' => $dossies,
            'truncado' => $truncado,
            'gerado_em' => now()->format('d/m/Y H:i'),
        ], 'portrait')->download('dossies_participantes_'.now()->format('Ymd_Hi').'.pdf');
    }

    /**
     * Payload da listagem dos participantes selecionados (escopo `user_id`). Base comum de
     * PDF/XLSX/CSV. Devolve `null` quando a seleção não tem nenhum participante válido.
     */
    private function listagemSelecionada(Request $request, \App\Services\Participantes\ParticipanteListagemBuilder $builder): ?array
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1|max:1000',
            'ids.*' => 'integer',
        ]);

        return $builder->montar((int) Auth::id(), $validated['ids']);
    }

    private function listagemVaziaRedirect()
    {
        return redirect()
            ->route('app.participantes')
            ->with('export_erro', 'Nenhum participante válido na seleção para exportar.');
    }

    /**
     * PDF de listagem ("de uma folha") dos participantes selecionados. Panorama tabular
     * (cadastral + papel + volume movimentado + regularidade), complementar ao dossiê.
     */
    public function exportarPdf(Request $request, \App\Services\Participantes\ParticipanteListagemBuilder $builder)
    {
        if (! Auth::user()) {
            return redirect('/login');
        }

        $dados = $this->listagemSelecionada($request, $builder);

        if ($dados === null) {
            return $this->listagemVaziaRedirect();
        }

        return $this->comTokenDownload(
            \App\Support\PdfReport::render('reports.participantes-listagem', $dados)
                ->download('participantes_'.now()->format('Ymd_Hi').'.pdf'),
            $request
        );
    }

    /** XLSX da listagem selecionada (mesmas colunas do PDF; movimentado/notas numéricos). */
    public function exportarXlsx(Request $request, \App\Services\Participantes\ParticipanteListagemBuilder $builder)
    {
        if (! Auth::user()) {
            return redirect('/login');
        }

        if (! \App\Support\Reports\XlsxReport::disponivel()) {
            abort(503, 'Exportação XLSX indisponível.');
        }

        $dados = $this->listagemSelecionada($request, $builder);

        if ($dados === null) {
            return $this->listagemVaziaRedirect();
        }

        return $this->comTokenDownload(
            (new \App\Services\Participantes\Export\ParticipanteListagemXlsxBuilder)
                ->download($dados, 'participantes_'.now()->format('Ymd_Hi').'.xlsx'),
            $request
        );
    }

    /** CSV da listagem selecionada (padrão canônico CsvExport: BOM + ";"). */
    public function exportarCsv(Request $request, \App\Services\Participantes\ParticipanteListagemBuilder $builder)
    {
        if (! Auth::user()) {
            return redirect('/login');
        }

        $dados = $this->listagemSelecionada($request, $builder);

        if ($dados === null) {
            return $this->listagemVaziaRedirect();
        }

        $fmtRs = fn ($v) => number_format((float) $v, 2, ',', '.');
        $limpa = fn ($v) => ($v === null || $v === '—') ? '' : $v;

        $colunas = ['Participante', 'Documento', 'UF', 'Situação', 'Regime', 'Papel', 'Notas', 'Movimentado (R$)', 'Regularidade', 'Últ. consulta'];
        $linhas = array_map(fn (array $p) => [
            $p['nome'],
            $p['documento'],
            $limpa($p['uf']),
            $limpa($p['situacao']),
            $limpa($p['regime']),
            $p['papel'],
            (int) $p['notas'],
            $fmtRs($p['movimentado']),
            $p['regularidade'],
            $limpa($p['ultima_consulta']),
        ], $dados['participantes']);
        $linhas[] = ['Total', '', '', '', '', '', '', $fmtRs($dados['total_movimentado']), '', ''];

        return $this->comTokenDownload(
            \App\Support\CsvExport::download('participantes_'.now()->format('Ymd_Hi').'.csv', $colunas, $linhas),
            $request
        );
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
                'redirect' => '/login',
            ]);
        }

        return redirect('/login');
    }

    /**
     * Verifica se a requisição é AJAX.
     */
    /**
     * Normaliza a URL de retorno para evitar open redirects e manter apenas rotas internas do app.
     */
    private function resolveReturnToUrl(Request $request, string $candidate): string
    {
        $fallback = '/app/dashboard';
        $candidate = trim($candidate);

        if ($candidate === '' || preg_match('/[\r\n]/', $candidate)) {
            return $fallback;
        }

        $parsed = parse_url($candidate);

        if ($parsed === false) {
            return $fallback;
        }

        $scheme = $parsed['scheme'] ?? null;
        $host = $parsed['host'] ?? null;
        $path = $parsed['path'] ?? '';
        $query = isset($parsed['query']) ? '?'.$parsed['query'] : '';
        $fragment = isset($parsed['fragment']) ? '#'.$parsed['fragment'] : '';

        if ($scheme !== null || $host !== null) {
            if ($host !== $request->getHost()) {
                return $fallback;
            }

            if ($scheme !== null && $scheme !== $request->getScheme()) {
                return $fallback;
            }
        }

        if (! is_string($path) || ! str_starts_with($path, '/app/')) {
            return $fallback;
        }

        return $path.$query.$fragment;
    }
}
