# FiscalDock — Backlog e Roadmap

Consolidado de pendências conhecidas, planejamento futuro e decisões abertas.

## Pendências Conhecidas

### Alta prioridade

- **Clearance DF-e real**: o módulo existe em frontend/backend via `ValidacaoController`, com dashboard, listagem, detalhe e busca avulsa. Falta integração real InfoSimples/SEFAZ/n8n, classificação `conforme/divergente/irregular`, persistência do resultado externo e leitura real dos KPIs baseados em fonte externa.
- **Integração NF-e/SEFAZ via InfoSimples**: a documentação de mapeamento existe, mas o app ainda não expõe campos persistidos como `situacao_sefaz` / `verificado_sefaz_em` nem o pipeline completo de consulta por chave de acesso.
- **Score Fiscal real**: `RiskScoreController` ainda possui geração simulada e não deve ser tratado como feature concluída até a integração externa substituir esse caminho.

### Decisão atual do MVP de clearance

- **MVP inicial do clearance NF-e:** seguir com `InfoSimples`
- **Objetivo do MVP:** validar aderência da feature com a menor complexidade de implementação possível
- **Fonte inicial:** usar `receita-federal/nfe` via InfoSimples como consulta nacional por chave; não consultar SEFAZ estadual por UF no MVP
- **Persistência:** a busca avulsa deve salvar a nota consultada no PostgreSQL via n8n, usando upsert em `xml_notas` para evitar duplicidade
- **Associação opcional:** usuário pode vincular a nota consultada a um cliente; `cliente_id` deve ser validado no Laravel antes do webhook n8n
- **Justificativa comercial inicial:** em volume baixo/intermediário, o InfoSimples ficou aceitável para começar e levemente melhor no comparativo de `300` notas (`R$ 100,00`) versus `Focus NFe` (`R$ 109,90`)
- **Ponto de virada observado:** em `1.000` notas, `Focus NFe` passa a ser melhor economicamente (`R$ 179,90`) do que `InfoSimples` (`R$ 240,00`)
- **Direção futura já definida:** se a feature provar aderência real, avaliar migração da camada principal para `Focus NFe`, mantendo o InfoSimples apenas como fallback ou ferramenta pontual
- **Alerta futuro possível:** divergência entre Receita/Portal Nacional e SEFAZ estadual deve ser considerada evolução posterior, reportada como alerta operacional de sincronização/inconsistência, não como fraude automática

### Média prioridade

- **Importação XML "pronta" de navegação/produto**: a feature existe, mas o menu ainda pode tratá-la como "Em Breve".
- **Busca avulsa de DF-e no Clearance**: a view `/app/validacao/buscar-nfe` já existe com seleção de NF-e/CT-e/NFS-e, chave de acesso, cliente opcional, custo estimado, estados previstos e histórico local. Falta backend real para consulta via InfoSimples/Receita Federal, persistência via n8n em `xml_notas` e retorno de status ao usuário.
- **Cruzamentos no BI entre consultas e clearance**: documentados, mas ainda backlog.
- **n8n — status=erro em consultas**: implementar envio de `status=erro` para `POST /api/consultas/progresso` quando ocorrer falha no processamento.

### Acabamento e dívida visual

- **Conversão visual pendente**: `autenticado/risk/*`, partes de `minha-empresa/*`, telas de monitoramento, formulários antigos, checkout — ainda usam estilo antigo (`rounded-lg`, `bg-blue-*`, badges `bg-*-100 text-*-700`).

## Public Site, SEO, Payments, and Plan Expansion

### Public site beyond the landing page

Concluído em 2026-04-14 (commit `aa9f99e`):
- **Blog**: 9 posts novos, 5 leves refatorados, tags + `relatedByTags()`, 5 topical clusters (`/blog/tema/{efd,clearance,consultas,compliance,sped}`) com FAQPage, navegação prev/next de série, sitemap dos hubs.
- **`/solucoes`, `/precos`, `/duvidas`**: interlinking contextual (cada módulo de `/solucoes` com precos/duvidas/blog cluster), `BreadcrumbList` + `ItemList`/`Product`/`FAQPage` JSON-LD por página, `Organization` + `WebSite` + `Service` em `/inicio`, `og_type`/`og_image`/`og_title` por página, copy alinhada ao estado real (Clearance "em construção", Score "em beta", CEIS "em breve", CND "em implementação").
- **Aquisição pública**: `/agendar` consolidado como contato comercial, `/termos` e `/privacidade` acessíveis na navegação pública, `/criar-conta` com trial autoatendido de 100 créditos por 30 dias, expiração automática do saldo promocional e contexto de trial no ambiente autenticado.

