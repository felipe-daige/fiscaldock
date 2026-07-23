<?php

use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\User;
use App\Services\Advocacia\CatalogoFontesAvulsas;
use App\Services\Consultas\FonteRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'test-token');
});

function cadastroRawSimples(): array
{
    // Resposta minhareceita de empresa optante do Simples (regime = "Simples Nacional").
    return [
        'razao_social' => 'EMPRESA TESTE LTDA',
        'descricao_situacao_cadastral' => 'ATIVA',
        'uf' => 'SP', 'municipio' => 'SAO PAULO',
        'opcao_pelo_simples' => true,
        'data_opcao_pelo_simples' => '2020-01-01',
        'identificador_matriz_filial' => 1,
    ];
}

function alvoParticipanteAnalise(User $user): int
{
    return DB::table('participantes')->insertGetId([
        'user_id' => $user->id, 'documento' => '19131243000197', 'razao_social' => 'PART',
        'uf' => 'SP', 'created_at' => now(), 'updated_at' => now(),
    ]);
}

it('analise_fiscal e uma fonte derivada (sem chamada externa) e nao vira etapa/consulta no job', function () {
    $registry = app(FonteRegistry::class);
    $fonte = $registry->get('analise_fiscal');

    expect($fonte)->not->toBeNull()
        ->and($fonte->provider())->toBe('derivado')
        ->and($fonte->pronta())->toBeTrue()
        ->and($fonte->custoCreditos())->toBe(0.0);

    // regime_tributario mapeia pra ela, mas fontesDe a devolve (o job é quem filtra derivado).
    $catalogo = app(CatalogoFontesAvulsas::class);
    expect($catalogo->precoDe('analise_fiscal'))->toBe(1.00);
});

it('consulta SEM analise fiscal NAO persiste regime tributario (so identidade/endereco gratis)', function () {
    Http::fake(['minhareceita.org/*' => Http::response(cadastroRawSimples(), 200)]);

    $user = User::factory()->create(['credits' => 10.0]);
    $pid = alvoParticipanteAnalise($user);

    $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => [$pid],
        'fontes' => ['cnd_federal'], // fiscal, mas SEM analise_fiscal
        'tab_id' => 'tab-free',
    ])->assertOk();

    $r = ConsultaResultado::whereHas('lote', fn ($q) => $q->where('user_id', $user->id))->first();
    $dados = $r->resultado_dados;

    // Identidade/endereço/situação: grátis, persistem.
    expect($dados['razao_social'])->toBe('EMPRESA TESTE LTDA')
        ->and($dados['situacao_cadastral'])->toBe('ATIVA')
        ->and($dados['endereco']['uf'])->toBe('SP');

    // Regime + Simples/MEI: bloqueados sem Análise Fiscal.
    expect($dados)->not->toHaveKey('regime_tributario')
        ->and($dados)->not->toHaveKey('historico_simples')
        ->and($dados)->not->toHaveKey('simples_nacional');
});

it('consulta COM analise fiscal persiste regime tributario derivado do mesmo cadastro gratis', function () {
    Http::fake(['minhareceita.org/*' => Http::response(cadastroRawSimples(), 200)]);

    $user = User::factory()->create(['credits' => 10.0]);
    $pid = alvoParticipanteAnalise($user);

    $resp = $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => [$pid],
        'fontes' => ['analise_fiscal'],
        'tab_id' => 'tab-paid',
    ])->assertOk()->assertJson(['valor_cobrado_reais' => 1.00]);

    $lote = ConsultaLote::find($resp->json('consulta_lote_id'));
    expect($lote->fontes_selecionadas)->toBe(['analise_fiscal'])
        ->and((float) $user->fresh()->credits)->toBe(9.00);

    $r = ConsultaResultado::where('consulta_lote_id', $lote->id)->first();
    $dados = $r->resultado_dados;

    expect($dados['regime_tributario'])->toBe('Simples Nacional')
        ->and($dados['simples_nacional'])->toBeTrue()
        ->and($dados['razao_social'])->toBe('EMPRESA TESTE LTDA');
});

it('analise fiscal so faz UMA chamada minhareceita (deriva do cadastro, sem call propria)', function () {
    $chamadas = 0;
    Http::fake(function () use (&$chamadas) {
        $chamadas++;

        return Http::response(cadastroRawSimples(), 200);
    });

    $user = User::factory()->create(['credits' => 10.0]);
    $pid = alvoParticipanteAnalise($user);

    $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => [$pid],
        'fontes' => ['analise_fiscal'],
        'tab_id' => 'tab-1call',
    ])->assertOk();

    // Matriz (identificador_matriz_filial=1) → sem 2ª chamada de matriz. Análise não chama nada.
    expect($chamadas)->toBe(1);
});
