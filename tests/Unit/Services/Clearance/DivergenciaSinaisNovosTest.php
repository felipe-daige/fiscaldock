<?php

use App\Services\Clearance\DivergenciaService;
use Illuminate\Support\Collection;
use Tests\TestCase;

uses(TestCase::class);

function snapEnr(array $o = []): object
{
    return (object) array_merge([
        'chave_acesso' => str_repeat('5', 44), 'status' => 'AUTORIZADA', 'status_label' => 'AUTORIZADA',
        'valor_total' => 100.0, 'emit_cnpj' => '11111111000111', 'dest_cnpj' => '00000000000191',
        'situacao_ambiente' => 'produção', 'data_emissao' => '2026-01-15',
    ], $o);
}

function svcComDeclarado(array $declarado): DivergenciaService
{
    $svc = Mockery::mock(DivergenciaService::class)->makePartial();
    $svc->shouldReceive('buscarDeclaradoPorChave')->andReturn([str_repeat('5', 44) => $declarado]);

    return $svc;
}

it('contraparte declarada ausente no SEFAZ vira partes_divergentes crítica (valor>0)', function () {
    $svc = svcComDeclarado(['valor_total' => 100.0, 'contraparte_cnpj' => '99999999000199', 'data_emissao' => '2026-01-15', 'origem' => 'efd', 'id' => 1]);

    // emit e dest do SEFAZ limpos e ambos diferentes da contraparte → divergência real.
    $r = $svc->analisar(new Collection([snapEnr(['emit_cnpj' => '11111111000111', 'dest_cnpj' => '22222222000122'])]), 1, 3);
    expect($r['breakdown']['partes_divergentes']['count'])->toBe(1);
    expect($r['veredito']['severidade'])->toBe('critica');
});

it('CNPJ do destinatário mascarado (zeros à esquerda) com sufixo igual NÃO vira divergência', function () {
    // Caso real lote 97: SEFAZ dest mascarado 00000932000105 == declarado 27371932000105.
    $svc = svcComDeclarado(['valor_total' => 1900.06, 'contraparte_cnpj' => '27371932000105', 'data_emissao' => '2024-07-31', 'origem' => 'efd', 'id' => 1]);

    $r = $svc->analisar(new Collection([snapEnr([
        'emit_cnpj' => '97551165000193', 'dest_cnpj' => '00000932000105', 'data_emissao' => '2024-07-31',
        'valor_total' => 1900.06,
    ])]), 1, 3);

    expect($r['breakdown']['partes_divergentes']['count'])->toBe(0);
    expect($r['veredito']['severidade'])->toBe('ok');
});

it('homologação escriturada vira operacionais crítica', function () {
    $svc = svcComDeclarado(['valor_total' => 100.0, 'contraparte_cnpj' => '11111111000111', 'data_emissao' => '2026-01-15', 'origem' => 'efd', 'id' => 1]);

    $r = $svc->analisar(new Collection([snapEnr(['situacao_ambiente' => 'homologação'])]), 1, 3);
    expect($r['breakdown']['operacionais']['count'])->toBe(1);
    expect($r['veredito']['severidade'])->toBe('critica');
});

it('data de emissão divergente vira revisar', function () {
    $svc = svcComDeclarado(['valor_total' => 100.0, 'contraparte_cnpj' => '11111111000111', 'data_emissao' => '2026-01-20', 'origem' => 'efd', 'id' => 1]);

    $r = $svc->analisar(new Collection([snapEnr(['data_emissao' => '2026-01-15'])]), 1, 3);
    expect($r['veredito']['total_revisar'])->toBeGreaterThanOrEqual(1);
});

it('contraparte presente e ambiente produção = ok', function () {
    $svc = svcComDeclarado(['valor_total' => 100.0, 'contraparte_cnpj' => '11111111000111', 'data_emissao' => '2026-01-15', 'origem' => 'efd', 'id' => 1]);

    $r = $svc->analisar(new Collection([snapEnr()]), 1, 3);
    expect($r['veredito']['severidade'])->toBe('ok');
});

it('ROI: lote sem exposição reporta conformes/total e multiplicador 0', function () {
    $svc = svcComDeclarado(['valor_total' => 100.0, 'contraparte_cnpj' => '11111111000111', 'data_emissao' => '2026-01-15', 'origem' => 'efd', 'id' => 1]);

    $r = $svc->analisar(new Collection([snapEnr()]), 1, 5);
    expect($r['kpis']['roi']['multiplicador'])->toBe(0);
    expect($r['kpis']['roi']['conformes'])->toBe(1);
    expect($r['kpis']['roi']['total_documentos'])->toBe(1);
});

it('ROI: com exposição calcula multiplicador = exposição / custo', function () {
    // nota fria: declarada R$ 1.000, NAO_ENCONTRADA → exposição 1000; custo 5 créditos = R$ 1,00 → 1000×.
    $svc = svcComDeclarado(['valor_total' => 1000.0, 'contraparte_cnpj' => '11111111000111', 'data_emissao' => '2026-01-15', 'origem' => 'efd', 'id' => 1]);

    $r = $svc->analisar(new Collection([snapEnr(['status' => 'NAO_ENCONTRADA', 'status_label' => 'NAO_ENCONTRADA', 'valor_total' => null])]), 1, 5);
    expect($r['kpis']['roi']['exposicao_reais'])->toBe(1000.0);
    expect($r['kpis']['roi']['multiplicador'])->toBe(1000);
});

it('cada documento traz motivo legível (justificativa do veredito)', function () {
    // ok → motivo de conformidade
    $ok = svcComDeclarado(['valor_total' => 100.0, 'contraparte_cnpj' => '11111111000111', 'data_emissao' => '2026-01-15', 'origem' => 'efd', 'id' => 1]);
    $rOk = $ok->analisar(new Collection([snapEnr()]), 1, 3);
    expect($rOk['sem_divergencia']->first()->motivos[0])->toContain('sem divergência');

    // homologação → motivo específico
    $homo = svcComDeclarado(['valor_total' => 100.0, 'contraparte_cnpj' => '11111111000111', 'data_emissao' => '2026-01-15', 'origem' => 'efd', 'id' => 1]);
    $rHomo = $homo->analisar(new Collection([snapEnr(['situacao_ambiente' => 'homologação'])]), 1, 3);
    expect($rHomo['divergencias']->first()->motivos)->toContain('Nota emitida em homologação (ambiente de teste) e escriturada nos livros.');
});
