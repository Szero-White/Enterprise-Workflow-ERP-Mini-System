# Enterprise Workflow & Operations Management System

A Laravel 12 backend portfolio project for **internal enterprise operations**. The system uses a configurable Workflow Engine as its core and connects approval-driven Procurement with Inventory and Asset Management.

> Portfolio focus: business rules, authorization, transactions, concurrency safety, traceability, automated tests, REST-style integration endpoints, CI, and a consistent responsive operations UI.

[![CI](https://github.com/Szero-White/Enterprise-Workflow-ERP-Mini-System/actions/workflows/ci.yml/badge.svg)](https://github.com/Szero-White/Enterprise-Workflow-ERP-Mini-System/actions/workflows/ci.yml)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)

## 🚀 Live Demo — Start Here

**Live demo:** https://workflow-erp.alwaysdata.net

The public environment is intentionally designed for recruiter evaluation: the end-to-end operational workflow remains writable, while destructive configuration/master-data actions and file uploads are restricted.

### Demo credentials

**Shared password:** `RecruiterDemo2026!`

| Role | Email | What to try |
|---|---|---|
| **Employee** | `employee@example.com` | Start here — create a Purchase Request |
| **Manager** | `manager@example.com` | Approve the first workflow step |
| **Procurement** | `procurement@example.com` | Approve, create/issue PO, receive goods |
| **Finance** | `finance@example.com` | Approve the finance step |
| **Director** | `director@example.com` | Final approval |
| **Asset Manager** | `asset@example.com` | Assign, return and maintain assets |
| **Admin** | `admin@example.com` | Inspect workflow/configuration/audit data (read-only in public demo) |
| HR | `hr@example.com` | Explore HR-oriented workflow configuration/demo data |

> The same credentials are also displayed directly on the login page, so reviewers do not need to search the repository before signing in.

### 3–5 minute recruiter walkthrough

1. Sign in as **Employee** and submit a laptop Purchase Request.
2. Approve the request in sequence as **Manager → Procurement → Finance → Director**.
3. Sign back in as **Procurement**, create and issue the Purchase Order, then post a Goods Receipt.
4. Verify warehouse stock and the automatically created individual Asset.
5. Sign in as **Asset Manager**, assign the laptop to an employee, return it, and inspect the lifecycle/history.
6. Optionally sign in as **Admin** to inspect workflow versioning, audit logs and configuration boundaries.

This single journey demonstrates the main engineering decisions in the repository: generic workflow orchestration, authorization, transaction boundaries, row locking, partial receipt protection, inventory ledgering and asset lifecycle consistency.

## Business flow

```text
Employee / Department
        │
        ▼
Purchase Request
        │
        ▼
Configurable Workflow Engine
Manager → Procurement → Finance → Director
        │
        ▼
Purchase Order
        │
        ▼
Goods Receipt
        │
        ├──────────────► Inventory movement / warehouse stock
        │
        └─ asset-trackable item
                 │
                 ▼
               Asset
                 │
          ┌──────┴──────┐
          ▼             ▼
     Assignment       Return
                          │
                    Maintenance
```

The project intentionally focuses on one coherent enterprise story rather than adding unrelated Sales / CRM modules.

## Engineering highlights

- Laravel 12, PHP 8.2+, Eloquent, Form Requests and service-layer business logic.
- Policy + role based authorization with least-privilege request visibility.
- Protected system roles: core role keys cannot be renamed or deleted accidentally.
- Workflow steps use exactly one approver strategy: **role, department or specific user**.
- Form and workflow configuration is versioned: published configuration becomes immutable after first use, while new changes are prepared by cloning an editable draft version.
- Only ready forms are exposed for employee submission: an active form must have fields, one active workflow and at least one approval step.
- Private workflow attachments are streamed through an authorized download endpoint instead of public storage URLs.
- Public recruiter-demo mode keeps operational flows writable while configuration/master data is read-only, disables uploads by default, rate-limits demo writes and supports deterministic scheduled resets.
- Baseline security headers are applied globally; HSTS is opt-in for stable HTTPS deployments.
- Database transactions and `lockForUpdate()` protect concurrent approval, receiving, stock and asset lifecycle writes.
- Append-only inventory movement ledger for traceability.
- VND monetary calculations use integer đồng in PHP instead of binary floating-point arithmetic.
- Purchase-order and item snapshots preserve historical document meaning.
- Queue-based realtime notification integration uses `afterCommit()`.
- Role-aware dashboard and shared Blade design system with a tokenized **Muted Blue + Soft Teal** visual theme.
- ERP CSS and JavaScript are compiled by Vite; feature scripts live under `resources/js/modules` instead of inline Blade scripts.
- GitHub Actions runs Composer install, frontend build and the Laravel test suite on push / pull request.

## Main modules

### Organization & access control

- Users, departments and roles.
- Demo roles: Admin, Employee, Manager, HR, Procurement, Finance, Director and Asset Manager.
- Active-user middleware prevents disabled accounts from continuing to access the application.
- Core system role keys are immutable because middleware and workflows depend on them.
- Policies protect purchase requests, workflow requests, attachments and asset lifecycle actions.

### Configurable Workflow Engine

- Versioned dynamic form templates and fields.
- Versioned workflow templates with ordered approval steps.
- Draft → Activate → Lock-on-first-use lifecycle keeps historical requests bound to the configuration that created them.
- Activating a workflow automatically deactivates sibling workflow versions for the same form, so only one workflow is active at a time.
- One explicit approver strategy per step: user, role or department.
- Approve, reject and return-for-edit actions.
- Approval history, notifications and audit logs.
- `WorkflowTransitionDispatcher` invokes domain-specific handlers without coupling Procurement into `ApprovalService`.

### Procurement

- Supplier master data.
- Structured Purchase Requests linked one-to-one with generic workflow requests.
- Item snapshots preserve SKU, name, unit, quantity and estimated cost.
- Purchase Orders preserve supplier / warehouse context and procurement item snapshots.
- Draft → Issued → Partially Received → Received lifecycle.
- Cancelled POs remain in history and can be replaced.
- Partial receiving, over-receipt protection and issue-date validation.

### Inventory

- Item / ItemCategory master data.
- Warehouses and stock positions by warehouse + item.
- Append-only inventory movement ledger.
- Low-stock detection using reorder levels.
- Service-level validation protects stock mutations outside controllers too.

### Asset Management

- Asset-trackable item flag.
- Goods Receipt creates one Asset record per physical tracked unit.
- Assignment removes one unit from warehouse stock; return restores it.
- Maintenance blocks reassignment until released.
- Duplicate assignment, fractional tracked receiving and invalid lifecycle dates are rejected.

## Architecture

```text
HTTP / API Request
       │
       ▼
Form Request validation
       │
       ▼
Controller
       │
       ├── Gate / Policy authorization
       ▼
Application / Domain Service
       │
       ├── DB transaction
       ├── row-level locking
       ├── domain validation
       ├── audit logging
       └── workflow / inventory / asset integrations
       ▼
Eloquent Models + Database
```

Important boundaries:

```text
app/Services/Workflow/
app/Services/Procurement/
app/Services/Inventory/
app/Services/Asset/
app/Policies/
app/Support/Navigation/
```

## UI design system

The interface uses a calm enterprise palette rather than a saturated brand color:

```text
Primary         #4F7FA8  Muted Blue
Primary soft    #EEF5F9
Accent          #5F9D9A  Soft Teal
Background      #F5F7F9
Surface         #FFFFFF
Text            #24313D
Border          #DFE6EB
```

Brand colors are centralized in `resources/css/erp/tokens.css`; components consume semantic tokens instead of repeating purple/brand hex values. Success, warning and danger colors remain separate from the brand palette so status meaning is not confused with primary actions.

## API v1

A small integration API is exposed under `/api/v1` with pagination, filtering, API Resources, HTTP Basic authentication and rate limiting. Basic auth keeps the portfolio dependency-light; a production public API would normally use Sanctum/OAuth behind HTTPS.

```text
GET  /api/v1/items
GET  /api/v1/inventory-stocks
GET  /api/v1/purchase-requests
GET  /api/v1/purchase-requests/{id}
POST /api/v1/purchase-requests
```

Local API example (`DEMO_MODE=false`):

```bash
curl -u employee@example.com:password \
  -H "Accept: application/json" \
  http://127.0.0.1:8000/api/v1/purchase-requests
```

The original `/internal-api/v1/*` session endpoints remain available for internal UI/integration compatibility.

OpenAPI contract: [`docs/openapi.yaml`](docs/openapi.yaml).

## Demo environment details

Local development keeps the intentionally simple shared password `password`. The Internet-facing demo uses the published recruiter credential shown in **Live Demo — Start Here** and on the login page. `DEMO_MODE=true` keeps the demo useful without treating the shared password as a security boundary.

While public demo mode is enabled:

- Admin/configuration, item/category/warehouse/supplier master data and manual stock receipt are read-only.
- Purchase Request → Approval → Purchase Order → Goods Receipt → Asset Assignment/Return remains writable for recruiter evaluation.
- Workflow file uploads are disabled by default to prevent public disk abuse.
- Writes are rate-limited per demo actor and can be reset using the guarded `demo:reset --force` command.
- The public server should set `DEMO_PASSWORD=RecruiterDemo2026!` so the README, login screen and seeded accounts stay consistent.

Production template: [`.env.production.example`](.env.production.example). Alwaysdata deployment runbook: [`docs/deployment-alwaysdata.md`](docs/deployment-alwaysdata.md).

## Local setup

### PowerShell / Windows

```powershell
composer install
npm ci
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm run build
php artisan serve
```

### Linux / macOS

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm run build
php artisan serve
```

The default configuration uses SQLite for a quick demo. `npm run build` compiles the actual ERP design system and feature JavaScript through Vite.

## Docker development

Docker Compose is provided as a **local development environment**, not a production container stack. The Dockerfile now builds Vite assets in a Node stage, and the Compose init service refreshes both `vendor` and `public/build` volumes so a clean clone does not depend on a pre-existing Vite manifest.

```bash
cp .env.docker.example .env
# set DB_PASSWORD / MYSQL_ROOT_PASSWORD, then generate APP_KEY

docker compose build
docker compose run --rm app php artisan key:generate
docker compose up -d
docker compose exec app php artisan migrate:fresh --seed
```

`php artisan serve` remains intentional here because this stack is for development. The public portfolio deployment uses the hosting provider's managed PHP web runtime instead of presenting this Compose file as a production Nginx/PHP-FPM stack.

## Quality checks

```bash
php artisan optimize:clear
php artisan migrate:fresh --seed
php artisan test
```

CI runs the same Laravel test suite and a frontend production build on GitHub Actions. Because the application shell loads the ERP assets through `@vite`, a successful frontend build now validates the assets actually used by the product UI.

## Portfolio demo scenario

1. Employee submits a Purchase Request for two laptops.
2. Manager, Procurement, Finance and Director approve it through the generic workflow engine.
3. Procurement creates and issues a Purchase Order.
4. Supplier delivery is recorded with a Goods Receipt.
5. Warehouse stock increases and two individual Asset records are created automatically.
6. Asset Manager assigns one laptop; warehouse stock decreases by one.
7. The laptop is returned; stock increases again.
8. A damaged return enters Maintenance and cannot be reassigned until released.
9. Admin reviews the audit trail and role-aware operations dashboard.

## Current portfolio scope

The codebase deliberately stops at Workflow + Procurement + Inventory + Asset Management. The current release focus is public-demo safety, reproducible deployment, backup/recovery, CI verification and recruiter-facing presentation rather than more unrelated business modules. Token-based API authentication and deeper observability remain optional future improvements, not blockers for the portfolio demo.
