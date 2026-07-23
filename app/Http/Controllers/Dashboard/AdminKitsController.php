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
        $data = [
            'kit' => $id !== null ? ConsultaKit::findOrFail($id) : null,
            'gruposFontes' => $this->catalogo->grupos(),
        ];

        if ($this->isAjaxRequest($request)) {
            return response(view($view, $data)->render())->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge(['initialView' => $view], $data));
    }

    public function save(Request $request, ?int $id = null)
    {
        $kit = $id !== null ? ConsultaKit::findOrFail($id) : new ConsultaKit;

        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:120'],
            'descricao' => ['nullable', 'string', 'max:255'],
            'fontes' => ['required', 'array', 'min:1'],
            'fontes.*' => ['string', 'in:'.implode(',', $this->catalogo->chavesDisponiveis())],
            'desconto_percentual' => ['required', 'numeric', 'min:0', 'max:100'],
            'ordem' => ['required', 'integer', 'min:0'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $anterior = $kit->exists
            ? $kit->only(['nome', 'descricao', 'fontes', 'desconto_percentual', 'ativo', 'ordem'])
            : null;

        $kit->fill([
            'nome' => $dados['nome'],
            'descricao' => $dados['descricao'] ?? null,
            'fontes' => array_values(array_unique($dados['fontes'])),
            'desconto_percentual' => round((float) $dados['desconto_percentual'], 2),
            'ativo' => (bool) ($dados['ativo'] ?? false),
            'ordem' => (int) $dados['ordem'],
        ]);
        if (! $kit->exists) {
            $kit->slug = $this->slugDisponivel($dados['nome']);
        }
        $kit->save();

        AdminActionLog::create([
            'admin_user_id' => (int) Auth::id(),
            'target_user_id' => null,
            'acao' => $anterior === null ? 'kit_criar' : 'kit_editar',
            'motivo' => ($anterior === null ? 'Criação' : 'Edição')." do kit {$kit->slug}",
            'detalhe' => ['kit_id' => $kit->id, 'antes' => $anterior, 'depois' => $kit->only(['nome', 'descricao', 'fontes', 'desconto_percentual', 'ativo', 'ordem'])],
            'ip' => $request->ip(),
            'created_at' => now(),
        ]);

        return redirect()->route('app.admin.kits.index')->with('status', "Kit \"{$kit->nome}\" salvo.");
    }

    public function destroy(Request $request, int $id)
    {
        $kit = ConsultaKit::findOrFail($id);
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
