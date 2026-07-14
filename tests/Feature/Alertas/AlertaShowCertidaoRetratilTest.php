<?php

use App\Models\Alerta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Detalhe do alerta certidao_positiva exibe as certidões no card retrátil DS
// (mesmo padrão do participante/lote), não mais em lista chapada.

test('detalhe do alerta certidao_positiva usa card retratil DS', function () {
    $user = User::factory()->create();

    $alerta = Alerta::create([
        'user_id' => $user->id,
        'tipo' => 'certidao_positiva',
        'categoria' => 'compliance',
        'severidade' => 'alta',
        'titulo' => 'Certidão positiva em fornecedor',
        'descricao' => 'CND Estadual positiva',
        'status' => 'aberto',
        'hash' => 'hash-teste-cert-retratil',
        'detalhes' => [
            'razao_social' => 'FORNECEDOR X',
            'documento' => '08070566001769',
            'certidoes' => [
                ['label' => 'CND Estadual', 'status' => 'Positiva', 'severidade' => 'alta'],
                ['label' => 'CNDT', 'status' => 'Positiva com efeitos de negativa', 'severidade' => 'media'],
            ],
            'valor_total' => 0,
        ],
    ]);

    $response = $this->actingAs($user)->get('/app/alertas/'.$alerta->id);

    $response->assertOk();
    $response->assertSee('CND Estadual');
    $response->assertSee('CNDT');
    // Card retrátil DS: chevron + colapsado por padrão + toggle "Ver tudo".
    $response->assertSee('detalhe-chevron', false);
    $response->assertSee('aria-expanded="false"', false);
    $response->assertSee('Ver tudo');
});

test('detalhe do alerta certidao_vencendo usa card retratil DS com prazos', function () {
    $user = User::factory()->create();

    $alerta = Alerta::create([
        'user_id' => $user->id,
        'tipo' => 'certidao_vencendo',
        'categoria' => 'compliance',
        'severidade' => 'media',
        'titulo' => 'Certidões próximas do vencimento',
        'descricao' => 'CND Estadual vence em breve',
        'status' => 'aberto',
        'hash' => 'hash-teste-cert-vencendo',
        'detalhes' => [
            'razao_social' => 'FORNECEDOR Y',
            'documento' => '08070566001769',
            'certidoes' => [
                ['label' => 'CND Estadual', 'validade' => '20/07/2026', 'dias' => 6, 'vencida' => false],
                ['label' => 'CRF FGTS', 'validade' => '01/07/2026', 'dias' => -13, 'vencida' => true],
            ],
        ],
    ]);

    $response = $this->actingAs($user)->get('/app/alertas/'.$alerta->id);

    $response->assertOk();
    $response->assertSee('Prazos das certidões');
    $response->assertSee('CND Estadual');
    $response->assertSee('Vence em 6 dias');
    $response->assertSee('Vencida há 13 dias');
    $response->assertSee('20/07/2026');
    // Card retrátil DS.
    $response->assertSee('detalhe-chevron', false);
    $response->assertSee('aria-expanded="false"', false);
});
