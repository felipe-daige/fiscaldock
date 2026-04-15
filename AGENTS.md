# Repository Guidelines

## Product Context
FiscalDock is a Brazilian fiscal/tax monitoring SaaS for SPED imports, tax lookups, and real-time processing. The main stack is Laravel 12, PostgreSQL, Vite/Tailwind, Docker, and n8n.

## Project Structure & Module Organization
Core backend code lives in `app/`, organized by concern such as `Models/`, `Services/`, `Http/`, and `Helpers/`. Routes are in `routes/`. Migrations, factories, and seeders are in `database/`. Frontend assets are under `resources/css` and `resources/js`; Vite builds them into `public/build`. Blade views live in `resources/views`. Tests are split into `tests/Feature` and `tests/Unit`. n8n handles async imports and consultations; Laravel persists results and streams progress via SSE.

## Build, Test, and Development Commands
Separate local development from container-backed operation. Use the project scripts instead of ad hoc commands when possible.

### Local development

These commands assume PHP and Node are available on the host:

- `composer setup`: install PHP and Node dependencies, create `.env`, generate the app key, run migrations, and build assets.
- `composer dev`: start the local Laravel server, queue listener, and Vite dev server together.
- `composer test`: clear config cache and run the full test suite.
- `npm run dev`: run Vite only for frontend work.
- `npm run build`: create production assets in `public/build`.
- `./vendor/bin/pint`: format PHP code to the project standard.

### Container-backed operation

In this repository, operational commands should usually run inside the main app container instead of assuming `php` is installed on the host:

- `docker exec fiscaldock-app-1 php artisan migrate --force`
- `docker exec fiscaldock-app-1 php artisan test`
- `docker exec fiscaldock-app-1 ./vendor/bin/pest`
- `docker exec fiscaldock-app-1 ./vendor/bin/pint`

Quick operational checks:

- `docker exec fiscaldock-app-1 php artisan about`
- `docker exec fiscaldock-app-1 php artisan migrate:status`
- `docker exec fiscaldock-app-1 php artisan queue:failed`
- `docker exec fiscaldock-app-1 php artisan schedule:list`
- `docker exec fiscaldock-app-1 php artisan pail`

### Runtime operational notes

The deployed stack is not just the web container. The current repository defines these runtime roles:

- `app`: web application container.
- `worker`: queue processing via `php artisan queue:work --verbose --tries=3 --timeout=90`.
- `scheduler`: scheduled tasks via `php artisan schedule:work`.
- `redis`: optional support service depending on the queue/cache strategy.

`routes/console.php` currently schedules `alertas:recalcular` daily at `06:00`. Queue defaults in `config/queue.php` point to `database`, but deployment files also include Redis-backed scenarios, so queue changes must verify both code defaults and deploy configuration.

## Coding Style & Naming Conventions
Follow `.editorconfig`: UTF-8, LF endings, spaces, and 4-space indentation; YAML uses 2 spaces. PHP follows PSR-4 and Laravel conventions: classes in `StudlyCase`, methods and variables in `camelCase`, tables and migrations in `snake_case`. Keep service classes in `app/Services` and helper logic in `app/Helpers`. Frontend entry points use simple lowercase names such as `resources/js/app.js`.

## Design System
Authenticated views in `resources/views/autenticado/` follow the `DANFE Modernizado` visual system, a modernized interface language inspired by Brazilian fiscal document layouts (`DANFE` / `NF-e`). The audience is accountants and fiscal analysts, so the UI should stay sober, dense, and operational rather than marketing-oriented.

Critical rule: do not use Tailwind background color classes for badges such as `bg-indigo-700`. Tailwind CSS v4 may compile colors through CSS variables / `oklch()`, which is not reliable enough for this project’s badge rendering targets. For highlighted badges, always use inline hex backgrounds with `style="background-color: #HEX"` plus `text-white`.

### Page

| Element | Class / Style |
|---|---|
| Page background | `bg-gray-100 min-h-screen` |
| Main container | `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8` |
| Page title | `text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide` |
| Subtitle | `text-xs text-gray-500` |

### Blocks / Sections

| Element | Class / Style |
|---|---|
| Section container | `bg-white rounded border border-gray-300 overflow-hidden` |
| Section header | `bg-gray-50 px-4 py-2 border-b border-gray-200` |
| Header label | `text-[10px] font-semibold text-gray-500 uppercase tracking-widest` |
| Header count badge | `text-[10px] font-semibold text-gray-400 bg-gray-200 px-2 py-0.5 rounded` |

### KPI Cards

| Element | Class / Style |
|---|---|
| Grid | `grid grid-cols-2 lg:grid-cols-N divide-x divide-gray-200` |
| KPI label | `text-[10px] font-semibold text-gray-400 uppercase tracking-wide` |
| KPI value | `text-lg font-bold text-gray-900` |
| Sub-value | `text-[11px] text-gray-500` |

Never use vibrant colors for the primary KPI value.

### Filters

| Element | Class / Style |
|---|---|
| Labels | `text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1` |
| Inputs / selects | `border border-gray-300 rounded text-sm focus:ring-1 focus:ring-gray-400 focus:border-gray-400` |
| Primary button | `bg-gray-800 text-white hover:bg-gray-700 rounded text-sm font-medium` |
| Secondary button | `bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 rounded` |

