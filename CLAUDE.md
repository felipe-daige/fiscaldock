# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

FiscalDock is a Laravel 12 application providing tax compliance and analysis services for Brazilian businesses. The platform processes SPED files (EFD Contribuições and EFD Fiscal), performs tax regime analysis, and integrates with external n8n workflows for asynchronous report generation.

**Tech Stack:**
- Backend: Laravel 12 (PHP 8.2+), PostgreSQL
- Frontend: Blade templates, Tailwind CSS 4.0, Vite
- Testing: Pest (PHP testing framework)
- Infrastructure: Docker, Docker Compose with Traefik reverse proxy
- Queue: Redis (optional, sync by default)
- External Integration: n8n webhooks for SPED processing

## Architectural Principles

**CRITICAL: Laravel is a Thin Presentation Layer**

This application follows a strict architectural division:

- **Laravel's Role (Lightweight):**
  - User authentication and session management
  - Request coordination and API endpoints
  - Database reads and simple writes
  - Credit management and business rules
  - Data presentation via Blade templates
  - Triggering n8n workflows via webhooks

- **n8n's Role (Heavy Processing):**
  - SPED file parsing and analysis
  - Tax regime consultations with government APIs
  - CND (Certidão Negativa de Débitos) checks
  - Monthly system data updates
  - All time-consuming processing tasks
  - External API integrations

**When Building New Features:**
- DO NOT implement heavy processing in Laravel
- DO NOT add complex business logic that should be in n8n
- DO coordinate with n8n for any data-intensive operations
- DO keep Laravel focused on user requests and data display
- DO use webhooks to trigger n8n workflows
- DO store results from n8n in the database for Laravel to display

Laravel is the platform where users order data; n8n is the engine that generates it.

## Development Commands

### Local Development (Docker)
```bash
# Start development environment
docker compose -f docker-compose.dev.yml up

# Start with queue worker profile
docker compose -f docker-compose.dev.yml --profile worker up

# Access application
# http://localhost:8080
```

### Native Development (without Docker)
```bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate
php artisan migrate

# Run development servers (concurrent)
composer dev
# This runs: php artisan serve, php artisan queue:listen, npm run dev

# Run individual services
php artisan serve
php artisan queue:listen --tries=1
npm run dev
```

### Testing
```bash
# Run all tests
composer test
# or
php artisan test

# Run specific test suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Run single test file
php artisan test tests/Feature/ExampleTest.php
```

### Code Quality
```bash
# Format code with Laravel Pint
./vendor/bin/pint

# Or run specific files
./vendor/bin/pint app/Services
```

### Database
```bash
# Run migrations
php artisan migrate

# Fresh migration with seed
php artisan migrate:fresh --seed

# Rollback
php artisan migrate:rollback
```

### Build Assets
```bash
# Build for production
npm run build

# Development watch mode
npm run dev
```

## Architecture

### Core Business Flow: RAF (Regime and Fiscal Analysis)

The RAF system demonstrates the Laravel-n8n architecture pattern:

1. **Upload Phase (Laravel - Coordination):**
   - User uploads SPED file via `/app/raf` (authenticated) or `/solucoes/raf` (public)
   - `DashboardController::uploadSped()` or `LandingPageController::uploadSpedPublic()`
   - Delegates to `SpedUploadService::uploadAndProcess()`
   - **Laravel sends file to n8n webhook** (different URLs for gratuito vs completa)
   - Creates `RafConsultaPendente` record with status tracking
   - Deducts credits from user (via `CreditService`) for authenticated requests
   - **Laravel's job ends here - it just coordinated the request**

2. **Processing Phase (n8n - Heavy Work):**
   - **n8n parses SPED file** (extracts participants, dates, company info)
   - **n8n queries government APIs** for tax regime and CND status
   - **n8n performs data analysis** and generates statistics
   - **n8n creates CSV report** with complete results
   - n8n sends results back to Laravel via `/api/data/receive/raf/csvfile` (base64 CSV)
   - `DataReceiverController::receiveCsv()` saves to `RafRelatorioProcessado` (just stores data)
   - SSE notifications sent to frontend via `/api/data/notifications/stream`

