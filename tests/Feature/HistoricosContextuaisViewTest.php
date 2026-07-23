<?php

use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\MonitoramentoPlano;
use App\Models\NfeConsulta;
use App\Models\Participante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function historicoContextualLote(User $user, array $overrides = []): ConsultaLote
{
    return ConsultaLote::create(array_merge([
        'user_id' => $user->id,
        'plano_id' => null,
        'status' => ConsultaLote::STATUS_CONCLUIDO,
        'total_participantes' => 1,
        'creditos_cobrados' => 1,
        'resultado_resumo' => ['tier' => 'basico', 'fluxo_origem' => 'lote'],
        'processado_em' => now(),
    ], $overrides));
}

function historicoContextualSnapshot(User $user, ConsultaLote $lote, string $chave, string $numero): NfeConsulta
{
    return NfeConsulta::create([
        'user_id' => $user->id,
        'consulta_lote_id' => $lote->id,
        'chave_acesso' => $chave,
        'tipo_documento' => 'NFE',
        'modelo' => '55',
        'numero' => $numero,
        'serie' => 1,
        'status' => 'AUTORIZADA',
        'consultado_em' => now(),
    ]);
}

function historicoContextualAvulsaLegada(User $user): ConsultaLote
{
    $lote = historicoContextualLote($user, [
        'creditos_cobrados' => 14,
        'resultado_resumo' => ['engine' => 'laravel-clearance'],
    ]);

    DB::table('credit_transactions')->insert([
        'user_id' => $user->id,
        'amount' => -14,
        'balance_after' => 0,
        'type' => 'clearance_lote',
        'description' => 'Clearance NF-e avulsa · 1 documento(s)',
        'created_at' => $lote->created_at,
        'updated_at' => $lote->created_at,
    ]);

    return $lote;
}

it('Verificar Notas mostra somente o histórico de lotes do próprio usuário', function () {
    $user = User::factory()->create();
    $outro = User::factory()->create();

    $lote = historicoContextualLote($user);
    $avulsa = historicoContextualLote($user, [
        'resultado_resumo' => ['tier' => 'basico', 'fluxo_origem' => 'avulsa'],
    ]);
    $interno = historicoContextualLote($user, [
        'creditos_cobrados' => 0,
        'resultado_resumo' => null,
    ]);
    $avulsaLegada = historicoContextualAvulsaLegada($user);
    $alheio = historicoContextualLote($outro);

    actingAs($user)->get('/app/clearance/notas')
        ->assertOk()
        ->assertSee(route('app.clearance.notas.historico'), false)
        ->assertSee('data-history-flow="clearance-lote"', false)
        ->assertSee("Lote #{$lote->id}")
        ->assertDontSee("Lote #{$avulsa->id}")
        ->assertDontSee("Lote #{$avulsaLegada->id}")
        ->assertDontSee("Lote #{$interno->id}")
        ->assertDontSee("Lote #{$alheio->id}");

    actingAs($user)->get(route('app.clearance.notas.historico'))
        ->assertOk()
        ->assertSee('data-history-view="clearance-lote"', false)
        ->assertSee("Lote #{$lote->id}")
        ->assertDontSee("Lote #{$avulsa->id}")
        ->assertDontSee("Lote #{$avulsaLegada->id}")
        ->assertDontSee("Lote #{$interno->id}")
        ->assertDontSee("Lote #{$alheio->id}");
});

