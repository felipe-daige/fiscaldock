<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Concerns\RespondeAjax;
use App\Http\Controllers\Controller;
use App\Models\ConsultaResultado;
use App\Models\Participante;
use App\Services\Clearance\CertificadoDigitalService;
use App\Services\Consultas\ResultadoDetalhePresenter;
use App\Services\RiskScoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class MinhaEmpresaController extends Controller
{
    use RespondeAjax;

    private const AUTH_VIEW_PREFIX = 'autenticado.minha-empresa.';

    private const AUTH_LAYOUT_VIEW = 'autenticado.layouts.app';

    public function __construct(
        protected RiskScoreService $riskScoreService,
        protected ResultadoDetalhePresenter $detalhePresenter,
        protected \App\Services\Efd\ConsolidadoFiscalService $consolidadoFiscal,
    ) {}

    /**
     * Dashboard da Minha Empresa.
     */
    public function index(Request $request)
    {
        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $user = Auth::user();
        $empresa = $user->empresaPropria();

        if (! $empresa) {
            abort(404, 'Empresa própria não encontrada.');
        }

        // Buscar ou criar participante correspondente ao CNPJ da empresa
        $cnpjLimpo = preg_replace('/\D/', '', $empresa->documento);
        $participante = Participante::firstOrCreate(
            ['user_id' => $user->id, 'documento' => $cnpjLimpo],
            [
                'razao_social' => $empresa->razao_social ?? $empresa->nome,
                'origem_tipo' => 'PROPRIO',
            ]
        );

        // Consulta e score podem estar ligados ao cliente OU ao participante espelho. O perfil
        // usa uma projeção única por CNPJ para cadastro, score e certidões não divergirem.
        $snapshot = app(\App\Services\Perfis\PerfilCnpjSnapshotService::class)
            ->resolver((int) $user->id, (string) $empresa->documento);
        $score = $snapshot['score'];
        $ultimaConsulta = $snapshot['ultima_consulta'];
        $dadosConsulta = $snapshot['dados'];

        // CNDs e certidoes
        $certidoes = $this->extrairCertidoes($dadosConsulta);

        // Alertas recentes. `_fontes_erro` = fontes pedidas pelo plano que não voltaram
        // (ex.: CND Federal com código de retry esgotado) — o usuário pagou, precisa saber.
        $alertas = $this->gerarAlertas($certidoes, $score, $dadosConsulta['_fontes_erro'] ?? []);

        // Contagens para KPIs. Notas = base unificada XML+EFD (deduplicada), não só XML.
        $totalParticipantes = Participante::where('user_id', $user->id)->count();
        $totalNotas = app(\App\Services\NotaFiscalService::class)
            ->listarUnificadas((int) $user->id, [], 1, 1)
            ->total();

        // Panorama fiscal (movimentação + contrapartes/negociantes) da empresa própria,
        // mesmo shape do card de cliente. Null quando não há movimento no acervo EFD.
        $fiscalResumo = app(\App\Services\Consultas\ClienteFiscalResumoService::class)
            ->paraClientes((int) $user->id, [$empresa->id], true)[$empresa->id] ?? null;

        // Notas recentes (base unificada XML+EFD) vinculadas ao CNPJ próprio, paginação AJAX.
        $notasFiscais = app(\App\Services\NotaFiscalService::class)
            ->listarUnificadas((int) $user->id, ['cliente_id' => $empresa->id], 10, 1, '/app/cliente/'.$empresa->id.'/notas');

        // Assinatura de monitoramento contínuo da empresa própria (alvo cliente OU participante).
        $monitoramento = \App\Models\MonitoramentoAssinatura::where('user_id', $user->id)
            ->where('status', 'ativo')
            ->where(function ($query) use ($empresa, $participante) {
                $query->where('cliente_id', $empresa->id)
                    ->orWhere('participante_id', $participante->id);
            })
            ->first();

        // Perfil do score (breakdown por categoria) — mesma decomposição de clientes/show e
        // risk/show, para a Minha Empresa não ficar só com o total sem o "porquê".
        $scoreDetalhamento = $score
            ? $this->riskScoreService->detalhar([
                'cadastral' => $score->score_cadastral,
                'cnd_federal' => $score->score_cnd_federal,
                'cnd_estadual' => $score->score_cnd_estadual,
                'fgts' => $score->score_fgts,
                'trabalhista' => $score->score_trabalhista,
            ])
            : [];

        $data = [
            'empresa' => $empresa,
            // Consolidado fiscal acumulado (C190/D190 de todas as importações EFD da empresa).
            'consolidadoFiscal' => $this->consolidadoFiscal
                ->porCliente((int) $empresa->id, (int) $user->id),
            'participante' => $participante,
            'score' => $score,
            'scoreDetalhamento' => $scoreDetalhamento,
            'ultimaConsulta' => $ultimaConsulta,
            'dadosConsulta' => $dadosConsulta,
            'certidoes' => $certidoes,
            // Certidões no padrão retrátil canônico (mesmo partial do participante/lote):
            // presenter monta os blocos por fonte; o card de cadastro fica de fora porque a
            // página já tem seção própria de dados cadastrais.
            'fontesConsulta' => $ultimaConsulta
                ? array_values(array_filter(
                    $this->detalhePresenter->blocos($ultimaConsulta),
                    fn (array $bloco) => ($bloco['chave'] ?? null) !== 'cadastro'
                ))
                : [],
            'certidoesConsulta' => $ultimaConsulta
                ? $this->detalhePresenter->certidoes($ultimaConsulta)
                : [],
            'alertas' => $alertas,
            'totalParticipantes' => $totalParticipantes,
            'totalNotas' => $totalNotas,
            'fiscalResumo' => $fiscalResumo,
            'notasFiscais' => $notasFiscais,
            'notasAjaxUrl' => '/app/cliente/'.$empresa->id.'/notas',
            'monitoramento' => $monitoramento,
            'certificado' => app(CertificadoDigitalService::class)->status($empresa),
        ];

        $data['perfilCnpj'] = [
            'is_cpf' => false,
            'alertas' => $alertas,
            'cadastro' => app(\App\Services\Perfis\PerfilCnpjViewData::class)
                ->cadastro($empresa, $dadosConsulta, $ultimaConsulta?->consultado_em),
            'score_detalhamento' => $scoreDetalhamento,
            'score_total' => $score?->score_total,
            'score_classificacao' => $score?->classificacao ?? 'nao_avaliado',
            'score_atualizado_em' => $score?->ultima_consulta_em ?? $ultimaConsulta?->consultado_em,
            'fontes_consulta' => $data['fontesConsulta'],
            'certidoes_consulta' => $data['certidoesConsulta'],
            'ultima_consulta' => $ultimaConsulta,
            'fiscal' => $fiscalResumo,
            'notas' => $notasFiscais,
            'total_notas' => $notasFiscais->total(),
            'notas_ajax_url' => '/app/cliente/'.$empresa->id.'/notas',
            'notas_contexto' => 'cliente',
            'entity_id' => $empresa->id,
            'historico' => app(\App\Services\Consultas\PerfilConsultaHistoricoService::class)
                ->paraCliente($empresa),
            'documento' => $empresa->documento,
        ];

        return $this->render($request, 'index', $data);
    }

    /**
     * Historico de consultas da empresa propria.
     */
    public function historico(Request $request)
    {
        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $user = Auth::user();
        $empresa = $user->empresaPropria();

        if (! $empresa) {
            abort(404, 'Empresa própria não encontrada.');
        }

        $cnpjLimpo = preg_replace('/\D/', '', $empresa->documento);
        $participante = Participante::where('user_id', $user->id)
            ->where('documento', $cnpjLimpo)
            ->first();

        // Resultado pode estar no participante espelho OU no cliente (empresa própria).
        $consultas = ConsultaResultado::where(function ($query) use ($participante, $empresa) {
            if ($participante) {
                $query->where('participante_id', $participante->id);
            }
            $query->orWhere('cliente_id', $empresa->id);
        })
            ->with('lote')
            ->latest('consultado_em')
            ->paginate(20);

        return $this->render($request, 'historico', [
            'empresa' => $empresa,
            'participante' => $participante,
            'consultas' => $consultas,
        ]);
    }

    /**
     * Extrai informacoes de certidoes dos dados da consulta.
     */
    private function extrairCertidoes(array $dados): array
    {
        return [
            'cnd_federal' => [
                'status' => $dados['cnd_federal']['status'] ?? null,
                'validade' => $this->normalizarValidade($dados['cnd_federal'] ?? null),
                'comprovante' => $this->comprovanteDe($dados['cnd_federal'] ?? null),
                'consultado' => isset($dados['cnd_federal']),
            ],
            'cnd_estadual' => [
                'status' => $dados['cnd_estadual']['status'] ?? $dados['cnd_estadual'] ?? null,
                'validade' => $this->normalizarValidade($dados['cnd_estadual'] ?? null),
                'comprovante' => $this->comprovanteDe($dados['cnd_estadual'] ?? null),
                'consultado' => isset($dados['cnd_estadual']),
            ],
            'fgts' => [
                'status' => $dados['crf_fgts']['status'] ?? $dados['crf_fgts'] ?? null,
                'validade' => $this->normalizarValidade($dados['crf_fgts'] ?? null),
                'comprovante' => $this->comprovanteDe($dados['crf_fgts'] ?? null),
                'consultado' => isset($dados['crf_fgts']),
            ],
            'cndt' => [
                'status' => $dados['cndt']['status'] ?? $dados['cndt'] ?? null,
                'validade' => $this->normalizarValidade($dados['cndt'] ?? null),
                'comprovante' => $this->comprovanteDe($dados['cndt'] ?? null),
                'consultado' => isset($dados['cndt']),
            ],
            'situacao_cadastral' => $dados['situacao_cadastral'] ?? null,
            'simples_nacional' => $dados['simples_nacional'] ?? null,
            'mei' => $dados['mei'] ?? null,
        ];
    }

    /** URL do comprovante (PDF/HTML) de um bloco de certidão, se houver. */
    private function comprovanteDe(mixed $bloco): ?string
    {
        if (! is_array($bloco)) {
            return null;
        }

        $url = $bloco['comprovante'] ?? null;

        return is_string($url) && trim($url) !== '' ? $url : null;
    }

    /**
     * Normaliza a validade de um bloco de certidão para ISO (Y-m-d).
     *
     * As Fontes emitem o campo canônico `data_validade` em formato BR (d/m/Y) — a leitura
     * antiga usava a chave `validade`, que não existe no payload, deixando toda validade nula
     * (coluna "Não informado" e alerta de vencimento que nunca dispara). Devolve ISO para a
     * view/alertas parsearem com `Carbon::parse` sem ambiguidade de locale.
     */
    private function normalizarValidade(mixed $bloco): ?string
    {
        if (! is_array($bloco)) {
            return null;
        }

        $raw = $bloco['data_validade'] ?? $bloco['validade'] ?? null;
        if (! is_string($raw) || trim($raw) === '') {
            return null;
        }

        try {
            return \Carbon\Carbon::createFromFormat('d/m/Y', trim($raw))->toDateString();
        } catch (\Throwable) {
            try {
                return \Carbon\Carbon::parse(trim($raw))->toDateString();
            } catch (\Throwable) {
                return null;
            }
        }
    }

    /**
     * Gera alertas baseados nas certidoes e score.
     */
    private function gerarAlertas(array $certidoes, ?object $score, array $fontesErro = []): array
    {
        $alertas = [];

        // Alerta de situacao cadastral
        $situacao = strtoupper($certidoes['situacao_cadastral'] ?? '');
        if (in_array($situacao, ['INAPTA', 'SUSPENSA', 'BAIXADA'])) {
            $alertas[] = [
                'tipo' => 'critico',
                'mensagem' => "Situacao cadastral: {$situacao}",
                'icone' => 'alert-triangle',
            ];
        }

        // Alertas de CNDs
        $this->addAlertaCnd($alertas, 'CND Federal', $certidoes['cnd_federal']);
        $this->addAlertaCnd($alertas, 'CND Estadual', $certidoes['cnd_estadual']);
        $this->addAlertaCnd($alertas, 'CRF (FGTS)', $certidoes['fgts']);
        $this->addAlertaCnd($alertas, 'CNDT', $certidoes['cndt']);

        // Fontes que o plano pediu mas não retornaram — consulta paga incompleta.
        $this->addAlertasFontesErro($alertas, $fontesErro);

        // Alerta de score critico
        if ($score && $score->classificacao === 'critico') {
            $alertas[] = [
                'tipo' => 'critico',
                'mensagem' => 'Score de risco critico: '.$score->score_total.'/100',
                'icone' => 'shield-alert',
            ];
        } elseif ($score && $score->classificacao === 'alto') {
            $alertas[] = [
                'tipo' => 'atencao',
                'mensagem' => 'Score de risco alto: '.$score->score_total.'/100',
                'icone' => 'shield-alert',
            ];
        }

        return $alertas;
    }

    /**
     * Adiciona alerta de CND se aplicavel.
     */
    private function addAlertaCnd(array &$alertas, string $nome, array $dados): void
    {
        if (! $dados['consultado']) {
            return;
        }

        $status = strtoupper($dados['status'] ?? '');

        if (in_array($status, ['POSITIVA', 'IRREGULAR'])) {
            $alertas[] = [
                'tipo' => 'critico',
                'mensagem' => "{$nome}: {$status}",
                'icone' => 'x-circle',
            ];
        } elseif (strpos($status, 'POSITIVA COM EFEITO') !== false) {
            $alertas[] = [
                'tipo' => 'atencao',
                'mensagem' => "{$nome}: Positiva com efeito de negativa",
                'icone' => 'alert-circle',
            ];
        }

        // Verificar validade proxima
        if (! empty($dados['validade'])) {
            $validade = \Carbon\Carbon::parse($dados['validade']);
            $diasRestantes = (int) now()->diffInDays($validade, false);

            if ($diasRestantes <= 0) {
                $alertas[] = [
                    'tipo' => 'critico',
                    'mensagem' => "{$nome}: Vencida",
                    'icone' => 'clock',
                ];
            } elseif ($diasRestantes <= 7) {
                $alertas[] = [
                    'tipo' => 'atencao',
                    'mensagem' => "{$nome}: Vence em {$diasRestantes} dias",
                    'icone' => 'clock',
                ];
            }
        }
    }

    /**
     * Adiciona um alerta por fonte que o plano pediu mas não retornou (`_fontes_erro`).
     *
     * Distinto de irregularidade: a certidão não foi emitida porque a fonte externa falhou
     * (timeout/recusa/retry esgotado). Como a consulta é paga, o usuário precisa saber que
     * aquele dado ficou em aberto.
     */
    private function addAlertasFontesErro(array &$alertas, array $fontesErro): void
    {
        $rotulos = [
            'cnd_federal' => 'CND Federal',
            'cnd_estadual' => 'CND Estadual',
            'cnd_municipal' => 'CND Municipal',
            'crf_fgts' => 'CRF (FGTS)',
            'cndt' => 'CNDT',
            'sintegra' => 'SINTEGRA',
        ];

        foreach ($fontesErro as $chave => $erro) {
            if (! is_string($chave)) {
                continue;
            }

            $nome = $rotulos[$chave] ?? strtoupper(str_replace('_', ' ', $chave));
            $tentativas = is_array($erro) ? ($erro['tentativas'] ?? null) : null;
            $sufixo = $tentativas ? " após {$tentativas} tentativa(s)" : '';

            $alertas[] = [
                'tipo' => 'atencao',
                'mensagem' => "{$nome}: consulta não foi concluída{$sufixo} — a fonte oficial não respondeu. Refaça a consulta desta fonte.",
                'icone' => 'alert-circle',
            ];
        }
    }

    /**
     * Verifica se e requisicao AJAX.
     */
    /**
     * Cadastra/substitui o certificado digital A1 da empresa própria.
     */
    public function salvarCertificado(Request $request)
    {
        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $request->validate([
            'certificado' => 'required|file|max:64', // KB — certificados A1 são pequenos
            'senha' => 'required|string',
        ]);

        $empresa = Auth::user()->empresaPropria();
        if (! $empresa) {
            return back()->withErrors(['certificado' => 'Configure sua empresa própria antes de cadastrar o certificado.']);
        }

        $ext = strtolower((string) $request->file('certificado')->getClientOriginalExtension());
        if (! in_array($ext, ['pfx', 'p12'], true)) {
            return back()->withErrors(['certificado' => 'Envie um arquivo .pfx ou .p12 (certificado A1). A3 (token/cartão) não é suportado por upload.']);
        }

        try {
            app(CertificadoDigitalService::class)->validarEArmazenar(
                $request->file('certificado'),
                (string) $request->input('senha'),
                $empresa
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', 'Certificado digital cadastrado com sucesso.');
    }

    /**
     * Remove o certificado digital da empresa própria.
     */
    public function removerCertificado(Request $request)
    {
        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $empresa = Auth::user()->empresaPropria();
        if ($empresa) {
            app(CertificadoDigitalService::class)->remover($empresa);
        }

        return back()->with('status', 'Certificado removido.');
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
