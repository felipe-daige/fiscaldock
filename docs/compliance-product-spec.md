# Compliance — Especificação do produto

Produto de consulta de CNPJ da FiscalDock. Agrega múltiplas fontes oficiais em uma única consulta atômica, cobrando em créditos por participante consultado.

## Visão geral

Compliance é o "pacote de regularidade" do participante. Cada consulta executa em paralelo as fontes abaixo e persiste em `consultas.resultado_dados.{chave}`. Novas fontes entram no pacote sem mudar a interface de cobrança — o cliente paga por consulta Compliance, não por fonte.

## Fontes

| # | Fonte | Slug InfoSimples | Categoria | Status | Chave em `resultado_dados` |
|---|---|---|---|---|---|
| 1 | minhareceita.org | (externo) | Cadastral | Ativo | `cadastro` |
| 2 | CND Federal (PGFN/RFB) | `receita-federal/pgfn` | Fiscal obrigatória | Em implementação | `cnd_federal` |
| 3 | CND Estadual | `sefaz/certidao-debitos` | Fiscal obrigatória | Em breve | `cnd_estadual` |
| 4 | CND Municipal | `pref/{uf}/{cidade}/cnd` | Fiscal obrigatória | Em breve | `cnd_municipal` |
| 5 | CNDT (TST) | `tribunal/tst/cndt` | Trabalhista obrigatória | Em breve | `cndt` |
| 6 | CRF FGTS (Caixa) | `caixa/regularidade` | FGTS obrigatória | Em breve | `crf_fgts` |
| 7 | CGU CNC | `cgu/cnc-tipo1` | Sanções | Em breve | `cgu_cnc` |
| 8 | CNJ Improbidade | `cnj/improbidade` | Reputacional | Em breve | `cnj_improbidade` |
| 9 | SINTEGRA | `sintegra/unificada` | Cadastral estadual | Em breve | `sintegra` |

## Escada comercial atual

Decisão vigente em `2026-04-15`, mantendo `1 crédito = R$ 0,20`:

| Plano | Créditos | Preço por consulta | Uso |
|---|---:|---:|---|
| Gratuito | 0 | R$ 0,00 | Consulta cadastral simples sem custo variável relevante |
| Validação | 5 | R$ 1,00 | CNPJ + Simples + SINTEGRA básico |
| Licitação | 10 | R$ 2,00 | Validação + CND Federal + CNDT/FGTS |
| Compliance | 18 | R$ 3,60 | Licitação + CND Estadual + CND Municipal |
| Due Diligence | 35 | R$ 7,00 | Compliance + sanções + CNJ + protestos/processos |

Novas fontes **não** devem ser adicionadas a planos baratos sem revalidar custo InfoSimples e margem. Reajuste é decisão comercial separada.

### Liberação por primeira compra

`Compliance` e `Due Diligence` ficam bloqueados para usuários que ainda só possuem trial, bônus ou créditos adicionados manualmente. A liberação acontece quando existir ao menos uma transação positiva em `credit_transactions` com:

- `type = purchase`
- `amount > 0`

Não criar booleano duplicado em `users` por padrão. Se no futuro houver necessidade de cache/segmentação comercial, o campo pode ser criado explicitamente, mas a regra atual usa o histórico de créditos como fonte de verdade.

## Ordem sugerida de rollout

1. CND Federal (em curso, 2026-04-07)
2. CND Estadual (SEFAZ) — maior demanda após Federal
3. CRF FGTS — exigência direta em contratação
4. CNDT — exigência de licitação
5. SINTEGRA — proteção de ICMS
6. CND Municipal — complexo (resolver slug UF+cidade)
7. CGU CNC — sanções consolidadas
8. CNJ Improbidade — requer busca por sócios (passo a mais)

## Specs individuais

- [`infosimples/cnd-estadual.md`](infosimples/cnd-estadual.md)
- [`infosimples/cnd-municipal.md`](infosimples/cnd-municipal.md)
- [`infosimples/cndt-tst.md`](infosimples/cndt-tst.md)
- [`infosimples/crf-fgts.md`](infosimples/crf-fgts.md)
- [`infosimples/cgu-cnc.md`](infosimples/cgu-cnc.md)
- [`infosimples/cnj-improbidade.md`](infosimples/cnj-improbidade.md)
- [`infosimples/sintegra-unificada.md`](infosimples/sintegra-unificada.md)

Referência base de outputs: [`infosimples-cnd-federal-outputs.md`](infosimples-cnd-federal-outputs.md) — reaproveitar grupos de códigos (600–622) e regra de `billable`.
