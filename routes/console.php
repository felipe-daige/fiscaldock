<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('alertas:recalcular')->dailyAt('06:00');
Schedule::command('trial:expirar-saldo')->dailyAt('01:00');
Schedule::command('importacao:expirar-travadas')->everyMinute();
// Fase 4 advocacia: ÚNICO dispatcher do follow-up das certidões de 2 etapas. everyMinute (não
// hourly) porque o retry técnico agenda re-conferências em 15s/30s; a chamada externa só acontece
// pros pedidos realmente vencidos (claim atômico), então o tick de 1 min é barato. withoutOverlapping
// evita dois sweeps concorrentes empilhando dispatches.
Schedule::command('certidoes:verificar-pedidos')->everyMinute()->withoutOverlapping();
Schedule::command('assinatura:conceder-saldo')->dailyAt('03:30');
Schedule::command('monitoramento:executar-pendentes')->dailyAt('04:00')->withoutOverlapping();
// LGPD fase 2.3: DRY-RUN diário (sem --force) — só lista/loga quem seria anonimizado.
// A anonimização real é irreversível em prod, então continua manual (`--force`) de propósito.
Schedule::command('lgpd:processar-exclusoes')->dailyAt('05:30');
// Resumo semanal: segunda 08:00, depois do recalcular de 06:00 daquele dia.
Schedule::command('alertas:enviar-resumo-semanal')->weeklyOn(1, '08:00')->withoutOverlapping();
