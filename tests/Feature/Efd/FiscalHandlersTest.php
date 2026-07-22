<?php

use App\Services\Efd\Driver\FiscalDriver;
use App\Services\Efd\Handlers\Handler0150;
use App\Services\Efd\Handlers\Handler0200;
use App\Services\Efd\Handlers\HandlerApuracaoE;
use App\Services\Efd\Handlers\HandlerC100;
use App\Services\Efd\Handlers\HandlerC170;
use App\Services\Efd\Handlers\HandlerC190;
use App\Services\Efd\Handlers\HandlerD100;
use App\Services\Efd\Handlers\HandlerD190;
use App\Services\Efd\Sped\SpedParser;
use App\Services\Efd\Sped\SpedRecord;

/**
 * Testa L2 (FiscalDriver + 8 handlers): índices de campo contra o set-node / Guia,
 * com linhas REAIS do UTIDA quando existem. Puro — o handler só mapeia (a engine F3
 * põe user_id/FK). O invariante crítico: C100 nunca dropa por COD_PART vazio.
 */
if (! function_exists('spedRec')) {
    function spedRec(string $linha): SpedRecord
    {
        return iterator_to_array((new SpedParser)->stream($linha))[0];
    }
}

// ── 0150 → participantes ────────────────────────────────────────────────────
it('Handler0150 mapeia participante (CNPJ em $p[5], não $p[4])', function () {
    // Linha real UTIDA (getnet). CNPJ=$p[5]; o set-node PIS/COFINS lia $p[4]=COD_PAIS.
    $linha = '|0150|FOR000000007|getnet adquirencia e servicos para meios de pagamento s.a.|01058|10440482000154||131042249116|3550308||av pres juscelino kubitschek|2041|wtorre jk|vila nova conceicao|';

    $row = (new Handler0150)->mapear(spedRec($linha), null);

    expect($row['documento'])->toBe('10440482000154')          // CNPJ, não '01058'
        ->and($row['razao_social'])->toBe('getnet adquirencia e servicos para meios de pagamento s.a.')
        ->and($row['inscricao_estadual'])->toBe('131042249116')
        ->and($row['codigo_municipal'])->toBe('3550308')
        ->and($row['bairro'])->toBe('vila nova conceicao')
        ->and($row['origem_tipo'])->toBe('SPED_EFD_FISCAL');
});

it('Handler0150 usa CPF quando não há CNPJ, e pula sem documento', function () {
    $cpf = (new Handler0150)->mapear(spedRec('|0150|X|Fulano|01058||12345678901|IE|3550308|'), null);
    expect($cpf['documento'])->toBe('12345678901');

    // Sem CNPJ nem CPF → não persistível.
    expect((new Handler0150)->mapear(spedRec('|0150|X|Anônimo|01058|||||'), null))->toBeNull();
});

// ── 0200 → efd_catalogo_itens ───────────────────────────────────────────────
it('Handler0200 preserva COD_ITEM literal (zeros à esquerda)', function () {
    $row = (new Handler0200)->mapear(spedRec('|0200|000247|ARROZ 5KG|7891234567890|X|KG|00|10063021|EX|0|LST|18,00|'), null);

    expect($row['cod_item'])->toBe('000247')  // NUNCA 247
        ->and($row['descr_item'])->toBe('ARROZ 5KG')
        ->and($row['cod_barra'])->toBe('7891234567890')
        ->and($row['unid_inv'])->toBe('KG')
        ->and($row['tipo_item'])->toBe('00')
        ->and($row['cod_ncm'])->toBe('10063021')
        ->and($row['aliq_icms'])->toBe('18.00');
});

// ── C100 → efd_notas ────────────────────────────────────────────────────────
it('HandlerC100 NUNCA dropa NFC-e (modelo 65) com COD_PART vazio', function () {
    // Linha real UTIDA — a classe que o merge n8n droppava.
    $linha = '|C100|1|0||65|00|002|1650|50260109305162000293650020000016501679079934|08012026|08012026|155,80|0|0,00|0,00|155,80|9|0,00|0,00|0,00|18,33|3,12||||||||';

    $row = (new HandlerC100)->mapear(spedRec($linha), null);

    expect($row)->not->toBeNull()
        ->and($row['chave_acesso'])->toBe('50260109305162000293650020000016501679079934')
        ->and($row['modelo'])->toBe('65')
        ->and($row['numero'])->toBe(1650)
        ->and($row['serie'])->toBe('002')
        ->and($row['data_emissao'])->toBe('2026-01-08')
        ->and($row['tipo_operacao'])->toBe('saida')      // IND_OPER=$p[2]='1'
        ->and($row['valor_total'])->toBe('155.80')
        ->and($row['cancelada'])->toBeFalse()
        ->and($row['metadados']['cod_part'])->toBeNull() // consumidor final
        ->and($row['metadados']['cod_sit'])->toBe('00');
});

