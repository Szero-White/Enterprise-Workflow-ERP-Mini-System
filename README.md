# Enterprise Workflow & Operations Management System

A Laravel 12 portfolio project for **internal enterprise operations**. The system is centered on a configurable Workflow Engine and is being expanded toward Procurement, Inventory and Asset Management.

## Current product direction

```text
Organization
    ↓
Workflow Engine
    ├── HR requests
    ├── Finance requests      (next phase)
    └── Purchase requests     (next phase)
                              ↓
                         Procurement
                              ↓
                         Goods Receipt
                              ↓
                          Inventory
                              ↓
                            Assets
```

The former Sales / CRM domain has been removed so the product has one coherent business story.

## Implemented today

### Organization & access control

- Users, departments and roles.
- Role-aware routes and navigation.
- Admin, manager, employee, HR and director demo roles.

### Configurable Workflow Engine

- Dynamic form templates and fields.
- Workflow templates and ordered approval steps.
- Approve, reject and return-to-employee actions.
- Approval history, notifications and audit logs.
- Leave-request workflow included in the seed data.

### Inventory foundation

- Item catalog (temporarily still named `Product` in Step 01; renamed in Step 02).
- Item categories.
- Warehouses.
- Stock by warehouse + item.
- Inventory movement ledger.
- Low-stock alerts.
- Manual stock receipt as a temporary foundation before Procurement is connected.

### Internal API v1

- `GET /internal-api/v1/products`
- `GET /internal-api/v1/inventory-stocks`

The API is session-authenticated and intended for internal integration only.

## Architecture principles

- Thin controllers.
- Form Requests for HTTP validation.
- Domain/application services for transaction logic.
- Eloquent models focused on persistence and relationships.
- Blade views do not own business rules.
- Audit and notification behavior remains cross-cutting and reusable.
- No hard-coded workflow for each request type; templates drive approval flows.

## Local setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate

touch database/database.sqlite
php artisan migrate:fresh --seed
npm run build
php artisan serve
```

Demo login:

```text
admin@example.com
password
```

## Tests

```bash
php artisan test
```

The uploaded source does not contain `vendor/`, so automated Laravel tests must be executed after `composer install` on the local machine.

## Refactor plan

- Step 01: remove legacy Sales / CRM. ✅
- Step 02: rename Product catalog to Item / ItemCategory and remove sales-oriented fields.
- Step 03: implement Supplier + Purchase Request + Purchase Order + Goods Receipt.
- Step 04: connect completed Purchase Request workflow to Procurement.
- Step 05: implement Asset + Assignment / Return, then final cleanup and test pass.

See [STEP_01_REMOVE_SALES_CRM.md](STEP_01_REMOVE_SALES_CRM.md) for the first cleanup increment.
