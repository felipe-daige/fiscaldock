<?php

use App\Jobs\ProcessarConsultaJob;
use App\Models\ConsultaKit;
use App\Models\User;
use App\Services\Advocacia\CatalogoFontesAvulsas;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'test-token');
});

function kitContencioso(float $desconto = 10): ConsultaKit
{
    return ConsultaKit::create([
        'nome' => 'Kit Contencioso', 'slug' => 'contencioso',
        'fontes' => ['certidao_stj', 'certidao_trf'],
        'desconto_percentual' => $desconto, 'ativo' => true, 'ordem' => 1,
    ]);
}

it('precificar aplica desconto POR FONTE na selecao exata do kit; selecao ajustada perde o desconto', function () {
    kitContencioso(10);
    $catalogo = app(CatalogoFontesAvulsas::class);

    $comKit = $catalogo->precificar(['certidao_trf', 'certidao_stj']); // ordem nao importa
    expect($comKit['kit']['nome'])->toBe('Kit Contencioso')
        ->and($comKit['precos'])->toBe(['certidao_trf' => 0.90, 'certidao_stj' => 0.90])
        ->and($comKit['total'])->toBe(1.80)
        ->and($comKit['bruto'])->toBe(2.00)
        ->and($comKit['desconto_reais'])->toBe(0.20);

    $ajustada = $catalogo->precificar(['certidao_stj', 'certidao_trf', 'cndt']);
    expect($ajustada['kit'])->toBeNull()
        ->and($ajustada['total'])->toBe(3.00);

    // Kit inativo nunca precifica. Instância nova: o catálogo memoiza os kits ativos por
    // request (produção), então mutar o BD exige um catálogo fresco.
    ConsultaKit::query()->update(['ativo' => false]);
    expect(app(CatalogoFontesAvulsas::class)->precificar(['certidao_stj', 'certidao_trf'])['kit'])->toBeNull();
});

it('calcular-custo devolve kit e desconto; executar debita o total COM desconto e precosVenda descontado', function () {
    Bus::fake();
    kitContencioso(10);

    $user = User::factory()->create(['credits' => 10.0]);
    $pid = DB::table('participantes')->insertGetId([
        'user_id' => $user->id, 'documento' => '19131243000197', 'razao_social' => 'PART',
        'uf' => 'SP', 'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->actingAs($user)->postJson('/app/consulta/nova/fontes/calcular-custo', [
        'fontes' => ['certidao_stj', 'certidao_trf'],
        'quantidade' => 2,
    ])->assertOk()->assertJson([
        'preco_por_alvo_reais' => 1.80,
        'preco_bruto_por_alvo_reais' => 2.00,
        'desconto_por_alvo_reais' => 0.20,
        'custo_total_reais' => 3.60,
        'kit' => ['nome' => 'Kit Contencioso'],
    ]);

    $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => [$pid],
        'fontes' => ['certidao_stj', 'certidao_trf'],
        'tab_id' => 't-kit',
    ])->assertOk()->assertJson(['valor_cobrado_reais' => 1.80]);

    expect((float) $user->fresh()->credits)->toBe(8.20);

    // Estorno de falha devolve o unitario COBRADO (com desconto), nunca o preco cheio.
    Bus::assertBatched(function ($batch) {
        $job = collect($batch->jobs)->first();

        return $job instanceof ProcessarConsultaJob
            && $job->precosVenda === ['certidao_stj' => 0.90, 'certidao_trf' => 0.90];
    });
});

it('tela de fontes mostra na vitrine SO os planos do sistema; kit nao-sistema fica fora', function () {
    // Kit global mas NÃO sistema (advocacia) → não entra na vitrine "Planos do contador".
    kitContencioso(10);
    // Plano do sistema (sistema=true) → entra na vitrine.
    ConsultaKit::create([
        'nome' => 'Validação Fiscal', 'slug' => 'sys-validacao',
        'fontes' => ['analise_fiscal'], 'desconto_percentual' => 0,
        'sistema' => true, 'ativo' => true, 'ordem' => 1,
    ]);
    $user = User::factory()->create(['credits' => 5.0]);

    $this->actingAs($user)->get('/app/consulta/painel')
        ->assertOk()
        ->assertSee('Planos do contador')
        ->assertSee('Validação Fiscal')
        ->assertDontSee('Kit Contencioso') // não-sistema não aparece na vitrine
        ->assertViewHas('kits', fn ($kits) => count($kits) === 1 && $kits[0]['slug'] === 'sys-validacao');
});

it('admin CRUD de kits: cria, edita, exclui; nao-admin bloqueado', function () {
    $comum = User::factory()->create(['is_admin' => false]);
    $this->actingAs($comum)->get('/app/admin/kits')->assertStatus(403);

    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->post('/app/admin/kits', [
        'nome' => 'Kit Teste', 'fontes' => ['certidao_stj', 'protestos'],
        'desconto_percentual' => 5, 'ordem' => 9, 'ativo' => 1,
    ])->assertRedirect(route('app.admin.kits.index'));

    $kit = ConsultaKit::where('nome', 'Kit Teste')->first();
    expect($kit)->not->toBeNull()
        ->and($kit->slug)->toBe('kit-teste')
        ->and($kit->fontes)->toBe(['certidao_stj', 'protestos']);

    $this->actingAs($admin)->post("/app/admin/kits/{$kit->id}", [
        'nome' => 'Kit Teste v2', 'fontes' => ['certidao_stj'],
        'desconto_percentual' => 7.5, 'ordem' => 9, 'ativo' => 0,
    ])->assertRedirect(route('app.admin.kits.index'));

    $kit->refresh();
    expect($kit->nome)->toBe('Kit Teste v2')
        ->and($kit->ativo)->toBeFalse()
        ->and((float) $kit->desconto_percentual)->toBe(7.5);

    // Fonte fora do catalogo e recusada.
    $this->actingAs($admin)->post('/app/admin/kits', [
        'nome' => 'Kit Invalido', 'fontes' => ['fonte_que_nao_existe'],
        'desconto_percentual' => 0, 'ordem' => 0,
    ])->assertSessionHasErrors('fontes.0');

    $this->actingAs($admin)->post("/app/admin/kits/{$kit->id}/excluir")
        ->assertRedirect(route('app.admin.kits.index'));
    expect(ConsultaKit::find($kit->id))->toBeNull();
});
