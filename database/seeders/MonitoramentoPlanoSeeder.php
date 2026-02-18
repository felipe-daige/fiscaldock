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
                'codigo' => 'gratuito',
                'nome' => 'Gratuito',
                'descricao' => 'Consulta instantânea de situação cadastral, regime tributário e quadro societário',
                'consultas_incluidas' => [
                    'situacao_cadastral', 'dados_cadastrais', 'endereco',
                    'cnaes', 'qsa', 'simples_nacional', 'mei',
                ],
                'custo_creditos' => 0,
                'is_gratuito' => true,
                'ordem' => 1,
            ],
            [
                'codigo' => 'validacao',
                'nome' => 'Validação',
                'descricao' => 'Valida Inscrição Estadual e verifica impedimentos em listas do TCU',
                'consultas_incluidas' => [
                    'situacao_cadastral', 'dados_cadastrais', 'endereco',
                    'cnaes', 'qsa', 'simples_nacional', 'mei',
                    'sintegra', 'tcu_consolidada',
                ],
                'custo_creditos' => 4,
                'is_gratuito' => false,
                'ordem' => 2,
            ],
            [
                'codigo' => 'licitacao',
                'nome' => 'Licitação',
                'descricao' => 'Todas as CNDs exigidas em editais, licitações e contratos públicos',
                'consultas_incluidas' => [
                    'situacao_cadastral', 'dados_cadastrais', 'endereco',
                    'cnaes', 'qsa', 'simples_nacional', 'mei',
                    'sintegra', 'tcu_consolidada',
                    'cnd_federal', 'crf_fgts', 'cnd_estadual', 'cndt',
                ],
                'custo_creditos' => 8,
                'is_gratuito' => false,
                'ordem' => 3,
            ],
            [
                'codigo' => 'compliance',
                'nome' => 'Compliance',
                'descricao' => 'Análise completa de risco financeiro com protestos e dívida ativa na PGFN',
                'consultas_incluidas' => [
                    'situacao_cadastral', 'dados_cadastrais', 'endereco',
                    'cnaes', 'qsa', 'simples_nacional', 'mei',
                    'sintegra', 'tcu_consolidada',
                    'cnd_federal', 'crf_fgts', 'cnd_estadual', 'cndt',
                    'protestos', 'lista_devedores_pgfn',
                ],
                'custo_creditos' => 10,
                'is_gratuito' => false,
                'ordem' => 4,
            ],
            [
                'codigo' => 'due_diligence',
                'nome' => 'Due Diligence',
                'descricao' => 'Investigação aprofundada com compliance trabalhista e ambiental (ESG)',
                'consultas_incluidas' => [
                    'situacao_cadastral', 'dados_cadastrais', 'endereco',
                    'cnaes', 'qsa', 'simples_nacional', 'mei',
                    'sintegra', 'tcu_consolidada',
                    'cnd_federal', 'crf_fgts', 'cnd_estadual', 'cndt',
                    'protestos', 'lista_devedores_pgfn',
                    'trabalho_escravo', 'ibama_autuacoes',
                ],
                'custo_creditos' => 12,
                'is_gratuito' => false,
                'ordem' => 5,
            ],
            [
                'codigo' => 'enterprise',
                'nome' => 'Enterprise',
                'descricao' => 'Raio-X completo do CNPJ, incluindo processos judiciais no CNJ',
                'consultas_incluidas' => [
                    'situacao_cadastral', 'dados_cadastrais', 'endereco',
                    'cnaes', 'qsa', 'simples_nacional', 'mei',
                    'sintegra', 'tcu_consolidada',
                    'cnd_federal', 'crf_fgts', 'cnd_estadual', 'cndt',
                    'protestos', 'lista_devedores_pgfn',
                    'trabalho_escravo', 'ibama_autuacoes',
                    'processos_cnj',
                ],
                'custo_creditos' => 14,
                'is_gratuito' => false,
                'ordem' => 6,
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
