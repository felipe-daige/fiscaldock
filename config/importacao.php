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

];
