<?php

use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('backfill arquiva o comprovante vivo e pula o expirado', function () {
    Storage::fake('local');
    config()->set('consultas.comprovantes.arquivar', true);
    Http::fake([
        '*' => Http::response('%PDF', 200, ['Content-Type' => 'application/pdf']),
    ]);

    $user = User::factory()->create();
    $lote = ConsultaLote::create([
        'user_id' => $user->id,
        'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1,
        'creditos_cobrados' => 2,
        'tab_id' => 'tab-backfill',
    ]);
    $vivo = now()->addHour()->timestamp;
    $expirado = now()->subHour()->timestamp;
    $resultado = ConsultaResultado::create([
        'consulta_lote_id' => $lote->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => [
            'cnd_federal' => [
                'comprovante' => "https://host/infosimples-storage/sig/{$vivo}/salt/vivo.pdf",
            ],
            'cndt' => [
                'comprovante' => "https://host/infosimples-storage/sig/{$expirado}/salt/morto.pdf",
            ],
        ],
    ]);

    $this->artisan('consultas:arquivar-comprovantes', ['--limite' => 10])
        ->expectsOutput('Arquivados: 1')
        ->expectsOutput('Pulados-expirados: 1')
        ->expectsOutput('Falhas: 0')
        ->assertSuccessful();

    $dados = $resultado->fresh()->resultado_dados;
    expect($dados['cnd_federal']['comprovante_arquivo'])->toStartWith("comprovantes/{$user->id}/")
        ->and($dados['cndt'])->not->toHaveKey('comprovante_arquivo');
    Storage::disk('local')->assertExists($dados['cnd_federal']['comprovante_arquivo']);
});
