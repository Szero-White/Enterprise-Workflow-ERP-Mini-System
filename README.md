# Enterprise Workflow & Operations Management System

A Laravel 12 backend portfolio project for **internal enterprise operations**. The system uses a configurable Workflow Engine as its core and connects approval-driven Procurement with Inventory and Asset Management.

> Portfolio focus: business rules, authorization, transactions, concurrency safety, traceability, automated tests, REST-style integration endpoints, CI, and a consistent responsive operations UI.

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
- Database transactions and `lockForUpdate()` protect concurrent approval, receiving, stock and asset lifecycle writes.
- Append-only inventory movement ledger for traceability.
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

Example:

```bash
curl -u employee@example.com:password   -H "Accept: application/json"   http://127.0.0.1:8000/api/v1/purchase-requests
```

The original `/internal-api/v1/*` session endpoints remain available for internal UI/integration compatibility.

OpenAPI contract: [`docs/openapi.yaml`](docs/openapi.yaml).

## Demo accounts

After `php artisan migrate:fresh --seed`, all demo accounts use password `password`. The login screen exposes demo credentials only in local/testing environments; do not publish these credentials with unrestricted admin access on a public production deployment.

```text
admin@example.com
employee@example.com
manager@example.com
hr@example.com
procurement@example.com
finance@example.com
director@example.com
asset@example.com
```

## Local setup

### PowerShell / Windows

```powershell
composer install
npm install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm run build
php artisan serve
```

### Linux / macOS

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm run build
php artisan serve
```

The default configuration uses SQLite for a quick demo. `npm run build` compiles the actual ERP design system and feature JavaScript through Vite.

## Docker development

Docker Compose is provided as a **local development environment**, not a production container stack.

```bash
cp .env.docker.example .env
# set DB_PASSWORD / MYSQL_ROOT_PASSWORD, then generate APP_KEY

docker compose build
docker compose run --rm app php artisan key:generate
docker compose up -d
docker compose exec app php artisan migrate:fresh --seed
```

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

The codebase deliberately stops at Workflow + Procurement + Inventory + Asset Management. The next production-oriented improvements would be token-based API authentication (Sanctum/OAuth), observability, deployment automation and real operational monitoring rather than more unrelated business modules.
