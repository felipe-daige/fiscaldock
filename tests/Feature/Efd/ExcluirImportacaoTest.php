<?php

use App\Models\Cliente;
use App\Models\EfdCatalogoItem;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\EfdNotaItem;
use App\Models\Participante;
use App\Models\User;
use App\Services\Efd\ExcluirImportacaoService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function excluirImpNovaImportacao(User $user, Cliente $cliente, array $attrs = []): EfdImportacao
{
    return EfdImportacao::create(array_merge([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'tipo_efd' => 'EFD ICMS/IPI',
        'cnpj' => '12345678000199',
        'periodo_inicio' => '2026-01-01',
        'periodo_fim' => '2026-01-31',
        'filename' => 'arq.txt',
        'status' => 'concluido',
        'creditos_cobrados' => 5,
    ], $attrs));
}

function excluirImpNovaNota(User $user, Cliente $cliente, EfdImportacao $imp, ?Participante $p = null, array $attrs = []): EfdNota
{
    return EfdNota::create(array_merge([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'importacao_id' => $imp->id,
        'participante_id' => $p?->id,
        'modelo' => '55',
        'numero' => (string) random_int(1, 999999),
        'serie' => '1',
        'origem_arquivo' => 'fiscal',
        'tipo_operacao' => 'saida',
        'valor_total' => 100,
    ], $attrs));
}

it('preview conta derivados e classifica participantes orfaos x compartilhados', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create(['user_id' => $user->id, 'razao_social' => 'Acme', 'documento' => '12345678000199']);

    $imp = excluirImpNovaImportacao($user, $cliente);
    $outraImp = excluirImpNovaImportacao($user, $cliente, ['periodo_inicio' => '2026-02-01', 'periodo_fim' => '2026-02-28']);

    $pOrfao = Participante::create(['user_id' => $user->id, 'cliente_id' => $cliente->id, 'importacao_efd_id' => $imp->id, 'razao_social' => 'Orfao', 'documento' => '11111111000111']);
    $pCompart = Participante::create(['user_id' => $user->id, 'cliente_id' => $cliente->id, 'importacao_efd_id' => $imp->id, 'razao_social' => 'Compart', 'documento' => '22222222000122']);

    $nota = excluirImpNovaNota($user, $cliente, $imp, $pOrfao);
    EfdNotaItem::create(['efd_nota_id' => $nota->id, 'user_id' => $user->id, 'numero_item' => 1, 'codigo_item' => 'X', 'descricao' => 'item', 'valor_total' => 0]);
    excluirImpNovaNota($user, $cliente, $outraImp, $pCompart);

    EfdCatalogoItem::create(['user_id' => $user->id, 'cliente_id' => $cliente->id, 'importacao_id' => $imp->id, 'cod_item' => 'X', 'descr_item' => 'item', 'tipo_item' => '00']);

    $preview = app(ExcluirImportacaoService::class)->preview($imp->fresh());

    expect($preview['notas'])->toBe(1)
        ->and($preview['itens'])->toBe(1)
        ->and($preview['catalogo'])->toBe(1)
        ->and($preview['participantes']['candidatos'])->toBe(2)
        ->and($preview['participantes']['orfaos'])->toBe(1)
        ->and($preview['participantes']['compartilhados'])->toBe(1);
});

