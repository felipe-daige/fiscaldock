<?php

namespace Database\Seeders;

use App\Models\MonitoramentoPlano;
use Illuminate\Database\Seeder;

class MonitoramentoPlanoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $planos = [
            [
                'codigo' => 'basico',
                'nome' => 'Básico',
                'descricao' => 'Verificação rápida de situação cadastral e regime tributário',
                'consultas_incluidas' => [
                    'situacao_cadastral',
                    'dados_cadastrais',
                    'endereco',
                    'cnaes',
                    'qsa',
                    'simples_nacional',
                    'mei',
                ],
                'custo_creditos' => 0,
                'is_gratuito' => true,
                'ordem' => 1,
            ],
            [
                'codigo' => 'cadastral_plus',
                'nome' => 'Cadastral+',
                'descricao' => 'Dados completos do CNPJ com SINTEGRA e verificação de listas restritivas',
                'consultas_incluidas' => [
                    'situacao_cadastral', 'dados_cadastrais', 'endereco', 'cnaes', 'qsa', 'simples_nacional', 'mei',
                    'sintegra', 'tcu_consolidada',
                ],
                'custo_creditos' => 3,
                'is_gratuito' => false,
                'ordem' => 2,
            ],
            [
                'codigo' => 'fiscal_federal',
                'nome' => 'Fiscal Federal',
                'descricao' => 'CND Federal (PGFN) e regularidade FGTS',
                'consultas_incluidas' => [
                    'situacao_cadastral', 'dados_cadastrais', 'endereco', 'cnaes', 'qsa', 'simples_nacional', 'mei',
                    'sintegra', 'tcu_consolidada',
                    'cnd_federal', 'crf_fgts',
                ],
                'custo_creditos' => 6,
                'is_gratuito' => false,
                'ordem' => 3,
            ],
            [
                'codigo' => 'fiscal_completo',
                'nome' => 'Fiscal Completo',
                'descricao' => 'CNDs Federal, Estadual e Trabalhista',
                'consultas_incluidas' => [
                    'situacao_cadastral', 'dados_cadastrais', 'endereco', 'cnaes', 'qsa', 'simples_nacional', 'mei',
                    'sintegra', 'tcu_consolidada',
                    'cnd_federal', 'crf_fgts',
                    'cnd_estadual', 'cndt',
                ],
                'custo_creditos' => 12,
                'is_gratuito' => false,
                'ordem' => 4,
            ],
            [
                'codigo' => 'due_diligence',
                'nome' => 'Due Diligence',
                'descricao' => 'Análise completa com detalhamento de dívida federal',
                'consultas_incluidas' => [
                    'situacao_cadastral', 'dados_cadastrais', 'endereco', 'cnaes', 'qsa', 'simples_nacional', 'mei',
                    'sintegra', 'tcu_consolidada',
                    'cnd_federal', 'crf_fgts',
                    'cnd_estadual', 'cndt',
                    'lista_devedores_pgfn',
                ],
                'custo_creditos' => 16,
                'is_gratuito' => false,
                'ordem' => 5,
            ],
            [
                'codigo' => 'esg',
                'nome' => 'ESG',
                'descricao' => 'Verificação de compliance ambiental e trabalhista',
                'consultas_incluidas' => [
                    'trabalho_escravo', 'ibama_autuacoes',
                ],
                'custo_creditos' => 6,
                'is_gratuito' => false,
                'ordem' => 6,
            ],
            [
                'codigo' => 'completo',
                'nome' => 'Completo',
                'descricao' => 'Todas as consultas disponíveis',
                'consultas_incluidas' => [
                    'situacao_cadastral', 'dados_cadastrais', 'endereco', 'cnaes', 'qsa', 'simples_nacional', 'mei',
                    'sintegra', 'tcu_consolidada',
                    'cnd_federal', 'crf_fgts',
                    'cnd_estadual', 'cndt',
                    'lista_devedores_pgfn',
                    'trabalho_escravo', 'ibama_autuacoes',
                ],
                'custo_creditos' => 22,
                'is_gratuito' => false,
                'ordem' => 7,
            ],
        ];

        foreach ($planos as $plano) {
            MonitoramentoPlano::updateOrCreate(
                ['codigo' => $plano['codigo']],
                $plano
            );
        }
    }
}
