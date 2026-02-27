<?php

namespace App\Services;

use App\Models\CreditTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditService
{
    /**
     * Retorna o saldo atual de creditos do usuario.
     */
    public function getBalance(User $user): int
    {
        return (int) $user->credits;
    }

    /**
     * Verifica se o usuario tem creditos suficientes.
     */
    public function hasEnough(User $user, float $amount): bool
    {
        return $this->getBalance($user) >= $amount;
    }

    /**
     * Desconta creditos do usuario.
     * Retorna true se a operacao foi bem-sucedida, false caso contrario.
     */
    public function deduct(User $user, float $amount, string $type = 'consulta_lote', ?string $description = null, ?Model $source = null): bool
    {
        if ($amount <= 0) {
            return true;
        }

        if (!$this->hasEnough($user, $amount)) {
            Log::warning('Tentativa de desconto de creditos insuficientes', [
                'user_id' => $user->id,
                'credits_atual' => $user->credits,
                'amount_solicitado' => $amount,
            ]);
            return false;
        }

        return DB::transaction(function () use ($user, $amount, $type, $description, $source) {
            $freshUser = User::lockForUpdate()->find($user->id);

            if (!$freshUser || $freshUser->credits < $amount) {
                return false;
            }

            $freshUser->credits = (int) floor($freshUser->credits - $amount);
            $freshUser->save();

            $user->credits = $freshUser->credits;

            $this->logTransaction($user, (int) -$amount, $freshUser->credits, $type, $description, $source);

            Log::info('Creditos descontados com sucesso', [
                'user_id' => $user->id,
                'amount' => $amount,
                'novo_saldo' => $freshUser->credits,
            ]);

            return true;
        });
    }

    /**
     * Adiciona creditos ao usuario.
     */
    public function add(User $user, float $amount, string $type = 'manual_add', ?string $description = null, ?Model $source = null): void
    {
        if ($amount <= 0) {
            return;
        }

        DB::transaction(function () use ($user, $amount, $type, $description, $source) {
            $freshUser = User::lockForUpdate()->find($user->id);

            if (!$freshUser) {
                return;
            }

            $freshUser->credits = (int) floor($freshUser->credits + $amount);
            $freshUser->save();

            $user->credits = $freshUser->credits;

            $this->logTransaction($user, (int) $amount, $freshUser->credits, $type, $description, $source);

            Log::info('Creditos adicionados com sucesso', [
                'user_id' => $user->id,
                'amount' => $amount,
                'novo_saldo' => $freshUser->credits,
            ]);
        });
    }

    /**
     * Registra transacao no historico.
     */
    private function logTransaction(User $user, int $amount, int $balanceAfter, string $type, ?string $description = null, ?Model $source = null): void
    {
        CreditTransaction::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'balance_after' => $balanceAfter,
            'type' => $type,
            'description' => $description,
            'source_type' => $source ? get_class($source) : null,
            'source_id' => $source?->id,
        ]);
    }
}
