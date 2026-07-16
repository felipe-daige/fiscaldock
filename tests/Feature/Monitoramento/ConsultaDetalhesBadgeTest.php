<?php

use App\Models\MonitoramentoConsulta;
use App\Models\MonitoramentoPlano;
use App\Models\User;
use App\Support\CertidaoBadge;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

/**
 * O detalhe da consulta do histórico de monitoramento classifica certidões no BACKEND
 * via CertidaoBadge (fonte única). O front só renderiza label/hex — a comparação exata
 * antiga ('NEGATIVA' ? verde : vermelho) marcava "Positiva com efeitos de negativa"
 * como irregular.
 */
it('injeta badge canonico por fonte no detalhe da consulta', function () {
    $user = User::factory()->create();
    $consulta = MonitoramentoConsulta::create([
        'user_id' => $user->id,
        'plano_id' => MonitoramentoPlano::porCodigo('licitacao')->id,
        'tipo' => 'avulso',
        'status' => 'sucesso',
        'creditos_cobrados' => 0,
        'resultado' => [
            'cnpj' => '13305697000150',
            'razao_social' => 'Empresa Badge',
            'detalhes' => [
                'cnd_federal' => ['status' => 'POSITIVA COM EFEITOS DE NEGATIVA', 'validade' => '01/12/2026'],
                'fgts' => ['status' => 'Regular'],
                'cnd_estadual' => ['status' => 'POSITIVA'],
            ],
        ],
    ]);

    $resposta = actingAs($user)
        ->getJson('/app/monitoramento/consulta/'.$consulta->id)
        ->assertOk()
        ->json();

    $detalhes = $resposta['resultado']['detalhes'];

    // Semântica de certidão: "com efeitos de negativa" = REGULAR (o front antigo errava)
    expect($detalhes['cnd_federal']['badge']['label'])->toBe('Regular')
        ->and($detalhes['cnd_federal']['badge']['hex'])->toBe(CertidaoBadge::HEX_REGULAR)
        ->and($detalhes['fgts']['badge']['label'])->toBe('Regular')
        ->and($detalhes['cnd_estadual']['badge']['label'])->toBe('Irregular')
        ->and($detalhes['cnd_estadual']['badge']['hex'])->toBe(CertidaoBadge::HEX_IRREGULAR);
});

it('preserva o caso INDETERMINADO da CND Federal no badge', function () {
    $user = User::factory()->create();
    $consulta = MonitoramentoConsulta::create([
        'user_id' => $user->id,
        'plano_id' => MonitoramentoPlano::porCodigo('licitacao')->id,
        'tipo' => 'avulso',
        'status' => 'sucesso',
        'creditos_cobrados' => 0,
        'resultado' => [
            'cnpj' => '13305697000150',
            'razao_social' => 'Empresa 611',
            'detalhes' => [
                'cnd_federal' => [
                    'status' => 'INDETERMINADO',
                    'conseguiu_emitir' => false,
                    'mensagem' => 'As informações disponíveis na RFB são insuficientes para emissão da certidão.',
                ],
            ],
        ],
    ]);

    $detalhes = actingAs($user)
        ->getJson('/app/monitoramento/consulta/'.$consulta->id)
        ->assertOk()
        ->json()['resultado']['detalhes'];

    expect($detalhes['cnd_federal']['badge']['indeterminado'] ?? false)->toBeTrue()
        ->and($detalhes['cnd_federal']['badge']['hex'])->toBe(CertidaoBadge::HEX_INDETERMINADO)
        ->and($detalhes['cnd_federal']['badge']['motivo'])->not->toBeNull();
});
