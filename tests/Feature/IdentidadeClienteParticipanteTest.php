<?php

use App\Models\Cliente;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\Participante;
use App\Models\User;
use App\Services\Identidades\IdentidadeClienteParticipanteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('movimento: participante COM nota que vira cliente é preservado (só carimba a contraparte)', function () {
    $user = User::factory()->create();
    $documento = '97551165000193';

    $empresaEscriturada = Cliente::create([
        'user_id' => $user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '44373108000600',
        'razao_social' => 'Empresa escriturada',
    ]);

    $participante = Participante::create([
        'user_id' => $user->id,
        'documento' => $documento,
        'tipo_documento' => 'PJ',
        'razao_social' => 'HIDRATOP',
    ]);

    $importacao = EfdImportacao::create([
        'user_id' => $user->id,
        'cliente_id' => $empresaEscriturada->id,
        'tipo_efd' => 'EFD ICMS/IPI',
        'filename' => 'efd.txt',
        'status' => 'concluido',
        'iniciado_em' => now(),
    ]);

    $nota = EfdNota::create([
        'user_id' => $user->id,
        'cliente_id' => $empresaEscriturada->id,
        'participante_id' => $participante->id,
        'importacao_id' => $importacao->id,
        'chave_acesso' => str_repeat('1', 44),
        'modelo' => '55',
        'numero' => '1',
        'serie' => '1',
        'data_emissao' => '2026-01-01',
        'tipo_operacao' => 'entrada',
        'valor_total' => 100,
        'origem_arquivo' => 'fiscal',
    ]);

    $loteId = DB::table('consulta_lotes')->insertGetId([
        'user_id' => $user->id,
        'status' => 'concluido',
        'total_participantes' => 1,
        'creditos_cobrados' => 5,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $resultadoId = DB::table('consulta_resultados')->insertGetId([
        'consulta_lote_id' => $loteId,
        'participante_id' => $participante->id,
        'resultado_dados' => json_encode(['situacao_cadastral' => 'ATIVA']),
        'status' => 'sucesso',
        'consultado_em' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $scoreId = DB::table('participante_scores')->insertGetId([
        'user_id' => $user->id,
        'participante_id' => $participante->id,
        'score_total' => 91,
        'classificacao' => 'baixo',
        'dados_consultados' => json_encode(['cnd_federal' => ['status' => 'NEGATIVA']]),
        'ultima_consulta_em' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $certidaoId = DB::table('certidoes')->insertGetId([
        'user_id' => $user->id,
        'participante_id' => $participante->id,
        'alvo_tipo' => 'participante',
        'alvo_documento' => $documento,
        'tipo' => 'cnd_federal',
        'status' => 'NEGATIVA',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $cliente = Cliente::create([
        'user_id' => $user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => $documento,
        'razao_social' => 'HIDRATOP',
    ]);

    // Participante COM nota = movimento real: SOBREVIVE. A nota ganha o vínculo de cliente
    // (contraparte_cliente_id) mas mantém participante_id — o movimento segue visível nas telas
    // de participante. Consulta/score/certidão permanecem no participante.
    expect(Participante::whereKey($participante->id)->exists())->toBeTrue()
        ->and($nota->refresh()->participante_id)->toBe($participante->id)
        ->and($nota->contraparte_cliente_id)->toBe($cliente->id)
        ->and(DB::table('consulta_resultados')->where('id', $resultadoId)->value('participante_id'))->toBe($participante->id)
        ->and(DB::table('participante_scores')->where('id', $scoreId)->value('participante_id'))->toBe($participante->id)
        ->and(DB::table('certidoes')->where('id', $certidaoId)->value('participante_id'))->toBe($participante->id)
        ->and(app(IdentidadeClienteParticipanteService::class)->totalDuplicidades())->toBe(0);
});

it('cadastro-duplicado: participante SEM nota que vira cliente é consolidado (dados e IM migram)', function () {
    $user = User::factory()->create();
    $documento = '97551165000193';

    // Pura duplicata de cadastro: participante sem nenhuma nota, com IM já resolvida.
    $participante = Participante::create([
        'user_id' => $user->id,
        'documento' => $documento,
        'tipo_documento' => 'PJ',
        'razao_social' => 'HIDRATOP',
        'inscricao_municipal' => 'IM-42',
    ]);

    $loteId = DB::table('consulta_lotes')->insertGetId([
        'user_id' => $user->id, 'status' => 'concluido', 'total_participantes' => 1,
        'creditos_cobrados' => 5, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $resultadoId = DB::table('consulta_resultados')->insertGetId([
        'consulta_lote_id' => $loteId, 'participante_id' => $participante->id,
        'resultado_dados' => json_encode(['situacao_cadastral' => 'ATIVA']),
        'status' => 'sucesso', 'consultado_em' => now(), 'created_at' => now(), 'updated_at' => now(),
    ]);
    $scoreId = DB::table('participante_scores')->insertGetId([
        'user_id' => $user->id, 'participante_id' => $participante->id, 'score_total' => 91,
        'classificacao' => 'baixo', 'dados_consultados' => json_encode(['cnd_federal' => ['status' => 'NEGATIVA']]),
        'ultima_consulta_em' => now(), 'created_at' => now(), 'updated_at' => now(),
    ]);
    $certidaoId = DB::table('certidoes')->insertGetId([
        'user_id' => $user->id, 'participante_id' => $participante->id, 'alvo_tipo' => 'participante',
        'alvo_documento' => $documento, 'tipo' => 'cnd_federal', 'status' => 'NEGATIVA',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    // Cliente novo, SEM IM própria — a do participante deve migrar.
    $cliente = Cliente::create([
        'user_id' => $user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => $documento,
        'razao_social' => 'HIDRATOP',
    ]);

    expect(Participante::whereKey($participante->id)->exists())->toBeFalse()
        ->and(DB::table('consulta_resultados')->where('id', $resultadoId)->value('cliente_id'))->toBe($cliente->id)
        ->and(DB::table('consulta_resultados')->where('id', $resultadoId)->value('participante_id'))->toBeNull()
        ->and(DB::table('participante_scores')->where('id', $scoreId)->value('cliente_id'))->toBe($cliente->id)
        ->and(DB::table('certidoes')->where('id', $certidaoId)->value('cliente_id'))->toBe($cliente->id)
        ->and(DB::table('certidoes')->where('id', $certidaoId)->value('alvo_tipo'))->toBe('cliente')
        ->and($cliente->fresh()->inscricao_municipal)->toBe('IM-42')
        ->and(app(IdentidadeClienteParticipanteService::class)->totalDuplicidades())->toBe(0);
});

it('recusa o espelho no cadastro manual com uma mensagem de dominio', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create([
        'user_id' => $user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '97551165000193',
        'razao_social' => 'HIDRATOP',
    ]);

    $this->actingAs($user)
        ->postJson('/app/participante/novo', [
            'tipo_documento' => 'PJ',
            'cnpj' => '97.551.165/0001-93',
            'razao_social' => 'HIDRATOP espelho',
        ])
        ->assertUnprocessable()
        ->assertJsonPath('cliente_id', $cliente->id)
        ->assertJsonValidationErrors('cnpj');

    expect(Participante::where('user_id', $user->id)->exists())->toBeFalse();
});

it('permite o mesmo documento em usuarios diferentes', function () {
    $donoCliente = User::factory()->create();
    $donoParticipante = User::factory()->create();
    $documento = '97551165000193';

    Cliente::create([
        'user_id' => $donoCliente->id,
        'tipo_pessoa' => 'PJ',
        'documento' => $documento,
        'razao_social' => 'Cliente de uma conta',
    ]);

    $participante = Participante::create([
        'user_id' => $donoParticipante->id,
        'documento' => $documento,
        'tipo_documento' => 'PJ',
        'razao_social' => 'Contraparte de outra conta',
    ]);

    expect($participante->exists)->toBeTrue()
        ->and(app(IdentidadeClienteParticipanteService::class)->totalDuplicidades())->toBe(0);
});
