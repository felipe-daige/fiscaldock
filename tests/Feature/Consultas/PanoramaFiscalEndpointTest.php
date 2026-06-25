<?php

use App\Models\Cliente;
use App\Models\EfdImportacao;
use App\Models\Participante;
use App\Models\User;
use Illuminate\Support\Facades\DB;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function notaFiscalEndpoint(array $attrs): void
{
    $userId    = $attrs['user_id'];
    $clienteId = $attrs['cliente_id'];

    if (! isset($attrs['importacao_id'])) {
        $imp = EfdImportacao::create([
            'user_id'     => $userId,
            'cliente_id'  => $clienteId,
            'tipo_efd'    => 'EFD ICMS/IPI',
            'filename'    => 'test.txt',
            'status'      => 'concluido',
            'iniciado_em' => now(),
        ]);
        $attrs['importacao_id'] = $imp->id;
    }

    DB::table('efd_notas')->insert(array_merge([
        'origem_arquivo' => 'fiscal',
        'cancelada'      => false,
        'modelo'         => '55',
        'numero'         => random_int(1, 9999999),
        'created_at'     => now(),
        'updated_at'     => now(),
    ], $attrs));
}

it('retorna panorama do participante do dono', function () {
    $user    = User::factory()->create();
    $cliente = Cliente::create(['user_id' => $user->id, 'documento' => '99888777000166', 'razao_social' => 'MINHA EMPRESA']);
    $part    = Participante::create(['user_id' => $user->id, 'documento' => '11222333000181', 'razao_social' => 'ACME LTDA']);
    notaFiscalEndpoint([
        'user_id'        => $user->id,
        'cliente_id'     => $cliente->id,
        'participante_id' => $part->id,
        'tipo_operacao'  => 'entrada',
        'valor_total'    => 100,
        'data_emissao'   => '2026-01-10',
    ]);

    $this->actingAs($user)
        ->getJson("/app/panorama-fiscal?scope=participante&id={$part->id}")
        ->assertOk()
        ->assertJsonPath('panorama.escopo', 'participante')
        ->assertJsonPath('panorama.kpis.total_comprado', 100);
});

it('404 para participante de outro usuario', function () {
    $dono  = User::factory()->create();
    $outro = User::factory()->create();
    $part  = Participante::create(['user_id' => $outro->id, 'documento' => '11222333000181', 'razao_social' => 'ACME LTDA']);

    $this->actingAs($dono)
        ->getJson("/app/panorama-fiscal?scope=participante&id={$part->id}")
        ->assertNotFound();
});

it('422 para scope invalido', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->getJson('/app/panorama-fiscal?scope=foo&id=1')
        ->assertStatus(422);
});
