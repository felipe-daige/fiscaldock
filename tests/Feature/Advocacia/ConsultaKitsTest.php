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
    $uid = User::factory()->create()->id;
    $catalogo = app(CatalogoFontesAvulsas::class);

    $comKit = $catalogo->precificar(['certidao_trf', 'certidao_stj'], $uid); // ordem nao importa
    expect($comKit['kit']['nome'])->toBe('Kit Contencioso')
        ->and($comKit['precos'])->toBe(['certidao_trf' => 0.90, 'certidao_stj' => 0.90])
        ->and($comKit['total'])->toBe(1.80)
        ->and($comKit['bruto'])->toBe(2.00)
        ->and($comKit['desconto_reais'])->toBe(0.20);

    $ajustada = $catalogo->precificar(['certidao_stj', 'certidao_trf', 'cndt'], $uid);
    expect($ajustada['kit'])->toBeNull()
        ->and($ajustada['total'])->toBe(3.00);

    // Kit inativo nunca precifica. Instância nova: o catálogo memoiza os kits ativos por
    // request (produção), então mutar o BD exige um catálogo fresco.
    ConsultaKit::query()->update(['ativo' => false]);
    expect(app(CatalogoFontesAvulsas::class)->precificar(['certidao_stj', 'certidao_trf'], $uid)['kit'])->toBeNull();
});

it('vitrine mostra o preco do PROPRIO kit, nao o de outro kit visivel com o mesmo conjunto', function () {
    // Dois kits visiveis (publico=todos) com EXATAMENTE as mesmas fontes, precos diferentes.
    ConsultaKit::create([
        'nome' => 'Licitação', 'slug' => 'lic-cheia',
        'fontes' => ['certidao_stj', 'certidao_trf'],
        'desconto_percentual' => 0, 'ativo' => true, 'ordem' => 1,
    ]);
    ConsultaKit::create([
        'nome' => 'Licitação Parceiro', 'slug' => 'lic-parceiro',
        'fontes' => ['certidao_stj', 'certidao_trf'],
        'desconto_percentual' => 0, 'preco_fixo' => 1.50, 'ativo' => true, 'ordem' => 2,
    ]);
    $uid = User::factory()->create()->id;

    $kits = collect(app(CatalogoFontesAvulsas::class)->kits($uid))->keyBy('slug');
    // Card cheio mostra o preco cheio (2.00); o do parceiro, o fixo (1.50) — cada um o SEU.
    expect($kits['lic-cheia']['preco_total'])->toBe(2.00)
        ->and($kits['lic-parceiro']['preco_total'])->toBe(1.50);
});

it('kit por PRECO FIXO rateia o valor entre as fontes e o total fecha exato', function () {
    // Preço fixo R$ 3,00 sobre 2 fontes de R$ 1,00 (bruto R$ 2,00): rateio proporcional 1,50 + 1,50.
    ConsultaKit::create([
        'nome' => 'Kit Fixo', 'slug' => 'kit-fixo',
        'fontes' => ['certidao_stj', 'certidao_trf'],
        'desconto_percentual' => 0, 'preco_fixo' => 3.00, 'ativo' => true, 'ordem' => 1,
    ]);
    $uid = User::factory()->create()->id;
    $catalogo = app(CatalogoFontesAvulsas::class);

    $p = $catalogo->precificar(['certidao_stj', 'certidao_trf'], $uid);
    expect($p['kit']['nome'])->toBe('Kit Fixo')
        ->and($p['kit']['preco_fixo'])->toBe(3.00)
        ->and($p['total'])->toBe(3.00)
        ->and(array_sum($p['precos']))->toBe(3.00) // rateio fecha EXATO no fixo (contrato do estorno)
        ->and($p['precos'])->toBe(['certidao_stj' => 1.50, 'certidao_trf' => 1.50]);
});

