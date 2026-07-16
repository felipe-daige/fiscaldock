<?php

use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\EfdImportacao;
use App\Models\Participante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function notaComValor(User $user, Participante $p, float $valor, string $tipoOperacao = 'entrada'): void
{
    $cliente = Cliente::firstOrCreate(
        ['user_id' => $user->id, 'documento' => '00000000000191'],
        ['razao_social' => 'Empresa Teste']
    );
    $imp = EfdImportacao::firstOrCreate(
        ['user_id' => $user->id, 'tipo_efd' => 'EFD ICMS/IPI'],
        []
    );

    DB::table('efd_notas')->insert([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'importacao_id' => $imp->id,
        'participante_id' => $p->id,
        'modelo' => '55',
        'numero' => random_int(1, 9999999),
        'tipo_operacao' => $tipoOperacao,
        'origem_arquivo' => 'fiscal',
        'valor_total' => $valor,
        'cancelada' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

it('ordena a listagem por valor movimentado desc por padrao', function () {
    $user = User::factory()->create();
    // Criado por último (created_at mais recente), mas com menor movimentação.
    $menor = Participante::create(['user_id' => $user->id, 'documento' => '11444777000161', 'razao_social' => 'MENOR MOVIMENTACAO LTDA']);
    $maior = Participante::create(['user_id' => $user->id, 'documento' => '11444777000242', 'razao_social' => 'MAIOR MOVIMENTACAO LTDA']);

    notaComValor($user, $menor, 100.0);
    notaComValor($user, $maior, 9000.0);

    actingAs($user)->get('/app/participantes')
        ->assertOk()
        ->assertSeeInOrder(['MAIOR MOVIMENTACAO LTDA', 'MENOR MOVIMENTACAO LTDA']);
});

it('exibe valor movimentado e quantidade de notas na linha', function () {
    $user = User::factory()->create();
    $p = Participante::create(['user_id' => $user->id, 'documento' => '11444777000161', 'razao_social' => 'MOVIMENTADO LTDA']);

    notaComValor($user, $p, 1500.5, 'entrada');
    notaComValor($user, $p, 500.0, 'saida');

    actingAs($user)->get('/app/participantes')
        ->assertOk()
        ->assertSee('R$&nbsp;2.000,50', false)
        ->assertSee('2 notas');
});

it('permite ordenar por mais recentes via param ordem', function () {
    $user = User::factory()->create();
    $antigo = Participante::create(['user_id' => $user->id, 'documento' => '11444777000161', 'razao_social' => 'ANTIGO LTDA']);
    $antigo->forceFill(['created_at' => now()->subDay()])->save();
    $novo = Participante::create(['user_id' => $user->id, 'documento' => '11444777000242', 'razao_social' => 'NOVO LTDA']);

    notaComValor($user, $antigo, 9000.0);

    actingAs($user)->get('/app/participantes?ordem=recentes')
        ->assertOk()
        ->assertSeeInOrder(['NOVO LTDA', 'ANTIGO LTDA']);
});

it('deriva a origem EFD pelo tipo da importacao vinculada quando origem_tipo esta vazio', function () {
    $user = User::factory()->create();
    $imp = EfdImportacao::create(['user_id' => $user->id, 'tipo_efd' => 'EFD ICMS/IPI']);

    Participante::create([
        'user_id' => $user->id,
        'documento' => '11444777000161',
        'razao_social' => 'IMPORTADO EFD LTDA',
        'importacao_efd_id' => $imp->id,
    ]);

    actingAs($user)->get('/app/participantes')
        ->assertOk()
        ->assertSee('EFD ICMS/IPI');
});

it('filtra por origem derivada (manual x efd)', function () {
    $user = User::factory()->create();
    $imp = EfdImportacao::create(['user_id' => $user->id, 'tipo_efd' => 'EFD ICMS/IPI']);

    Participante::create(['user_id' => $user->id, 'documento' => '11444777000161', 'razao_social' => 'IMPORTADO EFD LTDA', 'importacao_efd_id' => $imp->id]);
    Participante::create(['user_id' => $user->id, 'documento' => '11444777000242', 'razao_social' => 'CADASTRADO MANUAL LTDA', 'origem_tipo' => 'MANUAL']);

    actingAs($user)->get('/app/participantes?origem=manual')
        ->assertOk()
        ->assertSee('CADASTRADO MANUAL LTDA')
        ->assertDontSee('IMPORTADO EFD LTDA');

    actingAs($user)->get('/app/participantes?origem=efd')
        ->assertOk()
        ->assertSee('IMPORTADO EFD LTDA')
        ->assertDontSee('CADASTRADO MANUAL LTDA');
});

it('exibe badges de todas as fontes consultadas na ultima consulta', function () {
    $user = User::factory()->create();
    $p = Participante::create([
        'user_id' => $user->id,
        'documento' => '11444777000161',
        'razao_social' => 'CONSULTADO LTDA',
        'ultima_consulta_em' => now(),
    ]);

    $lote = ConsultaLote::create([
        'user_id' => $user->id,
        'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1,
        'creditos_cobrados' => 0,
        'tab_id' => 'tab-badges-fontes',
        'processado_em' => now(),
    ]);

    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id,
        'participante_id' => $p->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => [
            'situacao_cadastral' => 'ATIVA',
            'cnd_federal' => ['status' => 'NEGATIVA', 'data_validade' => now()->addMonths(3)->toDateString()],
            'crf_fgts' => ['status' => 'REGULAR'],
            'crf_fgts' => ['status' => 'REGULAR'],
        ],
        'consultado_em' => now(),
    ]);

    actingAs($user)->get('/app/participantes')
        ->assertOk()
        ->assertSee('Federal')
        ->assertSee('FGTS')
        ->assertSee('FGTS');
});

it('gera dossie em lote de participantes (PDF) para os ids selecionados', function () {
    $user = User::factory()->create();
    $p1 = Participante::create(['user_id' => $user->id, 'documento' => '11444777000161', 'razao_social' => 'DOSSIE UM LTDA']);
    $p2 = Participante::create(['user_id' => $user->id, 'documento' => '11444777000242', 'razao_social' => 'DOSSIE DOIS LTDA']);
    // Participante de OUTRO usuário não pode entrar mesmo com id na lista.
    $outro = User::factory()->create();
    $alheio = Participante::create(['user_id' => $outro->id, 'documento' => '11444777000323', 'razao_social' => 'ALHEIO LTDA']);

    $resp = actingAs($user)->post('/app/participantes/dossie-lote', [
        'ids' => [$p1->id, $p2->id, $alheio->id],
    ]);

    $resp->assertOk();
    expect($resp->headers->get('content-type'))->toContain('pdf');
    expect($resp->headers->get('content-disposition'))->toContain('dossies_participantes_');
});

it('exibe os botoes de dossie na listagem de participantes', function () {
    $user = User::factory()->create();
    Participante::create(['user_id' => $user->id, 'documento' => '11444777000161', 'razao_social' => 'QUALQUER LTDA']);

    actingAs($user)->get('/app/participantes')
        ->assertOk()
        ->assertSee('btn-dossie-lote-header', false)
        ->assertSee('btn-dossie-lote', false)
        ->assertSee('modal-dossie-lote', false)
        ->assertSee('/app/participantes/dossie-lote', false);
});
