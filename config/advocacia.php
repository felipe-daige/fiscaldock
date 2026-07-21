<?php

return [
    // Vertical advocacia — consulta à la carte por fonte (docs/advocacia/consultas-certidoes.md).
    // PREÇO DE VENDA por fonte, em R$ (≠ consultas.fontes.*, que é o CUSTO InfoSimples usado no
    // estorno). Default R$ 1,00/fonte (decisão 2026-07-21); ajuste guiado pelo header.price
    // observado nas respostas. Override por fonte em 'precos'.
    'preco_fonte_default' => (float) env('ADVOCACIA_PRECO_FONTE_DEFAULT', 1.00),

    // E-mail remetente das consultas de certidão que exigem `email` no formulário da fonte
    // (TRF unificada via CJF; TJs 2-etapas na fase 4). Remetente de SISTEMA — nunca o e-mail do
    // usuário — pra não vazar dado pessoal pra fonte externa.
    'email_solicitante' => env('ADVOCACIA_EMAIL_SOLICITANTE', 'consultas@fiscaldock.com.br'),

    'precos' => [
        // 'cnd_municipal' => 1.50,  // exemplo de override pontual
    ],

    // Grupos de apresentação da tela de seleção (ordem = ordem visual). As fontes judiciais
    // da fase 2 entram em grupos novos (judicial, trabalhista, integridade, passivo).
    'grupos' => [
        'judicial' => [
            'label' => 'Certidões judiciais',
            'fontes' => ['certidao_stj', 'certidao_trf', 'ceat_trt', 'certidao_mpt', 'certidao_mpf'],
        ],
        'integridade' => [
            'label' => 'Integridade e sanções',
            'fontes' => ['certidao_tcu', 'improbidade', 'ceis', 'cnep'],
        ],
        'passivo' => [
            'label' => 'Passivo e insolvência',
            'fontes' => ['protestos', 'falencias'],
        ],
        'fiscal' => [
            'label' => 'Certidões fiscais',
            'fontes' => ['cnd_federal', 'cnd_estadual', 'cnd_municipal', 'crf_fgts', 'cndt', 'sintegra'],
        ],
    ],
];
