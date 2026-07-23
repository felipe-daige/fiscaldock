<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Concerns\RespondeAjax;
use App\Http\Controllers\Controller;
use App\Models\AdminActionLog;
use App\Models\FontePreco;
use App\Services\Advocacia\CatalogoFontesAvulsas;
use App\Services\Consultas\FonteRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Painel admin de PREÇO POR FONTE (fonte_precos) — somente operador FiscalDock (EnsureAdmin).
 *
 * No modelo à la carte (migração 2026-07-22) não há mais escada fixa: cada consulta tem preço
 * próprio (R$ 1,00 default). Aqui o admin ajusta o preço de venda e liga/desliga cada fonte
 * comercialmente. Fonte única do preço: CatalogoFontesAvulsas::precoDe (DB → config → default).
 *
 * Padrão espelha AdminKitsController (form único, upsert em lote).
 */
class AdminFontesController extends Controller
{
    use RespondeAjax;

    private const AUTH_LAYOUT_VIEW = 'autenticado.layouts.app';

    public function __construct(
        private CatalogoFontesAvulsas $catalogo,
        private FonteRegistry $registry,
    ) {}

    public function index(Request $request)
    {
        $view = 'autenticado.admin.fontes';
        $data = ['grupos' => $this->linhas()];

        if ($this->isAjaxRequest($request)) {
            return response(view($view, $data)->render())->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge(['initialView' => $view], $data));
    }

    public function save(Request $request)
    {
        $chavesValidas = $this->chavesCatalogo();

        $dados = $request->validate([
            'precos' => ['array'],
            'precos.*' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'ativos' => ['array'],
        ]);

        $precos = $dados['precos'] ?? [];
        $ativos = $dados['ativos'] ?? [];

        if ($abaixoDoCusto = $this->fontesAbaixoDoCusto($precos, $chavesValidas)) {
            return back()->withInput()->withErrors([
                'precos' => 'Preço abaixo do custo do provedor em: '.implode(', ', $abaixoDoCusto)
                    .'. Ajuste o preço ou desative a consulta.',
            ]);
        }

        $existentes = FontePreco::all()->keyBy('chave');
        $antes = $existentes->map(fn ($f) => ['preco' => (float) $f->preco, 'ativo' => (bool) $f->ativo])->all();

        foreach ($chavesValidas as $chave) {
            $ativo = array_key_exists($chave, $ativos);
            $temPreco = isset($precos[$chave]) && $precos[$chave] !== '';
            $linha = $existentes->get($chave);

            // Default puro (sem preço custom, à venda, sem linha existente) → não materializa.
            if (! $temPreco && $ativo && $linha === null) {
                continue;
            }

            // Preço: custom se informado; senão preserva o override existente; senão o preço
            // efetivo atual (default do config) — para não "zerar" ao só desativar.
            $preco = $temPreco
                ? round((float) $precos[$chave], 2)
                : ($linha !== null ? (float) $linha->preco : $this->catalogo->precoDe($chave));

            FontePreco::updateOrCreate(['chave' => $chave], ['preco' => $preco, 'ativo' => $ativo]);
        }

        AdminActionLog::create([
            'admin_user_id' => (int) Auth::id(),
            'target_user_id' => null,
            'acao' => 'fonte_precos_editar',
            'motivo' => 'Edição de preços por fonte',
            'detalhe' => [
                'antes' => $antes,
                'depois' => FontePreco::all()->keyBy('chave')
                    ->map(fn ($f) => ['preco' => (float) $f->preco, 'ativo' => (bool) $f->ativo])->all(),
            ],
            'ip' => $request->ip(),
            'created_at' => now(),
        ]);

        return redirect()->route('app.admin.fontes.index')->with('status', 'Preços das consultas salvos.');
    }

    /**
     * Fontes cujo preço informado ficaria ABAIXO do custo do provedor, já rotuladas pro erro.
     *
     * Preço < custo é prejuízo por consulta; a R$ 0,00 é pior, porque o gate de saldo some
     * (`hasEnough($user, 0)` é sempre true) e a fonte paga vira ilimitada e gratuita. Fonte de
     * custo zero (cadastro/minhareceita) segue podendo ser R$ 0,00.
     *
     * @param  array<string, mixed>  $precos  preço informado por chave (string do form)
     * @param  list<string>  $chavesValidas
     * @return list<string>
     */
    private function fontesAbaixoDoCusto(array $precos, array $chavesValidas): array
    {
        $out = [];
        foreach ($chavesValidas as $chave) {
            if (! isset($precos[$chave]) || $precos[$chave] === '') {
                continue; // sem preço informado → mantém o vigente, nada a validar
            }

            $custo = $this->catalogo->custoDe($chave); // custo do provedor: fonte única no catálogo
            if ($custo > 0 && round((float) $precos[$chave], 2) < $custo) {
                $out[] = (string) config("consultas.fonte_nome.{$chave}", $chave)
                    .' (custo R$ '.number_format($custo, 2, ',', '.').')';
            }
        }

        return $out;
    }

    /** Chaves de fonte do catálogo comercial (todos os grupos de advocacia.grupos). */
    private function chavesCatalogo(): array
    {
        $chaves = [];
        foreach ((array) config('advocacia.grupos', []) as $grupo) {
            foreach ((array) ($grupo['fontes'] ?? []) as $chave) {
                $chaves[$chave] = true;
            }
        }

        return array_keys($chaves);
    }

    /**
     * Linhas do admin agrupadas: cada fonte com preço efetivo, override, ativo e status de origem.
     *
     * @return array<string, array{label: string, fontes: list<array<string, mixed>>}>
     */
    private function linhas(): array
    {
        $overrides = FontePreco::all()->keyBy('chave');
        $pausadas = (array) config('consultas.fontes_pausadas', []);

        $out = [];
        foreach ((array) config('advocacia.grupos', []) as $chaveGrupo => $grupo) {
            $fontes = [];
            foreach ((array) ($grupo['fontes'] ?? []) as $chave) {
                $fonte = $this->registry->get($chave);
                $override = $overrides->get($chave);
                $fontes[] = [
                    'chave' => $chave,
                    'nome' => (string) config("consultas.fonte_nome.{$chave}", $chave),
                    'preco' => $this->catalogo->precoDe($chave),
                    'tem_override' => $override !== null,
                    'ativo' => $override !== null ? (bool) $override->ativo : true,
                    'registrada' => $fonte !== null,
                    'pausada' => in_array($chave, $pausadas, true),
                ];
            }
            $out[$chaveGrupo] = ['label' => (string) ($grupo['label'] ?? $chaveGrupo), 'fontes' => $fontes];
        }

        return $out;
    }
}