it('rateio do preco fixo fecha EXATO nas bordas: 1 fonte, todas gratis, e divisao inexata', function () {
    $uid = User::factory()->create()->id;
    $catalogo = app(CatalogoFontesAvulsas::class);

    // 1 fonte só: o fixo cai inteiro nela.
    ConsultaKit::create(['nome' => 'K1', 'slug' => 'k1', 'fontes' => ['certidao_stj'],
        'desconto_percentual' => 0, 'preco_fixo' => 0.90, 'ativo' => true, 'ordem' => 1]);
    $p1 = $catalogo->precificar(['certidao_stj'], $uid);
    expect($p1['precos'])->toBe(['certidao_stj' => 0.90])
        ->and(array_sum($p1['precos']))->toBe(0.90);

    // Base bruta ZERO (fonte grátis) com fixo > 0 → divide igual, sem divisão por zero.
    ConsultaKit::create(['nome' => 'K2', 'slug' => 'k2', 'fontes' => ['cadastro'],
        'desconto_percentual' => 0, 'preco_fixo' => 0.50, 'ativo' => true, 'ordem' => 2]);
    $p2 = app(CatalogoFontesAvulsas::class)->precificar(['cadastro'], $uid);
    expect(array_sum($p2['precos']))->toBe(0.50);

    // Divisão inexata: R$ 1,00 sobre 3 fontes iguais = 0,33+0,33+0,34, o resto na 1ª. Soma = fixo.
    ConsultaKit::create(['nome' => 'K3', 'slug' => 'k3',
        'fontes' => ['certidao_stj', 'certidao_trf', 'cndt'],
        'desconto_percentual' => 0, 'preco_fixo' => 1.00, 'ativo' => true, 'ordem' => 3]);
    $p3 = app(CatalogoFontesAvulsas::class)->precificar(['certidao_stj', 'certidao_trf', 'cndt'], $uid);
    expect(array_sum($p3['precos']))->toBe(1.00)   // contrato duro: fecha no fixo
        ->and($p3['total'])->toBe(1.00)
        ->and(count($p3['precos']))->toBe(3);
});

