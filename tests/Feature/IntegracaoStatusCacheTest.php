<?php

use App\Models\IntegracaoStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

it('cacheia problemasCount e invalida quando um status muda', function () {
    IntegracaoStatus::create([
        'chave' => 'infosimples', 'nome' => 'InfoSimples', 'grupo' => IntegracaoStatus::GRUPO_CONSULTAS,
        'ordem' => 1, 'status' => IntegracaoStatus::STATUS_FORA,
    ]);

    expect(IntegracaoStatus::problemasCount())->toBe(1)
        ->and(Cache::has('integracao_status:problemas_count'))->toBeTrue();

    // saved() deve bustar o cache — sem bust, ficaria 1 por até 60s
    IntegracaoStatus::create([
        'chave' => 'sefaz', 'nome' => 'SEFAZ', 'grupo' => IntegracaoStatus::GRUPO_CONSULTAS,
        'ordem' => 2, 'status' => IntegracaoStatus::STATUS_DEGRADADO,
    ]);

    expect(IntegracaoStatus::problemasCount())->toBe(2);

    // deleted() também busta
    IntegracaoStatus::where('chave', 'sefaz')->first()->delete();
    expect(IntegracaoStatus::problemasCount())->toBe(1);
});
