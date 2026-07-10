<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Concerns\RespondeAjax;
use App\Http\Controllers\Controller;
use App\Models\AdminActionLog;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Painel admin de edição dos planos de assinatura (subscription_plans) — somente operador
 * FiscalDock (middleware EnsureAdmin na rota). Edita limites, capabilities e preço direto no
 * catálogo do backend, que é a fonte de verdade dos entitlements (EntitlementService lê daqui).
 *
 * Preço é editável, mas o `mp_preapproval_plan_id_*` (id do plano de cobrança no Mercado Pago)
 * NÃO é sincronizado automaticamente — mudar o valor aqui só afeta telas/gating; a cobrança
 * recorrente continua pelo valor do preapproval_plan do MP. A UI avisa isso.
 */
class AdminPlanosController extends Controller
{
    use RespondeAjax;

    private const AUTH_LAYOUT_VIEW = 'autenticado.layouts.app';

    /** Profundidades válidas do auto-monitor (rank cadastral → due_diligence). */
    private const PROFUNDIDADES = ['cadastral', 'validacao', 'licitacao', 'compliance', 'due_diligence'];

    /** Formatos de export possíveis (capability `export`). */
    private const EXPORT_FORMATS = ['csv', 'excel', 'api'];

    public function index(Request $request)
    {
        $view = 'autenticado.admin.planos';
        $data = ['planos' => SubscriptionPlan::orderBy('ordem')->get()];

        if ($this->isAjaxRequest($request)) {
            return response(view($view, $data)->render())->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge(['initialView' => $view], $data));
    }

    public function edit(Request $request, int $id)
    {
        $plano = SubscriptionPlan::findOrFail($id);
        $view = 'autenticado.admin.planos-editar';
        $data = [
            'plano' => $plano,
            'profundidades' => self::PROFUNDIDADES,
            'exportFormats' => self::EXPORT_FORMATS,
        ];

        if ($this->isAjaxRequest($request)) {
            return response(view($view, $data)->render())->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge(['initialView' => $view], $data));
    }

    public function update(Request $request, int $id)
    {
        $plano = SubscriptionPlan::findOrFail($id);

        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:120'],
            // Preços em R$ (padrão de exibição do projeto) — convertidos pra centavos ao salvar.
            'preco_mensal_reais' => ['required', 'numeric', 'min:0'],
            'preco_anual_reais' => ['required', 'numeric', 'min:0'],
            'creditos_inclusos' => ['required', 'integer', 'min:0'],
            'faixa_slug' => ['required', 'string', 'max:20'],
            'limite_clientes' => ['nullable', 'integer', 'min:0'],
            'limite_cnpjs_monitorados' => ['nullable', 'integer', 'min:0'],
            'frequencia_padrao_dias' => ['required', 'integer', 'min:1'],
            'profundidade_auto_monitor' => ['required', 'in:'.implode(',', self::PROFUNDIDADES)],
            'assentos_inclusos' => ['required', 'integer', 'min:1'],
            'rollover_cap_multiplicador' => ['required', 'numeric', 'min:0'],
            'ordem' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'mp_preapproval_plan_id_mensal' => ['nullable', 'string', 'max:120'],
            'mp_preapproval_plan_id_anual' => ['nullable', 'string', 'max:120'],
            // Capabilities
            'cap_bi' => ['required', 'in:basico,completo'],
            'cap_export' => ['nullable', 'array'],
            'cap_export.*' => ['in:'.implode(',', self::EXPORT_FORMATS)],
            'cap_pdf_executivo' => ['nullable', 'boolean'],
            'cap_clearance_lote' => ['nullable', 'boolean'],
            'cap_clearance_full' => ['nullable', 'boolean'],
            'cap_score_historico' => ['nullable', 'boolean'],
            'cap_retencao_meses' => ['nullable', 'integer', 'min:1'],
            'cap_frequencia_minima_dias' => ['required', 'integer', 'min:1'],
        ]);

        $anterior = $plano->only([
            'nome', 'preco_mensal_centavos', 'preco_anual_centavos', 'creditos_inclusos',
            'faixa_slug', 'limite_clientes', 'limite_cnpjs_monitorados', 'frequencia_padrao_dias',
            'profundidade_auto_monitor', 'assentos_inclusos', 'rollover_cap_multiplicador',
            'ordem', 'is_active', 'capabilities', 'mp_preapproval_plan_id_mensal', 'mp_preapproval_plan_id_anual',
        ]);

        // Export: lista sem duplicatas, na ordem canônica.
        $export = array_values(array_intersect(self::EXPORT_FORMATS, $dados['cap_export'] ?? []));

        $capabilities = [
            'bi' => $dados['cap_bi'],
            'export' => $export,
            'pdf_executivo' => (bool) ($dados['cap_pdf_executivo'] ?? false),
            'clearance_lote' => (bool) ($dados['cap_clearance_lote'] ?? false),
            'clearance_full' => (bool) ($dados['cap_clearance_full'] ?? false),
            'score_historico' => (bool) ($dados['cap_score_historico'] ?? false),
            // retenção: vazio = ilimitado (null), espelha o seeder.
            'retencao_meses' => $dados['cap_retencao_meses'] ?? null,
            'frequencia_minima_dias' => (int) $dados['cap_frequencia_minima_dias'],
        ];

        $plano->update([
            'nome' => $dados['nome'],
            'preco_mensal_centavos' => (int) round($dados['preco_mensal_reais'] * 100),
            'preco_anual_centavos' => (int) round($dados['preco_anual_reais'] * 100),
            'creditos_inclusos' => (int) $dados['creditos_inclusos'],
            'faixa_slug' => $dados['faixa_slug'],
            // vazio = ilimitado (null)
            'limite_clientes' => $dados['limite_clientes'] ?? null,
            'limite_cnpjs_monitorados' => $dados['limite_cnpjs_monitorados'] ?? null,
            'frequencia_padrao_dias' => (int) $dados['frequencia_padrao_dias'],
            'profundidade_auto_monitor' => $dados['profundidade_auto_monitor'],
            'assentos_inclusos' => (int) $dados['assentos_inclusos'],
            'rollover_cap_multiplicador' => (float) $dados['rollover_cap_multiplicador'],
            'ordem' => (int) $dados['ordem'],
            'is_active' => (bool) ($dados['is_active'] ?? false),
            'capabilities' => $capabilities,
            'mp_preapproval_plan_id_mensal' => $dados['mp_preapproval_plan_id_mensal'] ?? null,
            'mp_preapproval_plan_id_anual' => $dados['mp_preapproval_plan_id_anual'] ?? null,
        ]);

        AdminActionLog::create([
            'admin_user_id' => (int) Auth::id(),
            'target_user_id' => null,
            'acao' => 'plano_editar',
            'motivo' => "Edição do plano {$plano->codigo}",
            'detalhe' => ['plano_id' => $plano->id, 'antes' => $anterior, 'depois' => $plano->fresh()->only(array_keys($anterior))],
            'ip' => $request->ip(),
            'created_at' => now(),
        ]);

        return redirect()->route('app.admin.planos.index')->with('status', "Plano \"{$plano->nome}\" atualizado.");
    }
}
