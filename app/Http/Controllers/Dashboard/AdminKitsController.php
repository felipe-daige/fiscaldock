<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Concerns\RespondeAjax;
use App\Http\Controllers\Controller;
use App\Models\AdminActionLog;
use App\Models\ConsultaKit;
use App\Services\Advocacia\CatalogoFontesAvulsas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Painel admin dos kits da consulta avulsa por fontes (consulta_kits) — somente operador
 * FiscalDock (EnsureAdmin na rota). Kit é preset de seleção com desconto, não plano: editar
 * aqui muda apresentação e precificação da tela /app/consulta/painel na hora.
 *
 * Padrão espelha AdminPlanosController. Spec: docs/advocacia/consultas-certidoes.md (fase 3).
 */
class AdminKitsController extends Controller
{
    use RespondeAjax;

    private const AUTH_LAYOUT_VIEW = 'autenticado.layouts.app';

    public function __construct(private CatalogoFontesAvulsas $catalogo) {}

    public function index(Request $request)
    {
        $view = 'autenticado.admin.kits';
        $data = [
            'kits' => ConsultaKit::globais()->orderBy('ordem')->get(),
            'catalogoPrecos' => $this->catalogo,
        ];

        if ($this->isAjaxRequest($request)) {
            return response(view($view, $data)->render())->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge(['initialView' => $view], $data));
    }

    public function edit(Request $request, ?int $id = null)
    {
        $view = 'autenticado.admin.kits-editar';
        $kit = $id !== null ? ConsultaKit::globais()->findOrFail($id) : null;
        $data = [
            // `globais()`: o admin cura a vitrine, nunca o preset PESSOAL de um usuário (user_id
            // preenchido) — sem o escopo, um id qualquer abriria o "meu plano" de um cliente.
            'kit' => $kit,
            'gruposFontes' => $this->catalogo->grupos(),
            // Picker de segmentação (publico='selecionados'). Base é pequena — lista completa.
            'usuarios' => \App\Models\User::orderBy('name')->get(['id', 'name', 'email']),
            'usuariosSelecionados' => $kit !== null ? $kit->usuarios()->pluck('users.id')->all() : [],
        ];

        if ($this->isAjaxRequest($request)) {
            return response(view($view, $data)->render())->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge(['initialView' => $view], $data));
    }

