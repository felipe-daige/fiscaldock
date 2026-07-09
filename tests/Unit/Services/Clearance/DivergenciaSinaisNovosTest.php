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

it('CNPJ mascarado com asterisco: dígito visível igual NÃO vira divergência', function () {
    // dest SEFAZ 12.***.***\/0001-** ; declarado 12345678000199 → visíveis (12...0001) batem.
    $svc = svcComDeclarado(['valor_total' => 100.0, 'contraparte_cnpj' => '12345678000199', 'data_emissao' => '2026-01-15', 'origem' => 'efd', 'id' => 1]);

    $r = $svc->analisar(new Collection([snapEnr([
        'emit_cnpj' => '11111111000111', 'dest_cnpj' => '12.***.***/0001-**',
    ])]), 1, 3);

    expect($r['breakdown']['partes_divergentes']['count'])->toBe(0);
});

it('CNPJ mascarado com asterisco: dígito visível diferente vira divergência', function () {
    // dest SEFAZ 99.***.***\/0001-** ; declarado começa 12 → dígito visível diverge.
    $svc = svcComDeclarado(['valor_total' => 100.0, 'contraparte_cnpj' => '12345678000199', 'data_emissao' => '2026-01-15', 'origem' => 'efd', 'id' => 1]);

    $r = $svc->analisar(new Collection([snapEnr([
        'emit_cnpj' => '11111111000111', 'dest_cnpj' => '99.***.***/0001-**',
    ])]), 1, 3);

    expect($r['breakdown']['partes_divergentes']['count'])->toBe(1);
});

it('razão social divergente (CNPJ também difere) vira partes_divergentes revisar', function () {
    $svc = svcComDeclarado([
        'valor_total' => 100.0, 'contraparte_cnpj' => '55555555000155', 'contraparte_nome' => 'PADARIA DO ZE LTDA',
        'data_emissao' => '2026-01-15', 'origem' => 'efd', 'id' => 1,
    ]);

    // CNPJ declarado não bate nenhum lado (emit 111.. / dest 000..0191) → gate liberado.
    $r = $svc->analisar(new Collection([snapEnr(['emit_nome' => 'MERCADO CENTRAL SA', 'dest_nome' => 'OUTRA EMPRESA ME'])]), 1, 3);
    expect($r['breakdown']['partes_divergentes']['count'])->toBe(1);
    expect($r['veredito']['total_revisar'])->toBeGreaterThanOrEqual(1);
});

it('CNPJ confere: razão social divergente NÃO flaga (drift de cadastro)', function () {
    // Caso lote 229 doc 1: CNPJ igual, nome de registro diferente → mesma empresa, sem divergência.
    $svc = svcComDeclarado([
        'valor_total' => 100.0, 'contraparte_cnpj' => '11111111000111', 'contraparte_nome' => 'PANTANAL TRANSPORTES E LOGISTICA LTDA',
        'data_emissao' => '2026-01-15', 'origem' => 'efd', 'id' => 1,
    ]);

    $r = $svc->analisar(new Collection([snapEnr(['emit_nome' => 'PANTANAL OPERADOR DE TRANSPORTE MULTIMODAL', 'dest_nome' => ''])]), 1, 3);
    expect($r['breakdown']['partes_divergentes']['count'])->toBe(0);
    expect($r['veredito']['severidade'])->toBe('ok');
});

it('razão social igual (tolerante a sufixo societário) NÃO vira divergência', function () {
    $svc = svcComDeclarado([
        'valor_total' => 100.0, 'contraparte_cnpj' => '55555555000155', 'contraparte_nome' => 'PADARIA DO ZÉ LTDA',
        'data_emissao' => '2026-01-15', 'origem' => 'efd', 'id' => 1,
    ]);

    // SEFAZ traz emit = mesma empresa sem sufixo → normalização confere.
    $r = $svc->analisar(new Collection([snapEnr(['emit_nome' => 'Padaria do Ze', 'dest_nome' => 'DESTINO ME'])]), 1, 3);
    expect($r['breakdown']['partes_divergentes']['count'])->toBe(0);
});

