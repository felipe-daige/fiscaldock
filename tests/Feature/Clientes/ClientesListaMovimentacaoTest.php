<?php

use App\Models\Cliente;
use App\Models\EfdImportacao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function notaEfdCliente(User $user, Cliente $cliente, float $valor, string $tipoOperacao = 'saida', ?string $dataEmissao = null): void
{
    $imp = EfdImportacao::firstOrCreate(
        ['user_id' => $user->id, 'tipo_efd' => 'EFD ICMS/IPI'],
        []
    );

    DB::table('efd_notas')->insert([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'importacao_id' => $imp->id,
        'modelo' => '55',
        'numero' => random_int(1, 9999999),
        'tipo_operacao' => $tipoOperacao,
        'origem_arquivo' => 'fiscal',
        'valor_total' => $valor,
        'data_emissao' => $dataEmissao ?? '2026-03-15',
        'cancelada' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

it('ordena clientes por valor movimentado desc por padrao', function () {
    $user = User::factory()->create();
    $menor = Cliente::create(['user_id' => $user->id, 'documento' => '11444777000161', 'razao_social' => 'AAA MENOR LTDA']);
    $maior = Cliente::create(['user_id' => $user->id, 'documento' => '11444777000242', 'razao_social' => 'ZZZ MAIOR LTDA']);

    notaEfdCliente($user, $menor, 100.0);
    notaEfdCliente($user, $maior, 9000.0);

    // Alfabético colocaria AAA primeiro; por volume, ZZZ vence.
    actingAs($user)->get('/app/clientes')
        ->assertOk()
        ->assertSeeInOrder(['ZZZ MAIOR LTDA', 'AAA MENOR LTDA']);
});

it('exibe valor total, entradas/saidas e ultima emissao na coluna de movimentacao', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create(['user_id' => $user->id, 'documento' => '11444777000161', 'razao_social' => 'MOVIMENTADA LTDA']);

    notaEfdCliente($user, $cliente, 1500.5, 'entrada', '2026-01-10');
    notaEfdCliente($user, $cliente, 500.0, 'saida', '2026-04-20');

    actingAs($user)->get('/app/clientes')
        ->assertOk()
        ->assertSee('R$ 2.000,50')
        ->assertSee('2 notas')
        ->assertSee('até 04/2026');
});

it('permite ordenar por nome via param ordem', function () {
    $user = User::factory()->create();
    $semNota = Cliente::create(['user_id' => $user->id, 'documento' => '11444777000161', 'razao_social' => 'AAA SEM NOTA LTDA']);
    $comNota = Cliente::create(['user_id' => $user->id, 'documento' => '11444777000242', 'razao_social' => 'ZZZ COM NOTA LTDA']);

    notaEfdCliente($user, $comNota, 9000.0);

    actingAs($user)->get('/app/clientes?ordem=nome')
        ->assertOk()
        ->assertSeeInOrder(['AAA SEM NOTA LTDA', 'ZZZ COM NOTA LTDA']);
});

it('mantem a contagem de participantes com a ordenacao por movimentacao', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create(['user_id' => $user->id, 'documento' => '11444777000161', 'razao_social' => 'COM PARTICIPANTES LTDA']);
    \App\Models\Participante::create(['user_id' => $user->id, 'cliente_id' => $cliente->id, 'documento' => '11444777000242', 'razao_social' => 'PART LTDA']);

    notaEfdCliente($user, $cliente, 100.0);

    $resp = actingAs($user)->get('/app/clientes')->assertOk();

    // participantes_count sobrevive ao select('clientes.*') do joinSub.
    expect($resp->viewData('clientes')->getCollection()->firstWhere('id', $cliente->id)->participantes_count)->toBe(1);
});

it('exibe certidoes e status de consulta feita com escopo cliente (cliente_id direto)', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create(['user_id' => $user->id, 'documento' => '11444777000161', 'razao_social' => 'CONSULTADA DIRETO LTDA']);

    $lote = \App\Models\ConsultaLote::create([
        'user_id' => $user->id,
        'status' => \App\Models\ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1,
        'creditos_cobrados' => 0,
        'tab_id' => 'tab-escopo-cliente',
        'processado_em' => now(),
    ]);

    // Sem participante: resultado gravado direto no cliente (escopo cliente).
    \App\Models\ConsultaResultado::create([
        'consulta_lote_id' => $lote->id,
        'cliente_id' => $cliente->id,
        'status' => \App\Models\ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => [
            'situacao_cadastral' => 'ATIVA',
            'cnd_federal' => ['status' => 'NEGATIVA'],
            'cndt' => ['status' => 'NEGATIVA'],
        ],
        'consultado_em' => now(),
    ]);

    actingAs($user)->get('/app/clientes')
        ->assertOk()
        ->assertSee('Consultado recentemente')
        ->assertSee('Federal')
        ->assertSee('CNDT')
        ->assertDontSee('Sem certidões consultadas');
});

function notaXmlCliente(User $user, Cliente $cliente, float $valor, string $chave, int $tipoNota = 1, string $dataEmissao = '2026-05-10'): void
{
    DB::table('xml_notas')->insert([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'tipo_documento' => 'NFE',
        'origem' => 'importacao',
        'chave_acesso' => $chave,
        'numero_documento' => (string) random_int(1, 9999999),
        'data_emissao' => $dataEmissao,
        'valor_total' => $valor,
        'tipo_nota' => $tipoNota,
        'emit_documento' => '11444777000161',
        'dest_documento' => '11444777000242',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

it('soma notas XML na movimentacao do cliente', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create(['user_id' => $user->id, 'documento' => '11444777000161', 'razao_social' => 'SO XML LTDA']);

    notaXmlCliente($user, $cliente, 3000.0, str_repeat('1', 44));

    actingAs($user)->get('/app/clientes')
        ->assertOk()
        ->assertSee('R$ 3.000,00')
        ->assertSee('1 nota');
});

it('nao conta duas vezes a mesma nota presente no XML e no EFD (dedup por chave)', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create(['user_id' => $user->id, 'documento' => '11444777000161', 'razao_social' => 'DUPLICADA LTDA']);
    $chave = str_repeat('2', 44);

    // Mesma nota nas duas origens: EFD vence, XML é ignorado.
    $imp = EfdImportacao::firstOrCreate(['user_id' => $user->id, 'tipo_efd' => 'EFD ICMS/IPI'], []);
    DB::table('efd_notas')->insert([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'importacao_id' => $imp->id,
        'modelo' => '55',
        'numero' => 123,
        'chave_acesso' => $chave,
        'tipo_operacao' => 'saida',
        'origem_arquivo' => 'fiscal',
        'valor_total' => 1000.0,
        'data_emissao' => '2026-03-15',
        'cancelada' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    notaXmlCliente($user, $cliente, 1000.0, $chave);
    // XML exclusivo (chave diferente) entra na soma.
    notaXmlCliente($user, $cliente, 500.0, str_repeat('3', 44));

    actingAs($user)->get('/app/clientes')
        ->assertOk()
        ->assertSee('R$ 1.500,00')
        ->assertSee('2 notas');
});
