<?php

use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\User;
use App\Services\Efd\ConsolidadoFiscalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->cliente = DB::table('clientes')->insertGetId([
        'user_id' => $this->user->id, 'razao_social' => 'VAREJO', 'documento' => '09305162000293',
        'is_empresa_propria' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $this->imp = EfdImportacao::create([
        'user_id' => $this->user->id, 'cliente_id' => $this->cliente,
        'tipo_efd' => 'EFD ICMS/IPI', 'filename' => 'x.txt', 'status' => 'concluido', 'iniciado_em' => now(),
    ]);
});

function notaCom(int $userId, int $cli, int $impId, string $chave, string $oper, bool $cancelada = false): int
{
    return EfdNota::create([
        'user_id' => $userId, 'cliente_id' => $cli, 'importacao_id' => $impId,
        'chave_acesso' => str_pad($chave, 44, '0'), 'modelo' => '65', 'numero' => crc32($chave) % 100000,
        'serie' => '1', 'data_emissao' => '2026-01-10', 'tipo_operacao' => $oper,
        'valor_total' => 100, 'cancelada' => $cancelada,
    ])->id;
}

function cons(int $notaId, int $userId, string $cfop, string $cst, string $aliq, float $oper, float $bc, float $icms, float $st = 0): void
{
    DB::table('efd_notas_consolidados')->insert([
        'efd_nota_id' => $notaId, 'user_id' => $userId, 'cfop' => $cfop, 'cst_icms' => $cst,
        'aliquota_icms' => $aliq, 'valor_operacao' => $oper, 'valor_bc_icms' => $bc, 'valor_icms' => $icms,
        'valor_icms_st' => $st, 'valor_bc_icms_st' => 0, 'valor_reducao_bc' => 0, 'valor_ipi' => 0,
        'created_at' => now(), 'updated_at' => now(),
    ]);
}

it('agrega por CFOP/CST/alíquota e separa saída de entrada', function () {
    // 2 NFC-e saída mesmo CFOP/CST/alíq (agregam numa linha)
    $n1 = notaCom($this->user->id, $this->cliente, $this->imp->id, 'A', 'saida');
    $n2 = notaCom($this->user->id, $this->cliente, $this->imp->id, 'B', 'saida');
    cons($n1, $this->user->id, '5102', '020', '17.00', 100, 100, 17);
    cons($n2, $this->user->id, '5102', '020', '17.00', 200, 200, 34);
    // 1 saída CFOP diferente
    $n3 = notaCom($this->user->id, $this->cliente, $this->imp->id, 'C', 'saida');
    cons($n3, $this->user->id, '5405', '060', '0.00', 50, 0, 0, 9);
    // 1 entrada
    $n4 = notaCom($this->user->id, $this->cliente, $this->imp->id, 'D', 'entrada');
    cons($n4, $this->user->id, '1102', '000', '12.00', 80, 80, 9.6);

    $cf = (new ConsolidadoFiscalService)->porImportacao($this->imp->id, $this->user->id);

    expect($cf['tem_dados'])->toBeTrue();
    // 3 linhas de saída-CFOP/CST/alíq distintas? não — 5102/020/17 agrega n1+n2 → 2 linhas saída + 1 entrada
    expect($cf['linhas']->where('tipo_operacao', 'saida'))->toHaveCount(2);

    $l5102 = $cf['linhas']->firstWhere('cfop', '5102');
    expect((int) $l5102->notas)->toBe(2);            // n1 + n2 agregadas
    expect((float) $l5102->operacao)->toBe(300.0);   // 100 + 200
    expect((float) $l5102->icms)->toBe(51.0);        // 17 + 34

    // Totais de saída
    expect($cf['saidas']['notas'])->toBe(3);          // n1, n2, n3
    expect($cf['saidas']['operacao'])->toBe(350.0);   // 300 + 50
    expect($cf['saidas']['icms'])->toBe(51.0);
    // Entrada isolada
    expect($cf['entradas']['notas'])->toBe(1);
    expect($cf['entradas']['operacao'])->toBe(80.0);
});

