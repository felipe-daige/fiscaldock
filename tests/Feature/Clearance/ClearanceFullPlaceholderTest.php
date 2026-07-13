<?php

use App\Http\Controllers\Dashboard\ClearanceController;
use App\Jobs\ProcessarClearanceJob;
use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\NfeConsulta;
use App\Models\User;
use App\Services\ValidacaoContabilService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function fullPhUser(): User
{
    return User::factory()->trialAtivo()->create(['credits' => 1000]);
}

function fullPhCliente(User $u): Cliente
{
    return Cliente::firstOrCreate(
        ['user_id' => $u->id, 'is_empresa_propria' => true],
        ['tipo_pessoa' => 'PJ', 'documento' => '00000000000191', 'razao_social' => 'Propria']
    );
}

function fullPhEfdNota(User $u): EfdNota
{
    $cliente = fullPhCliente($u);
    $imp = EfdImportacao::firstOrCreate(
        ['user_id' => $u->id, 'cliente_id' => $cliente->id, 'tipo_efd' => 'EFD ICMS/IPI'],
        ['status' => 'concluido']
    );

    return EfdNota::create([
        'user_id' => $u->id, 'cliente_id' => $cliente->id, 'importacao_id' => $imp->id,
        'chave_acesso' => '35240413305697000150550000000404041953940992', 'modelo' => '55',
        'numero' => 1, 'serie' => '0', 'data_emissao' => '2026-01-15', 'tipo_operacao' => 'entrada',
        'valor_total' => 1000.00, 'valor_desconto' => 0, 'origem_arquivo' => 'fiscal', 'metadados' => [],
    ]);
}

it('com flag full OFF, tier=full é coagido para basico (não cobra o dobro)', function () {
    config()->set('clearance.full.habilitado', false);
    Bus::fake();
    Http::fake();
    $user = fullPhUser();
    $nota = fullPhEfdNota($user);

    actingAs($user)->postJson('/app/clearance/notas/validar', [
        'nota_ids' => [$nota->id], 'origens' => [$nota->id => 'efd'], 'tipo' => 'full', 'tab_id' => 'tab-full',
    ])->assertOk();

    $lote = ConsultaLote::latest('id')->first();
    expect($lote->creditos_cobrados)->toBe(ValidacaoContabilService::custoUnitarioPorTier('basico'));
    Bus::assertBatched(fn ($b) => collect($b->jobs)->every(fn ($j) => $j instanceof ProcessarClearanceJob));
});

// A comparação Declarado × SEFAZ de tributos/itens (superfície da Camada B) NÃO existe — o dado
// completo já chega com certificado, mas as telas não o confrontam. Gate PRÓPRIO: não pode ser
// `full.habilitado` (que é a Camada A = regularidade), senão ligar o Full some com o "em breve"
// de uma feature que não foi construída.
it('resultado mostra "em breve" de tributos/itens enquanto a comparação não existe', function () {
    config()->set('clearance.comparacao_declarado', false);
    config()->set('clearance.full.habilitado', true); // Full ligado NÃO pode esconder este bloco
    $user = fullPhUser();
    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'plano_id' => null, 'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1, 'creditos_cobrados' => 3, 'tab_id' => 'tab-ph', 'processado_em' => now(),
    ]);
    NfeConsulta::create([
        'user_id' => $user->id, 'consulta_lote_id' => $lote->id, 'chave_acesso' => str_repeat('5', 44),
        'tipo_documento' => 'NFE', 'modelo' => '55', 'status' => 'AUTORIZADA', 'valor_total' => 100, 'consultado_em' => now(),
    ]);

    actingAs($user)->get("/app/clearance/notas/resultado/{$lote->id}")
        ->assertOk()
        ->assertSee('Tributos e itens', false)
        ->assertSee('Em breve', false)
        ->assertSee('certificado digital A1', false);
});

it('com Full ON, a tela de notas oferece o Clearance completo (regularidade da contraparte)', function () {
    config()->set('clearance.full.habilitado', true);
    $user = fullPhUser();
    fullPhEfdNota($user);

    actingAs($user)->get('/app/clearance/notas')
        ->assertOk()
        // Escolha EXCLUSIVA de tier (radio), não checkbox: ou um, ou outro.
        ->assertSee('name="clearance_tier"', false)
        ->assertSee('id="tier-basico"', false)
        ->assertSee('id="tier-full"', false)
        ->assertSee('Clearance completo', false)
        ->assertSee('SINTEGRA', false)
        ->assertSee('CND Federal', false)
        // O completo é CUMULATIVO — a copy precisa dizer que inclui o básico.
        ->assertSee('Tudo do Clearance', false)
        // Preço fechado por nota do Clearance completo. Dinheiro::brl usa NBSP entre "R$" e o
        // número (valor monetário não quebra linha) — comparar com espaço normal aqui falharia.
        ->assertSee('R$'."\u{A0}".'2,00', false)
        // Certificado A1: consumo JÁ implementado — deixou de ser "em breve" e virou CTA.
        ->assertSee('Cadastrar certificado', false);
});