it('participante orfao por notas mas com dado pago (consulta/score/monitoramento) e tratado como compartilhado e preservado', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create(['user_id' => $user->id, 'razao_social' => 'Acme', 'documento' => '12345678000199']);

    $imp = excluirImpNovaImportacao($user, $cliente);

    // 4 participantes "órfãos por notas" (só nesta importação), cada um com 1 tipo de dado pago.
    $pConsultaResultado = Participante::create(['user_id' => $user->id, 'cliente_id' => $cliente->id, 'importacao_efd_id' => $imp->id, 'razao_social' => 'ConsResult', 'documento' => '11111111000111']);
    $pMonitConsulta = Participante::create(['user_id' => $user->id, 'cliente_id' => $cliente->id, 'importacao_efd_id' => $imp->id, 'razao_social' => 'MonitCons', 'documento' => '22222222000122']);
    $pAssinatura = Participante::create(['user_id' => $user->id, 'cliente_id' => $cliente->id, 'importacao_efd_id' => $imp->id, 'razao_social' => 'Assina', 'documento' => '33333333000133']);
    $pScore = Participante::create(['user_id' => $user->id, 'cliente_id' => $cliente->id, 'importacao_efd_id' => $imp->id, 'razao_social' => 'Score', 'documento' => '44444444000144']);
    // 1 participante puramente órfão (nenhum dado pago) — esse deve ser excluído.
    $pPuroOrfao = Participante::create(['user_id' => $user->id, 'cliente_id' => $cliente->id, 'importacao_efd_id' => $imp->id, 'razao_social' => 'Puro', 'documento' => '55555555000155']);

    $loteId = \Illuminate\Support\Facades\DB::table('consulta_lotes')->insertGetId(['user_id' => $user->id, 'total_participantes' => 1, 'created_at' => now(), 'updated_at' => now()]);
    $planoId = \Illuminate\Support\Facades\DB::table('monitoramento_planos')->insertGetId(['codigo' => 'teste', 'nome' => 'Teste', 'descricao' => 'Teste', 'consultas_incluidas' => '[]', 'custo_creditos' => 1, 'created_at' => now(), 'updated_at' => now()]);

    \Illuminate\Support\Facades\DB::table('consulta_resultados')->insert(['consulta_lote_id' => $loteId, 'participante_id' => $pConsultaResultado->id, 'created_at' => now(), 'updated_at' => now()]);
    \Illuminate\Support\Facades\DB::table('monitoramento_consultas')->insert(['user_id' => $user->id, 'participante_id' => $pMonitConsulta->id, 'plano_id' => $planoId, 'tipo' => 'avulso', 'created_at' => now(), 'updated_at' => now()]);
    \Illuminate\Support\Facades\DB::table('monitoramento_assinaturas')->insert(['user_id' => $user->id, 'participante_id' => $pAssinatura->id, 'plano_id' => $planoId, 'created_at' => now(), 'updated_at' => now()]);
    \App\Models\ParticipanteScore::create(['participante_id' => $pScore->id, 'cliente_id' => $cliente->id, 'user_id' => $user->id, 'score_total' => 50, 'classificacao' => 'medio']);

    $preview = app(ExcluirImportacaoService::class)->preview($imp->fresh());
    expect($preview['participantes']['candidatos'])->toBe(5)
        ->and($preview['participantes']['orfaos'])->toBe(1)            // só o puro órfão
        ->and($preview['participantes']['compartilhados'])->toBe(4);  // os 4 com dado pago

    app(ExcluirImportacaoService::class)->execute($imp->fresh(), excluirParticipantes: true);

    expect(Participante::find($pConsultaResultado->id))->not->toBeNull()
        ->and(Participante::find($pMonitConsulta->id))->not->toBeNull()
        ->and(Participante::find($pAssinatura->id))->not->toBeNull()
        ->and(Participante::find($pScore->id))->not->toBeNull()
        ->and(Participante::find($pPuroOrfao->id))->toBeNull();        // único excluído
});

it('execute apaga importacao e cascateia derivados, exclui orfao e preserva compartilhado', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create(['user_id' => $user->id, 'razao_social' => 'Acme', 'documento' => '12345678000199']);

    $imp = excluirImpNovaImportacao($user, $cliente);
    $outraImp = excluirImpNovaImportacao($user, $cliente, ['periodo_inicio' => '2026-02-01', 'periodo_fim' => '2026-02-28']);

    $pOrfao = Participante::create(['user_id' => $user->id, 'cliente_id' => $cliente->id, 'importacao_efd_id' => $imp->id, 'razao_social' => 'Orfao', 'documento' => '11111111000111']);
    $pCompart = Participante::create(['user_id' => $user->id, 'cliente_id' => $cliente->id, 'importacao_efd_id' => $imp->id, 'razao_social' => 'Compart', 'documento' => '22222222000122']);

    $nota = excluirImpNovaNota($user, $cliente, $imp, $pOrfao);
    \App\Models\EfdNotaItem::create(['efd_nota_id' => $nota->id, 'user_id' => $user->id, 'numero_item' => 1, 'codigo_item' => 'X', 'descricao' => 'item', 'valor_total' => 0]);
    $notaOutra = excluirImpNovaNota($user, $cliente, $outraImp, $pCompart);

    app(ExcluirImportacaoService::class)->execute($imp->fresh(), excluirParticipantes: true);

    expect(EfdImportacao::find($imp->id))->toBeNull()
        ->and(EfdNota::where('importacao_id', $imp->id)->count())->toBe(0)
        ->and(\App\Models\EfdNotaItem::find($nota->id))->toBeNull()
        ->and(Participante::find($pOrfao->id))->toBeNull()
        ->and(Participante::find($pCompart->id))->not->toBeNull()
        ->and(Participante::find($pCompart->id)->importacao_efd_id)->toBeNull()
        ->and(EfdNota::find($notaOutra->id))->not->toBeNull();
});

