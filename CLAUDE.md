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

## Docker Development

**Image version:** `docker-compose.dev.yml` uses `felipedaige/fiscaldock:X.X.X`. Keep in sync with latest release.

**Iniciar ambiente de desenvolvimento:**
```bash
docker compose -f docker-compose.dev.yml up -d
# Acesso: http://localhost:8080
```

**Troubleshooting 404 on API routes (dev):**
1. Check image version matches latest release
2. Clear caches: `docker compose -f docker-compose.dev.yml exec app php artisan route:clear && php artisan config:clear && php artisan cache:clear`
3. Restart containers: `docker compose -f docker-compose.dev.yml up -d`

**Testing n8n integration locally:**
```bash
# Verify route exists
docker compose -f docker-compose.dev.yml exec app php artisan route:list --path=api/monitoramento

# Test endpoint (should return 401, not 404)
curl -X POST "http://localhost:8080/api/monitoramento/sped/importacao-txt/progress" \
  -H "Content-Type: application/json" \
  -H "X-API-Token: test" \
  -d '{"user_id": 1, "tab_id": "test", "progresso": 50, "status": "processando"}'
```

## Docker Production (Swarm)

**Arquitetura:** Produção usa Docker Swarm com rede overlay `network_public` e Traefik como reverse proxy.

**Arquivos:**
- `docker-compose.yml` - Configuração de produção (Swarm)
- `docker-compose.dev.yml` - Configuração de desenvolvimento (local)

**Verificar serviços rodando:**
```bash
docker service ls | grep fiscaldock
```

**Deploy para produção (atualizar imagem):**
```bash
# 1. Baixar nova imagem
docker pull felipedaige/fiscaldock:X.X.X

# 2. Atualizar serviços Swarm
docker service update --image felipedaige/fiscaldock:X.X.X fiscaldock_app
docker service update --image felipedaige/fiscaldock:X.X.X fiscaldock_scheduler

# 3. Atualizar docker-compose.yml com a nova versão
```

**Troubleshooting 404 em produção:**

Se n8n retorna 404 para rotas da API:
1. Verificar se a rota existe no código local
2. Testar localmente: `curl -X POST "http://localhost:8080/api/..." -H "X-API-Token: test"`
3. Testar em produção: `curl -X POST "https://fiscaldock.com.br/api/..." -H "X-API-Token: test"`
4. Se local funciona mas produção não → **imagem desatualizada**
5. Fazer deploy da nova versão via `docker service update`

**IMPORTANTE:** O script `./deploy.sh` usa `docker compose` (não Swarm). Para produção, usar `docker service update`.

**Rede:** A rede `network_public` é overlay do Swarm e não permite `docker network connect` manual. Containers de dev e prod não se comunicam diretamente.

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
| Monitoramento Import (Contribuições) | `WEBHOOK_MONITORAMENTO_IMPORTACAO_CONTRIBUICOES_URL` |
| Monitoramento Import (Fiscal) | `WEBHOOK_MONITORAMENTO_IMPORTACAO_FISCAL_URL` |
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

**Fluxo padrão de progresso:**

| Etapa | progresso | status | mensagem | Resposta esperada |
|-------|-----------|--------|----------|-------------------|
| 1. Workflow iniciado | 0 | `iniciando` | "Iniciando processamento..." | 200 |
| 2. Empresa identificada | 5 | `processando` | "NOME DA EMPRESA" | 200 |
| 3. Processando | 10-90 | `processando` | "Processando participantes..." | 200 |
| 4. Concluído | 100 | `concluido` | "Importação concluída" | 200 |
| 5. Erro (se houver) | qualquer | `erro` | "Descrição do erro" | 200 |

**IMPORTANTE:** O campo `progresso` deve ser sempre um **inteiro válido** (0-100). Nunca enviar `"NaN"` ou strings.

**Payload n8n → Laravel:**
```json
{
  "user_id": 1,
  "tab_id": "uuid",
  "progresso": 45,
  "mensagem": "Processando participantes...",
  "status": "processando",
  "dados": {
    "nome_empresa": "EMPRESA XYZ LTDA",
    "total_participantes": 150,
    "total_cpfs": 30,
    "total_cnpjs": 120,
    "total_duplicados": 15,
    "total_a_analisar": 105,
    "importacao_id": 123,
    "participante_ids": [1, 2, 3, 4, 5]
  }
}
```

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `user_id` | int | ID do usuário |
| `tab_id` | string | UUID da aba do browser |
| `progresso` | int | 0-100 (obrigatório, deve ser inteiro) |
| `mensagem` | string | Texto exibido na UI |
| `status` | enum | `iniciando`, `processando`, `concluido`, `erro` |
| `dados` | object | **Flexível** - qualquer estrutura JSON |

