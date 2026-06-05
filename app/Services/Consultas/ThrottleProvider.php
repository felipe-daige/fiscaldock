<?php

namespace App\Services\Consultas;

use Illuminate\Support\Facades\Cache;

class ThrottleProvider
{
    /** Espera o necessário para respeitar a janela mínima entre chamadas ao provider. */
    public function aguardar(string $provider): void
    {
        $rps = (float) config("consultas.providers.{$provider}.rate_limit_por_segundo", 0);
        if ($rps <= 0) {
            return; // sem limite (ex: minhareceita)
        }

        $janelaMs = (int) ceil(1000 / $rps);
        $key = "consultas:throttle:{$provider}";

        $lock = Cache::lock("{$key}:lock", 10);
        $lock->block(10);
        try {
            $ultimaMs = (int) Cache::get($key, 0);
            $agoraMs = (int) (microtime(true) * 1000);
            $faltaMs = ($ultimaMs + $janelaMs) - $agoraMs;
            if ($faltaMs > 0) {
                usleep($faltaMs * 1000);
                $agoraMs += $faltaMs;
            }
            Cache::put($key, $agoraMs, 60);
        } finally {
            $lock->release();
        }
    }
}
