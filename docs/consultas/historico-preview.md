# Preview do histórico de consultas

> **Status:** concluído
> **Owner:** Codex
> **Backlog:** [docs/backlog.md](../backlog.md)

## Contexto

Em `/app/consulta/historico`, os lotes são apresentados principalmente pelo identificador técnico,
produto, quantidade e custo. Como essas informações se repetem, o usuário não reconhece rapidamente
qual empresa ou grupo de empresas pertence a cada consulta.

## Estado atual / Arquitetura

- Rota: `GET /app/consulta/historico`.
- Controller: `ConsultaController::historico`.
- View: `resources/views/autenticado/consulta/historico.blade.php`.
- Os alvos ficam vinculados por `consulta_resultados` e, para participantes, também por
  `consulta_lote_participantes`.
- A listagem é paginada em 20 lotes e deve evitar consultas adicionais por linha.

## Fluxos

Cada item do histórico deve priorizar a identidade da consulta:

1. razão social e CNPJ do primeiro alvo;
2. indicação da quantidade de outros CNPJs no mesmo lote;
3. produto e origem da execução;
4. resumo dos resultados concluídos e com falha;
5. data, lote, custo, status e exportação como metadados operacionais.

Quando ainda não houver resultado persistido, a prévia usa os participantes vinculados ao lote.
Quando nenhum alvo puder ser identificado, exibe uma descrição neutra baseada na quantidade.

## Gaps / Plano de fases

- [x] Fase 1: enriquecer a query paginada com até três alvos e contadores de resultado.
- [x] Fase 2: redesenhar a lista desktop/mobile com identidade visual por consulta.
- [x] Fase 3: cobrir alvos únicos, lotes múltiplos, fallback e exportações em testes.
- [x] Fase 4: validar a renderização responsiva.

## Testes

- Feature test da rota do histórico com alvo único reconhecível.
- Feature test de lote com vários alvos e resumo `+ N CNPJs`.
- Regressão das ações Abrir/CSV/XLSX/PDF.
- Validação HTTP e compilação das views.

## Decisões já tomadas

- 2026-07-23 — A razão social passa a ser o título do item; `Lote #ID` deixa de ser a principal
  referência visual.
- 2026-07-23 — O preview limita a carga a três alvos por lote, preservando a paginação atual.
- 2026-07-23 — Esta primeira entrega altera apenas o histórico de Consulta CNPJ.
- 2026-07-23 — O clique em qualquer área não interativa da linha abre o lote.
- 2026-07-23 — O histórico usa o layout compartilhado sem rolagem horizontal.

## Decisões em aberto

- Avaliar depois se o mesmo padrão deve ser aplicado ao histórico resumido no painel de consultas.

## Log de progresso

- 2026-07-23 — Task iniciada e escopo limitado à tela de histórico.
- 2026-07-23 — Preview, interação por clique, testes e responsividade concluídos.
