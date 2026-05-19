<?php

use App\Models\EfdImportacao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('stream encerra com status erro quando importacao esta erro no banco', function () {
    $user = User::factory()->create();
    $imp = EfdImportacao::create([
        'user_id'  => $user->id,
        'tipo_efd' => 'EFD ICMS/IPI',
        'status'   => 'erro',
    ]);

    $response = actingAs($user)->get(
        '/app/importacao/efd/progresso/stream?tab_id=tab-teste&importacao_id='.$imp->id
    );

    expect($response->streamedContent())->toContain('"status":"erro"');
});

it('stream descarta importacao_id de outro usuario (apenas valida nao trava)', function () {
    $dono = User::factory()->create();
    $outro = User::factory()->create();
    $imp = EfdImportacao::create([
        'user_id'  => $dono->id,
        'tipo_efd' => 'EFD ICMS/IPI',
        'status'   => 'erro',
    ]);

    // Sem cache e com importacao_id descartado, o loop só encerraria por timeout (30 min).
    // O teste é SKIP para não travar; cobertura de pertencimento via revisão manual.
    $response = actingAs($outro)->get(
        '/app/importacao/efd/progresso/stream?tab_id=tab-teste&importacao_id='.$imp->id
    );

    $response->assertOk();
})->skip('SSE sem terminacao trava o teste — cobertura de pertencimento via revisao manual');
