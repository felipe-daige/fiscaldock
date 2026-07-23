<?php

use App\Models\EfdDivergencia;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\User;
use App\Services\EfdAuditoriaService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function spedFixture(array $linhas): string
{
    return implode("\n", array_map(fn ($l) => "|{$l}|", $linhas))."\n";
}

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->cliente = \DB::table('clientes')->insertGetId([
        'user_id' => $this->user->id,
        'razao_social' => 'TESTE',
        'documento' => '00000000000100',
        'is_empresa_propria' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $sped = spedFixture([
        // 1 C100 normal + 1 C170
        'C100|0|1|FORN1|55|00|1|100|35240100000000000000000000000000000000000001|01012024|01012024|100.00|0|0|',
        'C170|1|ITEM01|DESCRICAO|1|UN|100.00|',
        // 1 C100 cancelada (deve virar divergencia INFO)
        'C100|0|1|FORN1|55|02|1|101|35240100000000000000000000000000000000000002|02012024|02012024|200.00|0|0|',
        // 1 C100 com C170 mas item nao foi gravado no banco (deve virar AVISO duplicacao)
        'C100|0|1|FORN1|55|00|1|102|35240100000000000000000000000000000000000003|03012024|03012024|300.00|0|0|',
        'C170|1|ITEM01|DESCRICAO|1|UN|300.00|',
    ]);

    $this->imp = EfdImportacao::create([
        'user_id' => $this->user->id,
        'tipo_efd' => 'EFD ICMS/IPI',
        'filename' => 'fixture.txt',
        'status' => 'concluido',
        'arquivo_base64' => json_encode($sped),
    ]);

    // Banco: simula que pipeline gravou:
    //  - nota 100 com 1 C170 (correto)
    //  - nota 102 sem C170 (perdeu)
    //  - nota cancelada 101 nao foi gravada (esperado)
    $nota100 = EfdNota::create([
        'user_id' => $this->user->id,
        'cliente_id' => $this->cliente,
        'importacao_id' => $this->imp->id,
        'chave_acesso' => '35240100000000000000000000000000000000000001',
        'modelo' => '55',
        'numero' => 100,
        'serie' => '1',
        'data_emissao' => '2024-01-01',
        'tipo_operacao' => 'entrada',
        'origem_arquivo' => 'fiscal',
        'valor_total' => 100,
    ]);
    \DB::table('efd_notas_itens')->insert([
        'efd_nota_id' => $nota100->id,
        'user_id' => $this->user->id,
        'numero_item' => 1,
        'codigo_item' => 'ITEM01',
        'valor_total' => 100,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    EfdNota::create([
        'user_id' => $this->user->id,
        'cliente_id' => $this->cliente,
        'importacao_id' => $this->imp->id,
        'chave_acesso' => '35240100000000000000000000000000000000000003',
        'modelo' => '55',
        'numero' => 102,
        'serie' => '1',
        'data_emissao' => '2024-01-03',
        'tipo_operacao' => 'entrada',
        'origem_arquivo' => 'fiscal',
        'valor_total' => 300,
    ]);
});

it('detecta C100 cancelada como divergencia INFO', function () {
    $svc = app(EfdAuditoriaService::class);
    $svc->auditar($this->imp);

    $canceladas = EfdDivergencia::where('motivo', EfdDivergencia::MOTIVO_CANCELADA_DESCARTADA)->get();

    expect($canceladas)->toHaveCount(1);
    expect($canceladas->first()->severidade)->toBe('info');
    expect($canceladas->first()->numero_documento)->toBe(101);
    expect($canceladas->first()->bloco)->toBe('C100');
});

it('detecta C170 do SPED ausente no banco (perdido no pipeline)', function () {
    $svc = app(EfdAuditoriaService::class);
    $svc->auditar($this->imp);

    $perdidos = EfdDivergencia::where('motivo', EfdDivergencia::MOTIVO_DUPLICADA_PROCESSAMENTO)
        ->where('bloco', 'C170')
        ->get();

    expect($perdidos)->toHaveCount(1);
    expect($perdidos->first()->numero_documento)->toBe(102);
});

it('idempotente: rodar duas vezes nao duplica divergencias', function () {
    $svc = app(EfdAuditoriaService::class);
    $svc->auditar($this->imp);
    $svc->auditar($this->imp);

    expect(EfdDivergencia::count())->toBe(2);
});

it('retorna sumario com contagens', function () {
    $svc = app(EfdAuditoriaService::class);
    $resultado = $svc->auditar($this->imp);

    expect($resultado)->toHaveKeys(['c100_sped', 'c100_banco', 'canceladas', 'c170_sped', 'c170_banco', 'divergencias_geradas']);
    expect($resultado['c100_sped'])->toBe(3);
    expect($resultado['c100_banco'])->toBe(2);
    expect($resultado['canceladas'])->toBe(1);
});

it('detecta A100 (NFS-e) do SPED ausente no banco — oráculo cobre contribuições', function () {
    // SPED PIS/COFINS: 2 A100 com chave (código de verificação). Só 1 no banco.
    $sped = spedFixture([
        'A100|1|0|FOR7|00|1|0|500|COD9CHK01|01022026|01022026|1000,00|',
        'A170|1|SERV01|Consultoria|1000,00|',
        'A100|1|0|FOR7|00|1|0|501|COD9CHK02|05022026|05022026|2000,00|', // esta some do banco
    ]);
    $imp = EfdImportacao::create([
        'user_id' => $this->user->id, 'cliente_id' => $this->cliente, 'tipo_efd' => 'EFD PIS/COFINS',
        'filename' => 'contrib.txt', 'status' => 'concluido',
        'arquivo_base64' => EfdImportacao::encodeConteudoSped($sped),
    ]);
    // Banco: só a 1ª NFS-e (com 1 item).
    $nfse = EfdNota::create([
        'user_id' => $this->user->id, 'cliente_id' => $this->cliente, 'importacao_id' => $imp->id,
        'chave_acesso' => 'COD9CHK01', 'modelo' => '00', 'numero' => 500, 'serie' => '1',
        'data_emissao' => '2026-02-01', 'tipo_operacao' => 'saida', 'origem_arquivo' => 'contribuicoes', 'valor_total' => 1000,
    ]);
    \DB::table('efd_notas_itens')->insert([
        'efd_nota_id' => $nfse->id, 'user_id' => $this->user->id, 'numero_item' => 1,
        'codigo_item' => 'SERV01', 'valor_total' => 1000, 'created_at' => now(), 'updated_at' => now(),
    ]);

    app(EfdAuditoriaService::class)->auditar($imp);

    // A 2ª A100 (COD9CHK02) faltando → ERRO com bloco A100.
    $erro = EfdDivergencia::where('importacao_id', $imp->id)->where('bloco', 'A100')
        ->where('severidade', 'erro')->get();
    expect($erro)->toHaveCount(1);
    expect($erro->first()->chave_acesso)->toBe('COD9CHK02');
});
