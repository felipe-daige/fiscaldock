<?php

namespace App\Console\Commands;

use App\Jobs\ProcessarEfdImportacaoJob;
use App\Models\EfdDivergencia;
use App\Models\EfdImportacao;
use App\Services\EfdAuditoriaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Harness de validação do motor Laravel (F4) — roda o `ProcessarEfdImportacaoJob` contra
 * um SPED real e passa o resultado pelo oráculo de aceite (`EfdAuditoriaService`):
 * `integridade()` (SPED × banco, barato) + `auditar()` (gera efd_divergencias).
 * Critério de virar a flag: `integridade.ok` E **0 divergência severidade ERRO**.
 *
 * Dry-run por default (transação revertida no fim) — NÃO toca o banco de produção; use
 * `--commit` só quando quiser persistir de verdade. Sem n8n. §10.9/§10.11 F4.
 */
class EfdMotorShadow extends Command
{
    protected $signature = 'efd:motor-shadow
        {arquivo : Caminho do SPED bruto (.txt)}
        {--user= : user_id dono da importação (obrigatório)}
        {--cliente= : cliente_id (default: empresa própria do usuário)}
        {--tipo=EFD ICMS/IPI : tipo_efd (EFD ICMS/IPI | EFD PIS/COFINS)}
        {--commit : Persiste de verdade (default: reverte a transação)}';

    protected $description = 'Roda o motor Laravel contra um SPED real e reporta integridade + divergências ERRO do auditor (dry-run por default).';

    public function handle(EfdAuditoriaService $auditoria): int
    {
        $arquivo = (string) $this->argument('arquivo');
        if (! is_file($arquivo)) {
            $this->error("Arquivo não encontrado: {$arquivo}");

            return self::FAILURE;
        }

        $userId = $this->option('user') !== null ? (int) $this->option('user') : null;
        if (! $userId) {
            $this->error('Informe --user=<id> (dono da importação).');

            return self::FAILURE;
        }

        if ($this->option('cliente') !== null) {
            $clienteId = (int) $this->option('cliente');
            // NUNCA confiar no --cliente sem validar o dono: um id de outro tenant gravaria
            // (com --commit) notas/catálogo no acervo dele e dispararia a trigger de histórico.
            if (! DB::table('clientes')->where('id', $clienteId)->where('user_id', $userId)->exists()) {
                $this->error("Cliente {$clienteId} não pertence ao user {$userId}.");

                return self::FAILURE;
            }
        } else {
            $clienteId = (int) DB::table('clientes')->where('user_id', $userId)
                ->orderByDesc('is_empresa_propria')->orderBy('id')->value('id');
        }

        if (! $clienteId) {
            $this->error("Nenhum cliente para user {$userId} — passe --cliente=<id>.");

            return self::FAILURE;
        }

        $conteudo = (string) file_get_contents($arquivo);
        $commit = (bool) $this->option('commit');

        $this->info(($commit ? 'COMMIT' : 'DRY-RUN')." · user={$userId} cliente={$clienteId} tipo={$this->option('tipo')}");

        DB::beginTransaction();

        try {
            $imp = EfdImportacao::create([
                'user_id' => $userId,
                'cliente_id' => $clienteId,
                'tipo_efd' => (string) $this->option('tipo'),
                'filename' => basename($arquivo),
                'arquivo_base64' => EfdImportacao::encodeConteudoSped($conteudo),
                'status' => 'processando',
                'iniciado_em' => now(),
            ]);

            // Motor Laravel síncrono, sem progresso (tab null). Reusa o Job real.
            ProcessarEfdImportacaoJob::dispatchSync($imp->id, null);
            $imp->refresh();

            // Oráculo de aceite.
            $integridade = $auditoria->integridade($imp);
            $auditoria->auditar($imp);
            $errosAuditor = EfdDivergencia::where('importacao_id', $imp->id)
                ->where('severidade', EfdDivergencia::SEVERIDADE_ERRO)
                ->count();

            $this->relatar($imp, $integridade, $errosAuditor);

            $aprovado = ($imp->status === 'concluido') && $integridade['ok'] && $errosAuditor === 0;

            if ($commit && $aprovado) {
                DB::commit();
                $this->info("Persistido (importação #{$imp->id}).");
            } else {
                DB::rollBack();
                $this->line($commit ? 'NÃO persistido (reprovado no oráculo).' : 'Revertido (dry-run).');
            }

            $this->newLine();
            $this->line($aprovado ? '<fg=green>✓ APROVADO</> — integridade ok e 0 ERRO. Seguro virar a flag.' : '<fg=red>✗ REPROVADO</> — NÃO virar a flag.');

            return $aprovado ? self::SUCCESS : self::FAILURE;
        } catch (Throwable $e) {
            DB::rollBack();
            $this->error('Motor falhou: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * @param  array{esperadas:int,no_banco:int,faltando:int,ok:bool,amostra_faltando:array<int,string>}  $integridade
     */
    /** Conta linhas-filho (itens/consolidados) DESTA importação via join em efd_notas. */
    private function contarFilhos(string $tabela, EfdImportacao $imp): int
    {
        return (int) DB::table($tabela)
            ->join('efd_notas', 'efd_notas.id', '=', "{$tabela}.efd_nota_id")
            ->where('efd_notas.importacao_id', $imp->id)
            ->count();
    }

    private function relatar(EfdImportacao $imp, array $integridade, int $errosAuditor): void
    {
        $notas = DB::table('efd_notas')->where('importacao_id', $imp->id);

        $this->newLine();
        $this->table(['Métrica', 'Valor'], [
            ['status', $imp->status],
            ['notas (efd_notas)', (clone $notas)->count()],
            ['— canceladas', (clone $notas)->where('cancelada', true)->count()],
            // Contar por importacao_id (via join): por user_id inflava com imports anteriores
            // e o operador não conseguia ver o que ESTE arquivo extraiu.
            ['consolidados (C190/D190)', $this->contarFilhos('efd_notas_consolidados', $imp)],
            ['itens (C170/A170)', $this->contarFilhos('efd_notas_itens', $imp)],
            ['participantes (0150)', DB::table('participantes')->where('importacao_efd_id', $imp->id)->count()],
            ['catálogo (0200)', DB::table('efd_catalogo_itens')->where('importacao_id', $imp->id)->count()],
            ['apuração ICMS (bloco E)', DB::table('efd_apuracoes_icms')->where('importacao_id', $imp->id)->count()],
            ['—', '—'],
            ['integridade.esperadas', $integridade['esperadas']],
            ['integridade.no_banco', $integridade['no_banco']],
            ['integridade.faltando', $integridade['faltando']],
            ['integridade.ok', $integridade['ok'] ? 'true' : 'false'],
            ['auditor: divergências ERRO', $errosAuditor],
        ]);

        if (! $integridade['ok']) {
            $this->warn('Chaves faltando (amostra): '.implode(', ', array_slice($integridade['amostra_faltando'], 0, 5)));
        }
    }
}
