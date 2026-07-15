<?php

namespace App\Services;

use App\Models\SaldoTransacao;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Ledger de saldo pré-pago em reais (R$). `users.credits` e `credit_transactions.amount`
 * armazenam valores em R$ com 2 casas decimais — a unidade legada de "créditos"
 * (1 crédito = R$ 0,20) foi removida em 2026-07-14.
 */
class SaldoService
{
    /**
     * Retorna o saldo atual do usuário em reais.
     */
    public function getBalance(User $user): float
    {
        $user = $user->accountOwner();

        return round($user->credits, 2);
    }

    /**
     * Verifica se o usuário tem saldo suficiente.
     */
    public function hasEnough(User $user, float $amount): bool
    {
        return $this->getBalance($user) >= round($amount, 2);
    }

    /**
     * Desconta saldo do usuário (valor em R$).
     * Retorna true se a operacao foi bem-sucedida, false caso contrario.
     */
    public function deduct(User $user, float $amount, string $type = 'consulta_lote', ?string $description = null, ?Model $source = null): bool
    {
        $user = $user->accountOwner();
        $amount = round($amount, 2);

        if ($amount <= 0) {
            return true;
        }

        if (! $this->hasEnough($user, $amount)) {
            Log::warning('Tentativa de desconto com saldo insuficiente', [
                'user_id' => $user->id,
                'saldo_atual' => $user->credits,
                'amount_solicitado' => $amount,
            ]);

            return false;
        }

        $ok = DB::transaction(function () use ($user, $amount, $type, $description, $source) {
            $freshUser = User::lockForUpdate()->find($user->id);

            if (! $freshUser || round($freshUser->credits, 2) < $amount) {
                return false;
            }

            $freshUser->credits = round($freshUser->credits - $amount, 2);

            if ((float) $freshUser->trial_credits_remaining > 0) {
                $trialConsumed = min($amount, round($freshUser->trial_credits_remaining, 2));
                $freshUser->trial_credits_remaining = round(max(0, (float) $freshUser->trial_credits_remaining - $trialConsumed), 2);
            }

            $freshUser->save();

            $user->credits = $freshUser->credits;

            $this->logTransaction($user, -$amount, (float) $freshUser->credits, $type, $description, $source);

            Log::info('Saldo descontado com sucesso', [
                'user_id' => $user->id,
                'amount' => $amount,
                'novo_saldo' => $freshUser->credits,
            ]);

            return true;
        });

        // Efeito colateral pós-débito: auto top-up por saldo baixo (nunca bloqueia/lança).
        if ($ok) {
            app(\App\Services\MercadoPago\AutoTopUpTrigger::class)->aposDeducao($user);
        }

        return $ok;
    }

    /**
     * Adiciona saldo ao usuário (valor em R$).
     */
    public function add(User $user, float $amount, string $type = 'manual_add', ?string $description = null, ?Model $source = null): void
    {
        $user = $user->accountOwner();
        $amount = round($amount, 2);

        if ($amount <= 0) {
            return;
        }

        DB::transaction(function () use ($user, $amount, $type, $description, $source) {
            $freshUser = User::lockForUpdate()->find($user->id);

            if (! $freshUser) {
                return;
            }

            $freshUser->credits = round($freshUser->credits + $amount, 2);
            $freshUser->save();

            $user->credits = $freshUser->credits;

            $this->logTransaction($user, $amount, (float) $freshUser->credits, $type, $description, $source);

            Log::info('Saldo adicionado com sucesso', [
                'user_id' => $user->id,
                'amount' => $amount,
                'novo_saldo' => $freshUser->credits,
            ]);
        });
    }

    public function grantTrial(User $user, float $amount, CarbonInterface $expiresAt, string $source = 'landing_signup'): void
    {
        $amount = round($amount, 2);

        if ($amount <= 0) {
            return;
        }

        DB::transaction(function () use ($user, $amount, $expiresAt, $source) {
            $freshUser = User::lockForUpdate()->find($user->id);

            if (! $freshUser) {
                return;
            }

            $freshUser->credits = round($freshUser->credits + $amount, 2);
            $freshUser->trial_used = true;
            $freshUser->trial_started_at = now();
            $freshUser->trial_expires_at = $expiresAt;
            $freshUser->trial_credits_granted = $amount;
            $freshUser->trial_credits_remaining = $amount;
            $freshUser->trial_credits_expired = 0;
            $freshUser->trial_source = $source;
            $freshUser->save();

            $user->credits = $freshUser->credits;
            $user->trial_used = $freshUser->trial_used;
            $user->trial_started_at = $freshUser->trial_started_at;
            $user->trial_expires_at = $freshUser->trial_expires_at;
            $user->trial_credits_granted = $freshUser->trial_credits_granted;
            $user->trial_credits_remaining = $freshUser->trial_credits_remaining;
            $user->trial_credits_expired = $freshUser->trial_credits_expired;
            $user->trial_source = $freshUser->trial_source;

            $this->logTransaction(
                $user,
                $amount,
                (float) $freshUser->credits,
                'trial_bonus',
                sprintf('Bônus de boas-vindas: R$ %s de saldo grátis válido até %s.', number_format($amount, 2, ',', '.'), $expiresAt->format('d/m/Y H:i')),
                null
            );
        });
    }

    public function expireTrialBalance(User $user): float
    {
        return DB::transaction(function () use ($user) {
            $freshUser = User::lockForUpdate()->find($user->id);

            if (! $freshUser || (float) $freshUser->trial_credits_remaining <= 0) {
                return 0.0;
            }

            if (! $freshUser->trial_expires_at || now()->lt($freshUser->trial_expires_at)) {
                return 0.0;
            }

            $amount = round(min($freshUser->trial_credits_remaining, (float) $freshUser->credits), 2);

            if ($amount <= 0) {
                $freshUser->trial_credits_remaining = 0;
                $freshUser->save();

                return 0.0;
            }

            $freshUser->credits = round(max(0, (float) $freshUser->credits - $amount), 2);
            $freshUser->trial_credits_remaining = round(max(0, (float) $freshUser->trial_credits_remaining - $amount), 2);
            $freshUser->trial_credits_expired = round($freshUser->trial_credits_expired + $amount, 2);
            $freshUser->save();

            $user->credits = $freshUser->credits;
            $user->trial_credits_remaining = $freshUser->trial_credits_remaining;
            $user->trial_credits_expired = $freshUser->trial_credits_expired;

            $this->logTransaction(
                $user,
                -$amount,
                (float) $freshUser->credits,
                'trial_expiration',
                'Expiração automática do saldo promocional restante do trial.',
                null
            );

            return $amount;
        });
    }

    /**
     * Registra transacao no historico (valores em R$).
     */
    private function logTransaction(User $user, float $amount, float $balanceAfter, string $type, ?string $description = null, ?Model $source = null): void
    {
        SaldoTransacao::create([
            'user_id' => $user->id,
            'amount' => round($amount, 2),
            'balance_after' => round($balanceAfter, 2),
            'type' => $type,
            'description' => $description,
            'source_type' => $source ? get_class($source) : null,
            'source_id' => $source?->id,
        ]);
    }
}
