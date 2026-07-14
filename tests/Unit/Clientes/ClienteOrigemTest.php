<?php

use App\Models\Cliente;
use App\Models\EfdImportacao;
use App\Models\User;
use App\Models\XmlImportacao;
use App\Support\ClienteOrigem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('aponta para o resultado da primeira importação EFD vinculada ao cliente', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create([
        'user_id' => $user->id,
        'documento' => '12345678000195',
        'tipo_pessoa' => 'PJ',
        'razao_social' => 'Cliente EFD',
    ]);
    $importacao = EfdImportacao::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'tipo_efd' => 'EFD ICMS/IPI',
        'filename' => 'sped-fiscal.txt',
    ]);

    expect(ClienteOrigem::dados($cliente))->toMatchArray([
        'label' => 'EFD ICMS/IPI',
        'arquivo' => 'sped-fiscal.txt',
        'url' => "/app/importacao/efd/{$importacao->id}",
    ]);
});

it('aponta para o resultado XML e mantém cadastro manual sem link', function () {
    $user = User::factory()->create();
    $clienteXml = Cliente::create([
        'user_id' => $user->id,
        'documento' => '12345678000196',
        'tipo_pessoa' => 'PJ',
        'razao_social' => 'Cliente XML',
        'origem_tipo' => 'NFE',
    ]);
    $importacao = XmlImportacao::create([
        'user_id' => $user->id,
        'cliente_id' => $clienteXml->id,
        'tipo_documento' => 'NFE',
        'filename' => 'lote-nfe.zip',
    ]);
    $manual = Cliente::create([
        'user_id' => $user->id,
        'documento' => '12345678000197',
        'tipo_pessoa' => 'PJ',
        'razao_social' => 'Cliente Manual',
        'origem_tipo' => 'MANUAL',
    ]);

    expect(ClienteOrigem::dados($clienteXml))->toMatchArray([
        'label' => 'XML NF-e',
        'arquivo' => 'lote-nfe.zip',
        'url' => "/app/importacao/xml/{$importacao->id}",
    ])->and(ClienteOrigem::dados($manual))->toMatchArray([
        'label' => 'Manual',
        'url' => null,
    ]);
});
