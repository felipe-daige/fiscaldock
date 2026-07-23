# Previews contextuais dos históricos

> **Status:** concluído
> **Owner:** Codex
> **Backlog:** solicitação direta em 2026-07-23

## Contexto

As listagens de histórico priorizavam identificadores técnicos, datas, custo e status. Como esses
campos se repetem, o usuário precisava abrir vários itens até reconhecer a operação desejada.

## Estado atual / Arquitetura

| Histórico | Rota | Identidade principal |
|---|---|---|
| Consulta CNPJ | `/app/consulta/historico` | razão social, CNPJ e demais alvos |
| Importações | `/app/importacao/historico` | cliente, arquivo, tipo, competência e volume |
| Clearance em lote | `/app/clearance/notas/historico` | documentos, primeira operação e veredito |
| Busca avulsa | `/app/clearance/buscar/historico` | documento, operação, valor e situação SEFAZ |

Os detalhes expansíveis de Clearance permanecem como fonte do conteúdo completo. A linha fechada
passa a funcionar como preview reconhecível.

## Fluxos

- A data usa um bloco compacto (`Hoje`, `Ontem` ou `dd/mm` + hora).
- A informação que identifica a operação é o título visual.
- Badges de categoria e status ficam centralizados no desktop.
- Lote, chave e outros identificadores técnicos permanecem como metadados secundários.
- Desktop e mobile usam o mesmo DOM com `tabela-cards`.
- A linha inteira abre o resultado do registro; controles e menus mantêm suas ações independentes.
- A lista não usa rolagem horizontal: até 1023 px, cada linha é reorganizada como card; no desktop,
  a tabela usa colunas fixas e conteúdo truncado ou quebrado dentro da própria célula.
- Snapshots legados de busca avulsa, que não possuem página de resultado, abrem o detalhe inline.

## Gaps / Plano de fases

- [x] Fase 1: Consulta CNPJ com alvos e resumo de resultados.
- [x] Fase 2: importações EFD/XML com preview contextual.
- [x] Fase 3: clearance em lote com operação e veredito na linha fechada.
- [x] Fase 4: busca avulsa com documento, partes, valor e situação.
- [x] Fase 5: clique na linha, testes e validação responsiva das quatro telas.

## Testes

- Feature tests por rota com dados reconhecíveis.
- Regressão dos detalhes expansíveis e ações existentes.
- Compilação Blade e lint PHP.
- Validação visual desktop/mobile.

## Decisões já tomadas

- 2026-07-23 — Cada histórico recebe informações específicas do seu domínio; não haverá um card
  genérico compartilhado.
- 2026-07-23 — Nenhuma migration será criada.
- 2026-07-23 — Os detalhes ricos de Clearance serão preservados e a mudança ficará concentrada na
  linha fechada.
- 2026-07-23 — Históricos não devem depender de scroll horizontal; o breakpoint específico para
  cards é 1023 px.
- 2026-07-23 — O clique na linha abre o resultado, exceto em snapshots legados sem rota própria,
  que expandem o detalhe local.

## Decisões em aberto

Nenhuma.

## Log de progresso

- 2026-07-23 — Preview de Consulta CNPJ concluído; 107 testes relacionados verdes.
- 2026-07-23 — Escopo ampliado para importações, clearance em lote e busca avulsa.
- 2026-07-23 — Previews contextuais concluídos nas quatro rotas.
- 2026-07-23 — Clique integral nas linhas e layout sem rolagem horizontal concluídos.
- 2026-07-23 — Testes direcionados: 4 aprovados, 112 assertions; Vite e Blade compilados.
