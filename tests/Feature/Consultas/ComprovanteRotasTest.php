<?php

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

it('dono baixa comprovante de certidão e não-dono recebe 404', function () {
    $dono = User::factory()->create();
    $intruso = User::factory()->create();
    $lote = ConsultaLote::create([
        'user_id' => $dono->id,
        'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1,
        'creditos_cobrados' => 2,
        'tab_id' => 'tab-rota',
    ]);
    $path = "comprovantes/{$dono->id}/2026/07/teste.pdf";
    Storage::disk('local')->put($path, '%PDF');
    $resultado = ConsultaResultado::create([
        'consulta_lote_id' => $lote->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => [
            'cnd_federal' => [
                'comprovante' => 'https://origem.example/cnd.pdf',
                'comprovante_arquivo' => $path,
            ],
        ],
    ]);
    $url = route('app.consulta.comprovante', [$resultado, 'cnd_federal']);

    $this->actingAs($dono)->get($url)
        ->assertOk()
        ->assertDownload('comprovante-cnd_federal.pdf');
    $this->actingAs($intruso)->get($url)->assertNotFound();
});

it('arquivo local ausente redireciona para a URL original da certidão', function () {
    $dono = User::factory()->create();
    $lote = ConsultaLote::create([
        'user_id' => $dono->id,
        'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1,
        'creditos_cobrados' => 2,
        'tab_id' => 'tab-fallback',
    ]);
    $original = 'https://origem.example/cnd.pdf';
    $resultado = ConsultaResultado::create([
        'consulta_lote_id' => $lote->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => [
            'cnd_federal' => [
                'comprovante' => $original,
                'comprovante_arquivo' => "comprovantes/{$dono->id}/ausente.pdf",
            ],
        ],
    ]);

    $this->actingAs($dono)
        ->get(route('app.consulta.comprovante', [$resultado, 'cnd_federal']))
        ->assertRedirect($original);
});

it('serve comprovante DF-e apenas ao dono', function () {
    $dono = User::factory()->create();
    $intruso = User::factory()->create();
    $path = "comprovantes/{$dono->id}/2026/07/nfe.pdf";
    Storage::disk('local')->put($path, '%PDF');
    $snapshot = NfeConsulta::create([
        'user_id' => $dono->id,
        'chave_acesso' => str_repeat('1', 44),
        'tipo_documento' => 'NFE',
        'status' => 'AUTORIZADA',
        'url_html' => 'https://origem.example/nfe.html',
        'payload' => ['comprovantes_arquivos' => ['html' => $path]],
    ]);
    $url = route('app.clearance.comprovante', ['nfe', $snapshot->id, 'html']);

    $this->actingAs($dono)->get($url)
        ->assertOk()
        ->assertDownload("comprovante-nfe-{$snapshot->id}-html.pdf");
    $this->actingAs($intruso)->get($url)->assertNotFound();
});