it('conta notas distintas: nota com 2 CFOPs não é contada 2x no total', function () {
    // 1 NFC-e com 2 linhas C190 (2 CFOPs) — 1 nota, 2 linhas
    $n = notaCom($this->user->id, $this->cliente, $this->imp->id, 'X', 'saida');
    cons($n, $this->user->id, '5102', '020', '17.00', 100, 100, 17);
    cons($n, $this->user->id, '5405', '060', '0.00', 50, 0, 0, 9);

    $cf = (new ConsolidadoFiscalService)->porImportacao($this->imp->id, $this->user->id);

    expect($cf['linhas']->where('tipo_operacao', 'saida'))->toHaveCount(2); // 2 linhas
    expect($cf['saidas']['notas'])->toBe(1);                                 // mas 1 nota distinta
    expect($cf['saidas']['operacao'])->toBe(150.0);
});

it('ignora notas canceladas', function () {
    $ok = notaCom($this->user->id, $this->cliente, $this->imp->id, 'OK', 'saida');
    $canc = notaCom($this->user->id, $this->cliente, $this->imp->id, 'CANC', 'saida', cancelada: true);
    cons($ok, $this->user->id, '5102', '020', '17.00', 100, 100, 17);
    cons($canc, $this->user->id, '5102', '020', '17.00', 999, 999, 999);

    $cf = (new ConsolidadoFiscalService)->porImportacao($this->imp->id, $this->user->id);

    expect($cf['saidas']['notas'])->toBe(1);
    expect($cf['saidas']['operacao'])->toBe(100.0); // cancelada fora
});

it('porCliente acumula TODAS as importações do cliente', function () {
    // 2ª importação do mesmo cliente
    $imp2 = EfdImportacao::create([
        'user_id' => $this->user->id, 'cliente_id' => $this->cliente,
        'tipo_efd' => 'EFD ICMS/IPI', 'filename' => 'fev.txt', 'status' => 'concluido', 'iniciado_em' => now(),
    ]);
    $n1 = notaCom($this->user->id, $this->cliente, $this->imp->id, 'JAN', 'saida');
    $n2 = notaCom($this->user->id, $this->cliente, $imp2->id, 'FEV', 'saida');
    cons($n1, $this->user->id, '5102', '020', '17.00', 100, 100, 17);
    cons($n2, $this->user->id, '5102', '020', '17.00', 300, 300, 51);

    // Por importação: só o mês
    $jan = (new ConsolidadoFiscalService)->porImportacao($this->imp->id, $this->user->id);
    expect($jan['saidas']['operacao'])->toBe(100.0);

    // Por cliente: acumulado dos dois meses, agregado na mesma linha
    $acc = (new ConsolidadoFiscalService)->porCliente($this->cliente, $this->user->id);
    expect($acc['saidas']['notas'])->toBe(2);
    expect($acc['saidas']['operacao'])->toBe(400.0);
    expect($acc['saidas']['icms'])->toBe(68.0);
    expect($acc['linhas'])->toHaveCount(1); // mesmo CFOP/CST/alíq agrega
});

it('porCliente respeita filtro de período e não vaza outro cliente', function () {
    $outroCliente = DB::table('clientes')->insertGetId([
        'user_id' => $this->user->id, 'razao_social' => 'OUTRO', 'documento' => '11222333000181',
        'is_empresa_propria' => false, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $meu = notaCom($this->user->id, $this->cliente, $this->imp->id, 'MEU', 'saida');
    $alheio = notaCom($this->user->id, $outroCliente, $this->imp->id, 'ALHEIO', 'saida');
    cons($meu, $this->user->id, '5102', '020', '17.00', 100, 100, 17);
    cons($alheio, $this->user->id, '5102', '020', '17.00', 999, 999, 999);

    $cf = (new ConsolidadoFiscalService)->porCliente($this->cliente, $this->user->id);
    expect($cf['saidas']['operacao'])->toBe(100.0); // não soma o outro cliente

    // Período que exclui a nota (emissão 2026-01-10)
    $fora = (new ConsolidadoFiscalService)->porCliente($this->cliente, $this->user->id, ['de' => '2026-02-01']);
    expect($fora['tem_dados'])->toBeFalse();

    $dentro = (new ConsolidadoFiscalService)->porCliente($this->cliente, $this->user->id, ['de' => '2026-01-01', 'ate' => '2026-01-31']);
    expect($dentro['saidas']['operacao'])->toBe(100.0);
});

it('retorna vazio quando não há consolidado', function () {
    $cf = (new ConsolidadoFiscalService)->porImportacao($this->imp->id, $this->user->id);

    expect($cf['tem_dados'])->toBeFalse();
    expect($cf['linhas'])->toHaveCount(0);
    expect($cf['saidas']['notas'])->toBe(0);
});
