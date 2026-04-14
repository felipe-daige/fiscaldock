# FiscalDock — Backlog e Roadmap

Consolidado de pendências conhecidas, planejamento futuro e decisões abertas.

## Pendências Conhecidas

### Alta prioridade

- **Clearance NF-e real**: o módulo existe em frontend/backend, mas `ClearanceController` ainda trata o clearance como não implementado. Falta integração real com SEFAZ/InfoSimples/n8n, classificação `conforme/divergente/irregular`, persistência do resultado e leitura real dos KPIs.
- **Integração NF-e/SEFAZ via InfoSimples**: a documentação de mapeamento existe, mas o app ainda não expõe campos persistidos como `situacao_sefaz` / `verificado_sefaz_em` nem o pipeline completo de consulta por chave de acesso.
- **Score Fiscal real**: `RiskScoreController` ainda possui geração simulada e não deve ser tratado como feature concluída até a integração externa substituir esse caminho.

### Média prioridade

- **Importação XML "pronta" de navegação/produto**: a feature existe, mas o menu ainda pode tratá-la como "Em Breve".
- **Busca avulsa de NF-e no Clearance**: input por chave de acesso e consulta direta na SEFAZ sem depender de nota importada — ainda não consolidado.
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

Pendente menor: refactor dos cards de `/precos` para ler do `@php $planos` como fonte única (hoje só o JSON-LD usa).

### XML import automation backlog

Módulo existe (`/app/importacao/xml`) mas automação operacional incompleta.

- Fortalecer automação e background processing end-to-end
- Reduzir handoff manual entre validação, import, extração de participantes
- Documentar o que é automático vs manual
- Alinhar roadmap com BI, notas fiscais, clearance

### Plan strategy: CND Federal, Estadual, Municipal

**Regra dura:** toda chamada InfoSimples tem custo real. Nenhuma CND entra em tier grátis/promocional.

- `validacao` (promoção grátis) e `gratuito` → apenas minhareceita.org
- CND Federal, Estadual, Municipal → obrigatoriamente em tier pago (`licitacao` ou novo `compliance`)
- Preço de cada plano deve somar custo InfoSimples por consulta + margem antes de definir créditos cobrados

Qualquer mudança de plano deve documentar:
- APIs/queries incluídas
- Custo de créditos ou impacto em subscription
- Se loops permanecem sequenciais no n8n
- Como `resultado_dados` é estendido sem quebrar consumidores atuais

### Candidate APIs for a new plan

- Ficar com InfoSimples primeiro quando expandir cobertura fiscal/compliance com baixa complexidade
- Só adicionar nova API externa se criar surface de plano claramente diferenciada (judicial/compliance, protest, labor, document-verification)
- Documentar confiabilidade da fonte, pricing model, retry/error behavior, mapeamento no Laravel/n8n

### Mercado Pago integration

Checkout ainda simulado. Mercado Pago é a direção planejada.

Escopo para rollout real:
- Fluxo de criação de preference/payment do Laravel
- Webhook endpoint(s) para status updates
- Reconciliar estados approved/rejected/pending/refunded
- Liberar créditos apenas após estado confirmado
- Alinhar copy do checkout UI com fluxo real
- Idempotência, webhook signature validation, recovery para notificações atrasadas

## Integrações InfoSimples — Planejamento CND

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

### Impacto nos planos

- Plano `compliance` (futuro, 9 créditos) incluirá CND Federal + Estadual + Municipal
- Plano `licitacao` pode ser estendido para incluir Estadual + Municipal

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

## Estratégia de planos e promoção

| Plano | Preço | API | Nota |
|---|---|---|---|
| `gratuito` | Grátis | minhareceita.org | Sem regime tributário (futuramente) |
| `validacao` | **Grátis (promoção)** | minhareceita.org | Promoção temporária. Futuramente cobrará 2 créditos |
| `licitacao` | 3 créditos | minhareceita.org + InfoSimples | Dois loops sequenciais no n8n |

**Diferença futura gratuito vs validação:** única mudança será que o gratuito não salvará `regime_tributario` no node Postgres de UPDATE participantes.

**Licitação = dois loops sequenciais:**
1. Loop 1: minhareceita.org (progresso 0-50%)
2. Loop 2: InfoSimples CND Federal (progresso 50-100%)
3. Loop 2 faz MERGE no `resultado_dados` existente (`||` JSONB)

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
