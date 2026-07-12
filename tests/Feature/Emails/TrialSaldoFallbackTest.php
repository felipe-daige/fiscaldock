<?php

// Retrocompatibilidade da env do trial: TRIAL_CREDITOS (legado, unidades) →
// saldo_reais (R$) na borda, sem mudar o valor silenciosamente num ambiente
// que não migrou. Ver config/trial.php.

function saldoReaisCom(array $env): float
{
    $orig = [];
    foreach ($env as $k => $v) {
        $orig[$k] = getenv($k);
        if ($v === null) {
            putenv($k);
        } else {
            putenv("$k=$v");
        }
    }

    // config/trial.php lê env() na hora — reavalia o arquivo.
    $valor = (require base_path('config/trial.php'))['saldo_reais'];

    foreach ($orig as $k => $v) {
        $v === false ? putenv($k) : putenv("$k=$v");
    }

    return $valor;
}

it('usa TRIAL_SALDO_REAIS quando presente', function () {
    expect(saldoReaisCom(['TRIAL_SALDO_REAIS' => '15', 'TRIAL_CREDITOS' => null]))->toBe(15.0);
});

it('converte TRIAL_CREDITOS legado (×0,20) quando só a antiga existe', function () {
    // 60 unidades = R$ 12,00 — preserva o valor antigo do ambiente não migrado.
    expect(saldoReaisCom(['TRIAL_SALDO_REAIS' => null, 'TRIAL_CREDITOS' => '60']))->toBe(12.0);
});

it('a nova ganha da legada quando ambas existem', function () {
    expect(saldoReaisCom(['TRIAL_SALDO_REAIS' => '20', 'TRIAL_CREDITOS' => '60']))->toBe(20.0);
});

it('default R$20 quando nenhuma existe', function () {
    expect(saldoReaisCom(['TRIAL_SALDO_REAIS' => null, 'TRIAL_CREDITOS' => null]))->toBe(20.0);
});
