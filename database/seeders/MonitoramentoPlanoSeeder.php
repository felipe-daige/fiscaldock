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
                'consultas_incluidas' => ['cnpj_situacao', 'simples_nacional'],
                'custo_creditos' => 0,
                'is_gratuito' => true,
                'ordem' => 1,
            ],
            [
                'codigo' => 'cadastral',
                'nome' => 'Cadastral+',
                'descricao' => 'Dados completos do CNPJ, SINTEGRA e Inscrição Estadual',
                'consultas_incluidas' => ['cnpj_completo', 'sintegra', 'inscricao_estadual'],
                'custo_creditos' => 3,
                'is_gratuito' => false,
                'ordem' => 2,
            ],
            [
                'codigo' => 'fiscal_federal',
                'nome' => 'Fiscal Federal',
                'descricao' => 'CND Federal (PGFN) e regularidade FGTS',
                'consultas_incluidas' => ['cnd_federal', 'fgts'],
                'custo_creditos' => 6,
                'is_gratuito' => false,
                'ordem' => 3,
            ],
            [
                'codigo' => 'fiscal_completo',
                'nome' => 'Fiscal Completo',
                'descricao' => 'CNDs Federal, Estadual e Trabalhista',
                'consultas_incluidas' => ['cnd_federal', 'fgts', 'cnd_estadual', 'cndt'],
                'custo_creditos' => 12,
                'is_gratuito' => false,
                'ordem' => 4,
            ],
            [
                'codigo' => 'due_diligence',
                'nome' => 'Due Diligence',
                'descricao' => 'Análise completa com protestos e processos judiciais',
                'consultas_incluidas' => ['cnd_federal', 'fgts', 'cnd_estadual', 'cndt', 'protestos', 'processos'],
                'custo_creditos' => 18,
                'is_gratuito' => false,
                'ordem' => 5,
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
