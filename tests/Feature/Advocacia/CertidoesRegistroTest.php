<?php

use App\Models\Certidao;
use App\Models\ConsultaLote;
use App\Models\User;
use App\Services\Consultas\CertidaoRegistro;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'test-token');
});

function alvoParticipante(float $saldo = 10.0): array
{
    $user = User::factory()->create(['credits' => $saldo]);
    $pid = DB::table('participantes')->insertGetId([
        'user_id' => $user->id, 'documento' => '19131243000197', 'razao_social' => 'PART',
        'uf' => 'SP', 'created_at' => now(), 'updated_at' => now(),
    ]);

    return [$user, $pid];
}

it('e2e: consulta avulsa grava registro em certidoes com validade da resposta e PDF arquivado', function () {
    Http::fake([
        'minhareceita.org/*' => Http::response([
            'razao_social' => 'EMPRESA REAL', 'descricao_situacao_cadastral' => 'ATIVA', 'situacao_cadastral' => 2,
            'uf' => 'SP', 'municipio' => 'SAO PAULO', 'qsa' => [], 'cnaes_secundarios' => [],
        ], 200),
        'api.infosimples.com/*' => Http::response([
            'code' => 200, 'code_message' => 'ok',
            'data' => [[
                'tipo' => 'Negativa', 'numero_certidao' => 'STJ-777',
                'emissao_data' => '20/07/2026', 'validade_data' => '18/10/2026',
                'site_receipt' => 'https://receipts.infosimples.test/stj.pdf',
            ]],
            'data_count' => 1, 'errors' => [], 'site_receipts' => [],
        ], 200),
        'receipts.infosimples.test/*' => Http::response('%PDF-1.4 fake', 200, ['Content-Type' => 'application/pdf']),
    ]);

    [$user, $pid] = alvoParticipante();

    $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => [$pid],
        'fontes' => ['certidao_stj'],
        'tab_id' => 't-cert',
    ])->assertOk();

    $certidao = Certidao::where('user_id', $user->id)->where('tipo', 'certidao_stj')->first();
    expect($certidao)->not->toBeNull()
        ->and($certidao->alvo_documento)->toBe('19131243000197')
        ->and($certidao->alvo_tipo)->toBe('participante')
        ->and($certidao->participante_id)->toBe($pid)
        ->and($certidao->status)->toBe('Negativa')
        ->and($certidao->certidao_codigo)->toBe('STJ-777')
        ->and($certidao->emitida_em->format('d/m/Y'))->toBe('20/07/2026')
        ->and($certidao->valida_ate->format('d/m/Y'))->toBe('18/10/2026')
        ->and($certidao->validade_origem)->toBe('resposta')
        ->and($certidao->orgao)->toBe('Superior Tribunal de Justiça (STJ)')
        ->and($certidao->arquivo_path)->toStartWith("comprovantes/{$user->id}/");
});

it('sem data_validade na resposta aplica a regra fixa do orgao (cnd_federal 180d)', function () {
    [$user, $pid] = alvoParticipante();

    $certidao = app(CertidaoRegistro::class)->registrar(
        'cnd_federal',
        ['status' => 'Negativa', 'emissao_data' => '01/07/2026'],
        $user->id, 'participante', $pid, '19131243000197', 0,
    );

    expect($certidao->valida_ate->format('d/m/Y'))->toBe('28/12/2026')
        ->and($certidao->validade_origem)->toBe('regra_orgao');
});

it('status nao-emitida (INDETERMINADO/NAO_ENCONTRADA) nao sobrescreve registro anterior; nova emissao faz upsert', function () {
    [$user, $pid] = alvoParticipante();
    $registro = app(CertidaoRegistro::class);

    $registro->registrar('certidao_stj', [
        'status' => 'Negativa', 'data_validade' => '01/08/2026',
    ], $user->id, 'participante', $pid, '19131243000197', 0);

    // Tentativa posterior indeterminada: usuário segue com a certidão emitida em mãos.
    expect($registro->registrar('certidao_stj', ['status' => 'INDETERMINADO'], $user->id, 'participante', $pid, '19131243000197', 0))->toBeNull();
    expect(Certidao::where('user_id', $user->id)->count())->toBe(1)
        ->and(Certidao::first()->status)->toBe('Negativa');

    // Re-emissão de verdade sobrescreve a MESMA linha (unique user+documento+tipo).
    $registro->registrar('certidao_stj', [
        'status' => 'Positiva', 'data_validade' => '01/12/2026',
    ], $user->id, 'participante', $pid, '19131243000197', 0);

    expect(Certidao::where('user_id', $user->id)->count())->toBe(1)
        ->and(Certidao::first()->status)->toBe('Positiva')
        ->and(Certidao::first()->valida_ate->format('d/m/Y'))->toBe('01/12/2026');
});

it('fonte de lista sem validade registra sem valida_ate (nao entra em alerta)', function () {
    [$user, $pid] = alvoParticipante();

    $certidao = app(CertidaoRegistro::class)->registrar(
        'protestos',
        ['status' => 'Negativa', 'nada_consta' => true, 'total_registros' => 0],
        $user->id, 'participante', $pid, '19131243000197', 0,
    );

    expect($certidao->valida_ate)->toBeNull()
        ->and($certidao->validade_origem)->toBeNull();
});

it('lote de PLANO tambem registra certidoes (nao e exclusivo do avulso)', function () {
    // O hook fica no ProcessarConsultaJob::consultarFonte — qualquer lote que consulte uma
    // FonteCertidaoInfoSimples alimenta o registro. Aqui basta cravar o serviço direto com
    // consulta_lote_id de lote de plano.
    [$user, $pid] = alvoParticipante();
    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'plano_id' => null, 'status' => 'concluido',
        'total_participantes' => 1, 'creditos_cobrados' => 0, 'tab_id' => 't',
    ]);

    $certidao = app(CertidaoRegistro::class)->registrar(
        'cndt', ['status' => 'Negativa', 'data_validade' => '10/01/2027'],
        $user->id, 'participante', $pid, '19131243000197', $lote->id,
    );

    expect($certidao->consulta_lote_id)->toBe($lote->id);
});
