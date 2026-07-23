<?php

return [
    // Validade default em DIAS (contada da emissão) quando a resposta da fonte NÃO traz
    // `data_validade`. Só órgãos com prazo legal conhecido; null/ausente = certidão fica sem
    // valida_ate e não entra nos alertas de vencimento. Parse da resposta SEMPRE vence a regra.
    // Spec: docs/advocacia/consultas-certidoes.md (fases 2 e 5).
    'validade_default_dias' => [
        'cnd_federal' => 180,  // Portaria RFB/PGFN — 180 dias
        'cndt' => 180,         // Lei 12.440/2011 — 180 dias
        'crf_fgts' => 30,      // CRF Caixa — 30 dias
    ],

    // Faixas de aviso de vencimento (dias restantes). A cada faixa cruzada nasce um alerta
    // novo na Central (e e-mail) e o da faixa anterior auto-resolve no recalcular diário.
    'alerta_faixas' => [15, 7, 1],

    // Piso de "vencida": certidão vencida há MAIS que isto para de alertar (o usuário já sabe;
    // re-emite quando quiser). Sem piso, uma certidão de um alvo abandonado geraria alerta
    // 'venceu em [data antiga]' para sempre no recalcular diário.
    'alerta_vencida_ate_dias' => 30,
];