3. **Confirmation Phase (Laravel - Presentation):**
   - User reviews report at `/app/raf/historico`
   - `RafController::confirmar()` or `RafController::cancelar()`
   - Credits refunded on cancellation via `CreditService::refund()`
   - **Laravel only manages UI and credits - no data processing**

### Service Layer Pattern

Key services in `app/Services/` (coordination layer, not heavy processing):

- **SpedUploadService**: Coordinates SPED file uploads to n8n webhooks, manages credit reservation
- **CreditService**: User credit management with transaction locking
- **CsvParserService**: Parses CSV data received from n8n responses
- **RegimeTributarioService**: Tax regime lookups and caching (simple DB queries)
- **DashboardDataService**: Aggregates dashboard statistics (DB reads only)
- **PrivCpfDataService**: CPF-related data formatting (receives processed data from n8n)

**Note:** Services orchestrate workflows and manage data, but DO NOT perform heavy processing. Complex operations are delegated to n8n.

### Data Models

**RAF Workflow Models:**
- `RafConsultaPendente`: Tracks pending SPED analysis requests (pre-processing)
- `RafRelatorioProcessado`: Stores completed reports with statistics (post-processing)
- `RafParticipante`: Individual participant/supplier records from RAF analysis (detailed data per CNPJ)

**Client Management:**
- `Cliente`: Client/company records
- `ClienteEndereco`: Client addresses
- `ClienteFuncionario`: Client employees
- `ClienteSolicitacao`: Service requests

**CPF Analysis (PrivCpf):**
- `PrivCpfCadastro`: CPF registration data
- `PrivCpfOperacao`: CPF operation records
- `PrivCpfRelacionamento`: CPF relationships

### Authentication & API

Two authentication methods:
1. **Session-based** (web routes): Standard Laravel auth for dashboard
2. **API Token** (X-API-Token header): For n8n webhook callbacks

API routes (`routes/api.php`):
- `/api/data/receive`: Receives processed data from n8n
- `/api/data/receive/raf/csvfile`: Receives CSV reports (base64 encoded)
- `/api/data/receive/raf/participantes`: Receives RAF participants from n8n (batch insert)
- `/api/data/csv/{id}`: Downloads CSV by report ID
- `/api/raf/confirm`: Confirms credit usage
- `/api/data/error`: Receives error notifications from n8n

### Frontend Structure

**View Organization** (`resources/views/`):
- `landing/`: Public marketing pages
- `autenticado/`: Authenticated dashboard pages
- `auth/`: Login/registration forms
- `layouts/`: Master layout templates

**JavaScript Architecture:**
- **Custom SPA Router** (`resources/js/spa.js`): Intercepts navigation, fetches pages via AJAX, dynamically loads page-specific JS
- **Page-Specific Scripts** (`public/js/`): Modular JS files per page (layout.js, toast.js, importacao_xml.js, raf.js, etc.)
- **Bootstrap** (`resources/js/bootstrap.js`): Global setup with Axios interceptors and fetch API wrapping
- **Inline Scripts**: Blade templates contain inline JavaScript for page-specific interactivity

**Interactivity Patterns:**
- Vanilla JavaScript DOM manipulation and event handling
- FormData API for file uploads with drag-and-drop
- EventSource API for SSE real-time notifications
- Custom toast notification system (`toast.js`)
- jQuery masks for input formatting (CNPJ, phone)
- CSRF token via meta tag

**No frontend frameworks** - Pure vanilla JavaScript throughout.

### Docker Architecture

**Production (docker-compose.yml):**
- `app`: Main web service (Nginx + PHP-FPM)
- `scheduler`: Laravel scheduler (`php artisan schedule:work`)
- `worker`: Queue worker (optional, enabled via `--profile worker`)
- Uses Traefik for SSL/routing

**Development (docker-compose.dev.yml):**
- Mounts source code as volumes for live reload
- PostgreSQL accessed via extra_hosts (172.19.0.1)
- Port 8080 exposed directly
- OPcache disabled for development
- Worker profile optional

### Environment Configuration

Critical `.env` variables:
- `DB_CONNECTION=pgsql` (PostgreSQL required)
- `QUEUE_CONNECTION=sync` (default) or `redis` (with worker)
- `CACHE_STORE=database` (default) or `redis`
- `SESSION_DRIVER=database`
- `TRUSTED_PROXIES=*` (for Traefik in production)
- `APP_KEY`: Must be generated with `php artisan key:generate`

