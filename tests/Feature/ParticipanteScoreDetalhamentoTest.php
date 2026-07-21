<?php

use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\User;
use App\Services\RiskScoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function planoScoreParticipante(): MonitoramentoPlano
{
    return MonitoramentoPlano::ativos()->first() ?? MonitoramentoPlano::create([
        'nome' => 'Gratuito', 'codigo' => 'gratuito', 'ativo' => true,
        'creditos_por_consulta' => 0, 'consultas_incluidas' => [], 'etapas' => [],
    ]);
}

it('perfil do participante mostra detalhamento do score quando há consulta', function () {
    $user = User::factory()->create();
    $part = Participante::create([
        'user_id' => $user->id, 'documento' => '11111111000111',
        'razao_social' => 'FORNECEDOR X', 'uf' => 'SP', 'crt' => '3',
    ]);
    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'plano_id' => planoScoreParticipante()->id, 'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1, 'creditos_cobrados' => 0, 'tab_id' => 'tab-'.uniqid(), 'processado_em' => now(),
    ]);
    $lote->participantes()->attach([$part->id]);
    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id, 'participante_id' => $part->id, 'status' => ConsultaResultado::STATUS_SUCESSO,
        'consultado_em' => now(),
        'resultado_dados' => ['situacao_cadastral' => 'ATIVA', 'cnd_federal' => ['status' => 'Negativa']],
    ]);

    $resp = $this->actingAs($user)->get("/app/participante/{$part->id}");
    $resp->assertOk();
    $resp->assertSee('Score de Risco');
    $resp->assertSee('Situação Cadastral');
    $resp->assertSee('Peso efetivo:');
});

it('perfil do participante sem consulta mostra empty-state do score', function () {
    $user = User::factory()->create();
    $part = Participante::create([
        'user_id' => $user->id, 'documento' => '22222222000122', 'razao_social' => 'SEM CONSULTA', 'uf' => 'SP',
    ]);
    $resp = $this->actingAs($user)->get("/app/participante/{$part->id}");
    $resp->assertOk();
    $resp->assertSee('Score não calculado');
});

it('perfil consolida certidões anteriores quando a consulta mais recente é apenas cadastral', function () {
    $user = User::factory()->create();
    $part = Participante::create([
        'user_id' => $user->id,
        'documento' => '08906558000142',
        'razao_social' => 'PARTICIPANTE COM CONSULTA PARCIAL',
        'uf' => 'MS',
        'latitude' => -15.0,
        'longitude' => -47.0,
    ]);
    $plano = planoScoreParticipante();

    $loteCompleto = ConsultaLote::create([
        'user_id' => $user->id,
        'plano_id' => $plano->id,
        'status' => ConsultaLote::STATUS_CONCLUIDO,
        'total_participantes' => 1,
        'creditos_cobrados' => 5,
        'tab_id' => 'completo-'.uniqid(),
        'processado_em' => now()->subDay(),
    ]);
    $loteCompleto->participantes()->attach([$part->id]);

    $dadosCompletos = [
        'situacao_cadastral' => 'ATIVA',
        'cnd_federal' => ['status' => 'Negativa', 'certidao_codigo' => 'FED-215', 'conseguiu_emitir' => true],
        'cnd_estadual' => ['status' => 'Positiva', 'certidao_codigo' => 'EST-215', 'conseguiu_emitir' => true],
        'cnd_municipal' => ['status' => 'INDISPONIVEL', 'mensagem' => 'Município sem cobertura online.'],
        'crf_fgts' => ['status' => 'REGULAR', 'certidao_codigo' => 'FGTS-215', 'conseguiu_emitir' => true],
        'cndt' => ['status' => 'Negativa', 'certidao_codigo' => 'CNDT-215', 'conseguiu_emitir' => true],
        'sintegra' => [
            'situacao' => 'HABILITADO',
            'inscricao_estadual' => '28.736.034-2',
            'comprovante' => 'https://example.com/sintegra-215.pdf',
        ],
        'consultas_realizadas' => ['situacao_cadastral', 'cnd_federal', 'cnd_estadual', 'cnd_municipal', 'crf_fgts', 'cndt', 'sintegra'],
    ];
    ConsultaResultado::create([
        'consulta_lote_id' => $loteCompleto->id,
        'participante_id' => $part->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'consultado_em' => now()->subDay(),
        'resultado_dados' => $dadosCompletos,
    ]);
    app(RiskScoreService::class)->atualizarScore($part, $dadosCompletos);

    $loteParcial = ConsultaLote::create([
        'user_id' => $user->id,
        'plano_id' => $plano->id,
        'status' => ConsultaLote::STATUS_CONCLUIDO,
        'total_participantes' => 1,
        'creditos_cobrados' => 0,
        'tab_id' => 'parcial-'.uniqid(),
        'processado_em' => now(),
    ]);
    $loteParcial->participantes()->attach([$part->id]);

    $dadosParciais = [
        'situacao_cadastral' => 'ATIVA',
        'razao_social' => 'PARTICIPANTE ATUALIZADO',
        'consultas_realizadas' => ['situacao_cadastral', 'dados_cadastrais'],
    ];
    ConsultaResultado::create([
        'consulta_lote_id' => $loteParcial->id,
        'participante_id' => $part->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'consultado_em' => now(),
        'resultado_dados' => $dadosParciais,
    ]);
    app(RiskScoreService::class)->atualizarScore($part, $dadosParciais);

    $scorePersistido = \App\Models\ParticipanteScore::where('participante_id', $part->id)->firstOrFail();
    expect($scorePersistido->dados_consultados['consultas_realizadas'])
        ->toContain('cnd_federal', 'cnd_estadual', 'cnd_municipal', 'crf_fgts', 'cndt', 'sintegra');

    $this->actingAs($user)
        ->get("/app/participante/{$part->id}")
        ->assertOk()
        ->assertSee('Certidões e Cadastros Fiscais')
        ->assertSee('FED-215')
        ->assertSee('EST-215')
        ->assertSee('FGTS-215')
        ->assertSee('CNDT-215')
        ->assertSee('CND Federal')
        ->assertSee('CND Estadual')
        ->assertSee('CND Municipal')
        ->assertSee('CRF FGTS (Caixa)')
        ->assertSee('CNDT (débitos trabalhistas)')
        ->assertSee('Certidões e Cadastros Fiscais')
        ->assertSee('SINTEGRA')
        ->assertSee('28.736.034-2')
        ->assertSee('Ver comprovante')
        ->assertSee('href="https://example.com/sintegra-215.pdf"', false);
});

it('perfil de CPF mostra origem EFD real e risco de crédito não avaliado', function () {
    $user = User::factory()->create();
    $importacao = \App\Models\EfdImportacao::create([
        'user_id' => $user->id,
        'tipo_efd' => 'EFD PIS/COFINS',
        'filename' => 'SPED-CONTRIBUICOES-01-2024.txt',
    ]);
    $part = Participante::create([
        'user_id' => $user->id,
        'documento' => '12345678901',
        'razao_social' => 'PESSOA DO SPED',
        'importacao_efd_id' => $importacao->id,
        'origem_tipo' => null,
        'latitude' => -15.0,
        'longitude' => -47.0,
    ]);

    $resp = $this->actingAs($user)->get("/app/participante/{$part->id}");

    $resp->assertOk()
        ->assertSee('EFD PIS/COFINS')
        ->assertSee('SPED-CONTRIBUICOES-01-2024.txt')
        ->assertSee('Risco de Crédito')
        ->assertSee('Risco de crédito não avaliado')
        ->assertDontSee('Faça uma Consulta de CNPJ deste CNPJ');
});
