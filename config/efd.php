<?php

// Listas de CFOP do BI (fonte: tabela CFOP CONFAZ — Ajuste SINIEF 07/2001).
// cfops_devolucao = devolução (venda=entrada, compra=saída). Editável por env.
$cfopsDevolucao = array_values(array_filter(array_map('intval', explode(',', (string) env(
    'EFD_CFOPS_DEVOLUCAO',
    // dev. de venda (entrada): 1201,1202,1410,1411,2201,2202,2410,2411
    // dev. de compra (saída):  5201,5202,5210,5410,5411,6201,6202,6210,6410,6411
    '1201,1202,1410,1411,2201,2202,2410,2411,5201,5202,5210,5410,5411,6201,6202,6210,6410,6411'
)))));

// Conserto/industrialização (não-receita, sem devolução — devolução entra via merge).
// 1407/2556 (compra p/ uso e consumo) SAÍRAM da lista em 2026-07-21: passam a compor
// a base comercial (aquisição) por decisão do contador.
$cfopsNaoReceita = array_values(array_filter(array_map('intval', explode(',', (string) env(
    'EFD_CFOPS_FORA_FATURAMENTO',
    '1915,1916,5916,6915,6916'
)))));

return [
    // Devolução: consumida pela seção Devoluções do BI E excluída do faturamento.
    'cfops_devolucao' => $cfopsDevolucao,

    // Não compõem faturamento: não-receita ∪ devolução (decisão nível-nota,
    // contador 2026-06-23 + auditoria 2026-06-29). Exclui a nota INTEIRA.
    'cfops_fora_faturamento' => array_values(array_unique(array_merge($cfopsNaoReceita, $cfopsDevolucao))),
];
