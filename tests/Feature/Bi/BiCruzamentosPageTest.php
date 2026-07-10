<?php

use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\Participante;
use App\Models\User;
use App\Services\RiskScoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function seedFornecedorIrregularComCompra(User $user): void
{
    $cliente = Cliente::create([
        'user_id' => $user->id, 'is_empresa_propria' => true,
        'tipo_pessoa' => 'PJ', 'documento' => '00000000000191', 'razao_social' => 'Escritorio ME',
    ]);
    $imp = EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'tipo_efd' => 'EFD ICMS/IPI', 'status' => 'concluido',
    ]);
    $forn = Participante::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id,
        'documento' => '11111111000111', 'razao_social' => 'Fornecedor Devedor SA',
    ]);
    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'plano_id' => null, 'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1, 'creditos_cobrados' => 10, 'tab_id' => 'tab-pg', 'processado_em' => now(),
    ]);
    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id, 'participante_id' => $forn->id, 'status' => 'sucesso',
        'resultado_dados' => ['cnd_federal' => ['status' => 'Positiva']], 'consultado_em' => now(),
    ]);
    app(RiskScoreService::class)->atualizarScore($forn, ['cnd_federal' => ['status' => 'Positiva']]);
    EfdNota::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'participante_id' => $forn->id,
        'importacao_id' => $imp->id, 'chave_acesso' => '35240000000000000000000000000000000000040001',
        'modelo' => '55', 'numero' => 40001, 'serie' => '0', 'data_emissao' => '2026-01-15',
        'tipo_operacao' => 'entrada', 'valor_total' => 1500.00, 'valor_desconto' => 0,
        'origem_arquivo' => 'fiscal', 'metadados' => [],
    ]);
}

it('renderiza a página de cruzamentos com o fornecedor irregular e suas compras', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    seedFornecedorIrregularComCompra($user);

    actingAs($user)
        ->get('/app/bi/cruzamentos')
        ->assertOk()
        ->assertSee('Cruzamentos')
        ->assertSee('Fornecedor Devedor SA')
        ->assertSee('CND Federal positiva')
        ->assertSee('1.500,00');
});

it('redireciona visitante não autenticado da página de cruzamentos', function () {
    $this->get('/app/bi/cruzamentos')->assertRedirect();
});

it('exibe o botão de aplicar filtro', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);

    actingAs($user)->get('/app/bi/cruzamentos')
        ->assertOk()
        ->assertSee('Aplicar filtro');
});

it('explica que a tela vazia é cobertura de dado, não bug (CNPJ consultado não é fornecedor)', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    $cliente = Cliente::create([
        'user_id' => $user->id, 'is_empresa_propria' => true,
        'tipo_pessoa' => 'PJ', 'documento' => '00000000000191', 'razao_social' => 'Escritorio ME',
    ]);
    $imp = EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'tipo_efd' => 'EFD ICMS/IPI', 'status' => 'concluido',
    ]);
    // CNPJ consultado (regular) que só aparece em SAÍDA — é cliente, não fornecedor (espelha prod).
    $p = Participante::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id,
        'documento' => '11111111000111', 'razao_social' => 'Cliente Nao Fornecedor SA',
    ]);
    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'plano_id' => null, 'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1, 'creditos_cobrados' => 10, 'tab_id' => 'tab-vazio', 'processado_em' => now(),
    ]);
    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id, 'participante_id' => $p->id, 'status' => 'sucesso',
        'resultado_dados' => ['cnd_federal' => ['status' => 'Negativa']], 'consultado_em' => now(),
    ]);
    app(RiskScoreService::class)->atualizarScore($p, ['cnd_federal' => ['status' => 'Negativa']]);
    EfdNota::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'participante_id' => $p->id,
        'importacao_id' => $imp->id, 'chave_acesso' => '35240000000000000000000000000000000000099001',
        'modelo' => '55', 'numero' => 99001, 'serie' => '0', 'data_emissao' => '2026-01-15',
        'tipo_operacao' => 'saida', 'valor_total' => 5000.00, 'valor_desconto' => 0,
        'origem_arquivo' => 'fiscal', 'metadados' => [],
    ]);

    actingAs($user)->get('/app/bi/cruzamentos')
        ->assertOk()
        ->assertSee('CNPJs consultados')
        ->assertSee('não é erro, é cobertura de dado');
});

it('mostra o card de risco de fornecedores na tela de alertas quando há risco', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    seedFornecedorIrregularComCompra($user);

    actingAs($user)
        ->get('/app/clearance/alertas')
        ->assertOk()
        ->assertSee('Risco de fornecedores')
        ->assertSee('/app/bi/cruzamentos'); // link pro detalhe
});

it('não mostra o card de risco na tela de alertas quando não há risco', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);

    actingAs($user)
        ->get('/app/clearance/alertas')
        ->assertOk()
        ->assertDontSee('Risco de fornecedores');
});
