# CLAUDE.md

## Project Overview

FiscalDock - Laravel 12 app for Brazilian tax compliance. Processes SPED files (EFD Contribuições/Fiscal), tax regime analysis, integrates with n8n for async processing.

**Stack:** Laravel 12, PHP 8.2+, PostgreSQL, Blade, Tailwind 4.0, Vite, Pest, Docker/Traefik

## Architecture: Laravel = Thin Layer, n8n = Heavy Work

**CRITICAL:** Laravel does SELECT only. n8n does all INSERT/UPDATE/DELETE via direct PostgreSQL.

| Laravel (Lightweight) | n8n (Heavy Processing) |
|-----------------------|------------------------|
| Auth, sessions | SPED parsing |
| Request coordination | Government API calls |
| Database reads (SELECT) | CND checks |
| Credit management | Report generation |
| Blade presentation | **ALL DB writes** |
| Trigger n8n webhooks | External integrations |

**Exceptions (Laravel writes):** `RafConsultaPendente`, `ImportacaoParticipante`, user sessions

## Commands

```bash
# Dev
docker compose -f docker-compose.dev.yml up  # http://localhost:8080
composer dev                                  # Native: serve + queue + vite

# Test & Quality
composer test                                 # or: php artisan test
./vendor/bin/pint                             # PSR-12 formatting

# Database
php artisan migrate
php artisan migrate:fresh --seed

# Deploy
./deploy.sh
```

## Core Modules

### RAF (Regime and Fiscal Analysis)
1. User uploads SPED → Laravel creates `RafConsultaPendente`, sends to n8n webhook
2. n8n parses file, queries APIs, creates CSV → sends to `/api/data/receive/raf/csvfile`
3. Laravel stores in `RafRelatorioProcessado`, user confirms/cancels

**Models:** `RafConsultaPendente`, `RafRelatorioProcessado`, `RafParticipante`

### Monitoramento (CNPJ Monitoring)
Continuous tracking of CNPJ tax status via subscriptions.

**Models:** `Participante`, `MonitoramentoPlano`, `MonitoramentoAssinatura`, `MonitoramentoConsulta`

**Flow:** Import CNPJs from SPED or manually → Create subscription → n8n cron executes queries → Results stored

**Routes:**
- `/app/monitoramento/sped` - Import from SPED
- `/app/monitoramento/avulso` - Single CNPJ query
- `/app/monitoramento/participante/{id}` - View details

### API Strategy (via n8n)

| API | Cost | Data |
|-----|------|------|
| Minha Receita | Free | CNPJ, Simples, QSA, CNAE |
| InfoSimples | R$0.26/call | CND, FGTS, SINTEGRA, CNDT, protestos |

**Plans:** Básico (0cr), Cadastral+ (3cr), Fiscal Federal (6cr), Fiscal Completo (12cr), Due Diligence (18cr)

## Webhooks

All URLs via `config('services.webhook.*')`, NO defaults in code.

| Operation | Env Variable |
|-----------|--------------|
| RAF Gratuito (Fiscal) | `WEBHOOK_SPED_FISCAL_URL` |
| RAF Gratuito (Contribuições) | `WEBHOOK_SPED_CONTRIBUICOES_URL` |
| RAF Completa (Fiscal) | `WEBHOOK_SPED_FISCAL_COMPLETA_URL` |
| RAF Completa (Contribuições) | `WEBHOOK_SPED_CONTRIBUICOES_COMPLETA_URL` |
| Monitoramento Import | `WEBHOOK_MONITORAMENTO_IMPORTACAO_TXT_URL` |
| Monitoramento Consulta | `WEBHOOK_MONITORAMENTO_CONSULTA_URL` |

```php
$url = config('services.webhook.sped_fiscal_url');  // Never use env() directly
```

## API Endpoints

**From n8n (X-API-Token header):**
- `POST /api/data/receive/raf/csvfile` - Receive CSV reports
- `POST /api/monitoramento/consulta/resultado` - Consultation results
- `POST /api/monitoramento/sped/importacao-txt/progress` - Import progress

**SSE (real-time):**
- `GET /app/monitoramento/progresso/stream?tab_id=xxx`
- `GET /api/data/notifications/stream`

## Progress System (user_id + tab_id)

Cache key: `progresso:{user_id}:{tab_id}`

**Payload n8n → Laravel:**
```json
{"user_id": 1, "tab_id": "uuid", "progresso": 45, "mensagem": "...", "status": "processando"}
```
Status: `iniciando`, `processando`, `concluido`, `erro`

## Credit System

**Credits are INTEGERS only.** R$1.00 per credit.

```php
$this->creditService->hasEnough($user, $amount);
$this->creditService->deduct($user, $amount);
$this->creditService->refund($user, $amount);
```

## Services

- `SpedUploadService` - Sends SPED to n8n, manages credits
- `CreditService` - Credit operations with transaction locking
- `CsvParserService` - Parses CSV from n8n responses
- `RegimeTributarioService` - Tax regime lookups

## Frontend

**No frameworks** - Vanilla JS only.

- SPA router: `resources/js/spa.js` (intercepts `[data-link]`)
- Page scripts: `public/js/{page}.js` with `window.init{Page}()`
- Cleanup: `window._cleanupFunctions.push(() => {...})`
- SSE: `EventSource` with cache-based updates

## n8n Automation

Subscription execution runs 100% in n8n cron (hourly). Laravel has NO scheduler for subscriptions.

n8n: checks `proxima_execucao_em <= NOW()`, deducts credits, queries APIs, stores results, schedules next execution.

**Docs:** `docs/n8n-inicio-rapido.md` (start here), `docs/n8n-workflow-visual.md`

## Environment

**Required:**
```env
DB_CONNECTION=pgsql
APP_KEY=base64:...
API_TOKEN=your-secure-token
WEBHOOK_SPED_*_URL=https://...
```

## File Upload

`POST /app/sped/upload` - Multipart form-data (not base64)

Fields: `file`, `tipo_efd`, `cliente_id`, `tab_id`

Controller: `SpedUploadController` - Proxies directly to n8n webhook

**Payload sent to n8n:** `user_id`, `filename`, `tab_id`, `tipo_efd`, `cliente_id`, `progress_url`

## Participantes - Rastreabilidade

**`origem_tipo`** (obrigatório): `SPED_EFD_FISCAL`, `SPED_EFD_CONTRIB`, `NFE`, `NFSE`, `MANUAL`

**`origem_ref`** (JSON opcional):
```json
// Importação SPED
{"arquivo": "SPED_2024.txt", "importado_em": "2026-01-18T10:30:00Z"}

// Via RAF
{"raf_relatorio_id": 123}

// Manual
null
```

**Docs:** `docs/n8n.md` (seção "Tabela Participantes")
