<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Janela de importação travada
    |--------------------------------------------------------------------------
    |
    | Minutos que uma importação EFD/XML pode ficar em "processando" sem
    | receber atualização de progresso antes de ser marcada como erro pelo
    | comando importacao:expirar-travadas. Sobrescrevível por env.
    |
    */

    'stale_minutos' => (int) env('IMPORTACAO_STALE_MINUTOS', 3),

    /*
    |--------------------------------------------------------------------------
    | Manutenção da Importação EFD
    |--------------------------------------------------------------------------
    |
    | Bloqueia novos uploads de SPED enquanto o pipeline n8n está sendo
    | reestruturado (ver docs/importacao-efd/REESTRUTURACAO-PIPELINE-2026-05-17.md).
    | Usuários listados em `usuarios_permitidos` continuam podendo importar para
    | validar o pipeline em produção. Leituras (listagem, detalhe, histórico)
    | permanecem liberadas para todos.
    |
    */

    'efd_manutencao' => [
        'ativa' => (bool) env('EFD_MANUTENCAO_ATIVA', true),
        'usuarios_permitidos' => [1],
    ],

];
