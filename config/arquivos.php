<?php

return [
    /*
    | Tamanho máximo de cada upload manual. O limite global da conta é resolvido
    | pela capability `armazenamento_mb` do plano; estes valores são fallback para
    | linhas antigas de subscription_plans que ainda não tenham a capability.
    */
    'upload_maximo_mb' => (int) env('ARQUIVOS_UPLOAD_MAXIMO_MB', 50),
    'upload_maximo_por_lote' => (int) env('ARQUIVOS_UPLOAD_MAXIMO_POR_LOTE', 10),

    /*
    | Faixas visuais do monitor de disco no console administrativo. A leitura
    | usa o mesmo filesystem que sustenta o disco `local`; não dispara alertas
    | externos nem executa comandos do sistema operacional.
    */
    'disco' => [
        'atencao_percentual' => (float) env('ARQUIVOS_DISCO_ATENCAO_PERCENTUAL', 70),
        'critico_percentual' => (float) env('ARQUIVOS_DISCO_CRITICO_PERCENTUAL', 85),
    ],

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