it('admin barra kit abaixo do custo do provedor: preco_fixo irrisorio E desconto=100', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    // certidao_stj+certidao_trf custam R$ 1,00 cada (config consultas.fontes) → custo R$ 2,00.
    // preco_fixo R$ 1,00 fica abaixo → recusado, kit nao criado.
    $this->actingAs($admin)->post('/app/admin/kits', [
        'nome' => 'Kit Barato', 'fontes' => ['certidao_stj', 'certidao_trf'],
        'desconto_percentual' => 0, 'preco_fixo' => 1.00, 'ordem' => 1, 'ativo' => 1, 'publico' => 'todos',
    ])->assertSessionHasErrors('preco_fixo');
    expect(ConsultaKit::where('nome', 'Kit Barato')->exists())->toBeFalse();

    // desconto 100% zera o preço → abaixo do custo; erro no campo do desconto.
    $this->actingAs($admin)->post('/app/admin/kits', [
        'nome' => 'Kit Zerado', 'fontes' => ['certidao_stj', 'certidao_trf'],
        'desconto_percentual' => 100, 'ordem' => 1, 'ativo' => 1, 'publico' => 'todos',
    ])->assertSessionHasErrors('desconto_percentual');
    expect(ConsultaKit::where('nome', 'Kit Zerado')->exists())->toBeFalse();

    // Preço >= custo passa (fixo R$ 2,50 sobre custo R$ 2,00).
    $this->actingAs($admin)->post('/app/admin/kits', [
        'nome' => 'Kit OK', 'fontes' => ['certidao_stj', 'certidao_trf'],
        'desconto_percentual' => 0, 'preco_fixo' => 2.50, 'ordem' => 1, 'ativo' => 1, 'publico' => 'todos',
    ])->assertRedirect(route('app.admin.kits.index'));
    expect(ConsultaKit::where('nome', 'Kit OK')->value('preco_fixo'))->not->toBeNull();
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

it('vitrine mostra TODO kit global ativo publico=todos (sistema ou nao); ativo=false some', function () {
    // `sistema` nao gate mais a vitrine: quem decide e ativo + publico. Ambos os kits ativos
    // globais (publico=todos por default) aparecem.
    kitContencioso(10);                       // nao-sistema, publico=todos
    ConsultaKit::create([
        'nome' => 'Validação Fiscal', 'slug' => 'sys-validacao',
        'fontes' => ['analise_fiscal'], 'desconto_percentual' => 0,
        'sistema' => true, 'ativo' => true, 'ordem' => 2,
    ]);
    // Kit desativado nunca aparece.
    ConsultaKit::create([
        'nome' => 'Kit Off', 'slug' => 'kit-off',
        'fontes' => ['certidao_stj'], 'desconto_percentual' => 0, 'ativo' => false, 'ordem' => 3,
    ]);
    $user = User::factory()->create(['credits' => 5.0]);

    $this->actingAs($user)->get('/app/consulta/painel')
        ->assertOk()
        ->assertSee('Validação Fiscal')
        ->assertSee('Kit Contencioso')
        ->assertDontSee('Kit Off')
        ->assertViewHas('kits', function ($kits) {
            $slugs = array_column($kits, 'slug');
            sort($slugs);

            return $slugs === ['contencioso', 'sys-validacao'];
        });
});

it('kit segmentado (publico=selecionados) aparece e cobra SO pros usuarios atribuidos', function () {
    $dono = User::factory()->create(['credits' => 5.0]);
    $outro = User::factory()->create(['credits' => 5.0]);

    $kit = ConsultaKit::create([
        'nome' => 'Kit VIP', 'slug' => 'kit-vip',
        'fontes' => ['certidao_stj', 'certidao_trf'],
        'desconto_percentual' => 50, 'publico' => 'selecionados', 'ativo' => true, 'ordem' => 1,
    ]);
    $kit->usuarios()->sync([$dono->id]);

    // Vitrine: so o dono ve o card.
    $this->actingAs($dono)->get('/app/consulta/painel')->assertOk()->assertSee('Kit VIP');
    $this->actingAs($outro)->get('/app/consulta/painel')->assertOk()->assertDontSee('Kit VIP');

    // Preco: dono leva o desconto do kit; o outro paga cheio (kit nem casa pra ele).
    $catalogo = app(CatalogoFontesAvulsas::class);
    expect($catalogo->precificar(['certidao_stj', 'certidao_trf'], $dono->id)['kit']['nome'])->toBe('Kit VIP');
    expect(app(CatalogoFontesAvulsas::class)->precificar(['certidao_stj', 'certidao_trf'], $outro->id)['kit'])->toBeNull();
});

it('admin CRUD de kits: cria, edita, exclui; nao-admin bloqueado', function () {
    $comum = User::factory()->create(['is_admin' => false]);
    $this->actingAs($comum)->get('/app/admin/kits')->assertStatus(403);

    $admin = User::factory()->create(['is_admin' => true]);

    // cnd_federal/cndt custam R$ 0,40 (config consultas.fontes): há margem p/ 5% de desconto.
    $this->actingAs($admin)->post('/app/admin/kits', [
        'nome' => 'Kit Teste', 'fontes' => ['cnd_federal', 'cndt'],
        'desconto_percentual' => 5, 'ordem' => 9, 'ativo' => 1, 'publico' => 'todos',
    ])->assertRedirect(route('app.admin.kits.index'));

    $kit = ConsultaKit::where('nome', 'Kit Teste')->first();
    expect($kit)->not->toBeNull()
        ->and($kit->slug)->toBe('kit-teste')
        ->and($kit->fontes)->toBe(['cnd_federal', 'cndt'])
        ->and($kit->publico)->toBe('todos');

    // Editar vira preco FIXO. R$ 0,80 sobre cnd_federal (custo R$ 0,40) respeita a margem.
    $this->actingAs($admin)->post("/app/admin/kits/{$kit->id}", [
        'nome' => 'Kit Teste v2', 'fontes' => ['cnd_federal'],
        'desconto_percentual' => 7.5, 'preco_fixo' => 0.80, 'ordem' => 9, 'ativo' => 0, 'publico' => 'todos',
    ])->assertRedirect(route('app.admin.kits.index'));

    $kit->refresh();
    expect($kit->nome)->toBe('Kit Teste v2')
        ->and($kit->ativo)->toBeFalse()
        ->and((float) $kit->desconto_percentual)->toBe(7.5)
        ->and((float) $kit->preco_fixo)->toBe(0.80);

    // Fonte fora do catalogo e recusada.
    $this->actingAs($admin)->post('/app/admin/kits', [
        'nome' => 'Kit Invalido', 'fontes' => ['fonte_que_nao_existe'],
        'desconto_percentual' => 0, 'ordem' => 0, 'publico' => 'todos',
    ])->assertSessionHasErrors('fontes.0');

    $this->actingAs($admin)->post("/app/admin/kits/{$kit->id}/excluir")
        ->assertRedirect(route('app.admin.kits.index'));
    expect(ConsultaKit::find($kit->id))->toBeNull();
});

it('kit DO SISTEMA nao pode ser excluido pelo CRUD admin; preset pessoal fica fora do painel', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $sistema = ConsultaKit::create([
        'nome' => 'Validação Fiscal', 'slug' => 'fiscal-validacao',
        'fontes' => ['cadastro', 'analise_fiscal'],
        'desconto_percentual' => 0, 'sistema' => true, 'ativo' => true, 'ordem' => 11,
    ]);

    // Excluir some da vitrine na hora e o seeder usa firstOrCreate por slug — só voltaria rodando
    // seeder em produção. Tirar da vitrine é desativar, não excluir.
    $this->actingAs($admin)->post("/app/admin/kits/{$sistema->id}/excluir")
        ->assertRedirect(route('app.admin.kits.index'))
        ->assertSessionHas('error');
    expect(ConsultaKit::find($sistema->id))->not->toBeNull();

    // Kit global comum segue excluível.
    $comum = kitContencioso();
    $this->actingAs($admin)->post("/app/admin/kits/{$comum->id}/excluir")->assertRedirect();
    expect(ConsultaKit::find($comum->id))->toBeNull();

    // Preset PESSOAL (user_id) não é kit do admin: nem lista, nem abre, nem se exclui por lá.
    $dono = User::factory()->create();
    $preset = ConsultaKit::create([
        'user_id' => $dono->id, 'nome' => 'Meu plano', 'slug' => 'meu-plano-u'.$dono->id,
        'fontes' => ['certidao_stj'], 'desconto_percentual' => 0, 'ativo' => true, 'ordem' => 0,
    ]);

    $this->actingAs($admin)->get('/app/admin/kits')->assertOk()->assertDontSee('Meu plano');
    $this->actingAs($admin)->get("/app/admin/kits/{$preset->id}/editar")->assertStatus(404);
    $this->actingAs($admin)->post("/app/admin/kits/{$preset->id}/excluir")->assertStatus(404);
    expect(ConsultaKit::find($preset->id))->not->toBeNull();
});

