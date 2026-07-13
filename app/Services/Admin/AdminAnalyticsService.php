<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;

/**
 * Métricas agregadas do negócio para o console admin (read-only, escopo global).
 * Tudo derivado das tabelas existentes; ciente de período (?periodo=30|90|365|tudo).
 */
class AdminAnalyticsService
{
    /**
     * @param  array{periodo?:string}  $filtros
     */
    public function resumo(array $filtros = []): array
    {
        $periodo = in_array($filtros['periodo'] ?? '', ['30', '90', '365', 'tudo'], true) ? $filtros['periodo'] : '30';
        $desde = $periodo === 'tudo' ? null : now()->subDays((int) $periodo)->toDateTimeString();
        $ativosDesde = now()->subDays(30)->timestamp;

        $mrrCentavos = DB::table('account_subscriptions as s')
            ->join('subscription_plans as p', 'p.id', '=', 's.subscription_plan_id')
            ->where('s.status', 'ativa')
            ->selectRaw("coalesce(sum(case when s.ciclo = 'anual' then p.preco_anual_centavos / 12.0 else p.preco_mensal_centavos end), 0) as mrr")
            ->value('mrr');

        $trialsUsados = DB::table('users')->where('trial_used', true)->count();
        $trialsConvertidos = DB::table('users')->where('trial_used', true)
            ->whereExists(fn ($q) => $q->from('credit_transactions')
                ->whereColumn('credit_transactions.user_id', 'users.id')
                ->where('type', 'purchase')->where('amount', '>', 0))
            ->count();

        return [
            'periodo' => $periodo,
            'crescimento' => [
                'total_usuarios' => DB::table('users')->count(),
                'novos' => DB::table('users')->when($desde, fn ($q) => $q->where('created_at', '>=', $desde))->count(),
                'ativos' => DB::table('sessions')->whereNotNull('user_id')->where('last_activity', '>=', $ativosDesde)->distinct()->count('user_id'),
            ],
            'trial' => [
                'total' => $trialsUsados,
                'em_curso' => DB::table('users')->where('trial_used', true)->where('trial_expires_at', '>=', now())->count(),
                'expirados' => DB::table('users')->where('trial_used', true)->where('trial_expires_at', '<', now())->count(),
                'convertidos' => $trialsConvertidos,
                'taxa_conversao' => $trialsUsados > 0 ? round(($trialsConvertidos / $trialsUsados) * 100, 1) : 0.0,
            ],
            'receita' => [
                'aprovada_total' => (float) DB::table('mercado_pago_payments')->where('status', 'approved')->sum('valor'),
                'aprovada_periodo' => (float) DB::table('mercado_pago_payments')->where('status', 'approved')
                    ->when($desde, fn ($q) => $q->where('created_at', '>=', $desde))->sum('valor'),
                'assinaturas_ativas' => DB::table('account_subscriptions')->where('status', 'ativa')->count(),
                'mrr' => round((float) $mrrCentavos / 100, 2),
                'recargas_ativas' => DB::table('recarga_automaticas')->where('status', 'ativa')->count(),
                'ultima_compra_em' => DB::table('mercado_pago_payments')->where('status', 'approved')->max('created_at'),
            ],
            'creditos' => [
                'vendidos' => (float) DB::table('credit_transactions')->where('type', 'purchase')->where('amount', '>', 0)->sum('amount'),
                'consumidos' => abs((float) DB::table('credit_transactions')->where('amount', '<', 0)->sum('amount')),
                // trial_credits_remaining é subconjunto de credits (grantTrial soma no credits) — não somar
                'saldo_base' => (float) DB::table('users')->sum('credits'),
            ],
            'uso' => [
                'consultas' => DB::table('consulta_lotes')->when($desde, fn ($q) => $q->where('created_at', '>=', $desde))->count(),
                'importacoes' => DB::table('efd_importacoes')->when($desde, fn ($q) => $q->where('created_at', '>=', $desde))->count()
                    + DB::table('xml_importacoes')->when($desde, fn ($q) => $q->where('created_at', '>=', $desde))->count(),
                'clearance' => DB::table('nfe_consultas')->when($desde, fn ($q) => $q->where('created_at', '>=', $desde))->count()
                    + DB::table('cte_consultas')->when($desde, fn ($q) => $q->where('created_at', '>=', $desde))->count(),
                'monitoramentos_ativos' => DB::table('monitoramento_assinaturas')->where('status', 'ativo')->count(),
            ],
        ];
    }
}