Concluído em 2026-04-14: `/precos` passou a usar modelo público de créditos + faixas, sem cards de mensalidade.

Concluído em 2026-04-14: CTAs públicos principais `Criar conta grátis` foram padronizados em `.btn-cta`, com fallback inline no layout público para preservar contraste e legibilidade mesmo se o CSS do Vite atrasar ou falhar. O rebuild de `public/build` também foi refeito com Node compatível ao Vite 7.

### XML import automation backlog

Módulo existe (`/app/importacao/xml`) mas automação operacional incompleta.

- Fortalecer automação e background processing end-to-end
- Reduzir handoff manual entre validação, import, extração de participantes
- Documentar o que é automático vs manual
- Alinhar roadmap com BI, notas fiscais, clearance

### Estratégia de créditos e faixas: CND Federal, Estadual, Municipal

**Regra dura:** toda chamada InfoSimples tem custo real. Nenhuma CND entra em trial ou camada gratuita.

- `gratuito` continua restrito a fontes sem custo variável relevante, como minhareceita.org
- `validacao` passa a incluir fontes InfoSimples leves, por isso cobra 5 créditos por consulta
- CND Federal, Estadual e Municipal entram apenas em produtos pagos por crédito, priorizando `licitacao` e `compliance`
- Tabela comercial vigente:
  - `1 crédito = R$ 0,20`
  - `Gratuito`: `0` créditos por consulta cadastral simples
  - `Validação`: `5` créditos por consulta (`R$ 1,00`) com CNPJ + Simples + SINTEGRA básico
  - `Licitação`: `10` créditos por consulta (`R$ 2,00`) com Validação + CND Federal + CNDT/FGTS
  - `Compliance`: `18` créditos por consulta (`R$ 3,60`) com Licitação + CND Estadual + CND Municipal
  - `Due Diligence`: `35` créditos por consulta (`R$ 7,00`) com Compliance + sanções + CNJ + protestos/processos
  - `Clearance`: `14 / 12 / 10 / 8` créditos por consulta nas faixas `Base / X / Y / Z`
  - Faixas por histórico pago acumulado: `1.000 / 5.000 / 20.000` créditos
  - A regra comercial atual é manter `1 crédito = R$ 0,20`; ajustes de margem devem mudar o custo em créditos do produto, não o preço do crédito.
- `Compliance` e `Due Diligence` ficam bloqueados até a primeira compra confirmada de créditos (`credit_transactions.type = purchase` e `amount > 0`). Trial, bônus e adição manual não liberam estes produtos.

Qualquer mudança comercial deve documentar:
- APIs/queries incluídas
- custo em créditos por consulta
- impacto na faixa/economia por volume
- se loops permanecem sequenciais no n8n
- como `resultado_dados` é estendido sem quebrar consumidores atuais

### Candidate APIs for paid products

- Ficar com InfoSimples primeiro quando expandir cobertura fiscal/compliance com baixa complexidade
- Só adicionar nova API externa se criar surface de produto claramente diferenciada (judicial/compliance, protesto, trabalhista, verificação documental)
- Documentar confiabilidade da fonte, pricing model, retry/error behavior, mapeamento no Laravel/n8n

### Clearance NF-e — evolução de fornecedor

- **Fornecedor do MVP:** `InfoSimples`
- **Critério para troca de fornecedor principal:** aumento de volume, necessidade de fluxo mais forte de MDe/recebidas e comprovação de aderência da feature
- **Fornecedor candidato para fase 2:** `Focus NFe`
- **Racional da fase 2:** melhor custo em volume e melhor alinhamento com operação contínua de documentos recebidos

### Mercado Pago integration

Checkout ainda simulado. Mercado Pago é a direção planejada para compra avulsa de créditos.

Escopo para rollout real:
- Fluxo de criação de preference/payment do Laravel
- Webhook endpoint(s) para status updates
- Reconciliar estados approved/rejected/pending/refunded
- Liberar créditos apenas após estado confirmado
- Alinhar copy do checkout UI com fluxo real
- Idempotência, webhook signature validation, recovery para notificações atrasadas

## Integrações InfoSimples — Roadmap Compliance (9 fontes)

Produto Compliance evolui como **pacote multi-fonte**. Ordem de rollout e status atuais em 2026-04-14:

