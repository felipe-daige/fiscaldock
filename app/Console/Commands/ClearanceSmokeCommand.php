<?php

namespace App\Console\Commands;

use App\Services\Clearance\Sefaz\DocumentoConsultaService;
use Illuminate\Console\Command;

/**
 * Smoke test do clearance SEFAZ numa ÚNICA chave: chama o InfoSimples de verdade (1 consulta paga),
 * normaliza e imprime o resultado cru. NÃO debita créditos nem persiste snapshot (não cria lote).
 * Use para validar o pipeline/param antes de rodar em lote pela UI.
 *
 * Ex: php artisan clearance:smoke 50240243648971004576550010001117211468024730 nfe
 */
class ClearanceSmokeCommand extends Command
{
    protected $signature = 'clearance:smoke {chave : Chave de acesso (44 dígitos)} {tipo=nfe : nfe|cte}';

    protected $description = 'Consulta uma chave na SEFAZ (InfoSimples) e imprime o snapshot — sem cobrar nem persistir';

    public function handle(DocumentoConsultaService $svc): int
    {
        $chave = preg_replace('/\D/', '', (string) $this->argument('chave'));
        $tipo = strtolower((string) $this->argument('tipo'));

        if (strlen($chave) !== 44) {
            $this->error("Chave inválida ({$chave}): precisa de 44 dígitos.");

            return self::FAILURE;
        }

        $this->warn('⚠️  Isto faz 1 consulta PAGA ao InfoSimples (~R$0,26). Não persiste nem cobra créditos.');
        $this->line("Chave: {$chave} | tipo: {$tipo} | modelo: ".substr($chave, 20, 2));

        try {
            $s = $svc->consultar($chave, $tipo);
        } catch (\Throwable $e) {
            $this->error('Exceção: '.$e->getMessage());

            return self::FAILURE;
        }

        $c = $s->colunas;
        $this->newLine();
        $this->table(['Campo', 'Valor'], [
            ['status', $s->status],
            ['persistível', $s->persistivel ? 'sim' : 'NÃO'],
            ['estornável', $s->estornavel ? 'sim' : 'não'],
            ['billable', $s->billable ? 'sim' : 'não'],
            ['infosimples_code', $c['infosimples_code'] ?? '—'],
            ['infosimples_msg', $c['infosimples_code_message'] ?? '—'],
            ['error_code', $s->errorCode ?? '—'],
            ['error_message', $s->errorMessage ?? '—'],
            ['valor_total', $c['valor_total'] ?? ($c['valor_prestacao'] ?? '—')],
            ['data_emissao', $c['data_emissao'] ?? '—'],
            ['emit_cnpj', $c['emit_cnpj'] ?? '—'],
            ['emit_nome', $c['emit_nome'] ?? '—'],
        ]);

        if ($s->persistivel) {
            $this->info("✓ Resultado persistível ({$s->status}) — o pipeline está OK.");
        } else {
            $this->error("✗ NÃO persistível ({$s->status}). Veja infosimples_code/msg acima p/ a causa.");
        }

        return self::SUCCESS;
    }
}
