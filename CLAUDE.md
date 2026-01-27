# CLAUDE.md

## Project Overview

FiscalDock - Laravel 12 app for Brazilian tax compliance. Processes SPED files (EFD Contribuicoes/Fiscal), tax regime analysis, integrates with n8n for async processing.

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
1. User uploads SPED -> Laravel creates `RafConsultaPendente`, sends to n8n webhook
2. n8n parses file, queries APIs, creates CSV -> sends to `/api/data/receive/raf/csvfile`
3. Laravel stores in `RafRelatorioProcessado`, user confirms/cancels

**Models:** `RafConsultaPendente`, `RafRelatorioProcessado`, `RafParticipante`

### Monitoramento (CNPJ Monitoring)
Continuous tracking of CNPJ tax status via subscriptions.

**Models:** `Participante`, `MonitoramentoPlano`, `MonitoramentoAssinatura`, `MonitoramentoConsulta`

**Routes:**
- `/app/monitoramento/sped` - Import SPED files
- `/app/monitoramento/xml` - Import XML (NF-e/NFS-e/CT-e)
- `/app/monitoramento/avulso` - Single CNPJ query
- `/app/monitoramento/participantes` - List all participantes
- `/app/monitoramento/clientes` - Client monitoring (placeholder)

### XML Import (NF-e, NFS-e, CT-e)

**Models:** `ImportacaoXml`, `XmlChaveProcessada`, `NotaFiscal`

**Flow:** User uploads XMLs -> Laravel sends ZIP to n8n -> n8n extracts participantes + optional notas_fiscais -> Progress via SSE

| Route | Description |
|-------|-------------|
| `POST /app/monitoramento/xml/importar` | Start import |
| `POST /api/monitoramento/xml/importacao/progress` | n8n sends progress |
| `GET /app/monitoramento/xml/progresso/stream` | SSE progress |

**Limits:** 50MB/file, 200MB total, 5000 XMLs max

## Database - Tabelas Principais

### participantes
| Campo | Descricao |
|-------|-----------|
| `user_id` | Owner |
| `cnpj` | CNPJ (unique per user) |
| `razao_social`, `nome_fantasia` | Company names |
| `uf`, `cep`, `municipio`, `telefone` | Address data |
| `crt` | 1=Simples, 2=Excesso, 3=Normal |
| `cliente_id` | FK to clientes (optional) |
| `origem_tipo` | `SPED_EFD_FISCAL`, `SPED_EFD_CONTRIB`, `NFE`, `NFSE`, `CTE`, `MANUAL` |
| `origem_ref` | JSONB with source details |

### notas_fiscais
| Campo | Tipo | Descricao |
|-------|------|-----------|
| `cliente_id` | FK nullable | Link para clientes |
| `chave_acesso` | string(44) | Chave unica da nota |
| `tipo_nota` | smallint | 0=entrada, 1=saida |
| `finalidade` | smallint | 1=normal, 2=compl, 3=ajuste, 4=devolucao |
| `emit/dest_participante_id` | FK | Link para participantes |
| `payload` | JSONB | JSON completo do XML |

### importacoes_xml
Tracks XML import jobs with status, counts, and `participante_ids` array.

### monitoramento_*
- `monitoramento_planos` - Available plans
- `monitoramento_assinaturas` - User subscriptions
- `monitoramento_consultas` - Query history

### raf_*
- `raf_consultas_pendentes` - Pending RAF analyses
- `raf_relatorios_processados` - Completed reports

## Webhooks

All URLs via `config('services.webhook.*')`, NO defaults in code.

| Operation | Env Variable |
|-----------|--------------|
| RAF Fiscal | `WEBHOOK_SPED_FISCAL_URL` |
| RAF Contribuicoes | `WEBHOOK_SPED_CONTRIBUICOES_URL` |
| Monitoramento SPED | `WEBHOOK_MONITORAMENTO_IMPORTACAO_*_URL` |
| Monitoramento XML | `WEBHOOK_MONITORAMENTO_IMPORTACAO_XML_URL` |
| Monitoramento Consulta | `WEBHOOK_MONITORAMENTO_CONSULTA_URL` |

