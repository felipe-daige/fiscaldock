<?php

use App\Models\IntegracaoStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function integracao(array $attrs = []): IntegracaoStatus
{
    return IntegracaoStatus::create(array_merge([
        'chave' => 'x'.uniqid(), 'nome' => 'X', 'grupo' => 'consultas', 'ordem' => 1,
        'status' => 'operacional',
    ], $attrs));
}

it('statusesValidos retorna os 4 níveis', function () {
    expect(IntegracaoStatus::statusesValidos())
        ->toBe(['operacional', 'degradado', 'fora', 'manutencao']);
});

it('scope grupo filtra por grupo', function () {
    integracao(['grupo' => 'consultas']);
    integracao(['grupo' => 'plataforma']);
    expect(IntegracaoStatus::query()->grupo('plataforma')->count())->toBe(1);
});

it('scope problemas pega tudo != operacional', function () {
    integracao(['status' => 'operacional']);
    integracao(['status' => 'fora']);
    integracao(['status' => 'degradado']);
    expect(IntegracaoStatus::query()->problemas()->count())->toBe(2);
});

it('problemasCount conta integrações com problema', function () {
    integracao(['status' => 'operacional']);
    integracao(['status' => 'manutencao']);
    expect(IntegracaoStatus::problemasCount())->toBe(1);
});

it('accessors label/emoji/corClasse mapeiam por status', function () {
    expect(integracao(['status' => 'fora'])->label)->toBe('Fora do ar');
    expect(integracao(['status' => 'operacional'])->emoji)->toBe('🟢');
    expect(integracao(['status' => 'degradado'])->corClasse)->toContain('amber');
});

it('relação atualizadoPor resolve o user', function () {
    $u = User::factory()->create();
    expect(integracao(['atualizado_por' => $u->id])->atualizadoPor->id)->toBe($u->id);
});