it('HandlerC100 lê COD_PART, tipo_operacao=entrada e cancelada por COD_SIT', function () {
    // Real UTIDA: 1ª C100, com COD_PART e IND_OPER=0 (entrada).
    $entrada = '|C100|0|1|FOR000000007|55|00|000|241052|35260110440482000154550000002410521854466981|31012026|31012026|7,20|2|0,00|0,00|7,20|0|0,00|0,00|0,00|0,00|0,00|0,00|0,00|0,00|0,00|0,00|0,00|0,00|';
    $row = (new HandlerC100)->mapear(spedRec($entrada), null);
    expect($row['tipo_operacao'])->toBe('entrada')
        ->and($row['modelo'])->toBe('55')
        ->and($row['valor_total'])->toBe('7.20')
        ->and($row['metadados']['cod_part'])->toBe('FOR000000007')
        ->and($row['cancelada'])->toBeFalse();

    // COD_SIT=02 → cancelada.
    $cancelada = (new HandlerC100)->mapear(spedRec('|C100|1|0||65|02|1|9|CHV|08012026|08012026|10,00|0|0,00|0,00|'), null);
    expect($cancelada['cancelada'])->toBeTrue();
});

// ── C170 → efd_notas_itens ──────────────────────────────────────────────────
it('HandlerC170 mapeia item real com os índices certos', function () {
    // Real UTIDA (o único C170 do arquivo).
    $linha = '|C170|1|303525||1,0000|5|7,20|0,00|0|041|2949|14|0,00|0,00|0,00|0,00|0,00|0,00||||0,00|0,00|0,00||0,00|0,0000|0,000|0,0000|0,00||0,00|0,0000|0,000|0,0000|0,00||0,00|';

    $row = (new HandlerC170)->mapear(spedRec($linha), null);

    expect($row['numero_item'])->toBe(1)
        ->and($row['codigo_item'])->toBe('303525')
        ->and($row['descricao'])->toBeNull()
        ->and($row['quantidade'])->toBe('1.0000')
        ->and($row['valor_total'])->toBe('7.20')
        ->and($row['cst_icms'])->toBe('041')     // $p[10]
        ->and($row['cfop'])->toBe(2949)          // $p[11], inteiro
        ->and($row['valor_unitario'])->toBeNull()
        ->and($row['metadados']['cod_nat'])->toBe('14'); // $p[12]
});

// ── C190 → efd_notas_consolidados ───────────────────────────────────────────
it('HandlerC190 mapeia o analítico real (ICMS 3,12 sobre 155,80)', function () {
    // Real UTIDA (C190 da NFC-e de 155,80).
    $row = (new HandlerC190)->mapear(spedRec('|C190|020|5102|17,00|155,80|18,33|3,12|0,00|0,00|137,47|0,00||'), null);

    expect($row['cst_icms'])->toBe('020')
        ->and($row['cfop'])->toBe(5102)
        ->and($row['aliquota_icms'])->toBe('17.00')
        ->and($row['valor_operacao'])->toBe('155.80')
        ->and($row['valor_bc_icms'])->toBe('18.33')
        ->and($row['valor_icms'])->toBe('3.12')
        ->and($row['valor_reducao_bc'])->toBe('137.47')
        ->and($row['cod_obs'])->toBeNull();
});

