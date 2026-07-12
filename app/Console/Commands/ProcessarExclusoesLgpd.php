<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * LGPD fase 2.2/2.3 — processa os pedidos de exclusão de conta (`deletion_requested_at`).
 *
 * Anonimiza a PII do TITULAR (nome/e-mail/telefone/CNPJ/empresa…), a PII da sua EMPRESA
 * PRÓPRIA (o cliente `is_empresa_propria` — é a própria pessoa jurídica do titular) e
 * desabilita o login. PRESERVA a carteira fiscal ADMINISTRADA (demais `clientes`,
 * participantes, SPED/XML) — dado de terceiros com retenção legal.
 *
 * Fase 2.3: pode rodar agendado em DRY-RUN (só lista/loga, sem mutar) — o scheduler NÃO
 * passa `--force`. A anonimização de fato continua manual (`--force`) de propósito, por
 * ser irreversível em produção. `--apos-dias=N` ignora pedidos mais novos que N dias.
 */
class ProcessarExclusoesLgpd extends Command
{
    protected $signature = 'lgpd:processar-exclusoes
        {--force : Executa a anonimização de fato (sem isto, apenas lista — dry-run)}
        {--apos-dias=0 : Só processa pedidos com pelo menos N dias (carência)}';

    protected $description = 'Anonimiza a PII de titulares que pediram exclusão, preservando dados fiscais (LGPD).';

    public function handle(): int
    {
        $aposDias = max(0, (int) $this->option('apos-dias'));
        $force = (bool) $this->option('force');
        $limite = now()->subDays($aposDias);

        $pendentes = User::whereNotNull('deletion_requested_at')
            ->whereNull('anonimizado_em')
            ->where('deletion_requested_at', '<=', $limite)
            ->orderBy('deletion_requested_at')
            ->get();

        if ($pendentes->isEmpty()) {
            $this->info('Nenhum pedido de exclusão elegível para processamento.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'E-mail', 'Pedido em'],
            $pendentes->map(fn (User $u) => [
                $u->id,
                $u->email,
                optional($u->deletion_requested_at)->format('d/m/Y'),
            ])->all()
        );

        if (! $force) {
            $this->warn("DRY-RUN: {$pendentes->count()} titular(es) seriam anonimizados. Nada foi alterado.");
            $this->line('Use --force para executar a anonimização.');

            return self::SUCCESS;
        }

        $anonimizados = 0;
        foreach ($pendentes as $user) {
            $this->anonimizar($user);
            $anonimizados++;
            Log::info('lgpd.exclusao.anonimizada', ['user_id' => $user->id]);
        }

        $this->info("{$anonimizados} titular(es) anonimizado(s). Dados fiscais preservados.");

        return self::SUCCESS;
    }

    private function anonimizar(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->forceFill([
                'name' => 'Titular',
                'sobrenome' => 'anonimizado',
                'email' => 'anon-'.$user->id.'@anonimizado.invalid',
                'telefone' => '', // coluna NOT NULL — placeholder vazio em vez de null
                'empresa' => null,
                'cargo' => null,
                'cnpj' => null,
                'faturamento_anual' => null,
                'desafio_principal' => null,
                'marketing_opt_in' => false,
                'password' => Str::random(48), // cast 'hashed' rehasha; login fica impossível
                'remember_token' => null,
                'anonimizado_em' => now(),
            ])->save();

            $this->anonimizarEmpresaPropria($user);
        });
    }

    /**
     * Fase 2.3 — anonimiza a PII da empresa própria do titular (o cliente `is_empresa_propria`).
     * Só os campos identificadores/contato/endereço; mantém os fiscais-estatísticos (uf,
     * município, porte, CNAE, regime, situação) que não identificam pessoa. A carteira
     * ADMINISTRADA (demais clientes) é deliberadamente preservada — retenção fiscal de terceiros.
     */
    private function anonimizarEmpresaPropria(User $user): void
    {
        Cliente::where('user_id', $user->id)
            ->empresaPropria()
            ->get()
            ->each(function (Cliente $cliente) {
                $cliente->forceFill([
                    // documento é NOT NULL + UNIQUE(user_id, documento) — placeholder por-id evita colisão.
                    'documento' => str_pad((string) $cliente->id, 14, '0', STR_PAD_LEFT),
                    'nome' => 'Empresa anonimizada',
                    'razao_social' => 'Empresa anonimizada',
                    'nome_fantasia' => null,
                    'telefone' => null,
                    'email' => null,
                    'inscricao_estadual' => null,
                    'endereco' => null,
                    'numero' => null,
                    'complemento' => null,
                    'bairro' => null,
                    'cep' => null,
                    'cnpj_matriz' => null,
                    'codigo_municipal' => null,
                    'suframa' => null,
                    'qsa' => null, // sócios (nomes/CPFs de PF) — PII de terceiros
                ])->save();
            });
    }
}
