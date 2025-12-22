<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditService
{
    /**
     * Retorna o saldo atual de créditos do usuário.
     */
    public function getBalance(User $user): int
    {
        return (int) $user->credits;
    }

    /**
     * Verifica se o usuário tem créditos suficientes.
     */
    public function hasEnough(User $user, int $amount): bool
    {
        return $this->getBalance($user) >= $amount;
    }

    /**
     * Desconta créditos do usuário.
     * Retorna true se a operação foi bem-sucedida, false caso contrário.
     */
    public function deduct(User $user, int $amount): bool
    {
        if ($amount <= 0) {
            return true; // Nada a descontar
        }

        if (!$this->hasEnough($user, $amount)) {
            Log::warning('Tentativa de desconto de créditos insuficientes', [
                'user_id' => $user->id,
                'credits_atual' => $user->credits,
                'amount_solicitado' => $amount,
            ]);
            return false;
        }

        // Usa transação para garantir atomicidade
        return DB::transaction(function () use ($user, $amount) {
            // Recarrega o usuário com lock para evitar race conditions
            $freshUser = User::lockForUpdate()->find($user->id);
            
            if (!$freshUser || $freshUser->credits < $amount) {
                return false;
            }

            $freshUser->credits -= $amount;
            $freshUser->save();

            // Atualiza o modelo original
            $user->credits = $freshUser->credits;

            Log::info('Créditos descontados com sucesso', [
                'user_id' => $user->id,
                'amount' => $amount,
                'novo_saldo' => $freshUser->credits,
            ]);

            return true;
        });
    }

    /**
     * Adiciona créditos ao usuário.
     * Para uso futuro com gateway de pagamento.
     */
    public function add(User $user, int $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        DB::transaction(function () use ($user, $amount) {
            $freshUser = User::lockForUpdate()->find($user->id);
            
            if (!$freshUser) {
                return;
            }

            $freshUser->credits += $amount;
            $freshUser->save();

            // Atualiza o modelo original
            $user->credits = $freshUser->credits;

            Log::info('Créditos adicionados com sucesso', [
                'user_id' => $user->id,
                'amount' => $amount,
                'novo_saldo' => $freshUser->credits,
            ]);
        });
    }
}