| # | Fonte | Slug | Status | Spec |
|---|---|---|---|---|
| 1 | minhareceita.org | (externo) | Ativo | — |
| 2 | CND Federal | `receita-federal/pgfn` | Em implementação | `docs/infosimples-cnd-federal-outputs.md` |
| 3 | CND Estadual | `sefaz/certidao-debitos` | Em breve | `docs/infosimples/cnd-estadual.md` |
| 4 | CRF FGTS | `caixa/regularidade` | Em breve | `docs/infosimples/crf-fgts.md` |
| 5 | CNDT | `tribunal/tst/cndt` | Em breve | `docs/infosimples/cndt-tst.md` |
| 6 | SINTEGRA | `sintegra/unificada` | Em breve | `docs/infosimples/sintegra-unificada.md` |
| 7 | CND Municipal | `pref/{uf}/{cidade}/cnd` | Em breve | `docs/infosimples/cnd-municipal.md` |
| 8 | CGU CNC | `cgu/cnc-tipo1` | Em breve | `docs/infosimples/cgu-cnc.md` |
| 9 | CNJ Improbidade | `cnj/improbidade` | Em breve | `docs/infosimples/cnj-improbidade.md` |

Visão de produto consolidada: `docs/compliance-product-spec.md`.

### CND Federal (em implementação 2026-04-07)

Decisão: **Opção A (n8n faz tudo)**, consistente com fluxo EFD. Workflow n8n `Consulta` branch `Licitacao` consulta `receita-federal/pgfn` via InfoSimples API.

- Spec: `docs/superpowers/specs/2026-04-07-cnd-federal-infosimples-workflow-design.md`
- Plano: `docs/superpowers/plans/2026-04-07-cnd-federal-infosimples.md`

### CND Estadual (SEFAZ) — Planejado 2026-04-09

Mesma arquitetura da CND Federal. InfoSimples centraliza os 27 estados em uma única estrutura.

- **Endpoint:** `https://api.infosimples.com/api/v2/consultas/sefaz/certidao-debitos`
- **Parâmetros:** `token`, `cnpj`, `cpf`, `ie`, `uf`, `cep`, `cpf_emissao`, `login_cpf`, `login_senha`, `preferencia_emissao` (obrigatoriedade varia por estado)
- **Response (`data[0]`):** `certidao_codigo`, `conseguiu_emitir_certidao_negativa`, `emissao_data`, `mensagem`, `validade_data`
- **Cobertura:** 27 UFs
- **Códigos de resposta:** mesmos 5 grupos da CND Federal

**Ponto de atenção:** UF extraída do campo `uf` da tabela `participantes` (vem do 0150 do SPED). Vazio = skip.

### CND Municipal (Prefeituras)

**Cada município tem seu próprio endpoint** no InfoSimples. Não existe endpoint unificado.

- **Padrão:** `pref-{uf}-{cidade}-cnd`
  - Exemplos: `pref-sp-campinas-cnd`, `pref-go-goiania-cnd`, `pref-pr-curitiba-cnd`
- **Parâmetros:** `token`, `cnpj` e/ou `cpf` (varia por município)
- **Response:** `conseguiu_emitir_certidao_negativa`, `cpf_cnpj`, `emissao_data`, `data_validade`, `mensagem`, `numero_certidao`, `titulo` (campos variam, normalização no Code node)
- **Cobertura:** ~80+ municípios (SP, RJ, MG, PR, SC, RS, GO, BA, CE, PE, PA, MT, MA, RN, TO, AP e outros)

**Pontos de atenção:**
1. Resolver endpoint via cidade do participante (campo `municipio` na tabela `participantes`)
2. Cobertura parcial — se município não está no catálogo, skip sem erro
3. Normalização de nome → slug (sem acentos, hífens): "São Luís" → `sao-luis`, "Goiânia" → `goiania`
4. Response heterogênea — Code node normaliza antes de gravar

### Estrutura planejada do `resultado_dados` (Estadual + Municipal)

```json
{
  "cnd_federal": { "..." },
  "cnd_estadual": {
    "status": "Negativa|Positiva com efeitos de negativa|Positiva|null",
    "uf": "SP",
    "certidao_codigo": "...",
    "emissao_data": "DD/MM/YYYY",
    "data_validade": "DD/MM/YYYY",
    "conseguiu_emitir": true,
    "mensagem": "..."
  },
  "cnd_municipal": {
    "status": "Negativa|Positiva com efeitos de negativa|Positiva|null",
    "municipio": "Goiânia",
    "uf": "GO",
    "numero_certidao": "...",
    "emissao_data": "DD/MM/YYYY",
    "data_validade": "DD/MM/YYYY",
    "conseguiu_emitir": true,
    "mensagem": "..."
  },
  "consultas_realizadas": ["cnd_federal", "cnd_estadual", "cnd_municipal"]
}
```