// ── D100 → efd_notas (CT-e) ─────────────────────────────────────────────────
it('HandlerD100 lê CHV_CTE em $p[10], NUM_DOC em $p[9], VL_DOC em $p[15]', function () {
    $chave = '53260112345678000199570010000009991234567890';
    $linha = "|D100|0|1|TR1|57|00|1|0|999|{$chave}|01022026|02022026|0||1500,00|50,00|";

    $row = (new HandlerD100)->mapear(spedRec($linha), null);

    expect($row['chave_acesso'])->toBe($chave)
        ->and($row['modelo'])->toBe('57')
        ->and($row['numero'])->toBe(999)         // NUM_DOC=$p[9] (após SUB)
        ->and($row['serie'])->toBe('1')
        ->and($row['data_emissao'])->toBe('2026-02-01')
        ->and($row['tipo_operacao'])->toBe('entrada')
        ->and($row['valor_total'])->toBe('1500.00')
        ->and($row['cancelada'])->toBeFalse();

    // D100 só cancela em COD_SIT=='02'.
    $cancelada = (new HandlerD100)->mapear(spedRec("|D100|0|1|TR1|57|02|1|0|999|{$chave}|01022026|02022026|0||1500,00|0,00|"), null);
    expect($cancelada['cancelada'])->toBeTrue();
});

// ── D190 → efd_notas_consolidados (ST/IPI = 0) ──────────────────────────────
it('HandlerD190 vai na mesma tabela do C190 com ST/IPI zerados', function () {
    $row = (new HandlerD190)->mapear(spedRec('|D190|00|5353|12,00|1000,00|1000,00|120,00|50,00|OBS1|'), null);

    expect($row['cst_icms'])->toBe('00')
        ->and($row['cfop'])->toBe(5353)
        ->and($row['valor_operacao'])->toBe('1000.00')
        ->and($row['valor_icms'])->toBe('120.00')
        ->and($row['valor_reducao_bc'])->toBe('50.00') // $p[8]
        ->and($row['cod_obs'])->toBe('OBS1')           // $p[9]
        ->and($row['valor_bc_icms_st'])->toBe('0')
        ->and($row['valor_icms_st'])->toBe('0')
        ->and($row['valor_ipi'])->toBe('0');
});

// ── Bloco E → efd_apuracoes_icms (agregador) ────────────────────────────────
it('HandlerApuracaoE agrega E100/E110/E116 reais numa linha', function () {
    $h = new HandlerApuracaoE;
    foreach ([
        '|E100|01012026|31012026|',
        '|E110|5590,70|0,00|0,00|0,00|0,00|0,00|0,00|0,00|0,00|5590,70|0,00|5590,70|0,00|0,00|',
        '|E116|000|5590,7|10022026|310|||||012026|',
    ] as $linha) {
        expect($h->mapear(spedRec($linha), null))->toBeNull(); // agregador: sem linha por registro
    }

    $row = $h->finalizar();

    expect($row['periodo_inicio'])->toBe('2026-01-01')
        ->and($row['periodo_fim'])->toBe('2026-01-31')
        ->and($row['icms_tot_debitos'])->toBe(5590.7)
        ->and($row['icms_sld_apurado'])->toBe(5590.7)   // $p[11]
        ->and($row['icms_a_recolher'])->toBe(5590.7)    // $p[13]
        ->and($row['difal_fcp'])->toBeNull()
        ->and($row['ipi'])->toBeNull();

    // E116 → obrigações jsonb (valor numérico, código texto).
    $obrig = $row['icms_obrigacoes']['items'][0];
    expect($obrig['ICMS_VALOR_OBRIGACAO'])->toBe(5590.7)
        ->and($obrig['ICMS_COD_RECEITA'])->toBe('310')
        ->and($obrig['ICMS_DATA_VENCIMENTO'])->toBe('10022026');
});

it('HandlerApuracaoE devolve null sem E100 (sem apuração ICMS)', function () {
    $h = new HandlerApuracaoE;
    $h->mapear(spedRec('|E110|1,00|0,00|0,00|0,00|0,00|0,00|0,00|0,00|0,00|0,00|0,00|1,00|0,00|0,00|'), null);

    expect($h->finalizar())->toBeNull();
});

// ── FiscalDriver ────────────────────────────────────────────────────────────
it('FiscalDriver expõe os 8 handlers, origem fiscal e instâncias estáveis', function () {
    $d = new FiscalDriver;

    expect($d->handlers())->toHaveCount(8)
        ->and($d->origemArquivo())->toBe('fiscal')
        ->and($d->tipoEfd())->toBe('EFD ICMS/IPI')
        ->and($d->registros())->toContain('C100', 'C190', 'D100', '0150', 'E110');

    // Agregador guarda estado ⇒ handlers() deve devolver a MESMA instância.
    expect($d->handlers()[7])->toBe($d->handlers()[7])
        ->and($d->handlers()[7])->toBeInstanceOf(HandlerApuracaoE::class);
});
