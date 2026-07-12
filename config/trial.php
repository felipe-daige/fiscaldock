<?php

return [
    // Saldo de boas-vindas em REAIS. Controllers e views consomem este valor.
    //
    // Retrocompatibilidade transitória: a env var era TRIAL_CREDITOS (em UNIDADES do
    // ledger, ×0,20 = R$). Um ambiente que ainda tenha só a antiga NÃO deve mudar o
    // valor do trial silenciosamente — se TRIAL_SALDO_REAIS não vier mas TRIAL_CREDITOS
    // existir, converte na borda. Remover este fallback quando todos os ambientes
    // estiverem migrados para TRIAL_SALDO_REAIS.
    'saldo_reais' => (float) env(
        'TRIAL_SALDO_REAIS',
        env('TRIAL_CREDITOS') !== null ? (float) env('TRIAL_CREDITOS') * 0.20 : 20
    ),
    'validade_dias' => (int) env('TRIAL_VALIDADE_DIAS', 60),
    'limite_consultas_gratuito' => (int) env('TRIAL_LIMITE_GRATUITO', 3),
];
