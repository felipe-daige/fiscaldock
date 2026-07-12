<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('alertas:recalcular')->dailyAt('06:00');
Schedule::command('trial:expire-credits')->dailyAt('01:00');
Schedule::command('importacao:expirar-travadas')->everyMinute();
Schedule::command('assinatura:conceder-creditos')->dailyAt('03:30');
Schedule::command('monitoramento:executar-pendentes')->dailyAt('04:00')->withoutOverlapping();
// LGPD fase 2.3: DRY-RUN diário (sem --force) — só lista/loga quem seria anonimizado.
// A anonimização real é irreversível em prod, então continua manual (`--force`) de propósito.
Schedule::command('lgpd:processar-exclusoes')->dailyAt('05:30');
// Resumo semanal: segunda 08:00, depois do recalcular de 06:00 daquele dia.
Schedule::command('alertas:enviar-resumo-semanal')->weeklyOn(1, '08:00')->withoutOverlapping();
