<?php

return [
    'providers' => [
        'minhareceita' => [
            'base_url' => env('MINHARECEITA_BASE_URL', 'https://minhareceita.org'),
            'timeout' => (int) env('MINHARECEITA_TIMEOUT', 20),
            'tries' => (int) env('MINHARECEITA_TRIES', 2),
        ],
        'infosimples' => [
            'base_url' => env('INFOSIMPLES_BASE_URL', 'https://api.infosimples.com/api/v2/consultas'),
            'token' => env('INFOSIMPLES_TOKEN'),
            'timeout' => (int) env('INFOSIMPLES_TIMEOUT', 120),
            'tries' => (int) env('INFOSIMPLES_TRIES', 3),
            'rate_limit_por_segundo' => (float) env('INFOSIMPLES_RATE_LIMIT', 1),
        ],
    ],

    // Grupos de código InfoSimples → status canônico (fonte: docs/infosimples/endpoints-catalog.md)
    'codigos' => [
        'sucesso' => [200, 201],
        'nao_encontrado' => [612],
        'erro_participante' => [608, 611, 619, 620],
        'retry' => [600, 605, 609, 610, 613, 614, 615, 618],
        'fatal' => [601, 602, 603, 604, 606, 607, 617, 621, 622],
    ],
];
