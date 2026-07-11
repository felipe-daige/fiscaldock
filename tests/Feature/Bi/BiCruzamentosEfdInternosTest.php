<?php

use App\Models\Cliente;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\Participante;
use App\Models\User;
use App\Services\Bi\CruzamentosEfdInternosService;
use App\Services\RiskScoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

/**
 * Fase 3 do BI cruzamentos: EFD-internos — M400 × CST das saídas e F600 × fonte pagadora.
 */
function f3Base(User $user): array
{
    $cliente = Cliente::create([
        'user_id' => $user->id, 'is_empresa_propria' => true,
        'tipo_pessoa' => 'PJ', 'documento' => '00000000000191', 'razao_social' => 'Escritorio ME',
    ]);
    $imp = EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'tipo_efd' => 'EFD PIS/COFINS',
        'status' => 'concluido', 'periodo_inicio' => '2026-01-01', 'periodo_fim' => '2026-01-31',
    ]);

    return [$cliente, $imp];
}

function f3Apuracao(User $user, Cliente $cliente, EfdImportacao $imp, float $m400 = 1000.00): void
{
    DB::table('efd_apuracoes_contribuicoes')->insert([
        'importacao_id' => $imp->id, 'user_id' => $user->id, 'cliente_id' => $cliente->id,
        'pis_nao_tributado' => json_encode(['M400' => [
            ['PIS_CST' => '04', 'PIS_RECEITA_TOTAL' => $m400, 'PIS_DESCRICAO' => ''],
        ]]),
        'created_at' => now(), 'updated_at' => now(),
    ]);
}

function f3NotaComItem(User $user, Cliente $cliente, EfdImportacao $imp, array $notaOv = [], array $itemOv = []): EfdNota
{
    $nota = EfdNota::create(array_merge([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'importacao_id' => $imp->id,
        'chave_acesso' => '3526'.str_pad((string) random_int(1, 999999), 40, '0', STR_PAD_LEFT),
        'modelo' => '55', 'numero' => random_int(1, 99999), 'serie' => '1', 'data_emissao' => '2026-01-15',
        'tipo_operacao' => 'saida', 'valor_total' => 1000, 'valor_desconto' => 0,
        'origem_arquivo' => 'contribuicoes', 'cancelada' => false, 'metadados' => [],
    ], $notaOv));

    DB::table('efd_notas_itens')->insert(array_merge([
        'efd_nota_id' => $nota->id, 'user_id' => $user->id, 'numero_item' => 1, 'codigo_item' => 'X',
        'quantidade' => 1, 'valor_total' => 1000, 'cfop' => 5102, 'cst_pis' => '06',
        'valor_icms' => 0, 'valor_pis' => 0, 'valor_cofins' => 0,
        'created_at' => now(), 'updated_at' => now(),
    ], $itemOv));

    return $nota;
}

function f3Retencao(User $user, Cliente $cliente, EfdImportacao $imp, array $ov = []): void
{
    DB::table('efd_retencoes_fonte')->insert(array_merge([
        'importacao_id' => $imp->id, 'user_id' => $user->id, 'cliente_id' => $cliente->id,
        'natureza' => '03', 'natureza_receita' => '01', 'data_retencao' => '2026-01-20',
        'base_calculo' => 100, 'cod_receita' => '5952',
        'valor_total' => 10, 'valor_pis' => 2, 'valor_cofins' => 8, 'cnpj' => '11111111000111',
        'created_at' => now(), 'updated_at' => now(),
    ], $ov));
}

it('M400 batendo com os itens CST 04-09 fica verde', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    [$cliente, $imp] = f3Base($user);
    f3Apuracao($user, $cliente, $imp, 1000.00);
    f3NotaComItem($user, $cliente, $imp);

    $linhas = app(CruzamentosEfdInternosService::class)->receitasNaoTributadasPorCompetencia($user->id);

    expect($linhas)->toHaveCount(1)
        ->and($linhas[0]['competencia'])->toBe('2026-01')
        ->and($linhas[0]['declarado'])->toBe(1000.00)
        ->and($linhas[0]['computado'])->toBe(1000.00)
        ->and($linhas[0]['flag'])->toBe('verde');
});

it('divergência M400 × itens vira flag vermelho e itens tributados/cancelados ficam fora', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    [$cliente, $imp] = f3Base($user);
    f3Apuracao($user, $cliente, $imp, 2000.00);
    f3NotaComItem($user, $cliente, $imp); // 1000 CST 06 conta
    f3NotaComItem($user, $cliente, $imp, [], ['cst_pis' => '01', 'valor_total' => 500]); // tributado: fora
    f3NotaComItem($user, $cliente, $imp, ['cancelada' => true], ['valor_total' => 400]); // cancelada: fora
    f3NotaComItem($user, $cliente, $imp, ['origem_arquivo' => 'fiscal'], ['valor_total' => 300]); // fiscal: fora

    $linhas = app(CruzamentosEfdInternosService::class)->receitasNaoTributadasPorCompetencia($user->id);

    expect($linhas[0]['declarado'])->toBe(2000.00)
        ->and($linhas[0]['computado'])->toBe(1000.00)
        ->and($linhas[0]['flag'])->toBe('vermelho');
});

