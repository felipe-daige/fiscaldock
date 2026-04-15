# FiscalDock — Instruções para Claude Code

Plataforma SaaS brasileira de monitoramento fiscal/tributário. Laravel 12 + PostgreSQL + Docker + n8n. Contadores e empresas importam arquivos SPED, fazem consultas tributárias e acompanham processos em tempo real.

## Comandos Essenciais

```bash
# Artisan (sempre dentro do container)
docker exec fiscaldock-app-1 php artisan migrate --force
docker exec fiscaldock-app-1 php artisan cache:clear
docker exec fiscaldock-app-1 php artisan view:clear

# Assets (Vite + Tailwind v4) — rodar com Node compatível
npm run build

# Testes
docker exec fiscaldock-app-1 php artisan test
docker exec fiscaldock-app-1 ./vendor/bin/pest

# Lint (PSR-12)
docker exec fiscaldock-app-1 ./vendor/bin/pint
```

## Regras Críticas

- **NUNCA criar novas migrations** — sempre editar as existentes. Migration central de EFD: `2026_01_30_000002_create_notas_sped_table.php`.
- **NUNCA editar `vendor/`** — vem da imagem Docker, não está montado do host.
- **Volume mount seletivo** (`/root/fiscaldock/docker-compose.yml`): `resources/`, `routes/`, `app/`, `config/`, `database/`, `public/js|css|build/` são montados do host. OPcache com `validate_timestamps=1` — edições refletem sem restart.
- **Container principal:** `fiscaldock-app-1` | **Scheduler:** `fiscaldock-scheduler-1`.
- `--force` só em `migrate` — outros comandos artisan não aceitam.
- **`public/build/` não está no git.** Se der `ViteManifestNotFoundException`, rodar `npm run build` com Node compatível ao Vite atual.
- **Landing pública:** a correção de legibilidade do CTA `Criar conta grátis` foi concluída em 2026-04-14. O padrão é `.btn-cta`, com fallback inline em `resources/views/landing_page/layouts/public.blade.php`.

## Arquitetura

- PHP 8.3 / Laravel 12 / PostgreSQL (`pgsql`)
- Tailwind CSS v4 + Vite
- n8n para processamento assíncrono via webhooks
- SSE (Server-Sent Events) para progresso em tempo real
- Sistema de créditos prepago: `CreditService` + `CreditTransaction`
- Escada comercial atual: `Gratuito` 0 créditos, `Validação` 5, `Licitação` 10, `Compliance` 18, `Due Diligence` 35. `1 crédito = R$ 0,20`.
- `Compliance` e `Due Diligence` ficam bloqueados até a primeira compra confirmada (`credit_transactions.type = purchase`, `amount > 0`). Trial, bônus e `manual_add` não liberam.
- **Actions** (`app/Actions/`): classes single-responsibility com método único `execute()`
- **Middleware `EnsureEmpresaPropriaExists`** (grupo `web`): cria automaticamente o registro de "empresa própria" do usuário em `clientes` (`is_empresa_propria = true`)
- **EFD Models:** `EfdNota`, `EfdNotaItem`, `EfdCatalogoItem`, `EfdApuracaoContribuicao`, `EfdApuracaoIcms`, `EfdRetencaoFonte`

## Estrutura de Arquivos

```
app/
  Actions/                  # single-responsibility, método execute()
  Http/Controllers/
    Dashboard/              # área autenticada
    Api/                    # webhooks, recebimento de dados
    Auth/
    Landing/                # páginas públicas
  Models/
  Services/                 # CreditService, etc.
resources/views/
  autenticado/              # área logada
  landing_page/             # páginas públicas
  emails/
database/migrations/        # EDITAR existentes, nunca criar novas
```

## Padrões de Código

- **PSR-12** (Pint)
- **Domínio em português, framework em inglês** — variáveis de negócio em pt-BR, classes/métodos Laravel em inglês
- **Injeção de dependência** no construtor dos controllers
- **Single-action controllers** usam `__invoke`
- **AJAX detection:** controllers retornam views parciais via `$request->ajax()` ou `$request->wantsJson()`
- **Design System DANFE Modernizado** — todas as views autenticadas seguem esse padrão. **Regra dura: nunca usar classes Tailwind de cor para background de badges** (Tailwind v4 compila para `oklch()` e não renderiza em todos os browsers). Sempre `style="background-color: #hex"` inline. Detalhes completos em `docs/design-system.md`.

## Convenções de Banco de Dados

- Prefixo por domínio: `efd_*` (importações EFD), `xml_*` (importações XML)
- Tabelas EFD: `efd_importacoes`, `efd_notas`, `efd_notas_itens`, `efd_participantes`, `efd_catalogo_itens`, `efd_apuracoes_contribuicoes`, `efd_apuracoes_icms`, `efd_retencoes_fonte`
- Tabelas XML: `xml_importacoes`, `xml_notas`

