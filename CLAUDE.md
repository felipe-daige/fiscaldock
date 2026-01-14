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
- `n8n_gratuito_efd_fiscal_webhook_url`: Free tax regime analysis (EFD Fiscal)
- `n8n_gratuito_efd_contrib_webhook_url`: Free tax regime analysis (EFD Contribuições)
- `n8n_completa_efd_fiscal_webhook_url`: Complete analysis with CND (EFD Fiscal)
- `n8n_completa_efd_contrib_webhook_url`: Complete analysis with CND (EFD Contribuições)

Without these webhooks, Laravel cannot process SPED files (it only coordinates, doesn't process).

### Database Schema Notes

- Uses PostgreSQL with migrations in `database/migrations/`
- `users.credits` (decimal): Credit balance tracking
- RAF tables track full lifecycle: pending → processed → confirmed/cancelled
- Date fields use Carbon/datetime casting for timezone handling

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
- Gratuito EFD Fiscal: `n8n_gratuito_efd_fiscal_webhook_url`
- Gratuito EFD Contribuições: `n8n_gratuito_efd_contrib_webhook_url`
- Completa EFD Fiscal: `n8n_completa_efd_fiscal_webhook_url`
- Completa EFD Contribuições: `n8n_completa_efd_contrib_webhook_url`

Stored in `.env` and accessed via `env()` in `SpedUploadService`.

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
