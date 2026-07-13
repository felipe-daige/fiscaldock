<?php

namespace App\Console\Commands;

use App\Services\Clearance\CertificadoDigitalService;
use App\Services\Clearance\Sefaz\DocumentoConsultaService;
use Illuminate\Console\Command;

/**
 * Smoke test do clearance SEFAZ numa ÚNICA chave: chama o InfoSimples de verdade (1 consulta paga),
 * normaliza e imprime o resultado cru. NÃO debita saldo nem persiste snapshot (não cria lote).
 * Use para validar o pipeline/param antes de rodar em lote pela UI.
 *
 * Ex: php artisan clearance:smoke 50240243648971004576550010001117211468024730 nfe
 *
 * Com --cliente={id}: se o cliente tiver certificado A1 válido, a consulta vai ASSINADA
 * (pkcs12_cert/pkcs12_pass) → é assim que se VALIDA o certificado antes de confiar nele em
 * produção. Olhe `nfe_completa`/`consulta_sem_certificado` no output: completa = o cert pegou.
 */
class ClearanceSmokeCommand extends Command
{
    protected $signature = 'clearance:smoke {chave : Chave de acesso (44 dígitos)} {tipo=nfe : nfe|cte} {--cliente= : ID do cliente — usa o certificado A1 dele, se houver}';

    protected $description = 'Consulta uma chave na SEFAZ (InfoSimples) e imprime o snapshot — sem cobrar nem persistir';

    public function handle(DocumentoConsultaService $svc, CertificadoDigitalService $certs): int
    {
        $chave = preg_replace('/\D/', '', (string) $this->argument('chave'));
        $tipo = strtolower((string) $this->argument('tipo'));
        $clienteId = $this->option('cliente') ? (int) $this->option('cliente') : null;

        if (strlen($chave) !== 44) {
            $this->error("Chave inválida ({$chave}): precisa de 44 dígitos.");

            return self::FAILURE;
        }

        $this->warn('⚠️  Isto faz 1 consulta PAGA ao InfoSimples (~R$0,26). Não persiste nem cobra saldo.');
        $this->line("Chave: {$chave} | tipo: {$tipo} | modelo: ".substr($chave, 20, 2));

        if ($clienteId) {
            $temCert = $certs->materialParaConsulta($clienteId) !== null;
            $gate = (bool) config('clearance.certificado.habilitado');
            $this->line("Cliente {$clienteId}: certificado A1 válido? ".($temCert ? 'SIM' : 'não')
                .' | flag clearance.certificado.habilitado: '.($gate ? 'on' : 'OFF'));
            $this->line($temCert && $gate
                ? '→ consulta vai ASSINADA (pkcs12_cert). Espere nfe_completa=sim.'
                : '→ consulta vai PÚBLICA (sem certificado).');
        }

        try {
            $s = $svc->consultar($chave, $tipo, $clienteId);
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
            ['dest_cnpj (mascarado?)', $c['dest_cnpj'] ?? '—'],
            // Prova do certificado: completa = a SEFAZ devolveu tributos/itens/contraparte reais.
            ['nfe_completa', ($c['nfe_completa'] ?? false) ? 'SIM' : 'não'],
            ['consulta_sem_certificado', ($c['consulta_sem_certificado'] ?? true) ? 'sim (pública)' : 'NÃO (assinada)'],
        ]);

        if ($s->persistivel) {
            $this->info("✓ Resultado persistível ({$s->status}) — o pipeline está OK.");
        } else {
            $this->error("✗ NÃO persistível ({$s->status}). Veja infosimples_code/msg acima p/ a causa.");
        }

        if ($clienteId && ($c['consulta_sem_certificado'] ?? true)) {
            $this->warn('⚠️  A consulta saiu PÚBLICA mesmo com --cliente. Ou o cliente não tem cert válido, '
                .'ou o InfoSimples recusou o pkcs12_cert (veja o log: "consulta com certificado recusada").');
        }

        return self::SUCCESS;
    }
}
