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
            // PLANOS DO SISTEMA (vitrine do contador) — a escada fiscal recriada como kits
            // (migração escada→à la carte 2026-07-22). `sistema=true` os coloca na seção "Planos do
            // contador"; nomes sinalizam o público. Gratuito = só a situação cadastral (R$ 0).
            [
                'slug' => 'fiscal-gratuito',
                'nome' => 'Cadastral do Contador (Grátis)',
                'descricao' => 'Confirma que o CNPJ existe, está ativo e com endereço válido. Sem custo.',
                'fontes' => ['cadastro'],
                'desconto_percentual' => 0,
                'sistema' => true,
                'ordem' => 10,
            ],
            [
                'slug' => 'fiscal-validacao',
                'nome' => 'Validação',
                'descricao' => 'Raio-X cadastral com parecer fiscal: regime tributário, Simples, QSA e CNAEs.',
                'fontes' => ['cadastro', 'analise_fiscal'],
                'desconto_percentual' => 0,
                'sistema' => true,
                'ordem' => 11,
            ],
            [
                'slug' => 'fiscal-licitacao',
                'nome' => 'Licitação',
                'descricao' => 'Análise fiscal + CND Federal, FGTS e CNDT para editais e contratos.',
                'fontes' => ['cadastro', 'analise_fiscal', 'cnd_federal', 'crf_fgts', 'cndt'],
                'desconto_percentual' => 0,
                'sistema' => true,
                'ordem' => 12,
            ],
            [
                'slug' => 'fiscal-compliance',
                'nome' => 'Compliance',
                'descricao' => 'Regularidade fiscal e trabalhista completa: federais + estaduais/municipais + SINTEGRA.',
                'fontes' => ['cadastro', 'analise_fiscal', 'cnd_federal', 'crf_fgts', 'cndt', 'cnd_estadual', 'cnd_municipal', 'sintegra'],
                'desconto_percentual' => 0,
                'sistema' => true,
                'ordem' => 13,
            ],
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