## API Endpoints

**From n8n (X-API-Token header):**
- `POST /api/data/receive/raf/csvfile` - Receive CSV reports
- `POST /api/monitoramento/consulta/resultado` - Consultation results
- `POST /api/monitoramento/sped/importacao-txt/progress` - SPED progress
- `POST /api/monitoramento/xml/importacao/progress` - XML progress

**SSE:**
- `/app/monitoramento/progresso/stream?tab_id=xxx` - SPED progress
- `/app/monitoramento/xml/progresso/stream?tab_id=xxx` - XML progress

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

**Status:** `iniciando` -> `processando` -> `concluido` | `erro`

---

## n8n Workflows

### Regra de Ouro: Templates JSON no n8n

| Tipo do valor | Formato no JSON | Exemplo |
|---------------|-----------------|---------|
| Numero | `{{ $json.campo }}` | `"progresso": {{ $json.progresso }}` |
| String | `"{{ $json.campo }}"` | `"tab_id": "{{ $json.tab_id }}"` |
| Boolean | `{{ $json.campo }}` | `"ativo": {{ $json.ativo }}` |
| Array | `{{ JSON.stringify($json.campo) }}` | `"ids": {{ JSON.stringify($json.ids) }}` |
| Object | `{{ JSON.stringify($json.campo) }}` | `"dados": {{ JSON.stringify($json.dados) }}` |
| Null | `null` | `"erro": null` |

### Workflow XML (NF-e/NFS-e/CT-e)

**Fluxo:**
```
WEBHOOK (ZIP) -> EXTRAIR -> LOOP XMLs -> INSERT participantes/notas -> UPDATE importacoes_xml -> PROGRESSO 100%
```

**Webhook campos recebidos:**
| Campo | Tipo | Descricao |
|-------|------|-----------|
| `user_id` | int | ID do usuario |
| `importacao_id` | int | ID em importacoes_xml |
| `tab_id` | string | UUID da aba (SSE) |
| `tipo_documento` | string | `NFE`, `NFSE` ou `CTE` |
| `cliente_id` | int/null | ID do cliente |
| `cliente_cnpj` | string/null | CNPJ para comparacao |
| `salvar_movimentacoes` | bool | Salvar em notas_fiscais? |
| `progress_url` | string | URL para progresso |
| `arquivo_url` | string | URL do ZIP |

**SQL participantes (UPSERT):**
```sql
INSERT INTO participantes (
    user_id, cnpj, razao_social, nome_fantasia, uf, cep,
    municipio, telefone, crt, cliente_id, origem_tipo, origem_ref,
    created_at, updated_at
) VALUES (...)
ON CONFLICT (user_id, cnpj) DO UPDATE SET
    razao_social = COALESCE(EXCLUDED.razao_social, participantes.razao_social),
    nome_fantasia = COALESCE(EXCLUDED.nome_fantasia, participantes.nome_fantasia),
    uf = COALESCE(EXCLUDED.uf, participantes.uf),
    updated_at = NOW()
RETURNING id, (xmax = 0) AS is_new;
```

**Mapeamento XML -> DB (participantes):**
| Campo DB | emit | dest |
|----------|------|------|
| `cnpj` | `emit.CNPJ` | `dest.CNPJ` |
| `razao_social` | `emit.xNome` | `dest.xNome` |
| `nome_fantasia` | `emit.xFant` | `dest.xFant` |
| `uf` | `emit.enderEmit.UF` | `dest.enderDest.UF` |
| `cep` | `emit.enderEmit.CEP` | `dest.enderDest.CEP` |
| `municipio` | `emit.enderEmit.xMun` | `dest.enderDest.xMun` |
| `telefone` | `emit.enderEmit.fone` | `dest.enderDest.fone` |
| `crt` | `emit.CRT` | NULL |

