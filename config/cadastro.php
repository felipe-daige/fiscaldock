<?php

return [
    // Rótulos legíveis dos campos de perfil coletados no signup.
    // Fonte única: usados no formulário público (/criar-conta) e no
    // painel admin de usuários. Os valores (chaves) são o que persiste no banco.
    'desafios' => [
        'documentos_espalhados' => 'Documentos espalhados sem histórico',
        'pendencias_fim_mes' => 'Corrida no fim do mês com pendências',
        'comunicacao_manual' => 'Comunicação manual sem rastreabilidade',
        'falta_visao' => 'Falta de visão do que está certo ou falta',
    ],

    'faturamento' => [
        'ate-360k' => 'Até R$ 360 mil',
        '360k-4.8m' => 'R$ 360 mil a R$ 4,8 milhões',
        '4.8m-300m' => 'R$ 4,8 milhões a R$ 300 milhões',
        'acima-300m' => 'Acima de R$ 300 milhões',
    ],
];
