<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Processa um auto top-up por saldo baixo (cobrança on-demand). Lógica em handle():
 * lock + re-check + teto diário + cobrança via CobrarAutoTopUp.
 */
class ProcessarAutoTopUpJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $userId) {}

    public function handle(): void
    {
        // Implementado na Task 7.
    }
}
