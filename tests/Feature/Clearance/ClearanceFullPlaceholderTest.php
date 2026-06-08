<?php

use App\Jobs\ProcessarClearanceJob;
use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\NfeConsulta;
use App\Models\User;
use App\Services\ValidacaoContabilService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function fullPhUser(): User
{
    return User::factory()->create(['credits' => 1000]);
}

function fullPhCliente(User $u): Cliente
{
    return Cliente::firstOrCreate(
        ['user_id' => $u->id, 'is_empresa_propria' => true],
        ['tipo_pessoa' => 'PJ', 'documento' => '00000000000191', 'razao_social' => 'Propria']
    );
}

function fullPhEfdNota(User $u): EfdNota
{
    $cliente = fullPhCliente($u);
    $imp = EfdImportacao::firstOrCreate(
        ['user_id' => $u->id, 'cliente_id' => $cliente->id, 'tipo_efd' => 'EFD ICMS/IPI'],
        ['status' => 'concluido']
    );

    return EfdNota::create([
        'user_id' => $u->id, 'cliente_id' => $cliente->id, 'importacao_id' => $imp->id,
        'chave_acesso' => '35240413305697000150550000000404041953940992', 'modelo' => '55',
        'numero' => 1, 'serie' => '0', 'data_emissao' => '2026-01-15', 'tipo_operacao' => 'entrada',
        'valor_total' => 1000.00, 'valor_desconto' => 0, 'origem_arquivo' => 'fiscal', 'metadados' => [],
    ]);
}

it('com flag full OFF, tier=full é coagido para basico (não cobra o dobro)', function () {
    config()->set('clearance.full.habilitado', false);
    Bus::fake();
    Http::fake();
    $user = fullPhUser();
    $nota = fullPhEfdNota($user);

    actingAs($user)->postJson('/app/clearance/notas/validar', [
        'nota_ids' => [$nota->id], 'origens' => [$nota->id => 'efd'], 'tipo' => 'full', 'tab_id' => 'tab-full',
    ])->assertOk();

    $lote = ConsultaLote::latest('id')->first();
    expect($lote->creditos_cobrados)->toBe(ValidacaoContabilService::custoUnitarioPorTier('basico'));
    Bus::assertBatched(fn ($b) => collect($b->jobs)->every(fn ($j) => $j instanceof ProcessarClearanceJob));
});
