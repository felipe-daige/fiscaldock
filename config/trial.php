<?php

return [
    // Saldo de boas-vindas em REAIS. Controllers e views consomem este valor.
    'saldo_reais' => (float) env('TRIAL_SALDO_REAIS', 20),
    'validade_dias' => (int) env('TRIAL_VALIDADE_DIAS', 60),
    'limite_consultas_gratuito' => (int) env('TRIAL_LIMITE_GRATUITO', 3),
];