## Decisões de Arquitetura

### Laravel não atualiza participantes

O Laravel **não atualiza** campos do `Participante` (situacao_cadastral, regime_tributario, razao_social, `ultima_consulta_em`) ao receber resultados de consultas. Toda atualização é responsabilidade do **n8n**.

Não reintroduzir lógica de atualização de participante em `DataReceiverController` (nem `receiveMonitoramentoConsulta`, nem `receiveConsultasProgresso`).

### API unificada de progresso de consultas

Endpoint único `POST /api/consultas/progresso` (`DataReceiverController::receiveConsultasProgresso`). Trata 3 cenários via campo `status`:

- **`processando`** — cache `progresso:{user_id}:{tab_id}` (TTL 600s), lido por SSE em `ConsultaController::streamProgresso`
- **`concluido`** — persiste `resultado_resumo` + `processado_em` no `ConsultaLote`, atualiza cache (SSE fecha)
- **`erro`** — persiste `error_code`/`error_message`. Se `refund_credits=true`, estorna créditos (`refund_amount` parcial, ausente = total)

### Extração EFD — n8n grava direto

- **n8n grava direto no PostgreSQL** — não há endpoint Laravel para dados de notas
- **Laravel só recebe progresso** via `POST /api/importacao/efd/notas/progresso` (campos `bloco`, `fase: inicio|processando|concluido|skip`)
- Progresso de participantes usa endpoint separado: `/api/monitoramento/efd/importacao/progress`
- `ProcessarNotaEfdAction` foi removido — toda persistência de notas é do n8n
- Laravel expõe notas por IDs via `GET /app/importacao/efd/notas?ids[]=...` (`MonitoramentoController::notasPorIds`)
- `efd_importacoes.resumo_final` (jsonb, nullable) persiste o resumo enviado no payload final
- Cache key por bloco: `efd_notas_progress:{user_id}:{importacao_id}:{bloco}`
- `efd_notas.origem_arquivo`: `'fiscal'` para C/D (ICMS/IPI), `'contribuicoes'` para A (PIS/COFINS)

**Chaves descritivas dos blocos** (baseadas no output, não no registro SPED):

| Chave | Registros SPED | Armazenamento |
|-------|---------------|---------------|
| `participantes` | 0150 | `efd_participantes` |
| `catalogo` | 0200 | `efd_catalogo_itens` |
| `notas_servicos` | A100+A170 | `efd_notas` + `efd_notas_itens` |
| `notas_mercadorias` | C100+C170 | `efd_notas` + `efd_notas_itens` |
| `notas_transportes` | D100+D190 | `efd_notas` + `efd_notas_itens` |
| `apuracao_pis_cofins` | M100→M610 | `efd_apuracoes_contribuicoes` |
| `apuracao_icms` | E100→E520 | `efd_apuracoes_icms` |
| `retencoes_fonte` | F600 | `efd_retencoes_fonte` |

Retrocompatibilidade: `resumo_final` antigo usa chaves `A`, `C`, `D` — frontend aceita ambas.

Detalhes dos blocos M/E/F (tabelas, models, pipeline n8n): ver memory files `project_efd_bloco_m.md`, `project_efd_bloco_e.md`, `project_efd_bloco_f_pendencias.md`, `project_n8n_efd_blocos_pipeline.md`. Estrutura completa do payload `resumo_final`: `docs/efd-resumo-final-payload.md`.

### Nomenclatura `tipo_efd`

O campo `efd_importacoes.tipo_efd` usa **exclusivamente** os valores canônicos `EFD ICMS/IPI` e `EFD PIS/COFINS`. Não usar valores antigos (`EFD_FISCAL`, `EFD_CONTRIB`, `EFD Fiscal`, `EFD Contribuições`) — foram migrados. Backend e frontend devem comparar contra os canônicos.

## Integrações Externas

| Integração | Descrição |
|---|---|
| **n8n** | Webhooks para SPED, importações, consultas, atualização de participantes |
| **X-API-Token** | Header para autenticação de chamadas de entrada (n8n → Laravel) |
| **Cache Laravel** | Progresso intermediário para SSE |
| **InfoSimples** | Enriquecimento de participantes e MVP de consulta avulsa NF-e. CND Federal em implementação; CND Estadual/Municipal, CNDT/FGTS, sanções e CNJ planejados. Clearance avulso começa por `receita-federal/nfe`; ver `docs/backlog.md`, `docs/compliance-product-spec.md` e `docs/integracao-infosimples-nfe-clearance-fiscal.md` |

Variáveis de ambiente relevantes:
- `WEBHOOK_SPED_*`, `WEBHOOK_IMPORTACAO_*`, `WEBHOOK_CONSULTAS_*`
- `WEBHOOK_EFD_NOTAS_URL` — extração EFD Contribuições (PIS/COFINS)
- `WEBHOOK_EFD_FISCAL_NOTAS_URL` — extração EFD Fiscal (ICMS/IPI)
- `INFOSIMPLES_TOKEN`