**Campo `dados`:** Passa direto do n8n → cache → SSE → frontend. Laravel não valida estrutura interna.

**Campo `participante_ids`:** Array de IDs dos participantes criados. Quando `status=concluido`, n8n deve:
1. Salvar o array na tabela `importacoes_participantes.participante_ids`
2. Incluir no payload de progresso para exibição imediata no frontend

```sql
-- Ao finalizar importação, salvar IDs dos participantes criados
UPDATE importacoes_participantes
SET participante_ids = '[1, 2, 3, 4, 5]', status = 'concluido'
WHERE id = 123;
```

**Frontend (sped.blade.php):**
- Stats cards aparecem imediatamente ao iniciar importação (com zeros)
- Valores atualizam em tempo real conforme dados chegam via SSE
- Em caso de erro/timeout: stats são ocultados, seção de erro aparece
- Em nova importação: stats resetam para zero mas permanecem visíveis

**Resposta do Laravel:**
```json
// Sucesso (200)
{"success": true}

// Erro de validação (422) - ex: progresso inválido
{"success": false, "errors": {"progresso": ["The progresso field must be an integer."]}}
```

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

## Troubleshooting: Upload SPED Monitoramento não envia para n8n

**Problema:** Ao clicar no botão "Importar" em `/app/monitoramento/sped`, às vezes o arquivo não é enviado para o n8n.

**Sintomas possíveis:**
- Botão fica em "Enviando..." e depois volta ao normal sem progresso
- Toast de erro aparece
- Nada acontece (silencioso)
- Toast "Aguarde a importação em andamento terminar" aparece

### Diagnóstico

**1. Verificar logs do Laravel:**
```bash
# Ver últimos logs de upload
grep -i "SpedUpload" storage/logs/laravel.log | tail -20

# Logs esperados (sucesso):
# SpedUpload: iniciando envio para n8n {...}
# SpedUpload: arquivo enviado com sucesso {...}

# Logs de erro:
# SpedUpload: webhook não configurado
# SpedUpload: erro na resposta do n8n
# SpedUpload: exceção ao enviar
```

**2. Verificar console do browser (F12):**
```
[Monitoramento SPED] Arquivo enviado com tab_id: xxx  // Sucesso
[Monitoramento SPED] Erro ao enviar arquivo: xxx      // Falha
```

**3. Verificar se webhook está configurado:**
```bash
grep "WEBHOOK_MONITORAMENTO_IMPORTACAO" .env
# Deve mostrar URLs para CONTRIBUICOES e FISCAL
```

### Causas Conhecidas

| Sintoma | Causa | Solução |
|---------|-------|---------|
| Toast "Aguarde importação em andamento" | Flag `importacaoEmAndamento` travada | Recarregar página (F5) |
| Log "webhook não configurado" | Variável `.env` faltando | Adicionar `WEBHOOK_MONITORAMENTO_IMPORTACAO_*_URL` |
| Log "erro na resposta do n8n" | n8n offline ou webhook errado | Verificar n8n e URL do webhook |
| Log "exceção ao enviar" + timeout | n8n lento ou rede instável | Verificar conectividade |
| Nenhum log de SpedUpload | Request não chegou ao Laravel | Verificar network tab do browser |

### Arquivos Relevantes

- `app/Http/Controllers/SpedUploadController.php` - Controller que envia para n8n
- `resources/views/autenticado/monitoramento/sped.blade.php` - Frontend com JS
- `config/services.php` - Configuração dos webhooks

### Para Reportar ao Claude

Se o problema persistir, forneça:
1. **Logs do Laravel:** `grep -i "SpedUpload" storage/logs/laravel.log | tail -30`
2. **Console do browser:** Copiar erros do F12 > Console
3. **Network tab:** Status da request para `/app/sped/upload`
4. **Tipo de SPED:** EFD Fiscal ou EFD Contribuições
5. **Comportamento:** O que aconteceu (toast, erro, silêncio)

### Histórico de Correções

**2026-01-21:** Flag `importacaoEmAndamento` não era resetada nos botões "Nova Importação" e "Tentar Novamente", causando bloqueio silencioso de novas importações. Corrigido em `sped.blade.php`.
