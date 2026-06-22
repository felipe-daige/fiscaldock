<?php
// tests/Feature/Admin/ImpersonacaoReadOnlyTest.php
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('GET passa durante impersonação', function () {
    $u = User::factory()->create();
    actingAs($u)->withSession(['impersonator_id' => 999])
        ->get('/app/dashboard')->assertOk();
});

it('POST é bloqueado (403) durante impersonação', function () {
    $u = User::factory()->create();
    actingAs($u)->withSession(['impersonator_id' => 999])
        ->post('/app/suporte', ['mensagem' => 'oi'])
        ->assertForbidden();
});

it('sem impersonação POST não é barrado pelo middleware', function () {
    // (sem impersonator_id; pode falhar por validação 4xx, mas não 403 do middleware)
    $u = User::factory()->create();
    $resp = actingAs($u)->post('/app/suporte', []);
    expect($resp->status())->not->toBe(403);
});