**Mapeamento XML -> DB (notas_fiscais):**
| Campo DB | Origem JSON |
|----------|-------------|
| `chave_acesso` | `protNFe.infProt.chNFe` |
| `numero_nota` | `ide.nNF` |
| `serie` | `ide.serie` |
| `data_emissao` | `ide.dhEmi` |
| `valor_total` | `total.ICMSTot.vNF` |
| `tipo_nota` | `ide.tpNF` (0=entrada, 1=saida) |
| `finalidade` | `ide.finNFe` (1=normal, 4=devolucao) |
| `payload` | TODO O JSON |

### Workflow SPED (EFD Fiscal/Contribuicoes)

**Fluxo:**
```
WEBHOOK (TXT) -> PARSE SPED -> EXTRAIR CNPJs -> INSERT participantes -> PROGRESSO 100%
```

**origem_tipo valores:**
- `SPED_EFD_FISCAL` - Arquivo SPED EFD Fiscal
- `SPED_EFD_CONTRIB` - Arquivo SPED EFD Contribuicoes

**Exemplo origem_ref:**
```json
{"arquivo": "SPED_EFD_2024.txt", "importado_em": "2026-01-18T10:30:00Z"}
```

### API de Progresso (Compartilhada)

**Endpoints:**
| Workflow | Endpoint |
|----------|----------|
| XML | `POST /api/monitoramento/xml/importacao/progress` |
| SPED | `POST /api/monitoramento/sped/importacao-txt/progress` |

**Header:** `X-API-Token: {API_TOKEN}`

**Payload campos:**
| Campo | Tipo | Obrigatorio | Descricao |
|-------|------|-------------|-----------|
| `user_id` | int | Sim | ID do usuario |
| `tab_id` | string(36) | Sim | UUID da aba |
| `progresso` | int(0-100) | Sim | Percentual |
| `mensagem` | string(255) | Nao | Texto UI |
| `status` | enum | Sim | `iniciando`, `processando`, `concluido`, `erro` |
| `importacao_id` | int | Nao | ID do registro |
| `error_code` | string(50) | Nao | Codigo erro |
| `error_message` | string(500) | Nao | Descricao erro |
| `dados` | object | Nao | Dados extras |

**Codigos de Erro:**
| error_code | Descricao |
|------------|-----------|
| `PARSE_ERROR` | XML/SPED mal formado |
| `INVALID_XML` | Tipo desconhecido |
| `INVALID_SPED` | Arquivo invalido |
| `DB_ERROR` | Erro ao salvar |
| `INFOSIMPLES_TIMEOUT` | Timeout API |
| `INFOSIMPLES_ERROR` | Erro API |
| `NO_PARTICIPANTS` | Nenhum participante |
| `UNKNOWN_ERROR` | Erro desconhecido |

---

## Planos e APIs

### Sistema de Creditos

- 1 credito = R$ 0,26 (custo InfoSimples)
- Consultas Minha Receita = GRATIS
- Creditos sao INTEGERS no sistema

```php
$this->creditService->hasEnough($user, $amount);
$this->creditService->deduct($user, $amount);
$this->creditService->refund($user, $amount);
```

### Consultas Gratuitas (Minha Receita)

| Consulta | Dados Retornados |
|----------|------------------|
| Situacao Cadastral | Ativa/baixada/inapta, data, motivo |
| Dados Cadastrais | Razao social, nome fantasia, capital social |
| Endereco | Logradouro, municipio, UF, CEP |
| CNAEs | Principal e secundarios |
| QSA | Socios com CPF/CNPJ, qualificacao |
| Simples Nacional | Optante sim/nao, data opcao |
| MEI | E MEI sim/nao |

### Consultas Pagas (InfoSimples)