it('UF da contraparte divergente (CNPJ também difere) vira revisar', function () {
    $svc = svcComDeclarado([
        'valor_total' => 100.0, 'contraparte_cnpj' => '55555555000155', 'contraparte_uf' => 'SP',
        'data_emissao' => '2026-01-15', 'origem' => 'efd', 'id' => 1,
    ]);

    // ambos lados do SEFAZ em UF != SP + CNPJ não bate → divergência.
    $r = $svc->analisar(new Collection([snapEnr(['emit_uf' => 'RJ', 'dest_uf' => 'MG'])]), 1, 3);
    expect($r['breakdown']['partes_divergentes']['count'])->toBe(1);
    expect($r['veredito']['total_revisar'])->toBeGreaterThanOrEqual(1);
});

it('CNPJ confere: UF divergente NÃO flaga', function () {
    $svc = svcComDeclarado([
        'valor_total' => 100.0, 'contraparte_cnpj' => '11111111000111', 'contraparte_uf' => 'SP',
        'data_emissao' => '2026-01-15', 'origem' => 'efd', 'id' => 1,
    ]);

    // CNPJ bate emit → gate bloqueia mesmo com UF RJ/MG.
    $r = $svc->analisar(new Collection([snapEnr(['emit_uf' => 'RJ', 'dest_uf' => 'MG'])]), 1, 3);
    expect($r['breakdown']['partes_divergentes']['count'])->toBe(0);
});

it('conferencias sempre presentes (7 campos) mesmo sem divergência', function () {
    $svc = svcComDeclarado([
        'valor_total' => 100.0, 'contraparte_cnpj' => '11111111000111', 'contraparte_nome' => 'EMPRESA X LTDA',
        'contraparte_uf' => 'MS', 'data_emissao' => '2026-01-15', 'numero' => '123', 'serie' => '1', 'modelo' => '55',
        'origem' => 'efd', 'id' => 1,
    ]);

    $r = $svc->analisar(new Collection([snapEnr([
        'emit_nome' => 'EMPRESA X', 'emit_uf' => 'MS', 'dest_uf' => 'MS', 'numero' => '123', 'serie' => '1', 'modelo' => '55',
    ])]), 1, 3);
    $conf = collect($r['sem_divergencia']->first()->conferencias);
    expect($conf->pluck('campo')->all())->toBe(['CNPJ contraparte', 'Razão social', 'UF', 'Inscrição Estadual', 'Data de emissão', 'Número / Série', 'Modelo']);
    expect($conf->firstWhere('campo', 'CNPJ contraparte')['status'])->toBe('confere');
    expect($conf->firstWhere('campo', 'Data de emissão')['status'])->toBe('confere');
    expect($conf->firstWhere('campo', 'Número / Série')['status'])->toBe('confere');
    expect($conf->firstWhere('campo', 'Modelo')['status'])->toBe('confere');
});

it('IE sem dado no cadastro mostra sem_dado + nota requer SINTEGRA', function () {
    $svc = svcComDeclarado([
        'valor_total' => 100.0, 'contraparte_cnpj' => '11111111000111', 'data_emissao' => '2026-01-15', 'origem' => 'efd', 'id' => 1,
    ]);

    $r = $svc->analisar(new Collection([snapEnr(['emit_ie' => '123456789'])]), 1, 3);
    $ieConf = collect($r['sem_divergencia']->first()->conferencias)->firstWhere('campo', 'Inscrição Estadual');
    expect($ieConf['status'])->toBe('sem_dado');
    expect($ieConf['nota'])->toContain('SINTEGRA');
});

it('IE confere quando contraparte é o emitente e dígitos batem', function () {
    // contraparte_cnpj == emit_cnpj do snapEnr → bateEmit true → confronto habilitado.
    $svc = svcComDeclarado([
        'valor_total' => 100.0, 'contraparte_cnpj' => '11111111000111', 'contraparte_ie' => '12.345.678-9',
        'data_emissao' => '2026-01-15', 'origem' => 'efd', 'id' => 1,
    ]);

    $r = $svc->analisar(new Collection([snapEnr(['emit_ie' => '123456789'])]), 1, 3);
    expect(collect($r['sem_divergencia']->first()->conferencias)->firstWhere('campo', 'Inscrição Estadual')['status'])->toBe('confere');
});

it('IE difere (contraparte emitente, dígitos diferentes) vira revisar', function () {
    $svc = svcComDeclarado([
        'valor_total' => 100.0, 'contraparte_cnpj' => '11111111000111', 'contraparte_ie' => '111111111',
        'data_emissao' => '2026-01-15', 'origem' => 'efd', 'id' => 1,
    ]);

    $r = $svc->analisar(new Collection([snapEnr(['emit_ie' => '999999999'])]), 1, 3);
    $l = $r['divergencias']->first();
    expect(collect($l->conferencias)->firstWhere('campo', 'Inscrição Estadual')['status'])->toBe('difere');
    expect($r['veredito']['total_revisar'])->toBeGreaterThanOrEqual(1);
});

