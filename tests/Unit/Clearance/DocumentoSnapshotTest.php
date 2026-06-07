<?php

use App\Services\Clearance\Sefaz\DocumentoSnapshot;

it('escolhe a tabela por tipo de documento', function () {
    $nfe = new DocumentoSnapshot('NFE', str_repeat('5', 44), 'AUTORIZADA', [], [], true, false, true);
    $cte = new DocumentoSnapshot('CTE', str_repeat('5', 44), 'AUTORIZADA', [], [], true, false, true);
    $nfce = new DocumentoSnapshot('NFCE', str_repeat('6', 44), 'AUTORIZADA', [], [], true, false, true);

    expect($nfe->tabela())->toBe('nfe_consultas');
    expect($nfce->tabela())->toBe('nfe_consultas');
    expect($cte->tabela())->toBe('cte_consultas');
});
