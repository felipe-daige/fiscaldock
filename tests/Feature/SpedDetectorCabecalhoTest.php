<?php

use App\Services\SpedDetectorService;

beforeEach(function () {
    $this->detector = new SpedDetectorService;
});

it('extrai cnpj, periodo e finalidade de EFD ICMS/IPI (layout real)', function () {
    $sped = "|0000|018|1|01062024|30062024|HIDRATOP COMERCIO LTDA|97551165000193||MS|283684410|5003702|1000063566||A|1|\r\n".
        "|E100|01062024|30062024|\r\n".
        "|E110|0|0|0|0|0|0|0|0|0|0|0|\r\n".
        "|9999|4|\r\n";

    $r = $this->detector->extrairCabecalho($sped);

    expect($r['tipo'])->toBe('EFD ICMS/IPI');
    expect($r['valido'])->toBeTrue();
    expect($r['cnpj'])->toBe('97551165000193');
    expect($r['periodo_inicio'])->toBe('2024-06-01');
    expect($r['periodo_fim'])->toBe('2024-06-30');
    expect($r['retificadora'])->toBeTrue(); // COD_FIN = 1
});

it('extrai cnpj, periodo e finalidade de EFD PIS/COFINS (layout real com IND_SIT_ESP/NUM_REC_ANTERIOR)', function () {
    $sped = "|0000|006|0|||01062024|30062024|HIDRATOP COMERCIO LTDA|97551165000193|MS|5003702||00|9|\r\n".
        "|A100|0|0|FORN|00||1|1|CHV|01062024|01062024|1000.00|9|0|1000.00|6.5|1000.00|30|0|0|0|\r\n".
        "|9999|3|\r\n";

    $r = $this->detector->extrairCabecalho($sped);

    expect($r['tipo'])->toBe('EFD PIS/COFINS');
    expect($r['cnpj'])->toBe('97551165000193');
    expect($r['periodo_inicio'])->toBe('2024-06-01');
    expect($r['periodo_fim'])->toBe('2024-06-30');
    expect($r['retificadora'])->toBeFalse(); // TIPO_ESCRIT = 0
});

it('retorna campos nulos quando o SPED é inválido (sem 0000)', function () {
    $r = $this->detector->extrairCabecalho("|0150|x|\r\n|9999|1|\r\n");

    expect($r['valido'])->toBeFalse();
    expect($r['cnpj'])->toBeNull();
    expect($r['periodo_inicio'])->toBeNull();
});

it('ignora data malformada no 0000', function () {
    $sped = "|0000|018|0|XX062024|30062024|EMPRESA|97551165000193||MS|1|1|1||A|1|\r\n".
        "|E100|x|\r\n|9999|2|\r\n";

    $r = $this->detector->extrairCabecalho($sped);

    expect($r['periodo_inicio'])->toBeNull();
    expect($r['periodo_fim'])->toBe('2024-06-30');
});
