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
docker compose -f docker-compose.dev.yml up -d  # http://localhost:8080
composer dev                                     # Native: serve + queue + vite

# Test & Quality
composer test                    # or: php artisan test
./vendor/bin/pint                # PSR-12 formatting

# Database
php artisan migrate
php artisan migrate:fresh --seed

# Deploy (Swarm)
docker service update --image felipedaige/fiscaldock:X.X.X fiscaldock_app
docker service update --image felipedaige/fiscaldock:X.X.X fiscaldock_scheduler
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

**Routes:** `/app/monitoramento/sped`, `/app/monitoramento/xml`, `/app/monitoramento/avulso`

### XML Import (NF-e, NFS-e, CT-e)

**Models:** `ImportacaoXml`, `XmlChaveProcessada`, `NotaFiscal`

**Flow:** User uploads XMLs → Laravel sends ZIP to n8n → n8n extracts participantes + optional notas_fiscais → Progress via SSE

**Docs:** `docs/n8n-workflows.md` - Complete flow, SQL examples, field mapping

| Route | Description |
|-------|-------------|
| `POST /app/monitoramento/xml/importar` | Start import |
| `POST /api/monitoramento/xml/importacao/progress` | n8n sends progress |
| `GET /app/monitoramento/xml/progresso/stream` | SSE progress |

**Limits:** 50MB/file, 200MB total, 5000 XMLs max

### API Strategy (via n8n)

| API | Cost | Data |
|-----|------|------|
| Minha Receita | Free | CNPJ, Simples, QSA, CNAE |
| InfoSimples | R$0.26/call | CND, FGTS, SINTEGRA, CNDT, protestos |

**Plans:** Básico (0cr), Cadastral+ (3cr), Fiscal Federal (6cr), Fiscal Completo (12cr), Due Diligence (16cr), Completo (22cr)

## Webhooks

All URLs via `config('services.webhook.*')`, NO defaults in code.

| Operation | Env Variable |
|-----------|--------------|
| RAF Fiscal | `WEBHOOK_SPED_FISCAL_URL` |
| RAF Contribuições | `WEBHOOK_SPED_CONTRIBUICOES_URL` |
| Monitoramento SPED | `WEBHOOK_MONITORAMENTO_IMPORTACAO_*_URL` |
| Monitoramento XML | `WEBHOOK_MONITORAMENTO_IMPORTACAO_XML_URL` |
| Monitoramento Consulta | `WEBHOOK_MONITORAMENTO_CONSULTA_URL` |

## API Endpoints

**From n8n (X-API-Token header):**
- `POST /api/data/receive/raf/csvfile` - Receive CSV reports
- `POST /api/monitoramento/consulta/resultado` - Consultation results
- `POST /api/monitoramento/sped/importacao-txt/progress` - SPED progress
- `POST /api/monitoramento/xml/importacao/progress` - XML progress

**SSE:** `/app/monitoramento/progresso/stream?tab_id=xxx`, `/app/monitoramento/xml/progresso/stream?tab_id=xxx`

## Progress System

Cache key: `progresso:{user_id}:{tab_id}` (TTL 10min)

```json
{
  "user_id": 1, "tab_id": "uuid",
  "progresso": 45, "status": "processando",
  "mensagem": "Processando...",
  "dados": { "total_participantes": 35 }
}
```

**Status:** `iniciando` → `processando` → `concluido` | `erro`

**Docs:** `docs/n8n-workflows.md` - Progress flow, error codes, participantes

## Credit System

**Credits are INTEGERS only.** R$1.00 per credit.

```php
$this->creditService->hasEnough($user, $amount);
$this->creditService->deduct($user, $amount);
$this->creditService->refund($user, $amount);
```

## Services

| Service | Responsibility |
|---------|----------------|
| `SpedUploadService` | Sends SPED to n8n, manages credits |
| `CreditService` | Credit operations with transaction locking |
| `CsvParserService` | Parses CSV from n8n responses |
| `RegimeTributarioService` | Tax regime lookups |

## Frontend

**No frameworks** - Vanilla JS only.

- SPA router: `resources/js/spa.js` (intercepts `[data-link]`)
- Page scripts: `public/js/{page}.js` with `window.init{Page}()`
- SSE: `EventSource` with cache-based updates

## n8n Automation

Subscription execution runs 100% in n8n cron (hourly). Laravel has NO scheduler for subscriptions.

**Docs:** `docs/n8n-workflows.md` (SPED + XML), `docs/planos-e-apis.md` (Planos + InfoSimples)

## Environment

```env
DB_CONNECTION=pgsql
APP_KEY=base64:...
API_TOKEN=your-secure-token
WEBHOOK_SPED_*_URL=https://...
WEBHOOK_MONITORAMENTO_*_URL=https://...
```

## Participantes - Rastreabilidade

**`origem_tipo`:** `SPED_EFD_FISCAL`, `SPED_EFD_CONTRIB`, `NFE`, `NFSE`, `CTE`, `MANUAL`

**Campos XML:** `cep`, `municipio`, `telefone`, `crt` (1=Simples, 2=Excesso, 3=Normal)

**`origem_ref`:** `{"arquivo": "SPED_2024.txt", "importado_em": "..."}` ou `{"raf_relatorio_id": 123}`

## Tabela notas_fiscais

Armazena metadados de NF-e/NFS-e/CT-e importados.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `cliente_id` | FK nullable | Link para clientes (filtro rapido) |
| `chave_acesso` | string(44) | Chave única da nota |
| `tipo_nota` | smallint | 0=entrada, 1=saída |
| `finalidade` | smallint | 1=normal, 2=compl, 3=ajuste, 4=devolução |
| `emit/dest_participante_id` | FK | Link para participantes |
| `payload` | JSONB | JSON completo do XML (futuro-proof) |

**Docs:** `docs/n8n-workflows.md` - SQL examples, field mapping

## Troubleshooting

**404 em rotas API:** Imagem Docker desatualizada → `docker service update --image ...`

**Upload SPED não envia:** Verificar logs `grep -i "SpedUpload" storage/logs/laravel.log | tail -20`
