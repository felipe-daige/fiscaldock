<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Re-base ONE-SHOT do ledger: converte a unidade legada de "créditos" (1 un = R$ 0,20)
 * para reais em todas as colunas monetárias persistidas. Idempotente via marker
 * `comercial_parametros.chave = 'ledger_unidade'` — roda uma única vez por banco.
 *
 * Rodar com a aplicação em manutenção e worker/scheduler parados: valores em cache
 * (estornos de lote em voo) não são convertidos — o deploy exige fila vazia.
 */
class RebaseLedgerReaisCommand extends Command
{
    protected $signature = 'saldo:rebase-reais {--force : não pergunta confirmação}';

    protected $description = 'Converte o ledger inteiro da unidade legada (créditos ×0,20) para reais. One-shot, guardado por marker.';

    /** Colunas convertidas: tabela => colunas (valor × 0,20, arredondado a 2 casas). */
    private const COLUNAS = [
        'users' => ['credits', 'trial_credits_granted', 'trial_credits_remaining', 'trial_credits_expired'],
        'credit_transactions' => ['amount', 'balance_after'],
        'consulta_lotes' => ['creditos_cobrados'],
        'monitoramento_consultas' => ['creditos_cobrados'],
        'efd_importacoes' => ['creditos_cobrados'],
        'mercado_pago_payments' => ['creditos'],
        'recarga_automaticas' => ['creditos', 'limite_creditos'],
        'account_subscriptions' => ['creditos_inclusos_saldo', 'limite_consumo_automatico'],
        'subscription_plans' => ['creditos_inclusos'],
    ];

    public function handle(): int
    {
        $marker = DB::table('comercial_parametros')->where('chave', 'ledger_unidade')->value('valor');

        if ($marker === 'reais') {
            $this->info('Ledger já está em reais (marker presente) — nada a fazer.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Converter TODO o ledger para reais (×0,20)? Operação única e irreversível sem backup.')) {
            return self::FAILURE;
        }

        DB::transaction(function () {
            // Colunas ainda inteiras precisam aceitar centavos antes do UPDATE.
            $this->garantirNumeric('account_subscriptions', 'limite_consumo_automatico');

            foreach (self::COLUNAS as $tabela => $colunas) {
                foreach ($colunas as $coluna) {
                    $afetadas = DB::update(
                        "UPDATE {$tabela} SET {$coluna} = round({$coluna} * 0.20, 2) WHERE {$coluna} IS NOT NULL"
                    );
                    $this->line(sprintf('  %s.%s — %d linha(s)', $tabela, $coluna, $afetadas));
                }
            }

            DB::table('comercial_parametros')->updateOrInsert(
                ['chave' => 'ledger_unidade'],
                ['valor' => 'reais', 'updated_by' => null, 'updated_at' => now(), 'created_at' => now()],
            );
        });

        $this->info('Ledger convertido para reais. Marker gravado.');

        return self::SUCCESS;
    }

    private function garantirNumeric(string $tabela, string $coluna): void
    {
        $tipo = DB::selectOne(
            'SELECT data_type FROM information_schema.columns WHERE table_name = ? AND column_name = ?',
            [$tabela, $coluna],
        )?->data_type;

        if ($tipo === 'integer' || $tipo === 'bigint') {
            DB::statement("ALTER TABLE {$tabela} ALTER COLUMN {$coluna} TYPE numeric(12,2)");
            $this->line("  ALTER {$tabela}.{$coluna} → numeric(12,2)");
        }
    }
}
