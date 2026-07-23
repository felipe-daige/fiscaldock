<?php

use App\Services\Efd\Driver\ContribDriver;
use App\Services\Efd\Handlers\Handler0150;
use App\Services\Efd\Handlers\HandlerA100;
use App\Services\Efd\Handlers\HandlerA170;
use App\Services\Efd\Handlers\HandlerApuracaoM;
use App\Services\Efd\Handlers\HandlerF600;
use App\Services\Efd\Sped\SpedParser;
use App\Services\Efd\Sped\SpedRecord;

/**
 * Testa L2 do PIS/COFINS (ContribDriver + handlers próprios A100/A170/F600/M). Índices
 * contra o set-node EFD Contribuições (fields[N] ⟺ $p[N+2]). Puro — só mapeia; a engine
 * põe escopo/FK. Prova a modularização: 0150/0200/C100/C170 são reusados do fiscal.
 */
if (! function_exists('spedRecContrib')) {
    function spedRecContrib(string $linha): SpedRecord
    {
        return iterator_to_array((new SpedParser)->stream($linha))[0];
    }
}

// ── A100 → efd_notas (NFS-e, modelo '00') ───────────────────────────────────
it('HandlerA100 mapeia NFS-e: modelo 00, VL_ISS em $p[21], PIS/COFINS em metadados', function () {
    // |A100|OPER|EMIT|COD_PART|SIT|SER|SUB|NUM|CHV|DT_DOC|DT_EXE|VL_DOC|PGTO|VL_DESC|
    //       BC_PIS|VL_PIS|BC_COF|VL_COF|PIS_RET|COF_RET|VL_ISS|
    $linha = '|A100|1|0|FOR7|00|1|0|500||01022026|01022026|1000,00|0|10,00|900,00|5,85|900,00|27,00|0|0|410,50|';

    $row = (new HandlerA100)->mapear(spedRecContrib($linha), null);

    expect($row['modelo'])->toBe('00')
        ->and($row['numero'])->toBe(500)
        ->and($row['serie'])->toBe('1')
        ->and($row['chave_acesso'])->toBeNull()          // NFS-e sem chave
        ->and($row['tipo_operacao'])->toBe('saida')       // IND_OPER=1
        ->and($row['valor_total'])->toBe('1000.00')
        ->and($row['valor_desconto'])->toBe('10.00')
        ->and($row['cancelada'])->toBeFalse()
        ->and($row['metadados']['cod_part'])->toBe('FOR7')
        ->and($row['metadados']['vl_iss'])->toBe('410.50') // $p[21], normalizado p/ o BI
        ->and($row['metadados']['vl_pis'])->toBe('5.85')
        ->and($row['metadados']['vl_cofins'])->toBe('27.00');
});

it('HandlerA100 marca cancelada quando COD_SIT=02', function () {
    $linha = '|A100|1|0|FOR7|02|1|0|500||01022026|01022026|1000,00|0|0|0|0|0|0|0|0|0|';
    $row = (new HandlerA100)->mapear(spedRecContrib($linha), null);

    expect($row['cancelada'])->toBeTrue();
});

// ── A170 → efd_notas_itens (serviço, só PIS/COFINS) ─────────────────────────
it('HandlerA170 mapeia item de serviço: PIS/COFINS tipado, sem ICMS/CFOP/quantidade', function () {
    // |A170|NUM|COD|DESCR|VL_ITEM|VL_DESC|NAT_BC|IND_ORIG|CST_PIS|BC_PIS|ALIQ_PIS|VL_PIS|
    //       CST_COF|BC_COF|ALIQ_COF|VL_COF|COD_CTA|COD_CCUS|
    $linha = '|A170|1|SERV01|Consultoria|1000,00|0|00|0|01|900,00|0,65|5,85|01|900,00|3,00|27,00|3.1.1|CC1|';

    $row = (new HandlerA170)->mapear(spedRecContrib($linha), null);

    expect($row['numero_item'])->toBe(1)
        ->and($row['codigo_item'])->toBe('SERV01')
        ->and($row['descricao'])->toBe('Consultoria')
        ->and($row['valor_total'])->toBe('1000.00')
        ->and($row['cst_pis'])->toBe('01')
        ->and($row['aliquota_pis'])->toBe('0.65')
        ->and($row['valor_pis'])->toBe('5.85')
        ->and($row['cst_cofins'])->toBe('01')
        ->and($row['aliquota_cofins'])->toBe('3.00')
        ->and($row['valor_cofins'])->toBe('27.00')
        ->and($row['cfop'])->toBeNull()
        ->and($row['cst_icms'])->toBeNull()
        ->and($row['quantidade'])->toBeNull();
});

// ── F600 → efd_retencoes_fonte ──────────────────────────────────────────────
it('HandlerF600 mapeia retenção na fonte', function () {
    // |F600|NAT|DT_RET|BC_RET|VL_RET|COD_REC|IND_NAT|CNPJ|VL_PIS|VL_COFINS|IND_DEC|
    $linha = '|F600|01|15022026|10000,00|485,00|5952|1|11.222.333/0001-81|32,50|150,00|0|';

    $row = (new HandlerF600)->mapear(spedRecContrib($linha), null);

    expect($row['natureza'])->toBe('01')
        ->and($row['data_retencao'])->toBe('2026-02-15')
        ->and($row['base_calculo'])->toBe('10000.00')
        ->and($row['valor_total'])->toBe('485.00')
        ->and($row['cod_receita'])->toBe('5952')
        ->and($row['cnpj'])->toBe('11222333000181')     // só dígitos
        ->and($row['valor_pis'])->toBe('32.50')
        ->and($row['valor_cofins'])->toBe('150.00')
        ->and($row['ind_declarante'])->toBe('0')
        ->and($row['dados_brutos']['REG'])->toBe('F600');
});

