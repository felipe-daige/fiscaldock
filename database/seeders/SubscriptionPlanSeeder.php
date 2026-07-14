<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Fonte da composição comercial (spec 2026-06-08-tiers-entitlements-cfo-design).
     * Editável no futuro painel admin; aqui é só o seed inicial.
     */
    public function run(): void
    {
        foreach ($this->definitions() as $def) {
            SubscriptionPlan::updateOrCreate(['codigo' => $def['codigo']], $def);
        }
    }

    /** @return array<int, array<string, mixed>> */
    public static function definitions(): array
    {
        return [
            [
                'codigo' => 'free', 'nome' => 'Free', 'ordem' => 1,
                'preco_mensal_centavos' => 0, 'preco_anual_centavos' => 0,
                'creditos_inclusos' => 0, 'faixa_slug' => 'base',
                'limite_clientes' => null, 'limite_cnpjs_monitorados' => 1,
                'frequencia_padrao_dias' => 30, 'profundidade_auto_monitor' => 'cadastral',
                'assentos_inclusos' => 1, 'preco_assento_extra_centavos' => 0,
                'rollover_cap_multiplicador' => 1, 'is_active' => true,
                'capabilities' => [
                    'bi' => 'basico', 'export' => [], 'pdf_executivo' => true,
                    'clearance_lote' => false, 'clearance_full' => false,
                    'score_historico' => false, 'retencao_meses' => 6,
                    'frequencia_minima_dias' => 30,
                    'armazenamento_mb' => 250,
                ],
            ],
            [
                'codigo' => 'essencial', 'nome' => 'Essencial', 'ordem' => 2,
                'preco_mensal_centavos' => 9900, 'preco_anual_centavos' => 99000,
                'creditos_inclusos' => 175, 'faixa_slug' => 'base',
                'limite_clientes' => null, 'limite_cnpjs_monitorados' => null,
                'frequencia_padrao_dias' => 1, 'profundidade_auto_monitor' => 'due_diligence',
                'assentos_inclusos' => 2, 'preco_assento_extra_centavos' => 3900,
                'rollover_cap_multiplicador' => 1, 'is_active' => true,
                'capabilities' => [
                    'bi' => 'completo', 'export' => ['csv'], 'pdf_executivo' => true,
                    'clearance_lote' => true, 'clearance_full' => false,
                    'score_historico' => false, 'retencao_meses' => null,
                    'frequencia_minima_dias' => 1,
                    'armazenamento_mb' => 2 * 1024,
                ],
            ],
            [
                'codigo' => 'profissional', 'nome' => 'Profissional', 'ordem' => 3,
                'preco_mensal_centavos' => 24900, 'preco_anual_centavos' => 249000,
                'creditos_inclusos' => 400, 'faixa_slug' => 'x',
                'limite_clientes' => null, 'limite_cnpjs_monitorados' => null,
                'frequencia_padrao_dias' => 1, 'profundidade_auto_monitor' => 'due_diligence',
                'assentos_inclusos' => 3, 'preco_assento_extra_centavos' => 3900,
                'rollover_cap_multiplicador' => 1, 'is_active' => true,
                'capabilities' => [
                    'bi' => 'completo', 'export' => ['csv', 'excel'], 'pdf_executivo' => true,
                    'clearance_lote' => true, 'clearance_full' => false,
                    'score_historico' => false, 'retencao_meses' => null,
                    'frequencia_minima_dias' => 1,
                    'armazenamento_mb' => 10 * 1024,
                ],
            ],
            [
                'codigo' => 'escritorio', 'nome' => 'Escritório', 'ordem' => 4,
                'preco_mensal_centavos' => 59900, 'preco_anual_centavos' => 599000,
                'creditos_inclusos' => 1000, 'faixa_slug' => 'y',
                'limite_clientes' => null, 'limite_cnpjs_monitorados' => null,
                'frequencia_padrao_dias' => 1, 'profundidade_auto_monitor' => 'due_diligence',
                'assentos_inclusos' => 10, 'preco_assento_extra_centavos' => 3900,
                'rollover_cap_multiplicador' => 1, 'is_active' => true,
                'capabilities' => [
                    'bi' => 'completo', 'export' => ['csv', 'excel'], 'pdf_executivo' => true,
                    'clearance_lote' => true, 'clearance_full' => false,
                    'score_historico' => false, 'retencao_meses' => null,
                    'frequencia_minima_dias' => 1,
                    'armazenamento_mb' => 50 * 1024,
                ],
            ],
            [
                'codigo' => 'enterprise', 'nome' => 'Enterprise', 'ordem' => 5,
                'preco_mensal_centavos' => 0, 'preco_anual_centavos' => 0, // sob consulta
                'creditos_inclusos' => 0, 'faixa_slug' => 'z',
                'limite_clientes' => null, 'limite_cnpjs_monitorados' => null,
                'frequencia_padrao_dias' => 1, 'profundidade_auto_monitor' => 'due_diligence',
                'assentos_inclusos' => 9999, 'preco_assento_extra_centavos' => 0,
                'rollover_cap_multiplicador' => 1, 'is_active' => false,
                'capabilities' => [
                    'bi' => 'completo', 'export' => ['csv', 'excel'], 'pdf_executivo' => true,
                    'clearance_lote' => true, 'clearance_full' => false,
                    'score_historico' => false, 'retencao_meses' => null,
                    'frequencia_minima_dias' => 1,
                    'armazenamento_mb' => 200 * 1024,
                ],
            ],
        ];
    }
}
