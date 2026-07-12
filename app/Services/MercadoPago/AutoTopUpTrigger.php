<?php

namespace App\Services\MercadoPago;

use App\Jobs\ProcessarAutoTopUpJob;
use App\Models\RecargaAutomatica;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Gatilho leve do auto top-up por saldo. Chamado por SaldoService::deduct APÓS um
 * débito bem-sucedido, como efeito colateral. Nunca bloqueia, nunca lança — qualquer
 * erro é logado e engolido para não afetar a consulta em curso.
 */
class AutoTopUpTrigger
{
    public function aposDeducao(User $user): void
    {
        try {
            $r = RecargaAutomatica::where('user_id', $user->id)
                ->where('gatilho', RecargaAutomatica::GATILHO_SALDO)
                ->where('status', RecargaAutomatica::STATUS_ATIVA)
                ->first();

            if ($r === null || $r->cobranca_em_andamento || $r->limite_creditos === null) {
                return;
            }

            if ((int) $user->credits >= (int) $r->limite_creditos) {
                return;
            }

            $cooldown = (int) config('services.mercadopago.auto_topup.cooldown_minutos', 5);
            if ($r->ultima_tentativa_em && $r->ultima_tentativa_em->gt(now()->subMinutes($cooldown))) {
                return;
            }

            ProcessarAutoTopUpJob::dispatch($user->id);
        } catch (Throwable $e) {
            Log::warning('AutoTopUpTrigger falhou (ignorado)', ['user_id' => $user->id, 'erro' => $e->getMessage()]);
        }
    }
}