it('com Full OFF, a tela de notas não oferece a escolha (só o card "em breve")', function () {
    config()->set('clearance.full.habilitado', false);
    $user = fullPhUser();
    fullPhEfdNota($user);

    actingAs($user)->get('/app/clearance/notas')
        ->assertOk()
        ->assertDontSee('id="tier-full"', false)
        ->assertSee('Clearance completo', false)
        ->assertSee('Em breve', false);
});

// Preço por TIER, idêntico em lote e busca avulsa: básico R$ 1,00 · completo R$ 2,00.
// O que muda o preço é o tier, nunca a origem (lote vs avulsa).
it('preço: básico 5 un (R$ 1,00) e completo 10 un (R$ 2,00); avulsa espelha o básico', function () {
    expect(ClearanceController::CLEARANCE_NFE_AVULSA_CUSTO)
        ->toBe(ValidacaoContabilService::CUSTO_DOCUMENTO)
        ->and(ValidacaoContabilService::custoUnitarioPorTier('basico'))->toBe(5)
        ->and(ValidacaoContabilService::custoUnitarioPorTier('full'))->toBe(10);
});

it('lote com tier=full debita R$ 2,00 por nota e marca o tier no lote', function () {
    config()->set('clearance.full.habilitado', true);
    Bus::fake();
    Http::fake();
    $user = fullPhUser();
    $nota = fullPhEfdNota($user);
    $saldo = $user->credits;

    actingAs($user)->postJson('/app/clearance/notas/validar', [
        'nota_ids' => [$nota->id], 'origens' => [$nota->id => 'efd'], 'tipo' => 'full', 'tab_id' => 'tab-f2',
    ])->assertOk();

    $lote = ConsultaLote::latest('id')->first();
    expect($lote->creditos_cobrados)->toBe(ValidacaoContabilService::CUSTO_DOCUMENTO_FULL)
        ->and($lote->resultado_resumo['tier'] ?? null)->toBe('full');
    expect($user->fresh()->credits)->toBe($saldo - ValidacaoContabilService::CUSTO_DOCUMENTO_FULL);
});

// Regressão (2026-07-13): o lote fechava ('finalizado' → front recarrega) ANTES de investigar a
// contraparte — o usuário caía no resultado com "em apuração" que nunca atualizava. Agora quem
// fecha o lote é o batch da regularidade; o clearance só emite 100% quando as DUAS fases acabam.
it('tier=full: o lote NÃO é fechado pelo batch dos documentos (quem fecha é a regularidade)', function () {
    config()->set('clearance.full.habilitado', true);
    Bus::fake();
    Http::fake();
    $user = fullPhUser();
    $nota = fullPhEfdNota($user);

    actingAs($user)->postJson('/app/clearance/notas/validar', [
        'nota_ids' => [$nota->id], 'origens' => [$nota->id => 'efd'], 'tipo' => 'full', 'tab_id' => 'tab-chain',
    ])->assertOk();

    $lote = ConsultaLote::latest('id')->first();
    // Segue 'processando': o fechamento acontece só ao fim da fase de contrapartes.
    expect($lote->status)->toBe(ConsultaLote::STATUS_PROCESSANDO);

    // E os jobs dos documentos param em 50% da barra — a 2ª metade é da contraparte.
    Bus::assertBatched(fn ($b) => collect($b->jobs)->every(
        fn ($j) => $j instanceof ProcessarClearanceJob && $j->pctSpan === 50
    ));
});

it('busca avulsa com tipo=full debita R$ 2,00 (mesmo preço do lote)', function () {
    config()->set('clearance.full.habilitado', true);
    config()->set('clearance.busca_avulsa.habilitada', true);
    Bus::fake();
    Http::fake();
    $user = fullPhUser();
    $cli = fullPhCliente($user);
    $saldo = $user->credits;

    actingAs($user)->postJson('/app/clearance/buscar/consultar', [
        'tipo_documento' => 'nfe',
        'chave_acesso' => '35240413305697000150550000000404041953940992',
        'cliente_id' => $cli->id, 'tab_id' => 'tab-av-full', 'tipo' => 'full',
    ])->assertOk();

    expect($user->fresh()->credits)->toBe($saldo - ValidacaoContabilService::CUSTO_DOCUMENTO_FULL);
});

it('busca avulsa: tipo=full com a flag OFF é coagido para básico (não cobra o dobro)', function () {
    config()->set('clearance.full.habilitado', false);
    config()->set('clearance.busca_avulsa.habilitada', true);
    Bus::fake();
    Http::fake();
    $user = fullPhUser();
    $cli = fullPhCliente($user);
    $saldo = $user->credits;

    actingAs($user)->postJson('/app/clearance/buscar/consultar', [
        'tipo_documento' => 'nfe',
        'chave_acesso' => '35240413305697000150550000000404041953940992',
        'cliente_id' => $cli->id, 'tab_id' => 'tab-av-coerc', 'tipo' => 'full',
    ])->assertOk();

    expect($user->fresh()->credits)->toBe($saldo - ValidacaoContabilService::CUSTO_DOCUMENTO);
});

// O placeholder "em breve" do certificado na Empresa foi substituído pelo cadastro real
// (ver CertificadoCadastroTest). Falta só o CONSUMO do certificado na consulta — enquanto
// isso, o bloco de tributos/itens no resultado segue "em breve".