// ── Bloco M + 0110 → efd_apuracoes_contribuicoes (agregador) ────────────────
it('HandlerApuracaoM agrega M200/M600/0110 numa linha', function () {
    $handler = new HandlerApuracaoM;
    // M200 (PIS): $p[2..13]; total a recolher = $p[13]
    $handler->mapear(spedRecContrib('|M200|100,00|0|0|100,00|0|0|100,00|0|0|0|0|7166,99|'), null);
    // M600 (COFINS): idem
    $handler->mapear(spedRecContrib('|M600|500,00|0|0|500,00|0|0|500,00|0|0|0|0|33078,20|'), null);
    // 0110 regime cumulativo
    $handler->mapear(spedRecContrib('|0110|2|1|1|2|'), null);
    // detalhe M210 (vira jsonb)
    $handler->mapear(spedRecContrib('|M210|01|100000,00|100000,00|0,65|650,00|'), null);

    $row = $handler->finalizar();

    expect($row['pis_total_recolher'])->toBe(7166.99)
        ->and($row['cofins_total_recolher'])->toBe(33078.20)
        ->and($row['pis_nao_cumulativo'])->toBe(100.0)
        ->and($row['cofins_nao_cumulativo'])->toBe(500.0)
        ->and($row['cod_inc_tributaria'])->toBe('2')
        ->and($row['ind_regime_cumulativo'])->toBe('2')
        ->and($row['pis_detalhes']['items'])->toHaveCount(1);
});

it('HandlerApuracaoM devolve null sem bloco M nem 0110', function () {
    expect((new HandlerApuracaoM)->finalizar())->toBeNull();
});

it('HandlerApuracaoM nomeia M210/M610 e casa receita não tributada PIS(M410)+COFINS(M810)', function () {
    $handler = new HandlerApuracaoM;
    $handler->mapear(spedRecContrib('|M200|1|0|0|1|0|0|1|0|0|0|0|100,00|'), null);
    $handler->mapear(spedRecContrib('|M600|1|0|0|1|0|0|1|0|0|0|0|500,00|'), null);
    // M210 PIS por CST 51: base 1509119,83 · aliq 0,65 · valor 9797,32 ($p[4],[8],[11])
    $handler->mapear(spedRecContrib('|M210|51|1537009,17|1509119,83|0|1839,82|1507280,01|0,65|0||9797,32|0|0|0|0|9797,32||'), null);
    $handler->mapear(spedRecContrib('|M610|51|1537009,17|1509119,83|0|1839,82|1507280,01|3|0||45218,40|0|0|0|0|45218,40||'), null);
    // Receita não tributada: M400/M410 (PIS) + M800/M810 (COFINS), mesma natureza 302
    $handler->mapear(spedRecContrib('|M400|04|710356,89|||'), null);
    $handler->mapear(spedRecContrib('|M410|302|710356,89|||'), null);
    $handler->mapear(spedRecContrib('|M800|04|710356,89|||'), null);
    $handler->mapear(spedRecContrib('|M810|302|710356,89|||'), null);

    $row = $handler->finalizar();

    // M210/M610 nomeados (não _campos cru) e com dot-decimal pra tela.
    $m210 = $row['pis_detalhes']['items'][0];
    expect($m210['COD_CONT'])->toBe('51')
        ->and($m210['VL_BC_CONT'])->toBe('1509119.83')
        ->and($m210['ALIQ_PIS'])->toBe('0.65')
        ->and($m210['VL_CONT_APUR'])->toBe('9797.32');
    expect($row['cofins_detalhes']['items'][0]['ALIQ_COFINS'])->toBe('3');

    // Receita não tributada: 1 linha, PIS e COFINS casados por natureza + CST-pai.
    expect($row['pis_nao_tributado']['items'])->toHaveCount(1);
    $nt = $row['pis_nao_tributado']['items'][0];
    expect($nt['CST_PIS'])->toBe('04')
        ->and($nt['NAT_REC'])->toBe('302')
        ->and($nt['VL_REC'])->toBe('710356.89')
        ->and($nt['VL_REC_COFINS'])->toBe('710356.89'); // M810 casado (antes era dropado)
});

// ── 0150 contrib (reuso com origem própria) ─────────────────────────────────
it('Handler0150 com origem contrib marca SPED_EFD_CONTRIB (CNPJ ainda em $p[5])', function () {
    $linha = '|0150|FOR7|Fornecedor Servico|01058|11222333000181||123456||rua x|10||centro|';

    $row = (new Handler0150('SPED_EFD_CONTRIB'))->mapear(spedRecContrib($linha), null);

    expect($row['documento'])->toBe('11222333000181')
        ->and($row['origem_tipo'])->toBe('SPED_EFD_CONTRIB');
});

// ── ContribDriver ───────────────────────────────────────────────────────────
it('ContribDriver: origem contribuicoes, tipo PIS/COFINS, reusa C100/C170 e adiciona A/F/M', function () {
    $driver = new ContribDriver;

    expect($driver->origemArquivo())->toBe('contribuicoes')
        ->and($driver->tipoEfd())->toBe('EFD PIS/COFINS')
        ->and($driver->registros())->toContain('A100', 'A170', 'F600', 'M200', 'C100', 'C170', '0150', '0200')
        ->and($driver->registros())->not->toContain('C190', 'D100', 'D190'); // contrib não persiste esses

    // Instâncias estáveis (agregador M tem estado).
    expect($driver->handlers())->toBe($driver->handlers());
});
