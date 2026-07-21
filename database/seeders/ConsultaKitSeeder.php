<?php

namespace Database\Seeders;

use App\Models\ConsultaKit;
use Illuminate\Database\Seeder;

/**
 * Kits iniciais da consulta avulsa por fontes (vertical advocacia, fase 3).
 * firstOrCreate por slug: rodar de novo NUNCA sobrescreve edição feita no admin
 * (mesma regra de merge cirúrgico dos planos — prod não é reseedado).
 *
 * Só fontes single-call implementadas; TJs 2 etapas entram nos kits quando a fase 4 landar.
 */
class ConsultaKitSeeder extends Seeder
{
    public function run(): void
    {
        $kits = [
            [
                'slug' => 'licitacao',
                'nome' => 'Kit Licitação',
                'descricao' => 'Regularidade fiscal completa + integridade (TCU/CEIS/CNEP) para habilitação em licitações.',
                'fontes' => ['cnd_federal', 'cnd_estadual', 'cnd_municipal', 'crf_fgts', 'cndt', 'sintegra', 'certidao_tcu', 'ceis', 'cnep'],
                'desconto_percentual' => 10,
                'ordem' => 1,
            ],
            [
                'slug' => 'contencioso',
                'nome' => 'Kit Contencioso',
                'descricao' => 'Panorama pré-petição: certidões judiciais, ações trabalhistas, protestos e falência.',
                'fontes' => ['certidao_stj', 'certidao_trf', 'ceat_trt', 'certidao_mpt', 'certidao_mpf', 'protestos', 'falencias'],
                'desconto_percentual' => 10,
                'ordem' => 2,
            ],
            [
                'slug' => 'due-diligence',
                'nome' => 'Kit Due Diligence',
                'descricao' => 'Todas as fontes disponíveis: fiscal, judicial, integridade e passivo oculto.',
                'fontes' => [
                    'cnd_federal', 'cnd_estadual', 'cnd_municipal', 'crf_fgts', 'cndt', 'sintegra',
                    'certidao_stj', 'certidao_trf', 'ceat_trt', 'certidao_mpt', 'certidao_mpf',
                    'certidao_tcu', 'improbidade', 'ceis', 'cnep', 'protestos', 'falencias',
                ],
                'desconto_percentual' => 15,
                'ordem' => 3,
            ],
        ];

        foreach ($kits as $kit) {
            ConsultaKit::firstOrCreate(['slug' => $kit['slug']], $kit);
        }
    }
}