it('execute com excluirParticipantes=false preserva todos os participantes mas zera importacao_efd_id', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create(['user_id' => $user->id, 'razao_social' => 'Acme', 'documento' => '12345678000199']);
    $imp = excluirImpNovaImportacao($user, $cliente);
    $p = Participante::create(['user_id' => $user->id, 'cliente_id' => $cliente->id, 'importacao_efd_id' => $imp->id, 'razao_social' => 'P', 'documento' => '11111111000111']);
    excluirImpNovaNota($user, $cliente, $imp, $p);

    app(ExcluirImportacaoService::class)->execute($imp->fresh(), excluirParticipantes: false);

    expect(Participante::find($p->id))->not->toBeNull()
        ->and(Participante::find($p->id)->importacao_efd_id)->toBeNull();
});

it('execute remove efd_catalogo_historico e nao estorna creditos nem apaga cliente', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create(['user_id' => $user->id, 'razao_social' => 'Acme', 'documento' => '12345678000199']);
    $imp = excluirImpNovaImportacao($user, $cliente, ['creditos_cobrados' => 5]);
    \Illuminate\Support\Facades\DB::table('efd_catalogo_historico')->insert([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'cod_item' => 'X',
        'campo' => 'cod_ncm', 'valor_anterior' => '1', 'valor_novo' => '2',
        'importacao_id' => $imp->id, 'changed_at' => now(),
    ]);
    $saldoAntes = app(\App\Services\SaldoService::class)->getBalance($user->fresh());

    app(ExcluirImportacaoService::class)->execute($imp->fresh(), excluirParticipantes: true);

    expect(\Illuminate\Support\Facades\DB::table('efd_catalogo_historico')->where('importacao_id', $imp->id)->count())->toBe(0)
        ->and(Cliente::find($cliente->id))->not->toBeNull()
        ->and(app(\App\Services\SaldoService::class)->getBalance($user->fresh()))->toBe($saldoAntes);
});

it('destroy bloqueia importacao em processando (409)', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create(['user_id' => $user->id, 'razao_social' => 'Acme', 'documento' => '12345678000199']);
    $imp = excluirImpNovaImportacao($user, $cliente, ['status' => 'processando']);

    $this->actingAs($user)
        ->deleteJson("/app/importacao/efd/{$imp->id}")
        ->assertStatus(409);

    expect(EfdImportacao::find($imp->id))->not->toBeNull();
});

it('destroy nega importacao de outro usuario (404)', function () {
    $dono = User::factory()->create();
    $outro = User::factory()->create();
    $cliente = Cliente::create(['user_id' => $dono->id, 'razao_social' => 'Acme', 'documento' => '12345678000199']);
    $imp = excluirImpNovaImportacao($dono, $cliente);

    $this->actingAs($outro)
        ->deleteJson("/app/importacao/efd/{$imp->id}")
        ->assertStatus(404);

    expect(EfdImportacao::find($imp->id))->not->toBeNull();
});

it('destroy exclui importacao concluida do dono', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create(['user_id' => $user->id, 'razao_social' => 'Acme', 'documento' => '12345678000199']);
    $imp = excluirImpNovaImportacao($user, $cliente);

    $this->actingAs($user)
        ->deleteJson("/app/importacao/efd/{$imp->id}", ['excluir_participantes' => false])
        ->assertStatus(200)
        ->assertJson(['success' => true]);

    expect(EfdImportacao::find($imp->id))->toBeNull();
});

it('preview-exclusao retorna contagens para o dono', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create(['user_id' => $user->id, 'razao_social' => 'Acme', 'documento' => '12345678000199']);
    $imp = excluirImpNovaImportacao($user, $cliente);

    $this->actingAs($user)
        ->getJson("/app/importacao/efd/{$imp->id}/preview-exclusao")
        ->assertStatus(200)
        ->assertJsonStructure(['notas', 'itens', 'participantes' => ['candidatos', 'orfaos', 'compartilhados']]);
});
