<?php

return [
    // Vertical advocacia — consulta à la carte por fonte (docs/advocacia/consultas-certidoes.md).
    // PREÇO DE VENDA por fonte, em R$ (≠ consultas.fontes.*, que é o CUSTO InfoSimples usado no
    // estorno). Default R$ 1,00/fonte (decisão 2026-07-21); ajuste guiado pelo header.price
    // observado nas respostas. Override por fonte em 'precos'.
    'preco_fonte_default' => (float) env('ADVOCACIA_PRECO_FONTE_DEFAULT', 1.00),

    // E-mail para onde a certidão é enviada quando a fonte exige `email` no formulário (TRF
    // unificada via CJF; TJs 2-etapas na fase 4). MVP: e-mail de sistema do Felipe (recebe,
    // baixa, repassa ao usuário). Depois: caixa de sistema dedicada. Nunca o e-mail do usuário.
    'email_solicitante' => env('ADVOCACIA_EMAIL_SOLICITANTE', 'felipedaige@gmail.com'),

    // (Removido: `cpf_solicitante` de sistema. O CPF do solicitante da CEAT é SEMPRE o do dono da
    // conta — users.cpf, injetado no alvo pelo job. Um CPF de sistema como requerente emitiria
    // certidão em nome de terceiro, PII. Sem CPF do dono → CEAT INDISPONÍVEL, não cobra.)

    'precos' => [
        // Consulta cadastral (situação) é GRÁTIS (minhareceita, custo zero) — plano Gratuito.
        'cadastro' => 0.00,
        // 'cnd_municipal' => 1.50,  // exemplo de override pontual

        // Fonte de 2 ETAPAS custa ≥2 chamadas pagas (pedido + N conferências ~R$0,24 cada), então
        // não cabe no R$ 1,00 das single-call — a R$ 1,00 a margem virava negativa no pior caso.
        // Reverter é 1 linha se a decisão comercial for outra.
        'certidao_tjms' => 2.00,
    ],

    // TJMS — certidão cível de 2 etapas (fase 4). `modelo`/`comarca` exigem GRAFIA EXATA do eSAJ.
    // VALIDADO por smoke real (pedido 10559945, 2026-07-22): modelo "WEB - Ação Cível" +
    // comarca "Dourados" → 200. `comarca` default = Title-case do município (minhareceita manda
    // em caixa alta); o mapa abaixo cobre exceções acentuadas / grafia divergente.
    'tjms' => [
        'modelo' => env('ADVOCACIA_TJMS_MODELO', 'WEB - Ação Cível'),
        // Override município (CAIXA ALTA, como vem da minhareceita) → comarca (grafia exata eSAJ).
        'comarca_por_municipio' => [
            // exceções que o Title-case não acerta (acento / preposição minúscula):
            // 'AGUA CLARA' => 'Água Clara', 'CORUMBA' => 'Corumbá', 'PONTA PORA' => 'Ponta Porã',
        ],
        // Cada conferência (`obter-certidao`) é uma chamada PAGA (~R$ 0,24). Intervalo curto e fixo
        // dava margem NEGATIVA (12 polls = ~R$ 2,88 contra R$ 1,00 de receita). Escala + teto:
        // 1ª em 1h (o TJMS costuma emitir no mesmo dia → resolve em 1 chamada), depois 4h/12h/24h.
        // 1ª conferência 1h após o pedido; depois o backoff da escala. `prazo_inicial_min` é o
        // 1º degrau: mantenha-o = intervalos_min[0] para uma cadência única (1h,+1h,+4h,+12h,+24h).
        'prazo_inicial_min' => (int) env('ADVOCACIA_TJMS_PRAZO_INICIAL_MIN', 60),
        'intervalos_min' => [60, 240, 720, 1440],
        'max_verificacoes' => (int) env('ADVOCACIA_TJMS_MAX_VERIFICACOES', 5),
    ],

    // Grupos de apresentação da tela de seleção (ordem = ordem visual). As fontes judiciais
    // da fase 2 entram em grupos novos (judicial, trabalhista, integridade, passivo).
    'grupos' => [
        'judicial' => [
            'label' => 'Certidões judiciais',
            'fontes' => ['certidao_stj', 'certidao_trf', 'ceat_trt', 'certidao_mpt', 'certidao_mpf', 'certidao_tjms'],
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
            'label' => 'Cadastro e certidões fiscais',
            // `cadastro` (situação, GRÁTIS) é selecionável p/ rodar a consulta cadastral pura (plano
            // Gratuito). analise_fiscal (paga) abre o raio-X tributário derivado do mesmo cadastro.
            'fontes' => ['cadastro', 'analise_fiscal', 'cnd_federal', 'cnd_estadual', 'cnd_municipal', 'crf_fgts', 'cndt', 'sintegra'],
        ],
    ],
];
