<?php

return [
    // Bônus de boas-vindas concedido no signup.
    // Calibrado em 60cr/60dias (CFO §9 + CLAUDE.md) — 60 dias deixa o usuário ver
    // ao menos 1 ciclo de re-monitoramento. Sobrescrevível por env.
    'creditos' => (int) env('TRIAL_CREDITOS', 60),
    'validade_dias' => (int) env('TRIAL_VALIDADE_DIAS', 60),

    // Teto GLOBAL de consultas (CNPJs) liberadas ANTES da 1ª compra — pool único
    // somado entre TODOS os planos pagos abaixo. Esgotou o pool, só liberando com depósito.
    'limite_consultas_sem_compra' => (int) env('TRIAL_LIMITE_CONSULTAS_SEM_COMPRA', 5),

    // Planos pagos sujeitos ao teto (Gratuito fica de fora). O pool é compartilhado entre eles.
    'planos_com_teto' => ['validacao', 'licitacao', 'compliance', 'due_diligence'],
];
