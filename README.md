# Enterprise Workflow & Operations Management System

A Laravel 12 backend portfolio project for internal enterprise operations. The application combines a configurable approval workflow with Procurement, Inventory, and Asset Management.

> Engineering focus: business rules, authorization, transactions, concurrency safety, auditability, automated tests, REST-style integration endpoints, CI, and a responsive operations UI.

[![CI](https://github.com/Szero-White/Enterprise-Workflow-ERP-Mini-System/actions/workflows/ci.yml/badge.svg)](https://github.com/Szero-White/Enterprise-Workflow-ERP-Mini-System/actions/workflows/ci.yml)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)

## Live Demo

**Demo URL:** [https://workflow-erp.alwaysdata.net](https://workflow-erp.alwaysdata.net)

The public demo keeps the normal role-based authorization rules. Demo mode adds write-rate limits and disables dynamic workflow file uploads unless uploads are explicitly enabled.

### Demo credentials

| Role | Email | Password | What to try |
|---|---|---|---|
| **Employee** | `employee@example.com` | `RecruiterDemo2026!` | Create a Purchase Request |
| **Manager** | `manager@example.com` | `RecruiterDemo2026!` | Approve the first workflow step |
| **Procurement** | `procurement@example.com` | `RecruiterDemo2026!` | Approve, create/issue a Purchase Order, and receive goods |
| **Finance** | `finance@example.com` | `RecruiterDemo2026!` | Approve the finance step |
| **Director** | `director@example.com` | `RecruiterDemo2026!` | Perform the final approval |
| **Asset Manager** | `asset@example.com` | `RecruiterDemo2026!` | Assign, return, and maintain assets |
| **Admin** | `admin@example.com` | `RecruiterDemo2026!` | Inspect and manage workflow/configuration data according to normal authorization |
| **HR** | `hr@example.com` | `RecruiterDemo2026!` | Explore HR-oriented workflow data |

The login page also exposes the configured demo accounts and demo password when demo mode is enabled.

### Recruiter walkthrough

1. Sign in as **Employee** and submit a Purchase Request.
2. Approve it in sequence as **Manager -> Procurement -> Finance -> Director**.
3. Sign in as **Procurement**, create and issue the Purchase Order, then post a Goods Receipt.
4. Verify inventory stock and the Asset records created for asset-trackable items.
5. Sign in as **Asset Manager**, assign and return an asset.
6. Optionally sign in as **Admin** to inspect workflow configuration and audit data.

## Business Flow

```text
Employee / Department
        |
        v
Purchase Request
        |
        v
Configurable Workflow
Manager -> Procurement -> Finance -> Director
        |
        v
Purchase Order
        |
        v
Goods Receipt
        |
        +--> Inventory stock and movement history
        |
        +--> Asset creation for asset-trackable items
                 |
                 v
               Asset
                 |
          Assignment / Return
                 |
                 v
            Maintenance
```

## Engineering Highlights

- Laravel 12 and PHP 8.2+.
- Eloquent ORM for persistence and relationships.
- Form Requests for input validation.
- Policies and middleware for authorization and request-level access control.
- Service-layer business logic for workflow, procurement, inventory, and asset operations.
- Database transactions and row locking for concurrency-sensitive writes.
- Versioned Dynamic Forms and workflow configuration with ordered approval steps.
- Draft/current/legacy/inactive lifecycle semantics that preserve in-flight request history.
- Dynamic field types for common business inputs, including text, textarea, number, email, phone, date, time, date-time, URL, select, radio, checkbox, and file.
- Generic conditional field visibility/required rules without hard-coding a specific business form.
- Business-context notifications with request type, requester, useful request details, contextual actions, and legacy-notification presentation support.
- Approval history, notifications, and audit logging.
- Purchase Request, Purchase Order, and Goods Receipt lifecycle.
- Inventory stock tracking and inventory movement history.
- Asset registration, assignment, return, and maintenance lifecycle.
- Versioned REST-style API under `/api/v1`.
- Vite-managed frontend assets.
- PHPUnit test suite and GitHub Actions CI.
- Docker files for local containerized development.
- Alwaysdata deployment documentation for the portfolio environment.

## Project Structure

```text
app/
  Contracts/             Application contracts
  Enums/                 Domain status/value enums
  Http/
    Controllers/         HTTP and API controllers
    Middleware/          Request-level guards and safety controls
    Requests/            Validation rules
    Resources/           API response resources
  Jobs/                  Queued jobs
  Models/                Eloquent models and relationships
  Policies/              Resource authorization
  Services/              Business logic
  Support/               Shared domain/application helpers

database/
  factories/             Test factories
  migrations/            Database schema
  seeders/               Demo and reference data

resources/
  css/                    Application styles
  js/                     Frontend JavaScript modules
  views/                  Blade templates

routes/
  web.php                 Browser routes
  api.php                 API routes
  console.php             Console commands

scripts/
  dev.ps1                 Windows local-development launcher

tests/
  Feature/                End-to-end application behavior
  Unit/                   Focused unit tests

docs/
  development.md          Local development guide
  deployment-alwaysdata.md
  openapi.yaml
```

## Application Architecture

```text
HTTP / API Request
        |
        v
Route + Middleware
        |
        v
Form Request Validation
        |
        v
Controller
        |
        +--> Policy / Gate Authorization
        |
        v
Application Service
        |
        +--> Business Rules
        +--> Database Transaction
        +--> Row Locking
        +--> Audit / Notification Integration
        |
        v
Eloquent Models
        |
        v
Database
```

Controllers stay focused on HTTP concerns. Validation belongs in Form Requests, authorization belongs in Policies/Middleware, and multi-step business logic belongs in Services.

## Dynamic Forms and Workflow Versioning

Dynamic Forms are configuration data, not hard-coded request screens. Administrators can define reusable fields using the supported business input types:

```text
Text / Textarea / Number / Email / Phone
Date / Time / DateTime / URL
Select / Radio / Checkbox / File
```

Conditional fields can depend on earlier fields in the same form. The backend validates those conditions and removes hidden values even if a client submits them manually.

Published configuration is immutable for new changes. A change is made by creating an editable draft version, updating the draft, and publishing it. Existing requests remain bound to the form/workflow version that created them. This keeps approval history reproducible while allowing configuration to evolve safely.

Lifecycle labels used by the administration UI are:

```text
Draft      -> editable configuration, not yet published
Current    -> configuration used for new requests
Legacy     -> no new requests; existing in-flight requests continue
Inactive   -> retired from active processing
```

## Notification Architecture

Notification content is built from business context instead of exposing internal request codes as the primary message. The notification layer is organized around reusable content builders/presenters so workflow and operations modules follow the same user-facing standard.

A notification can carry:

- a business title such as the form/request type;
- the requester or relevant actor;
- concise request-specific details;
- a contextual destination/action;
- the related workflow, purchase, receipt, or asset identifiers needed for navigation.

Stored historical notifications remain untouched. When related business data still exists, the presenter can enrich older notification records for display without rewriting audit history. Realtime delivery, when configured, uses the same stored title/message/data payload as the notification center.

## Local Development

### Requirements

- PHP 8.2+
- Composer
- Node.js and npm
- `pdo_sqlite` for the default SQLite setup

GitHub Actions uses PHP 8.3 and Node.js 20.

### Recommended Windows startup

From the project root:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\dev.ps1
```

Open:

```text
http://127.0.0.1:8000
```

`http://localhost:5173` is the Vite asset server, not the application URL.

The startup script safely prepares missing local dependencies/configuration, applies pending migrations, seeds a newly created SQLite database, and then opens separate Backend and Frontend PowerShell terminals.

### Manual first-time setup

```powershell
composer install
npm ci
Copy-Item .env.example .env
php artisan key:generate
New-Item database\database.sqlite -ItemType File -Force
php artisan migrate --seed
npm run build
```

For Windows, start development with:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\dev.ps1
```

The launcher performs the safe setup checks first, then opens two PowerShell windows:

```text
Backend   -> Laravel server + queue listener
Frontend  -> Vite development server on port 5173
```

Open the application at `http://127.0.0.1:8000`. Port `5173` is only the Vite asset server. The launcher fails clearly if port `8000` or `5173` is already occupied instead of silently choosing another port.

The repository's `composer run dev` command also starts Laravel Pail. Because Pail requires the PHP `pcntl` extension, use that Composer command only in environments where `pcntl` is available. Native Windows development should use `scripts/dev.ps1`.

See [`docs/development.md`](docs/development.md) for the complete local workflow.

## API v1

The repository exposes these versioned endpoints:

```text
GET  /api/v1/items
GET  /api/v1/inventory-stocks
GET  /api/v1/purchase-requests
GET  /api/v1/purchase-requests/{id}
POST /api/v1/purchase-requests
```

The API uses HTTP Basic authentication, active-user checks, role middleware, demo-safety middleware, and rate limiting.

OpenAPI contract: [`docs/openapi.yaml`](docs/openapi.yaml)

## Demo Mode

Demo behavior is configured through `config/demo.php` and environment variables.

Important defaults in `.env.example`:

```text
DEMO_MODE=false
DEMO_PASSWORD=password
DEMO_UPLOADS_ENABLED=false
DEMO_MAX_WRITES_PER_MINUTE=15
DEMO_MAX_WRITES_PER_HOUR=60
```

When `DEMO_MODE=true`:

- normal application authorization still applies;
- non-safe HTTP writes are rate-limited;
- dynamic workflow file uploads are prohibited when `DEMO_UPLOADS_ENABLED=false`;
- demo seeding rejects the default password `password`;
- the guarded `demo:reset` console command can reset demo data.

The public deployment must use a non-default demo password.

## Tests and Quality Checks

Run the test suite:

```powershell
php artisan test
```

Check PHP formatting:

```powershell
vendor\bin\pint --test
```

Build frontend assets:

```powershell
npm run build
```

Check Git whitespace errors before committing:

```powershell
git diff --check
```

The PHPUnit configuration uses an in-memory SQLite database for tests.

## Database Safety

For normal development, apply pending migrations:

```powershell
php artisan migrate
```

Reset the local database only when deleting all local data is intentional:

```powershell
php artisan migrate:fresh --seed
```

Do not use `migrate:fresh` against a database that contains data you need to keep.

## Docker Development

The repository includes:

```text
docker-compose.yml
docker/php/Dockerfile
.env.docker.example
```

These files provide an alternative local development environment. The default SQLite workflow remains the simplest option for a quick local review.

## Deployment

Deployment-related files include:

```text
.env.production.example
docs/deployment-alwaysdata.md
.github/workflows/ci.yml
```

The public portfolio deployment uses the hosting provider's PHP runtime rather than `php artisan serve` as a production web server.

## Current Scope

The repository intentionally focuses on:

**Workflow + Procurement + Inventory + Asset Management**

The current scope prioritizes business correctness, authorization, transactional safety, concurrency handling, traceability, automated tests, reproducible local setup, and recruiter-facing demonstration.
