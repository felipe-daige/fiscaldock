<?php

use App\Models\EfdImportacao;
use App\Models\Participante;
use App\Models\User;
use App\Support\ParticipanteOrigem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('deriva EFD PIS COFINS e arquivo pelo vínculo mesmo com origem_tipo nula', function () {
    $user = User::factory()->create();
    $importacao = EfdImportacao::create([
        'user_id' => $user->id,
        'tipo_efd' => 'EFD PIS/COFINS',
        'filename' => 'sped-contribuicoes.txt',
    ]);
    $participante = Participante::create([
        'user_id' => $user->id,
        'documento' => '12345678901',
        'importacao_efd_id' => $importacao->id,
        'origem_tipo' => null,
    ]);

    expect(ParticipanteOrigem::dados($participante))
        ->toMatchArray([
            'label' => 'EFD PIS/COFINS',
            'hex' => '#7c3aed',
            'arquivo' => 'sped-contribuicoes.txt',
            'url' => "/app/importacao/efd/{$importacao->id}",
        ]);
});

it('só classifica como manual quando o campo declara cadastro manual sem importação', function () {
    $user = User::factory()->create();
    $participante = Participante::create([
        'user_id' => $user->id,
        'documento' => '12345678901',
        'origem_tipo' => 'MANUAL',
    ]);

    expect(ParticipanteOrigem::dados($participante))
        ->toMatchArray(['label' => 'Manual', 'url' => null]);
});
