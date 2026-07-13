<?php

use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\NfeConsulta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
});

function rotularMakeLote(User $user): ConsultaLote
{
    return ConsultaLote::create([
        'user_id' => $user->id,
        'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1,
        'creditos_cobrados' => 2,
        'tab_id' => 'tab-rotular',
    ]);
}

it('renomeia comprovante legado de consulta com rótulo e atualiza o JSONB', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create([
        'user_id' => $user->id,
        'nome' => 'Empresa Alvo',
        'documento' => '04252011000110',
    ]);
    $legado = "comprovantes/{$user->id}/2026/07/01JLEGADOULID.html";
    Storage::disk('local')->put($legado, '<html></html>');

    $resultado = ConsultaResultado::create([
        'consulta_lote_id' => rotularMakeLote($user)->id,
        'cliente_id' => $cliente->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => [
            'cnd_federal' => ['comprovante_arquivo' => $legado],
        ],
    ]);

    $this->artisan('comprovantes:rotular-legado', ['--user' => $user->id])
        ->expectsOutput('Renomeados: 1')
        ->assertSuccessful();

    $novo = $resultado->fresh()->resultado_dados['cnd_federal']['comprovante_arquivo'];
    expect(basename($novo))->toBe('CND Federal 04252011000110__01JLEGADOULID.html');
    Storage::disk('local')->assertExists($novo);
    Storage::disk('local')->assertMissing($legado);
});

it('renomeia comprovante legado de snapshot NF-e e atualiza o payload', function () {
    $user = User::factory()->create();
    $chave = str_repeat('3524', 11);
    $legado = "comprovantes/{$user->id}/2026/07/01JSNAPULID.html";
    Storage::disk('local')->put($legado, '<html></html>');

    $snapshot = NfeConsulta::create([
        'user_id' => $user->id,
        'chave_acesso' => $chave,
        'tipo_documento' => 'NFE',
        'status' => 'AUTORIZADA',
        'consultado_em' => now(),
        'payload' => ['comprovantes_arquivos' => ['html' => $legado]],
    ]);

    $this->artisan('comprovantes:rotular-legado', ['--user' => $user->id])
        ->expectsOutput('Renomeados: 1')
        ->assertSuccessful();

    $novo = data_get($snapshot->fresh()->payload, 'comprovantes_arquivos.html');
    expect(basename($novo))->toBe("NF-e {$chave} - espelho__01JSNAPULID.html");
    Storage::disk('local')->assertExists($novo);
});

it('ignora arquivos já rotulados, de outros usuários e respeita dry-run', function () {
    $user = User::factory()->create();
    $outro = User::factory()->create();

    $rotulado = "comprovantes/{$user->id}/2026/07/CNDT 04252011000110__01JOK.html";
    Storage::disk('local')->put($rotulado, '<html></html>');
    ConsultaResultado::create([
        'consulta_lote_id' => rotularMakeLote($user)->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => ['cndt' => ['comprovante_arquivo' => $rotulado]],
    ]);

    $legadoOutro = "comprovantes/{$outro->id}/2026/07/01JOUTRO.html";
    Storage::disk('local')->put($legadoOutro, '<html></html>');
    ConsultaResultado::create([
        'consulta_lote_id' => rotularMakeLote($outro)->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => ['cnd_estadual' => ['comprovante_arquivo' => $legadoOutro]],
    ]);

    $this->artisan('comprovantes:rotular-legado', ['--user' => $user->id])
        ->expectsOutput('Renomeados: 0')
        ->assertSuccessful();
    Storage::disk('local')->assertExists($rotulado);
    Storage::disk('local')->assertExists($legadoOutro);

    $this->artisan('comprovantes:rotular-legado', ['--user' => $outro->id, '--dry-run' => true])
        ->expectsOutput('Renomeáveis (dry-run): 1')
        ->assertSuccessful();
    Storage::disk('local')->assertExists($legadoOutro);
});