it('filtro de período recorta as competências do M400', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    [$cliente, $imp] = f3Base($user);
    f3Apuracao($user, $cliente, $imp, 1000.00);
    f3NotaComItem($user, $cliente, $imp);

    $imp2 = EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'tipo_efd' => 'EFD PIS/COFINS',
        'status' => 'concluido', 'periodo_inicio' => '2026-02-01', 'periodo_fim' => '2026-02-28',
    ]);
    f3Apuracao($user, $cliente, $imp2, 700.00);
    f3NotaComItem($user, $cliente, $imp2, ['data_emissao' => '2026-02-15'], ['valor_total' => 700]);

    $svc = app(CruzamentosEfdInternosService::class);

    expect($svc->receitasNaoTributadasPorCompetencia($user->id))->toHaveCount(2)
        ->and($svc->receitasNaoTributadasPorCompetencia($user->id, [
            'data_inicio' => '2026-02-01', 'data_fim' => '2026-02-28',
        ]))->toHaveCount(1)
        ->and($svc->receitasNaoTributadasPorCompetencia($user->id, [
            'data_inicio' => '2026-02-01', 'data_fim' => '2026-02-28',
        ])[0]['competencia'])->toBe('2026-02');
});

it('retenções agrupam por fonte pagadora com regularidade da projeção canônica', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    [$cliente, $imp] = f3Base($user);

    $fonte = Participante::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id,
        'documento' => '11111111000111', 'razao_social' => 'Fonte Pagadora SA',
    ]);
    app(RiskScoreService::class)->atualizarScore($fonte, ['cnd_federal' => ['status' => 'Positiva']]);

    f3Retencao($user, $cliente, $imp);
    f3Retencao($user, $cliente, $imp, ['valor_total' => 20, 'valor_pis' => 4, 'valor_cofins' => 16]);
    f3Retencao($user, $cliente, $imp, ['cnpj' => '22222222000122', 'valor_total' => 5]); // outra fonte, sem participante

    $linhas = app(CruzamentosEfdInternosService::class)->retencoesPorFonte($user->id);

    expect($linhas)->toHaveCount(2)
        ->and($linhas[0]['cnpj'])->toBe('11111111000111') // maior retido primeiro
        ->and($linhas[0]['razao_social'])->toBe('Fonte Pagadora SA')
        ->and($linhas[0]['qtd'])->toBe(2)
        ->and($linhas[0]['valor_pis'])->toBe(6.00)
        ->and($linhas[0]['valor_cofins'])->toBe(24.00)
        ->and($linhas[0]['valor_total'])->toBe(30.00)
        ->and($linhas[0]['consultada'])->toBeTrue()
        ->and($linhas[0]['motivos'])->toContain('CND Federal positiva')
        ->and($linhas[1]['consultada'])->toBeFalse()
        ->and($linhas[1]['razao_social'])->toBe('—');
});

it('retenções respeitam filtro de período (data_retencao) e cliente', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    [$cliente, $imp] = f3Base($user);
    f3Retencao($user, $cliente, $imp); // 2026-01-20
    f3Retencao($user, $cliente, $imp, ['data_retencao' => '2026-03-10']);

    $svc = app(CruzamentosEfdInternosService::class);

    expect($svc->retencoesPorFonte($user->id)[0]['qtd'])->toBe(2)
        ->and($svc->retencoesPorFonte($user->id, [
            'data_inicio' => '2026-03-01', 'data_fim' => '2026-03-31',
        ])[0]['qtd'])->toBe(1)
        ->and($svc->retencoesPorFonte($user->id, ['cliente_id' => $cliente->id + 999]))->toHaveCount(0);
});

it('não vaza dado de outro usuário', function () {
    $dono = User::factory()->trialAtivo()->create(['credits' => 100]);
    [$cliente, $imp] = f3Base($dono);
    f3Apuracao($dono, $cliente, $imp);
    f3NotaComItem($dono, $cliente, $imp);
    f3Retencao($dono, $cliente, $imp);

    $outro = User::factory()->trialAtivo()->create(['credits' => 100]);
    $svc = app(CruzamentosEfdInternosService::class);

    expect($svc->receitasNaoTributadasPorCompetencia($outro->id))->toHaveCount(0)
        ->and($svc->retencoesPorFonte($outro->id))->toHaveCount(0);
});

it('página renderiza as seções novas com flag e fonte pagadora', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    [$cliente, $imp] = f3Base($user);
    f3Apuracao($user, $cliente, $imp, 1000.00);
    f3NotaComItem($user, $cliente, $imp);
    Participante::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id,
        'documento' => '11111111000111', 'razao_social' => 'Fonte Pagadora SA',
    ]);
    f3Retencao($user, $cliente, $imp);

    actingAs($user)->get('/app/bi/cruzamentos')
        ->assertOk()
        ->assertSee('Receitas não tributadas declaradas (M400)')
        ->assertSee('Retenções na fonte (F600)')
        ->assertSee('Fonte Pagadora SA')
        ->assertSee('Não consultada')
        ->assertSee('01/2026');
});