### Impacto na tabela comercial

- `Compliance` é o produto principal para agrupar CND Federal + Estadual + Municipal
- `Clearance` permanece mais caro do que `Compliance` em todas as faixas
- códigos legados como `licitacao` permanecem apenas por compatibilidade técnica, não como linguagem comercial

**Workflow n8n — loops sequenciais adicionais:**
1. minhareceita.org (existente — dados cadastrais)
2. CND Federal (existente — `receita-federal/pgfn`)
3. CND Estadual (novo — `sefaz/certidao-debitos`, precisa de `uf`)
4. CND Municipal (novo — `pref-{uf}-{cidade}-cnd`, precisa de mapeamento município→slug)

### InfoSimples — Resumo técnico

- **Auth:** token como parâmetro (`INFOSIMPLES_TOKEN` no `.env`)
- **Endpoint:** `https://api.infosimples.com/api/v2/consultas/{servico}`
- **Resposta:** `{ code, code_message, data, header, site_receipts }`
- **5 grupos:** sucesso (200/201), não encontrado (612), erro do participante (608/611/619/620), temporário/retry (600/605/609/610/613/614/615/618), fatal (601-607/617/621/622)

**Mapeamento crítico (InfoSimples → Laravel):**
- `data[0].tipo` → `cnd_federal.status` (lido por `ConsultaController` via `strtoupper()`)
- `data[0].validade` → `cnd_federal.data_validade`

## Estratégia comercial vigente

| Produto | Créditos | Preço por consulta | Uso |
|---|---:|---:|---|
| `Gratuito` | 0 | R$ 0,00 | Consulta cadastral simples sem custo variável relevante |
| `Validação` | 5 | R$ 1,00 | CNPJ + Simples + SINTEGRA básico |
| `Licitação` | 10 | R$ 2,00 | Validação + CND Federal + CNDT/FGTS |
| `Compliance` | 18 | R$ 3,60 | Licitação + CND Estadual + CND Municipal |
| `Due Diligence` | 35 | R$ 7,00 | Compliance + sanções + CNJ + protestos/processos |
| `Clearance` | 14 / 12 / 10 / 8 | R$ 2,80 / R$ 2,40 / R$ 2,00 / R$ 1,60 | Validação premium de documentos fiscais por faixa |

`Enterprise` fica fora da escada comercial atual; permanece apenas como código legado/inativo até existir uma oferta corporativa real.

Pacotes avulsos de referência:

| Pacote | Créditos | Preço |
|---|---|---|
| `Starter` | 100 | R$ 20 |
| `Growth` | 500 | R$ 100 |
| `Business` | 1.000 | R$ 200 |
| `Enterprise` | 5.000 | R$ 1.000 |

**Prioridades futuras:** SINTEGRA/Simples/CNPJ/CEIS → PGFN/Protestos/FGTS/SEFAZ → NFe/CTe/NFS-e/CNEP

## Cruzamentos EFD planejados para dashboards

| Cruzamento | Dados envolvidos | Alerta |
|------------|-----------------|--------|
| ICMS declarado vs notas | `efd_apuracoes_icms` E110 vs soma `efd_notas` C/D | Divergência entre ICMS apurado e débitos/créditos |
| PIS/COFINS declarado vs notas | `efd_apuracoes_contribuicoes` M200/M600 vs soma `efd_notas` A | Créditos perdidos ou contribuição indevida |
| Receitas não tributadas vs CSTs | M400/M410 vs CSTs de `efd_notas_itens` | Receitas isentas que normalmente são tributadas |
| Estoque vs movimentação | H010 vs entradas/saídas C100 | Principal gatilho de malha fiscal SEFAZ |
| Retenções para compensação | `efd_retencoes_fonte` F600 + `participantes` | Retidos compensáveis na apuração |
| ICMS-ST vs regime do fornecedor | E210 + `participantes` (regime_tributario) | ST sobre fornecedor que já recolheu (bitributação) |

## Cruzamentos Consultas × Clearance (BI)

- Fornecedor com CND positiva + notas dele canceladas na SEFAZ
- Fornecedor no CEIS + volume de compras dele no período
- Nota com divergência de valor + regime tributário do emitente
- Notas frias + situação cadastral do emitente