it('admin segmenta kit por usuario: publico=selecionados sincroniza a pivot; sem usuario e recusado', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $u1 = User::factory()->create();
    $u2 = User::factory()->create();

    // publico=selecionados SEM nenhum usuario → erro (esconderia de todos).
    $this->actingAs($admin)->post('/app/admin/kits', [
        'nome' => 'Kit Seg', 'fontes' => ['certidao_stj'],
        'desconto_percentual' => 0, 'ordem' => 1, 'ativo' => 1, 'publico' => 'selecionados',
    ])->assertSessionHasErrors('usuarios');
    expect(ConsultaKit::where('nome', 'Kit Seg')->exists())->toBeFalse();

    // Com usuarios → grava e sincroniza a pivot.
    $this->actingAs($admin)->post('/app/admin/kits', [
        'nome' => 'Kit Seg', 'fontes' => ['certidao_stj'],
        'desconto_percentual' => 0, 'ordem' => 1, 'ativo' => 1,
        'publico' => 'selecionados', 'usuarios' => [$u1->id, $u2->id],
    ])->assertRedirect(route('app.admin.kits.index'));

    $kit = ConsultaKit::where('nome', 'Kit Seg')->firstOrFail();
    expect($kit->publico)->toBe('selecionados')
        ->and($kit->usuarios()->pluck('users.id')->sort()->values()->all())->toBe([$u1->id, $u2->id]);

    // Voltar para 'todos' zera a pivot.
    $this->actingAs($admin)->post("/app/admin/kits/{$kit->id}", [
        'nome' => 'Kit Seg', 'fontes' => ['certidao_stj'],
        'desconto_percentual' => 0, 'ordem' => 1, 'ativo' => 1, 'publico' => 'todos',
    ])->assertRedirect(route('app.admin.kits.index'));

    expect($kit->fresh()->usuarios()->count())->toBe(0);
});
