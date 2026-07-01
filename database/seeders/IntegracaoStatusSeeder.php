<?php

namespace Database\Seeders;

use App\Models\IntegracaoStatus;
use Illuminate\Database\Seeder;

/**
 * Semeia o catálogo fixo de integrações. Idempotente e não-destrutivo: firstOrCreate só cria
 * o que falta — NÃO sobrescreve status/mensagem que o admin já ajustou.
 */
class IntegracaoStatusSeeder extends Seeder
{
    public function run(): void
    {
        $catalogo = [
            // grupo consultas
            ['chave' => 'cadastro', 'nome' => 'Dados Cadastrais', 'grupo' => 'consultas', 'ordem' => 1],
            ['chave' => 'cnd_federal', 'nome' => 'CND Federal (Receita/PGFN)', 'grupo' => 'consultas', 'ordem' => 2],
            ['chave' => 'cnd_estadual', 'nome' => 'CND Estadual (SEFAZ)', 'grupo' => 'consultas', 'ordem' => 3],
            ['chave' => 'cnd_municipal', 'nome' => 'CND Municipal', 'grupo' => 'consultas', 'ordem' => 4],
            ['chave' => 'cndt', 'nome' => 'CNDT (débitos trabalhistas)', 'grupo' => 'consultas', 'ordem' => 5],
            ['chave' => 'crf_fgts', 'nome' => 'CRF FGTS (Caixa)', 'grupo' => 'consultas', 'ordem' => 6],
            ['chave' => 'sintegra', 'nome' => 'SINTEGRA', 'grupo' => 'consultas', 'ordem' => 7],
            // grupo plataforma
            ['chave' => 'pagamento', 'nome' => 'Pagamento (Mercado Pago)', 'grupo' => 'plataforma', 'ordem' => 1],
            ['chave' => 'whatsapp', 'nome' => 'WhatsApp (Evolution)', 'grupo' => 'plataforma', 'ordem' => 2],
            ['chave' => 'importacao_efd', 'nome' => 'Importação EFD', 'grupo' => 'plataforma', 'ordem' => 3],
        ];

        foreach ($catalogo as $item) {
            IntegracaoStatus::firstOrCreate(
                ['chave' => $item['chave']],
                [
                    'nome' => $item['nome'],
                    'grupo' => $item['grupo'],
                    'ordem' => $item['ordem'],
                    'status' => IntegracaoStatus::STATUS_OPERACIONAL,
                ],
            );
        }
    }
}
