<?php

return [
    'creditos' => (int) env('TRIAL_CREDITOS', 100),          // R$20 @ R$0,20/credito
    'validade_dias' => (int) env('TRIAL_VALIDADE_DIAS', 60),
    'limite_consultas_gratuito' => (int) env('TRIAL_LIMITE_GRATUITO', 3),
];