it('Buscar Nota mostra somente snapshots de buscas avulsas do próprio usuário', function () {
    $user = User::factory()->create();
    $outro = User::factory()->create();

    $lote = historicoContextualLote($user);
    $avulsa = historicoContextualLote($user, [
        'resultado_resumo' => ['tier' => 'basico', 'fluxo_origem' => 'avulsa'],
    ]);
    $avulsaAlheia = historicoContextualLote($outro, [
        'resultado_resumo' => ['tier' => 'basico', 'fluxo_origem' => 'avulsa'],
    ]);
    $avulsaLegada = historicoContextualAvulsaLegada($user);

    historicoContextualSnapshot($user, $lote, str_repeat('1', 44), '111111');
    $snapshotPreview = historicoContextualSnapshot($user, $avulsa, str_repeat('2', 44), '222222');
    $snapshotPreview->update([
        'data_emissao' => '2026-07-10 09:15:00',
        'emit_cnpj' => '11444777000161',
        'emit_nome' => 'CLIENTE IDENTIFICADO LTDA',
        'dest_cnpj' => '07863768000138',
        'dest_nome' => 'PARTICIPANTE IDENTIFICADO LTDA',
        'valor_total' => 912.34,
        'eventos' => [[
            'evento' => 'Autorização de Uso',
            'data_autorizacao' => '10/07/2026 às 09:16:00-03:00',
            'protocolo' => '135260000000001',
        ]],
    ]);
    $clienteIdentificado = Cliente::create([
        'user_id' => $user->id,
        'documento' => '11444777000161',
        'razao_social' => 'CLIENTE IDENTIFICADO LTDA',
    ]);
    $participanteIdentificado = Participante::create([
        'user_id' => $user->id,
        'documento' => '07863768000138',
        'razao_social' => 'PARTICIPANTE IDENTIFICADO LTDA',
    ]);
    historicoContextualSnapshot($outro, $avulsaAlheia, str_repeat('3', 44), '333333');
    historicoContextualSnapshot($user, $avulsaLegada, str_repeat('5', 44), '555555');
    NfeConsulta::create([
        'user_id' => $user->id,
        'consulta_lote_id' => null,
        'chave_acesso' => str_repeat('4', 44),
        'tipo_documento' => 'NFE',
        'modelo' => '55',
        'numero' => '444444',
        'serie' => 1,
        'status' => 'AUTORIZADA',
        'consultado_em' => now()->subMinute(),
    ]);

    actingAs($user)->get('/app/clearance/buscar')
        ->assertOk()
        ->assertSee(route('app.clearance.buscar.historico'), false)
        ->assertSee('data-history-flow="clearance-avulsa"', false)
        ->assertSee('Nº 222222')
        ->assertSee('Nº 555555')
        ->assertSee('Nº 444444')
        ->assertSee('Snapshot legado sem lote associado')
        ->assertDontSee('Nº 111111')
        ->assertDontSee('Nº 333333');

    // Mesmo adulterando o parâmetro, a rota exclusiva não pode exibir o fluxo em lote.
    actingAs($user)->get(route('app.clearance.buscar.historico', ['origem_fluxo' => 'lote']))
        ->assertOk()
        ->assertSee('data-history-view="clearance-avulsa"', false)
        ->assertSee('tabela-cards historico-tabela', false)
        ->assertSee('data-history-result-url="', false)
        ->assertSee('data-history-fallback-details="historico-busca-detalhe-', false)
        ->assertSee('Documento consultado')
        ->assertSee('CLIENTE IDENTIFICADO LTDA → PARTICIPANTE IDENTIFICADO LTDA')
        ->assertSee('11.444.777/0001-61 → 07.863.768/0001-38')
        ->assertSee('2 partes vinculadas')
        ->assertSee('R$ 912,34')
        ->assertSee('1 evento SEFAZ')
        ->assertSee('Ver detalhes')
        ->assertSee('Resultado resumido')
        ->assertSee('data-history-details="', false)
        ->assertSee('data-history-identifications', false)
        ->assertSee('Identificados no sistema')
        ->assertSee('Cliente')
        ->assertSee('CLIENTE IDENTIFICADO LTDA')
        ->assertSee(route('app.cliente.detalhes', ['id' => $clienteIdentificado->id]), false)
        ->assertSee('Participante')
        ->assertSee('PARTICIPANTE IDENTIFICADO LTDA')
        ->assertSee(route('app.participante', ['id' => $participanteIdentificado->id]), false)
        ->assertSee('data-history-timeline', false)
        ->assertSee('Linha do tempo')
        ->assertSee('Emissão')
        ->assertSee('Autorizada')
        ->assertSee('Protocolo 135260000000001')
        ->assertSee('Consultada no FiscalDock')
        ->assertSee('Nº 222222')
        ->assertSee('Nº 555555')
        ->assertSee('Nº 444444')
        ->assertDontSee('Nº 111111')
        ->assertDontSee('Nº 333333');
});

it('Consulta CNPJ mostra somente lotes CNPJ do próprio usuário', function () {
    $user = User::factory()->create();
    $outro = User::factory()->create();
    $plano = MonitoramentoPlano::query()->firstOrFail();

    $cnpj = historicoContextualLote($user, [
        'plano_id' => $plano->id,
        'creditos_cobrados' => 3,
        'resultado_resumo' => null,
    ]);
    $clearance = historicoContextualLote($user);
    $cnpjAlheio = historicoContextualLote($outro, [
        'plano_id' => $plano->id,
        'creditos_cobrados' => 3,
        'resultado_resumo' => null,
    ]);

    actingAs($user)->get('/app/consulta/painel')
        ->assertOk()
        ->assertSee('data-history-flow="consulta-cnpj"', false)
        ->assertSee("Lote #{$cnpj->id}")
        ->assertDontSee("Lote #{$clearance->id}")
        ->assertDontSee("Lote #{$cnpjAlheio->id}");
});