## Produtos e Rotas

### Dashboard de Notas Fiscais

`/app/notas-fiscais/dashboard` — `DashboardNotasFiscaisController`. 6 abas (Visão Geral, CFOP, Participantes, Tributário, Alertas, Compliance) carregadas via JSON endpoint + ApexCharts client-side. Filtros globais: período, cliente, participante, tipo EFD, importação.

### Clearance de Documentos Fiscais (produto separado de Consultas)

Dois produtos independentes, cruzamento apenas no BI:

| | Consultas de CNPJs | Clearance de Notas |
|---|---|---|
| Foco | Participante (CNPJ) | Documento fiscal (NF-e, CT-e, NFS-e) |
| Views | `/app/consulta/*` | `/app/validacao`, `/app/validacao/notas`, `/app/validacao/buscar-nfe` |

**Clearance — controller:** `ValidacaoController`. Não existe `ClearanceController`; não reintroduzir rotas apontando para controller inexistente.

**Rotas:**
- `GET /app/validacao` — `app.clearance.index` (dashboard KPIs)
- `GET /app/validacao/notas` — `app.clearance.notas` (listagem com filtros, paginação, bulk select)
- `GET /app/validacao/notas/todos-ids` — `app.clearance.todos-ids` (cross-page select)
- `POST /app/validacao/notas/validar` — `app.clearance.validar`
- `POST /app/validacao/importacao/{id}/validar` — `app.clearance.validar-importacao`
- `POST /app/validacao/calcular-custo` — `app.clearance.calcular-custo`
- `GET /app/validacao/nota/{id}` — `app.clearance.nota`
- `GET /app/validacao/alertas` — `app.clearance.alertas`
- `GET /app/validacao/buscar-nfe` — busca avulsa frontend-ready de DF-e

**Views e JS principais:**
- `resources/views/autenticado/validacao/index.blade.php`
- `resources/views/autenticado/validacao/notas.blade.php` + `public/js/clearance-notas.js`
- `resources/views/autenticado/validacao/buscar-nfe.blade.php` + `public/js/clearance-buscar-nfe.js`
- `resources/views/autenticado/validacao/nota.blade.php`
- `resources/views/autenticado/validacao/alertas.blade.php`

**Status:** dashboard/listagem/detalhe usam dados locais de XML/importações e validação contábil local. A busca avulsa permite selecionar NF-e/CT-e/NFS-e e associar cliente, mas a execução real ainda deve ser ligada ao n8n + InfoSimples e persistir em `xml_notas`. Integração SEFAZ/InfoSimples ainda não é fonte de verdade em produção.

**Decisão MVP de fornecedor:** começar com InfoSimples `receita-federal/nfe` para consulta avulsa NF-e por chave. Não consultar SEFAZ estadual por UF no MVP. Se houver aderência, avaliar Focus NFe para escala/recebidas/MDe e manter InfoSimples como fallback ou consulta pontual.

**Persistência prevista para busca avulsa:** Laravel valida o input e `cliente_id` opcional; n8n consulta o provedor, normaliza, faz upsert em `xml_notas`, salva payload completo e associa ao cliente quando informado. Chave operacional de idempotência: `user_id + nfe_id`.

### Sidebar

```
PAINEL           Dashboard · Alertas
DOCUMENTOS       Notas Fiscais (Listagem · Dashboard · Catálogo) · Importação (XML · EFD · Histórico)
INTELIGÊNCIA     BI Fiscal · Resumo Fiscal · Clearance NF-e (Dashboard · Verificar Notas)
CONSULTAS        Consulta CNPJ (Nova · Histórico · Produtos) · Score Risco
CADASTROS        Empresa · Clientes · Participantes
```

## Testes

- Framework: **Pest**
- `tests/Feature/` — integração e HTTP
- `tests/Unit/` — services e models

## Referências

**Memory files (carregados automaticamente via `MEMORY.md`):**
- Blocos EFD: `project_efd_bloco_m.md`, `project_efd_bloco_e.md`, `project_efd_bloco_f_pendencias.md`, `project_efd_bloco_h_pendente.md`
- Pipeline n8n: `project_n8n_efd_blocos_pipeline.md`, `project_n8n_efd_workflow.md`
- Catálogo EFD: `project_catalogo_efd.md`
- Créditos, faixas e InfoSimples: `project_planos_estrategia.md`, `project_infosimples_integracao.md`
- Consultas NF: `project_consultas_notas_fiscais.md`

**Docs locais (fora do git):**
- `docs/design-system.md` — DANFE Modernizado (tabelas completas, badges, cores)
- `docs/efd-resumo-final-payload.md` — estrutura `resumo_final`, Code node n8n
- `docs/backlog.md` — pendências, roadmap, CND Estadual/Municipal, checkout de compra de créditos