**n8n Webhook URLs (required for processing):**

All webhook URLs are configured via `.env` and accessed via `config('services.webhook.*')`:

```env
# RAF - Consulta Gratuita (apenas regime tributário)
WEBHOOK_SPED_CONTRIBUICOES_URL=https://your-n8n.example.com/webhook/...
WEBHOOK_SPED_FISCAL_URL=https://your-n8n.example.com/webhook/...

# RAF - Consulta Completa (CND + regime tributário)
WEBHOOK_SPED_CONTRIBUICOES_COMPLETA_URL=https://your-n8n.example.com/webhook/...
WEBHOOK_SPED_FISCAL_COMPLETA_URL=https://your-n8n.example.com/webhook/...

# Monitoramento - Importação de arquivo .txt
WEBHOOK_MONITORAMENTO_IMPORTACAO_TXT_URL=https://your-n8n.example.com/webhook/...

# Credenciais (se necessário)
WEBHOOK_SPED_USERNAME=
WEBHOOK_SPED_PASSWORD=

# Token para APIs internas (n8n -> Laravel)
API_TOKEN=your-secure-token
```

**Security Note:** Webhook URLs have NO default values in `config/services.php`. This prevents accidental exposure of internal URLs if the repository becomes public. All URLs MUST be configured in `.env`.

