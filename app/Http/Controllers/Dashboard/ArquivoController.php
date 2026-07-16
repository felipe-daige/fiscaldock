<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Concerns\RespondeAjax;
use App\Http\Controllers\Controller;
use App\Services\Arquivos\ArquivoUsuarioService;
use App\Services\Entitlements\EntitlementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArquivoController extends Controller
{
    use RespondeAjax;

    private const VIEW = 'autenticado.arquivos.index';

    private const LAYOUT = 'autenticado.layouts.app';

    public function __construct(
        private ArquivoUsuarioService $arquivos,
        private EntitlementService $entitlements,
        private \App\Services\Subscription\AddonService $addons = new \App\Services\Subscription\AddonService,
    ) {}

    /** Números do add-on de espaço pra tela/modal (preço, pacotes atuais, pró-rata, saldo). */
    private function espacoAddonInfo(\App\Models\User $user): array
    {
        // Só o dono contrata add-on — membro não vê o card; evita queries de assinatura/saldo à toa.
        if (! app(\App\Support\AccountContext::class)->isOwner()) {
            return ['is_owner' => false, 'tem_assinatura' => false, 'pacotes' => 0, 'pacote_mb' => $this->addons->pacoteEspacoMb(), 'preco_mensal' => 0.0, 'fracao' => 1.0, 'saldo' => 0.0];
        }

        $owner = $user->accountOwner();
        $sub = $owner->subscription()
            ->where('status', \App\Models\AccountSubscription::STATUS_ATIVA)
            ->first();

        return [
            'is_owner' => true,
            'tem_assinatura' => (bool) $sub,
            'pacotes' => $sub ? (int) $sub->espaco_extra_pacotes : 0,
            'pacote_mb' => $this->addons->pacoteEspacoMb(),
            'preco_mensal' => $this->addons->precoPacoteEspacoReais(),
            'fracao' => $sub ? $this->addons->fracaoRestante($sub) : 1.0,
            'saldo' => app(\App\Services\SaldoService::class)->getBalance($owner),
        ];
    }

    /**
     * Contratar/reduzir pacotes de espaço adicional (add-on recorrente via saldo). Owner-only.
     * Sucesso → redireciona pra billing (/app/saldo, débito visível no histórico).
     */
    public function espaco(Request $request): RedirectResponse
    {
        $context = app(\App\Support\AccountContext::class);
        abort_unless($context->isOwner(), 403);

        $data = $request->validate([
            'espaco_extra_pacotes' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        try {
            $this->addons->definirEspacoExtraPacotes($request->user(), (int) $data['espaco_extra_pacotes']);
        } catch (\RuntimeException $e) {
            return redirect()->route('app.arquivos.index')->withErrors(['espaco_extra_pacotes' => $e->getMessage()]);
        }

        return redirect()->route('app.saldo')->with('success', 'Espaço adicional atualizado. O ajuste aparece no histórico de saldo.');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $todos = $this->arquivos->listar($user);
        $resumo = $this->arquivos->resumo($user, $todos);
        $busca = trim((string) $request->query('q', ''));
        $origem = (string) $request->query('origem', 'todos');
        if (! in_array($origem, ['todos', 'upload', 'comprovante', 'importacao'], true)) {
            $origem = 'todos';
        }

        $filtrados = $todos
            ->when($origem !== 'todos', fn ($itens) => $itens->where('origem', $origem))
            ->when($busca !== '', function ($itens) use ($busca) {
                $termo = Str::lower($busca);

                return $itens->filter(fn (array $arquivo) => Str::contains(
                    Str::lower(implode(' ', array_filter([
                        $arquivo['nome'],
                        $arquivo['extensao'],
                        $arquivo['origem_label'],
                        $arquivo['dono_documento'],
                        $arquivo['dono_documento'] !== null ? preg_replace('/\D/', '', $arquivo['dono_documento']) : null,
                        $arquivo['dono_nome'],
                    ]))),
                    $termo,
                ));
            })
            ->values();

        $porPagina = 20;
        $pagina = max(1, (int) $request->query('page', 1));
        $paginados = new LengthAwarePaginator(
            $filtrados->forPage($pagina, $porPagina)->values(),
            $filtrados->count(),
            $porPagina,
            $pagina,
            ['path' => $request->url(), 'query' => $request->query()],
        );
        $plano = $this->entitlements->planFor($user);
        $data = [
            'arquivos' => $paginados,
            'resumoArquivos' => $resumo,
            'buscaArquivos' => $busca,
            'origemArquivos' => $origem,
            'planoArquivos' => $plano,
            'uploadMaximoMb' => (int) config('arquivos.upload_maximo_mb', 50),
            'uploadMaximoPorLote' => (int) config('arquivos.upload_maximo_por_lote', 10),
            'extensoesArquivos' => (array) config('arquivos.extensoes_permitidas', []),
            'espacoAddon' => $this->espacoAddonInfo($user),
        ];

        if ($this->isAjaxRequest($request)) {
            return response(view(self::VIEW, $data)->render())->header('Content-Type', 'text/html');
        }

        return view(self::LAYOUT, array_merge(['initialView' => self::VIEW], $data));
    }

    public function store(Request $request): RedirectResponse
    {
        $maximoMb = max(1, (int) config('arquivos.upload_maximo_mb', 50));
        $maximoLote = max(1, (int) config('arquivos.upload_maximo_por_lote', 10));
        $extensoes = implode(',', (array) config('arquivos.extensoes_permitidas', []));

        $validated = $request->validate([
            'arquivos' => ['required', 'array', 'min:1', "max:{$maximoLote}"],
            'arquivos.*' => ['required', 'file', 'max:'.($maximoMb * 1024), "mimes:{$extensoes}"],
        ], [
            'arquivos.required' => 'Selecione ao menos um arquivo.',
            'arquivos.max' => "Envie no máximo {$maximoLote} arquivos por vez.",
            'arquivos.*.max' => "Cada arquivo pode ter no máximo {$maximoMb} MB.",
            'arquivos.*.mimes' => 'Formato não permitido. Use PDF, XML, TXT, CSV, XLS, XLSX, ZIP, JPG ou PNG.',
        ]);

        $uploads = array_values(array_filter($validated['arquivos']));
        if ($uploads === []) {
            throw ValidationException::withMessages(['arquivos' => 'Selecione ao menos um arquivo válido.']);
        }

        $this->arquivos->armazenar($request->user(), $uploads);

        return redirect()->route('app.arquivos.index')->with(
            'success',
            count($uploads) === 1 ? 'Arquivo enviado com sucesso.' : count($uploads).' arquivos enviados com sucesso.',
        );
    }

    public function download(Request $request, string $arquivo): StreamedResponse
    {
        $item = $this->arquivos->localizar($request->user(), $arquivo);
        abort_if($item === null, 404, 'Arquivo não encontrado.');
        abort_unless($item['baixavel'], 404, 'O arquivo original desta importação não é retido pelo FiscalDock.');

        if ($item['origem'] === 'importacao') {
            return $this->arquivos->downloadImportacaoEfd($request->user(), $item);
        }

        return \Illuminate\Support\Facades\Storage::disk('local')->download(
            $item['path'],
            $item['nome_download'],
        );
    }

    public function preview(Request $request, string $arquivo): StreamedResponse
    {
        $item = $this->arquivos->localizar($request->user(), $arquivo);
        abort_if($item === null, 404, 'Arquivo não encontrado.');
        abort_unless($item['previewavel'], 404, 'Este arquivo não possui visualização.');

        $contentType = $this->arquivos->previewContentType($item['extensao']);
        abort_if($contentType === null, 404, 'Este formato não possui visualização.');

        $headers = [
            'Content-Type' => $contentType,
            'X-Content-Type-Options' => 'nosniff',
        ];
        if (str_starts_with($contentType, 'text/html')) {
            // Comprovante HTML é conteúdo de terceiro (InfoSimples) — origem
            // opaca via CSP sandbox: sem script, sem cookies, sem same-origin.
            $headers['Content-Security-Policy'] = 'sandbox';
        }

        return \Illuminate\Support\Facades\Storage::disk('local')->response(
            $item['path'],
            $item['nome_download'],
            $headers,
        );
    }

    public function destroy(Request $request, string $arquivo): RedirectResponse
    {
        $item = $this->arquivos->localizar($request->user(), $arquivo);
        abort_if($item === null, 404, 'Arquivo não encontrado.');
        abort_unless($item['pode_excluir'], 403, 'Comprovantes do sistema não podem ser excluídos por esta tela.');

        if (! $this->arquivos->excluirUpload($request->user(), $arquivo)) {
            return redirect()->route('app.arquivos.index')->with('error', 'Não foi possível excluir o arquivo.');
        }

        return redirect()->route('app.arquivos.index')->with('success', 'Arquivo excluído com sucesso.');
    }
}