    public function save(Request $request, ?int $id = null)
    {
        $kit = $id !== null ? ConsultaKit::globais()->findOrFail($id) : new ConsultaKit;

        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:120'],
            'descricao' => ['nullable', 'string', 'max:255'],
            'fontes' => ['required', 'array', 'min:1'],
            'fontes.*' => ['string', 'in:'.implode(',', $this->catalogo->chavesDisponiveis())],
            'desconto_percentual' => ['required', 'numeric', 'min:0', 'max:100'],
            // Preço fixo do kit inteiro (R$). Vazio = precifica pela soma das fontes com desconto%.
            'preco_fixo' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'publico' => ['required', 'in:todos,selecionados'],
            'usuarios' => ['array'],
            'usuarios.*' => ['integer', 'exists:users,id'],
            'ordem' => ['required', 'integer', 'min:0'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        // publico='selecionados' sem nenhum usuário esconderia o kit de todos — provável engano.
        if ($dados['publico'] === 'selecionados' && empty($dados['usuarios'])) {
            return back()->withInput()->withErrors([
                'usuarios' => 'Selecione ao menos um usuário ou marque "Todos os usuários".',
            ]);
        }

        $precoFixo = ($dados['preco_fixo'] ?? '') === '' || $dados['preco_fixo'] === null
            ? null
            : round((float) $dados['preco_fixo'], 2);

        $anterior = $kit->exists
            ? $kit->only(['nome', 'descricao', 'fontes', 'desconto_percentual', 'preco_fixo', 'publico', 'ativo', 'ordem'])
            : null;

        $kit->fill([
            'nome' => $dados['nome'],
            'descricao' => $dados['descricao'] ?? null,
            'fontes' => array_values(array_unique($dados['fontes'])),
            'desconto_percentual' => round((float) $dados['desconto_percentual'], 2),
            'preco_fixo' => $precoFixo,
            'publico' => $dados['publico'],
            'ativo' => (bool) ($dados['ativo'] ?? false),
            'ordem' => (int) $dados['ordem'],
        ]);

        // Piso de preço: o total do kit (fixo OU com desconto%) não pode ficar abaixo do custo do
        // provedor das fontes — mesma regra que o gate por fonte do admin aplica (fontesAbaixoDoCusto).
        // Pega tanto preco_fixo irrisório quanto desconto=100. Fontes de custo zero podem dar kit R$0.
        $resumo = $this->catalogo->resumoKit($kit);
        $custo = $this->catalogo->custoSelecao($resumo['fontes']);
        if ($resumo['total'] < $custo) {
            $campo = $precoFixo !== null ? 'preco_fixo' : 'desconto_percentual';

            return back()->withInput()->withErrors([
                $campo => 'Preço do kit (R$ '.number_format($resumo['total'], 2, ',', '.')
                    .') abaixo do custo do provedor (R$ '.number_format($custo, 2, ',', '.').').',
            ]);
        }

        if (! $kit->exists) {
            $kit->slug = $this->slugDisponivel($dados['nome']);
        }
        $kit->save();

        // Pivot de segmentação: 'todos' zera a lista (kit vale pra todos), 'selecionados' grava a seleção.
        $kit->usuarios()->sync($dados['publico'] === 'selecionados' ? ($dados['usuarios'] ?? []) : []);

        AdminActionLog::create([
            'admin_user_id' => (int) Auth::id(),
            'target_user_id' => null,
            'acao' => $anterior === null ? 'kit_criar' : 'kit_editar',
            'motivo' => ($anterior === null ? 'Criação' : 'Edição')." do kit {$kit->slug}",
            'detalhe' => ['kit_id' => $kit->id, 'antes' => $anterior, 'depois' => $kit->only(['nome', 'descricao', 'fontes', 'desconto_percentual', 'preco_fixo', 'publico', 'ativo', 'ordem']), 'usuarios' => $dados['publico'] === 'selecionados' ? ($dados['usuarios'] ?? []) : 'todos'],
            'ip' => $request->ip(),
            'created_at' => now(),
        ]);

        return redirect()->route('app.admin.kits.index')->with('status', "Kit \"{$kit->nome}\" salvo.");
    }

    public function destroy(Request $request, int $id)
    {
        $kit = ConsultaKit::globais()->findOrFail($id);

        // Plano DO SISTEMA (vitrine oficial do contador) não se exclui pelo CRUD: some na hora da
        // tela de consulta e o ConsultaKitSeeder usa firstOrCreate por slug, então só voltaria
        // rodando seeder em produção. Para tirar da vitrine, desative (`ativo=false`) na edição.
        if ($kit->sistema) {
            return redirect()->route('app.admin.kits.index')
                ->with('error', "\"{$kit->nome}\" é um plano do sistema e não pode ser excluído — desative-o na edição para tirá-lo da vitrine.");
        }

        $kit->delete();

        AdminActionLog::create([
            'admin_user_id' => (int) Auth::id(),
            'target_user_id' => null,
            'acao' => 'kit_excluir',
            'motivo' => "Exclusão do kit {$kit->slug}",
            'detalhe' => ['kit_id' => $id, 'antes' => $kit->only(['nome', 'slug', 'fontes', 'desconto_percentual'])],
            'ip' => $request->ip(),
            'created_at' => now(),
        ]);

        return redirect()->route('app.admin.kits.index')->with('status', "Kit \"{$kit->nome}\" excluído.");
    }

    private function slugDisponivel(string $nome): string
    {
        $base = Str::slug(Str::limit($nome, 50, '')) ?: 'kit';
        $slug = $base;
        for ($i = 2; ConsultaKit::where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }
}