### Desktop Tables

| Element | Class / Style |
|---|---|
| `thead tr` | `border-b border-gray-300` |
| `th` | `px-3 py-2.5 text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50` |
| `tbody` | `divide-y divide-gray-100` |
| Row hover | `hover:bg-gray-50/50 transition-colors` |
| Primary text | `text-sm text-gray-700` |
| Currency value | `text-sm font-semibold text-gray-900 text-right font-mono` |

### Badges

Always use inline hex colors for badge backgrounds.

```html
<span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white"
      style="background-color: #HEX">LABEL</span>
```

| Category | Variant | Hex color | Example |
|---|---|---|---|
| Origin | `EFD` | `#4338ca` | `style="background-color: #4338ca"` |
| Origin | `XML` | `#0f766e` | `style="background-color: #0f766e"` |
| Operation type | `Entrada` | `#047857` | `style="background-color: #047857"` |
| Operation type | `Saida` | `#d97706` | `style="background-color: #d97706"` |
| Model | `NF-e`, `CT-e`, etc. | `#374151` | `style="background-color: #374151"` |
| Status | `OK` | `#047857` | `style="background-color: #047857"` |
| Status | `Divergente` | `#d97706` | `style="background-color: #d97706"` |
| Status | `Sem Mov.` / `Inativo` | `#9ca3af` | `style="background-color: #9ca3af"` |
| Code | `NCM`, `CFOP` | `#4338ca` | `style="background-color: #4338ca"` |
| Quantity | Numeric count | `#374151` | `style="background-color: #374151"` |

### Pagination

| Element | Class / Style |
|---|---|
| Container | `border-t border-gray-300 px-4 py-3` |
| Range info | `text-[10px] text-gray-500 uppercase tracking-wide` |
| Default button | `px-3 py-1.5 text-[10px] text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50` |
| Active button | `text-[10px] font-bold text-white rounded` + `style="background-color: #1f2937"` |
| Disabled button | `text-[10px] text-gray-400 bg-gray-100 border border-gray-200 rounded` |

### Mobile Cards

| Element | Class / Style |
|---|---|
| Container | `divide-y divide-gray-100` inside the white block |
| Card | `px-4 py-3` |
| Labels | `text-[10px] text-gray-400 uppercase` |
| Badges | Same inline hex rules as desktop |
| Action links | `text-xs text-gray-600 hover:text-gray-900 hover:underline` |

### Links and Interactions

| Element | Class / Style |
|---|---|
| Table link | `text-gray-900 hover:text-gray-600 hover:underline` |
| Navigation link | `text-gray-600 hover:text-gray-900 hover:underline` |
| Expand button | `text-gray-400 hover:text-gray-700 transition-colors` |

### Charts

For ApexCharts in authenticated fiscal dashboards, use a restrained palette and neutral framing.

| Element | Value |
|---|---|
| Single-series bar color | `#374151` |
| Grid border | `#e5e7eb` |
| Donut / categorical palette | `['#374151','#047857','#d97706','#dc2626','#7c3aed','#0891b2','#ea580c','#65a30d','#db2777','#4f46e5']` |
| Chart labels | `text-[10px] font-semibold text-gray-400 uppercase tracking-wide` |

### Alerts

| Element | Class / Style |
|---|---|
| Alert container | `bg-white rounded border border-gray-300 p-4` |
| Left border | `border-l-4` with contextual color such as `border-l-red-500`, `border-l-amber-500`, `border-l-blue-500` |
| Alert text | `text-sm text-gray-700` |

### Converted Views

These views already follow the `DANFE Modernizado` pattern and should be used as references for future work:

- `autenticado/importacao/efd-nota.blade.php`
- `autenticado/notas-fiscais/index.blade.php`
- `autenticado/catalogo/index.blade.php`
- `autenticado/bi/index.blade.php` with `public/js/bi.js`
- `autenticado/dashboard/index.blade.php`
- `autenticado/risk/*`
- `autenticado/minha-empresa/*`
- `autenticado/monitoramento/*`
- `autenticado/plano/checkout.blade.php`
- `autenticado/resumo-fiscal/index.blade.php`

### Pending Conversions

The previous visual debt covering `risk/*`, parts of `minha-empresa/*`, monitoring screens, older forms, and checkout was completed on 2026-04-15. Treat regressions to the older visual language, such as colorful cards, `bg-blue-600`, `rounded-lg`, or badges in the `bg-*-100 text-*-700` style, as bugs to fix when found.

## Testing Guidelines
Tests run with Pest on top of PHPUnit. Put request and integration coverage in `tests/Feature`; keep narrow logic checks in `tests/Unit`. Name files with the subject followed by `Test.php`, for example `MinhaEmpresaTest.php`. Run `composer test` before opening a PR. Add or update tests for behavior changes in `app/` or `resources/views`.

