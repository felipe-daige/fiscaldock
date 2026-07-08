<?php

use App\Models\IntegracaoStatus;
use Database\Seeders\IntegracaoStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('semeia 11 integrações (7 consultas + 4 plataforma)', function () {
    (new IntegracaoStatusSeeder())->run();
    expect(IntegracaoStatus::count())->toBe(11);
    expect(IntegracaoStatus::query()->grupo('consultas')->count())->toBe(7);
    expect(IntegracaoStatus::query()->grupo('plataforma')->count())->toBe(4);
    expect(IntegracaoStatus::where('chave', 'whatsapp')->value('nome'))->toBe('WhatsApp');
    expect(IntegracaoStatus::where('chave', 'mensageria_email')->value('nome'))->toBe('Mensageria / E-mail');
});

it('é idempotente (rodar 2x não duplica)', function () {
    (new IntegracaoStatusSeeder())->run();
    (new IntegracaoStatusSeeder())->run();
    expect(IntegracaoStatus::count())->toBe(11);
});

it('não sobrescreve status/mensagem ajustados pelo admin', function () {
    (new IntegracaoStatusSeeder())->run();
    IntegracaoStatus::where('chave', 'cnd_federal')
        ->update(['status' => 'fora', 'mensagem' => 'Receita instável']);

    (new IntegracaoStatusSeeder())->run();

    $cnd = IntegracaoStatus::where('chave', 'cnd_federal')->first();
    expect($cnd->status)->toBe('fora');
    expect($cnd->mensagem)->toBe('Receita instável');
});
