<?php

return [
    // Busca avulsa por chave (NF-e/CT-e) — segue DESLIGADA por padrão (comportamento atual).
    'busca_avulsa' => [
        'habilitada' => (bool) env('CLEARANCE_BUSCA_AVULSA_HABILITADA', false),
    ],

    // Clearance Full — Camada A: regularidade da CONTRAPARTE (participante externo) da nota.
    // Reusa o motor da Consulta CNPJ (3 fontes) e projeta em participante_scores. Enquanto
    // 'habilitado' é false, a UI mostra placeholders "em breve" e o tier 'full' é coagido a
    // 'basico'. Spec: docs/clearance/clearance-full-camada-a.md
    'full' => [
        'habilitado' => (bool) env('CLEARANCE_FULL_HABILITADO', false),

        // Sub-atributos de consultas_incluidas → resolvem exatamente {cadastro, sintegra,
        // cnd_federal} via FonteRegistry. Fontes nota-cêntricas (validade/crédito da nota);
        // CNDT/FGTS/estadual/municipal ficam no Consulta CNPJ (não duplicar Compliance).
        'consultas_incluidas' => ['situacao_cadastral', 'sintegra', 'cnd_federal'],

        // Janela de frescura: participante consultado há menos de N dias reaproveita o
        // participante_scores existente (R$ 0, sem chamar InfoSimples). Fora dela, reconsulta.
        'frescura_dias' => (int) env('CLEARANCE_FULL_FRESCURA_DIAS', 30),

        // PREÇO: não fica aqui. O Clearance completo é preço FECHADO por nota —
        // `ValidacaoContabilService::CUSTO_DOCUMENTO_FULL` (R$ 2,00), contra
        // `CUSTO_DOCUMENTO` (R$ 1,00) do básico. Dedup + frescura reduzem o CUSTO EXTERNO
        // (margem), nunca o preço cobrado.
    ],

    // Comparação Declarado × SEFAZ de TRIBUTOS e ITEM-A-ITEM (superfície da Camada B).
    // NÃO CONSTRUÍDA — o dado completo já chega quando há certificado, mas as telas ainda não o
    // confrontam com o declarado (EFD/XML). Item-a-item no XML depende de `xml_notas_itens`, que
    // não existe. Enquanto false, o resultado do clearance mostra o bloco "em breve".
    // NÃO gatear isso em `full.habilitado` — Full (Camada A = regularidade) é outra coisa.
    'comparacao_declarado' => (bool) env('CLEARANCE_COMPARACAO_DECLARADO', false),

    // Certificado digital A1 na consulta SEFAZ (Camada B). Quando o cliente da nota tem cert
    // válido cadastrado, a consulta vai ASSINADA (InfoSimples `pkcs12_cert`/`pkcs12_pass`) e a
    // SEFAZ devolve o documento COMPLETO (tributos, itens, XML, contraparte sem máscara).
    // Sem cert → consulta pública (comportamento de sempre). O gate real é o DADO (tem cert ou
    // não); esta flag é só o kill-switch. Spec: docs/clearance/certificado-a1.md
    'certificado' => [
        'habilitado' => (bool) env('CLEARANCE_CERTIFICADO_HABILITADO', true),
    ],
];
