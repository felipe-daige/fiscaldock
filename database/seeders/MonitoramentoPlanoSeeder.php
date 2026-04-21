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
                'descricao' => 'Cartão de visita do CNPJ: confirma que a empresa existe, está ativa e tem endereço válido',
                'consultas_incluidas' => [
                    'situacao_cadastral', 'dados_cadastrais', 'endereco',
                ],
                'etapas' => [
                    ['numero' => 1, 'chave' => 'cadastrais', 'label' => 'Cadastrais'],
                ],
                'custo_creditos' => 0,
                'is_gratuito' => true,
                'is_active' => true,
                'ordem' => 1,
            ],
            [
                'codigo' => 'validacao',
                'nome' => 'Validação',
                'descricao' => 'Raio-X cadastral completo com parecer fiscal automático: regime tributário, histórico no Simples, QSA detalhado, CNAEs secundários e sinalizações para o contador',
                'consultas_incluidas' => [
                    'situacao_cadastral', 'dados_cadastrais', 'endereco',
                    'cnaes', 'cnaes_secundarios', 'qsa', 'qsa_detalhado',
                    'simples_nacional', 'mei',
                    'regime_tributario', 'historico_simples',
                    'capital_social', 'natureza_juridica', 'porte', 'data_inicio_atividade',
                    'parecer_fiscal',
                ],
                'etapas' => [
                    ['numero' => 1, 'chave' => 'cadastrais', 'label' => 'Cadastrais'],
                    ['numero' => 2, 'chave' => 'parecer', 'label' => 'Parecer fiscal'],
                ],
                'custo_creditos' => 5,
                'is_gratuito' => false,
                'is_active' => true,
                'ordem' => 2,
            ],
            [
                'codigo' => 'licitacao',
                'nome' => 'Licitação',
                'descricao' => 'Validação com CND Federal, CNDT e FGTS para editais e contratos',
                'consultas_incluidas' => [
                    'situacao_cadastral', 'dados_cadastrais', 'endereco',
                    'cnaes', 'qsa', 'simples_nacional', 'mei',
                    'cnd_federal', 'cndt', 'crf_fgts',
                ],
                'etapas' => [
                    ['numero' => 1, 'chave' => 'cadastrais', 'label' => 'Cadastrais'],
                    ['numero' => 2, 'chave' => 'certidoes_federais', 'label' => 'Certidões Federais'],
                ],
                'custo_creditos' => 10,
                'is_gratuito' => false,
                'is_active' => true,
                'ordem' => 3,
            ],
            [
                'codigo' => 'compliance',
                'nome' => 'Compliance',
                'descricao' => 'Regularidade fiscal e trabalhista completa',
                'consultas_incluidas' => [
                    'situacao_cadastral', 'dados_cadastrais', 'endereco',
                    'cnaes', 'qsa', 'simples_nacional', 'mei',
                    'sintegra', 'cnd_federal', 'crf_fgts', 'cndt',
                    'cnd_estadual', 'cnd_municipal',
                ],
                'etapas' => [
                    ['numero' => 1, 'chave' => 'cadastrais', 'label' => 'Cadastrais'],
                    ['numero' => 2, 'chave' => 'certidoes_federais', 'label' => 'Certidões Federais'],
                    ['numero' => 3, 'chave' => 'certidoes_estaduais', 'label' => 'Certidões Estaduais/Municipais'],
                ],
                'custo_creditos' => 18,
                'is_gratuito' => false,
                'is_active' => true,
                'ordem' => 4,
            ],
            [
                'codigo' => 'due_diligence',
                'nome' => 'Due Diligence',
                'descricao' => 'Compliance ampliado com sanções, CNJ, protestos e processos',
                'consultas_incluidas' => [
                    'situacao_cadastral', 'dados_cadastrais', 'endereco',
                    'cnaes', 'qsa', 'simples_nacional', 'mei',
                    'sintegra', 'cnd_federal', 'crf_fgts', 'cndt',
                    'cnd_estadual', 'cnd_municipal',
                    'cgu_cnc', 'cnj_improbidade', 'protestos', 'processos',
                ],
                'etapas' => [
                    ['numero' => 1, 'chave' => 'cadastrais', 'label' => 'Cadastrais'],
                    ['numero' => 2, 'chave' => 'certidoes_federais', 'label' => 'Certidões Federais'],
                    ['numero' => 3, 'chave' => 'certidoes_estaduais', 'label' => 'Certidões Estaduais/Municipais'],
                    ['numero' => 4, 'chave' => 'sancoes', 'label' => 'Sanções e Processos'],
                ],
                'custo_creditos' => 35,
                'is_gratuito' => false,
                'is_active' => true,
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
                'etapas' => [
                    ['numero' => 1, 'chave' => 'cadastrais', 'label' => 'Cadastrais'],
                    ['numero' => 2, 'chave' => 'certidoes_federais', 'label' => 'Certidões Federais'],
                    ['numero' => 3, 'chave' => 'certidoes_estaduais', 'label' => 'Certidões Estaduais/Municipais'],
                    ['numero' => 4, 'chave' => 'sancoes', 'label' => 'Sanções e Processos'],
                ],
                'custo_creditos' => 12,
                'is_gratuito' => false,
                'is_active' => false,
                'ordem' => 99,
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
