<?php

return [
    /*
    | Tamanho máximo de cada upload manual. O limite global da conta é resolvido
    | pela capability `armazenamento_mb` do plano; estes valores são fallback para
    | linhas antigas de subscription_plans que ainda não tenham a capability.
    */
    'upload_maximo_mb' => (int) env('ARQUIVOS_UPLOAD_MAXIMO_MB', 50),
    'upload_maximo_por_lote' => (int) env('ARQUIVOS_UPLOAD_MAXIMO_POR_LOTE', 10),

    'quota_padrao_mb' => (int) env('ARQUIVOS_QUOTA_PADRAO_MB', 250),
    'quota_por_plano_mb' => [
        'free' => 250,
        'essencial' => 2 * 1024,
        'profissional' => 10 * 1024,
        'escritorio' => 50 * 1024,
        'enterprise' => 200 * 1024,
    ],

    'extensoes_permitidas' => [
        'pdf', 'xml', 'txt', 'csv', 'xls', 'xlsx', 'zip', 'jpg', 'jpeg', 'png',
    ],
];