it('IE indeterminada quando contraparte é o destinatário (SEFAZ só tem IE do emitente)', function () {
    // contraparte_cnpj bate o DEST (mascarado zeros) e NÃO o emit → bateEmit false → indeterminado.
    $svc = svcComDeclarado([
        'valor_total' => 100.0, 'contraparte_cnpj' => '27371932000105', 'contraparte_ie' => '555',
        'data_emissao' => '2026-01-15', 'origem' => 'efd', 'id' => 1,
    ]);

    $r = $svc->analisar(new Collection([snapEnr([
        'emit_cnpj' => '97551165000193', 'dest_cnpj' => '00000932000105', 'emit_ie' => '123456789',
    ])]), 1, 3);
    $ieConf = collect($r['sem_divergencia']->concat($r['divergencias'])->first()->conferencias)->firstWhere('campo', 'Inscrição Estadual');
    expect($ieConf['status'])->toBe('indeterminado');
});

it('número/série declarado divergente do documento vira revisar', function () {
    $svc = svcComDeclarado([
        'valor_total' => 100.0, 'contraparte_cnpj' => '11111111000111', 'data_emissao' => '2026-01-15',
        'numero' => '999', 'serie' => '1', 'modelo' => '55', 'origem' => 'efd', 'id' => 1,
    ]);

    $r = $svc->analisar(new Collection([snapEnr(['numero' => '123', 'serie' => '1', 'modelo' => '55'])]), 1, 3);
    $l = $r['divergencias']->first();
    expect(collect($l->conferencias)->firstWhere('campo', 'Número / Série')['status'])->toBe('difere');
    expect($r['veredito']['total_revisar'])->toBeGreaterThanOrEqual(1);
});

it('número/série cai pra chave quando SEFAZ não traz o campo', function () {
    // chave com número 43238 embutido (pos 25-33 = 000043238). Declarado bate → confere.
    $chave = '35240246970030000202570010000432381000772218';
    $svc = Mockery::mock(DivergenciaService::class)->makePartial();
    $svc->shouldReceive('buscarDeclaradoPorChave')->andReturn([$chave => [
        'valor_total' => 100.0, 'contraparte_cnpj' => '11111111000111', 'data_emissao' => '2026-01-15',
        'numero' => '43238', 'serie' => '1', 'modelo' => '57', 'origem' => 'efd', 'id' => 1,
    ]]);
    $snap = snapEnr(['chave_acesso' => $chave, 'numero' => null, 'serie' => null, 'modelo' => null]);
    $r = $svc->analisar(new Collection([$snap]), 1, 3);
    $l = $r['sem_divergencia']->concat($r['divergencias'])->first();
    expect(collect($l->conferencias)->firstWhere('campo', 'Número / Série')['status'])->toBe('confere');
});

it('conferencia marca difere quando o campo realmente diverge', function () {
    $svc = svcComDeclarado([
        'valor_total' => 100.0, 'contraparte_cnpj' => '55555555000155', 'contraparte_nome' => 'PADARIA DO ZE',
        'contraparte_uf' => 'SP', 'data_emissao' => '2026-01-20', 'origem' => 'efd', 'id' => 1,
    ]);

    $r = $svc->analisar(new Collection([snapEnr([
        'emit_cnpj' => '11111111000111', 'dest_cnpj' => '22222222000122',
        'emit_nome' => 'MERCADO CENTRAL', 'dest_nome' => 'OUTRO', 'emit_uf' => 'RJ', 'dest_uf' => 'MG', 'data_emissao' => '2026-01-15',
    ])]), 1, 3);
    $conf = collect($r['divergencias']->first()->conferencias);
    expect($conf->firstWhere('campo', 'CNPJ contraparte')['status'])->toBe('difere');
    expect($conf->firstWhere('campo', 'Razão social')['status'])->toBe('difere');
    expect($conf->firstWhere('campo', 'UF')['status'])->toBe('difere');
    expect($conf->firstWhere('campo', 'Data de emissão')['status'])->toBe('difere');
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