| Consulta | Endpoint | Custo |
|----------|----------|-------|
| SINTEGRA | `/api/v2/consultas/sintegra/unificada` | 1 cr |
| TCU Consolidada | `/api/v2/consultas/tcu/consulta-consolidada-pj` | 1 cr |
| CND Federal | `/api/v2/consultas/receita-federal/pgfn-nova` | 1 cr |
| CRF (FGTS) | `/api/v2/consultas/caixa/regularidade` | 1 cr |
| CND Estadual | `/api/v2/consultas/sefaz/certidao-debitos` | 1 cr |
| CNDT | `/api/v2/consultas/tst/cndt` | 1 cr |
| Lista Devedores PGFN | `/api/v2/consultas/pgfn/lista-devedores` | 1 cr |
| Trabalho Escravo | `/api/v2/consultas/sit/trabalho-escravo` | 1 cr |
| IBAMA Autuacoes | `/api/v2/consultas/ibama/autuacoes` | 1 cr |
| Protestos | `/api/v2/consultas/ieptb/protestos` | 1 cr |
| Processos CNJ | `/api/v2/consultas/cnj/seeu-processos` | 1 cr |

**Base URL:** `https://api.infosimples.com`

### Planos de Monitoramento

| Plano | Creditos | Inclui |
|-------|----------|--------|
| Basico | 0 | Dados gratuitos (Minha Receita) |
| Cadastral+ | 3 | Basico + SINTEGRA + TCU |
| Fiscal Federal | 6 | Cadastral+ + CND Federal + FGTS |
| Fiscal Completo | 12 | Fiscal Federal + CND Estadual + CNDT |
| Due Diligence | 16 | Fiscal Completo + Lista Devedores |
| ESG | 6 | Trabalho Escravo + IBAMA |
| Completo | 22 | Tudo |

**Frequencias:** Diaria, Semanal, Quinzenal, Mensal

**Exemplo:** 30 CNPJs, perfil Fiscal, semanal = 360 cr/ciclo = 1.440 cr/mes

### Alertas do Monitoramento

| Evento | Criticidade |
|--------|-------------|
| Empresa baixada/inapta | Alta |
| IE baixada/suspensa | Alta |
| Entrou CEIS/CNEP | Alta |
| Lista trabalho escravo | Alta |
| CND venceu/positiva | Media |
| CRF irregular | Media |
| Desenquadrou Simples | Media |
| Nova autuacao IBAMA | Baixa |

---

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

**Code Node reference:** `docs/Processar XML/n8n-code-node-resumo.js`

## Environment

```env
DB_CONNECTION=pgsql
APP_KEY=base64:...
API_TOKEN=your-secure-token
WEBHOOK_SPED_*_URL=https://...
WEBHOOK_MONITORAMENTO_*_URL=https://...
```

## Troubleshooting

**404 em rotas API:** Imagem Docker desatualizada -> `docker service update --image ...`

**Upload SPED nao envia:** Verificar logs `grep -i "SpedUpload" storage/logs/laravel.log | tail -20`

**Erro 404 no endpoint de progresso:**
```bash
docker compose -f docker-compose.dev.yml pull
docker compose -f docker-compose.dev.yml up -d
docker compose exec app php artisan route:clear
```

**Erro "JSON parameter needs to be valid JSON":**
Use `JSON.stringify()` em arrays no n8n:
```json
"participante_ids": {{ JSON.stringify($json.ids || []) }}
```

## Arquivos Relacionados

| Arquivo | Funcao |
|---------|--------|
| `docs/Processar XML/n8n-code-node-resumo.js` | Code Node n8n |
| `app/Http/Controllers/Api/DataReceiverController.php` | Recebe progresso |
| `app/Http/Controllers/Dashboard/MonitoramentoController.php` | SSE SPED |
| `app/Http/Controllers/Dashboard/XmlImportacaoController.php` | SSE XML |
| `routes/api.php` | Rotas API |