## Commit & Pull Request Guidelines
Recent history mixes Portuguese summaries with prefixes such as `feat(...)`, `fix(...)`, and `Refactor:`. Prefer short, imperative commit messages and use a prefix when it clarifies scope. PRs should include a summary, affected areas, migration or config impact, linked issue if available, and screenshots for UI changes.

## Critical Project Rules
Do not create new migrations unless maintainers explicitly request it; this codebase currently expects edits to the existing migration set. The application code is mounted into the container from the host, so local file changes are reflected immediately; avoid `docker cp` workflows for code or migrations. Use `--force` only with `php artisan migrate`.

Be careful with `composer setup`: it runs `php artisan migrate --force`. Confirm the target database and environment before using it, especially in shared or persistent environments.

Do not assume `docker-compose.yml` is a local-only developer setup. In this repository it also reflects operational concerns such as mounted application code, persistent `vendor`/`storage` volumes, and dedicated `worker` and `scheduler` services. The image-based deploy flow is represented separately by `Dockerfile` and `docker-stack.yml`.

## Product and Monetization Roadmap

### Current product and implementation gaps

- Canonical backlog is maintained in `docs/backlog.md`; keep this section synchronized when backlog status changes.
- Clearance DF-e is partially delivered: authenticated UI exists for `/app/validacao`, `/app/validacao/notas`, and `/app/validacao/buscar-nfe`. Pending work is real InfoSimples/n8n execution, persistence of external results in `xml_notas`, KPI reads from external source data, and operational result classification.
- NF-e/SEFAZ via InfoSimples is pending: expose a persisted contract for `situacao_sefaz` and `verificado_sefaz_em` (dedicated fields or normalized payload equivalent), and complete the key-based pipeline. MVP uses `receita-federal/nfe`; do not build direct per-state SEFAZ fallback before usage/conversion evidence.
- Score Fiscal still depends on simulated `RiskScoreController` paths and should not be treated as a finished product while the real external consultation flow is missing.
- XML import exists but still has product-completion work in navigation, end-to-end automation hardening, and alignment between what the UI advertises and what is fully operational.
- The main authenticated visual-debt backlog was completed on 2026-04-15; maintain DANFE Modernizado consistency and fix any specific regressions when found.
- Operational docs must be kept in sync with the actual container/runtime names and with the real commands available in the environment.
- Public acquisition CTAs on the landing pages were standardized on `btn-cta` with inline fallback in the public layout on 2026-04-14; do not treat that issue as an open visual backlog item unless a new regression appears.

Active backlog items:

- Ad-hoc DF-e lookup must be wired from Laravel to n8n + InfoSimples, with ownership validation for optional `cliente_id`, cost/status feedback, and upsert into `xml_notas`.
- BI cross-checks between consultations and clearance are pending: combine participant regularity/certificates with SEFAZ/XML document status in dashboards and alerts.
- n8n must send `status=erro` to `/api/consultas/progresso` for all consultation failure paths. Laravel already receives this status; the remaining work is workflow coverage and refund semantics.
- CND Federal, CNDT, FGTS, CND Estadual, CND Municipal, sanções, CNJ, protestos and processes are part of the credit-based product ladder and must keep clear per-query economics before implementation.
- Current product ladder: `Gratuito` 0 credits, `Validação` 5 credits, `Licitação` 10 credits, `Compliance` 18 credits, `Due Diligence` 35 credits. Keep `1 crédito = R$ 0,20`; margin changes should alter credits consumed, not credit unit price.
- `Compliance` and `Due Diligence` are blocked until the first confirmed credit purchase. The source of truth is `credit_transactions.type = purchase` with `amount > 0`; do not add a duplicated boolean in `users` unless explicitly requested for caching/segmentation.
- When evaluating new paid products or tier expansions, document any candidate external APIs before implementation. Current likely candidates are fiscal/compliance sources that materially expand the `resultado_dados` surface without overlapping the existing free queries.
- Mercado Pago integration is planned for real credit-purchase checkout flows. The current checkout UI must be treated as placeholder until payment provider integration, webhook handling, status reconciliation, and credit release rules are defined.
- InfoSimples is the chosen MVP provider for ad-hoc NF-e lookup. Use `receita-federal/nfe` first; do not build direct per-state SEFAZ fallback until the feature has usage/conversion evidence. Future scale candidate: Focus NFe.
- Remove "Em Breve" from XML import only after XML automation is operational end-to-end.

When changing docs, code, or planning around these items, prefer documenting:
- target routes/views affected
- product objective
- external dependency or API involved
- plan/pricing impact
- what is implemented vs only designed

## Configuration & Safety Notes
Start from `.env.example`; do not commit secrets. `public/build/` is generated, not a source directory, so rebuild assets when needed. Confirm migration effects before merging, especially around SPED imports, consultation status updates, and credit-related flows.

Webhook endpoints and `API_TOKEN` are operational prerequisites for the import and consultation flows; treat them as required integration settings, not optional polish.

There is currently a naming inconsistency around consultation webhooks in the repository: `.env.example` references `WEBHOOK_CONSULTAS_URL`, while deployment and entrypoint files reference `WEBHOOK_CONSULTAS_LOTES_URL`. When touching this integration, verify the effective contract before renaming variables or assuming one name is authoritative.