Without these webhooks, Laravel cannot process SPED files (it only coordinates, doesn't process).

### Database Schema Notes

- Uses PostgreSQL with migrations in `database/migrations/`
- `users.credits` (decimal): Credit balance tracking
- RAF tables track full lifecycle: pending → processed → confirmed/cancelled
- Date fields use Carbon/datetime casting for timezone handling

### RAF Participantes (Detailed Supplier Data)

The `raf_participantes` table stores individual participant/supplier records from RAF SPED analysis. While `RafRelatorioProcessado` stores aggregated statistics, `RafParticipante` stores the detailed data for each CNPJ.

**Table Structure:**
```
raf_participantes
├── raf_relatorio_processado_id (FK) - Links to parent report
├── user_id (FK) - For direct queries without JOIN
├── cliente_id (FK, nullable) - Optional client association
├── tipo_efd - 'EFD Fiscal' or 'EFD Contribuições'
├── modalidade - 'gratuito' or 'completa'
├── consultante_cnpj - CNPJ of company that owns the SPED
├── cnpj - Participant/supplier CNPJ
├── razao_social, situacao_cadastral, regime_tributario
├── cnd_* fields (nullable) - CND data (only for 'completa' mode)
└── data_inicio, data_final - Analysis period
```

**Relationship with Monitoramento:**
- `raf_participantes`: Historical data per report (same CNPJ can appear multiple times)
- `participantes` (Monitoramento): Active monitoring list (unique CNPJ per user)
- These tables are **independent** - RAF is history, Monitoramento is active tracking

**n8n Integration:**
```
POST /api/data/receive/raf/participantes
{
  "raf_relatorio_processado_id": 123,
  "participantes": [
    {
      "tipo_efd": "EFD Fiscal",
      "modalidade": "completa",
      "consultante_cnpj": "12345678000100",
      "cnpj": "98765432000199",
      "razao_social": "Fornecedor XYZ",
      "situacao_cadastral": "ativa",
      "regime_tributario": "Simples",
      "cnd_situacao": "Regular",
      ...
    }
  ]
}
```

### Monitoramento Module

The Monitoramento (Monitoring) module enables continuous tracking of CNPJ tax and fiscal status.

**Data Models:**
- `Participante`: CNPJs being monitored (imported from SPED or added manually)
- `MonitoramentoPlano`: Subscription plans with different consultation levels
- `MonitoramentoAssinatura`: Active/paused/cancelled subscriptions linking participantes to plans
- `MonitoramentoConsulta`: Individual consultation records with results

**Business Flow (follows Laravel-n8n pattern):**

1. **Participant Management (Laravel):**
   - Import participants from RAF SPED reports (`/app/monitoramento/sped`)
   - Add CNPJs manually (`/app/monitoramento/avulso`)
   - View participant details (`/app/monitoramento/participante/{id}`)

2. **Subscription Management (Laravel):**
   - Create subscriptions with plan and frequency selection
   - Pause/reactivate/cancel subscriptions
   - Track subscription status and next execution date

3. **Consultation Execution (n8n - Heavy Work):**
   - **n8n receives consultation request** via webhook
   - **n8n queries InfoSimples APIs** based on plan configuration
   - **n8n returns results** to Laravel callback endpoint
   - Laravel stores results in `MonitoramentoConsulta.resultado`

4. **Results Display (Laravel):**
   - View consultation history with filters
   - Display detailed results in modals
   - Track credits and statistics

**n8n Webhook URLs (add to .env):**
```env
n8n_monitoramento_webhook_url=https://n8n.example.com/webhook/monitoramento
```

**API Callback Endpoint:**
- `POST /api/data/receive/monitoramento` - Receives consultation results from n8n

**InfoSimples API Integration (via n8n):**

The monitoring module uses InfoSimples APIs for government data queries. Different plans include different API sets:

| API | Description | Plans |
|-----|-------------|-------|
| `rfb/cnpj` | Basic CNPJ data and cadastral situation | All plans |
| `rfb/simples` | Simples Nacional status | All plans |
| `sintegra` | State tax registration (ICMS) | Cadastral+, Fiscal |
| `pgfn/cnd` | Federal tax clearance certificate | Fiscal Federal+ |
| `caixa/crf-fgts` | FGTS regularity certificate | Fiscal Federal+ |
| `tst/cndt` | Labor debts certificate | Fiscal Completo+ |
| `cenprot/protestos` | Protest records | Due Diligence |

**Plan Structure:**
```json
{
  "codigo": "fiscal_federal",
  "nome": "Fiscal Federal",
  "consultas_incluidas": ["cnpj", "simples", "sintegra", "pgfn", "fgts"],
  "custo_creditos": 6
}
```

**Subscription Frequencies:**
- `diario`: Daily at 8:00 AM
- `semanal`: Weekly at 8:00 AM
- `quinzenal`: Every 2 weeks at 8:00 AM
- `mensal`: Monthly at 8:00 AM

### Importacao de Arquivo .txt (SSE)

O modulo de Monitoramento permite importar CNPJs via arquivo .txt com acompanhamento em tempo real via SSE.

**Fluxo:**
```
Frontend                 Laravel                  n8n
   |                        |                      |
   | Upload .txt ---------->| POST /importar-txt   |
   |                        | Envia base64 ------->|
   |                        |<--- importacao_id ---|
   | SSE /stream/{id} <-----|                      |
   |                        |                      |
   |       +----------------|<-- POST /progress ---| (a cada CNPJ)
   |<------| SSE data       |                      |
   |       +----------------|                      |
   +--------- Concluido ----+----------------------+
```

**Tabela `importacoes_participantes`:**
- Rastreia historico de importacoes de arquivos .txt
- Campos: user_id, tipo_efd, filename, total_cnpjs, processados, importados, duplicados, status

**Arquivos Principais:**
- `MonitoramentoController::importarTxt()` - Recebe arquivo, envia base64 para n8n
- `MonitoramentoController::streamImportacao()` - SSE que le do cache
- `DataReceiverController::receiveImportacaoTxtProgress()` - Recebe progresso do n8n, armazena em cache

**Rotas:**
```php
// Web (autenticado)
POST /app/monitoramento/importar-txt
GET  /app/monitoramento/importacao/stream/{id}

// API (n8n)
POST /api/monitoramento/sped/importacao-txt/progress
```

**Variavel de Ambiente:**
```env
WEBHOOK_MONITORAMENTO_IMPORTACAO_TXT_URL=https://n8n.example.com/webhook/importacao-txt
```

**Acesso no código:**
```php
$webhookUrl = config('services.webhook.monitoramento_importacao_txt_url');
```

**Payload para n8n:**
```json
{
    "user_id": 1,
    "tipo_efd": "EFD Fiscal",
    "filename": "fornecedores.txt",
    "file_base64": "MTIzNDU2NzgwMDAxOTAK...",
    "progress_url": "https://fiscaldock.com.br/api/monitoramento/sped/importacao-txt/progress"
}
```

**Payload de Progresso (n8n -> Laravel):**
```json
{
    "importacao_id": 123,
    "status": "processando",
    "total_cnpjs": 150,
    "processados": 75,
    "importados": 70,
    "duplicados": 5
}
```

**IMPORTANTE:** Laravel NAO edita banco durante importacao. n8n faz todas as operacoes de banco e envia progresso para Laravel armazenar em cache. O SSE le do cache e envia para o frontend.

## Development Patterns

### Laravel-n8n Integration Pattern

**For any new feature requiring data processing, follow this pattern:**

1. **Laravel receives user request**
   - Validate input
   - Check user permissions and credits
   - Create pending record in database

2. **Laravel triggers n8n workflow**
   - Send data via HTTP POST to n8n webhook
   - Include `resume_url` for n8n to callback
   - Store minimal tracking data

3. **n8n performs heavy work**
   - Data parsing and transformation
   - External API calls (government, third-party)
   - Complex calculations
   - Report generation

4. **n8n sends results back to Laravel**
   - POST to Laravel API endpoint (e.g., `/api/data/receive`)
   - Include processed data (CSV, JSON, etc.)
   - Laravel stores in database

5. **Laravel presents results**
   - User views data via Blade templates
   - SSE for real-time notifications (optional)
   - Confirm/cancel actions manage credits

**Example: Monthly data updates are managed by n8n cron jobs, NOT Laravel schedulers.**

### Credit System
Always use `CreditService` for credit operations:
```php
// Check and deduct credits atomically
if (!$this->creditService->hasEnough($user, $amount)) {
    return response()->json(['error' => 'Insufficient credits'], 400);
}
$this->creditService->deduct($user, $amount);
```

Refunds on errors/cancellations:
```php
$this->creditService->refund($user, $amount);
```

### SPED File Processing
Use `SpedUploadService` to send SPED files to n8n (Laravel does NOT parse SPED files):
```php
// Laravel sends file to n8n webhook and waits for callback
$result = $this->spedUploadService->uploadAndProcess(
    file: $request->file('sped_file'),
    tipo: 'EFD Fiscal', // or 'EFD Contribuições'
    isAuthenticated: true,
    modalidade: 'completa', // or 'gratuito'
    userId: $user->id,
    tabId: $request->input('tab_id')
);
// This creates RafConsultaPendente and sends to n8n
// n8n does the actual SPED parsing and returns results via callback
```

### Webhook URLs
Different n8n webhooks for different operations:

| Operation | Config Key | Env Variable |
|-----------|------------|--------------|
| RAF Gratuito (EFD Fiscal) | `services.webhook.sped_fiscal_url` | `WEBHOOK_SPED_FISCAL_URL` |
| RAF Gratuito (EFD Contribuições) | `services.webhook.sped_contribuicoes_url` | `WEBHOOK_SPED_CONTRIBUICOES_URL` |
| RAF Completa (EFD Fiscal) | `services.webhook.sped_fiscal_completa_url` | `WEBHOOK_SPED_FISCAL_COMPLETA_URL` |
| RAF Completa (EFD Contribuições) | `services.webhook.sped_contribuicoes_completa_url` | `WEBHOOK_SPED_CONTRIBUICOES_COMPLETA_URL` |
| Monitoramento Importação .txt | `services.webhook.monitoramento_importacao_txt_url` | `WEBHOOK_MONITORAMENTO_IMPORTACAO_TXT_URL` |

**Access Pattern:**
```php
// Always use config(), never env() directly
$url = config('services.webhook.sped_fiscal_url');

// SpedUploadService handles webhook selection automatically
$this->spedUploadService->uploadAndProcess(...);
```

**Important:** If a webhook URL is not configured, `SpedUploadService::getWebhookUrl()` throws an `InvalidArgumentException` instead of using fallback URLs.

### SSE Notifications
Real-time updates use Server-Sent Events (vanilla EventSource API):
- Route: `/api/data/notifications/stream`
- Frontend: Vanilla JavaScript EventSource with `connectSSE()` and `disconnectSSE()` functions (see `raf.blade.php`)
- Backend: Streams JSON events for report completion/errors
- Cleanup: SPA router automatically disconnects SSE on page navigation

### Custom SPA Navigation
The application uses a custom single-page navigation system (`resources/js/spa.js`):
- Intercepts clicks on elements with `[data-link]` attribute
- Fetches pages via fetch API and updates content
- Dynamically loads page-specific JavaScript from `/js/{pageName}.js`
- Manages cleanup of intervals, event listeners, and Swiper instances
- Handles browser back/forward navigation

**Page-specific JS pattern:**
```javascript
// public/js/mypage.js
window.initMyPage = function() {
    // Page initialization code
    console.log('MyPage initialized');
};
```

**Cleanup pattern:**
```javascript
window._cleanupFunctions.push(() => {
    // Cleanup code (remove listeners, clear intervals, etc.)
});
```

## Code Conventions

- Follow PSR-12 coding standards (enforced by Laravel Pint)
- Use type hints for method parameters and return types
- Service classes injected via constructor dependency injection
- Use Eloquent relationships instead of manual joins
- Form requests for validation when appropriate
- Database transactions for multi-step operations (especially credits)

## Deployment

### Production Environment (Docker Compose)

Production uses Docker Compose (not Swarm) with Traefik for SSL/routing.

**Quick Deploy:**
```bash
./deploy.sh
```

### Required `.env` Variables for Production

The production `.env` file at `/opt/hub_contabil/.env` must contain:

```env
# Application
APP_KEY=base64:YourGeneratedKey...
APP_ENV=production
APP_DEBUG=false
APP_URL=https://fiscaldock.com.br
ASSET_URL=https://fiscaldock.com.br

# Database
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=fiscaldock
DB_USERNAME=postgres
DB_PASSWORD=YourDatabasePassword

# Cache & Session
CACHE_STORE=database
SESSION_DRIVER=database

# Other variables as needed...
```

### Deployment Steps

**1. Build and push new Docker image (if code changed):**
```bash
# Build image
docker build -t felipedaige/fiscaldock:VERSION .

# Push to registry
docker push felipedaige/fiscaldock:VERSION

# Update version in docker-compose.yml (all services)
```

**2. Update `.env` on production server (if needed):**
```bash
ssh root@fiscaldock
cd /opt/hub_contabil
nano .env  # Edit as needed
```

**3. Deploy:**
```bash
./deploy.sh
```

Or manually:
```bash
docker compose up -d
```

**4. Verify deployment:**
```bash
# Check container status
docker compose ps

# Check logs for errors
docker compose logs app --tail 50

# Test the site
curl -I https://fiscaldock.com.br
```

### Common Deployment Issues

| Symptom | Cause | Fix |
|---------|-------|-----|
| 500 Error: "No application encryption key" | APP_KEY not set | Add APP_KEY to .env and run `./deploy.sh` |
| 500 Error: "no password supplied" | DB_PASSWORD not set | Add DB_PASSWORD to .env and run `./deploy.sh` |
| JS/CSS not loading | ASSET_URL wrong | Set `ASSET_URL=https://fiscaldock.com.br` in .env |
| Containers not starting | Network issue | Ensure `network_public` exists: `docker network create network_public` |

### Useful Commands

```bash
# Deploy
./deploy.sh

# View logs
docker compose logs -f app

# Restart
docker compose restart

# Stop
docker compose down

# Enter container
docker compose exec app bash

# Clear Laravel caches
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
```

### Development vs Production

| Aspect | Development | Production |
|--------|-------------|------------|
| File | `docker-compose.dev.yml` | `docker-compose.yml` |
| Command | `docker compose -f docker-compose.dev.yml up` | `./deploy.sh` |
| Network | `devnet` (bridge) | `network_public` (Traefik) |
| Source code | Mounted from host | Baked into image |
| Assets | Built by Vite on demand | Pre-built in Docker image |
| APP_URL | `http://localhost:8080` | `https://fiscaldock.com.br` |
| ASSET_URL | `http://localhost:8080` | `https://fiscaldock.com.br` |

### Docker Compose Files

| File | Purpose |
|------|---------|
| `docker-compose.yml` | Production environment |
| `docker-compose.dev.yml` | Development environment |

### Docker Image Contents

The production image `felipedaige/fiscaldock:X.X.X` includes:
- Multi-stage build (vendor dependencies, Vite assets, runtime)
- Nginx + PHP-FPM + Supervisor
- Pre-built Vite assets in `/var/www/html/public/build/`
- OPcache enabled for performance
- Storage linked to named volume `app_storage`
