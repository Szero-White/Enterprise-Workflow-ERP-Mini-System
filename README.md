# Enterprise Workflow & Operations Management System

A Laravel 12 portfolio project for **internal enterprise operations**. The system is centered on a configurable Workflow Engine and connects approval-driven Procurement with Inventory and Asset Management.

## Current product direction

```text
Organization
    ↓
Workflow Engine
    ├── HR requests
    ├── Finance requests
    └── Purchase requests
              ↓
         Procurement
              ↓
         Purchase Order
              ↓
         Goods Receipt
              ↓
          Inventory
              ↓
      Asset Management
        ├── Assignment
        └── Return
```

The former Sales / CRM domain has been removed so the product has one coherent internal-operations story.

## Implemented today

### Organization & access control

- Users, departments and roles.
- Role-aware routes and navigation.
- Admin, manager, employee, HR, procurement, finance, director and asset-manager demo roles.

### Configurable Workflow Engine

- Dynamic form templates and fields.
- Workflow templates and ordered approval steps.
- Approve, reject and return-to-employee actions.
- Approval history, notifications and audit logs.
- Leave-request workflow included in the seed data.
- Workflow transition dispatcher keeps domain completion handlers outside approval controllers/services.

### Procurement

- Supplier master.
- Structured Purchase Requests linked to the generic workflow request.
- Manager → Procurement → Finance → Director approval flow.
- Purchase Orders with supplier, warehouse and item snapshots.
- PO issue/cancel lifecycle and replacement of cancelled orders while preserving history.
- Goods Receipts with partial receiving and over-receipt protection.
- Goods receipt automatically posts inventory movements.

### Inventory

- Item master (`Item` / `ItemCategory`) for internal materials and equipment.
- Warehouses and stock by warehouse + item.
- Inventory movement ledger.
- Low-stock alerts.
- Manual stock receipt for non-asset-tracked materials.
- Asset assignment/return movements keep physical warehouse stock synchronized.

### Asset Management

- Items can be marked as asset-trackable.
- Goods Receipt creates one traceable `Asset` record per received unit for asset-tracked items.
- Asset codes preserve the source Goods Receipt and acquisition cost.
- Assignment records employee custody and removes one physical unit from warehouse stock.
- Return records destination warehouse and condition, then restores warehouse stock.
- Returned assets can enter maintenance before becoming available for assignment again.
- Serial number and operational notes can be maintained without changing historical procurement data.

### Internal API v1

- `GET /internal-api/v1/items`
- `GET /internal-api/v1/inventory-stocks`

The API is session-authenticated and intended for internal integration only.

## Architecture principles

- Thin controllers.
- Form Requests for HTTP validation.
- Domain/application services for transaction logic.
- Database transactions and row locks around stock, procurement and asset lifecycle transitions.
- Eloquent models focused on persistence and relationships.
- Blade views do not own business rules.
- Audit and notification behavior remains cross-cutting and reusable.
- No hard-coded controller per dynamic request type; workflow templates drive generic approvals and completion handlers bridge approved requests into domain modules.

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

Demo accounts use password `password`:

```text
admin@example.com
employee@example.com
manager@example.com
procurement@example.com
finance@example.com
director@example.com
asset@example.com
```

## Tests

```bash
php artisan test
```

The distributed source does not contain `vendor/`, so Laravel tests must be executed after `composer install` on the local machine.

## Refactor milestones

- Step 01: remove legacy Sales / CRM. ✅
- Step 02: normalize Product → Item / ItemCategory and Inventory terminology. ✅
- Step 03: add workflow-driven Procurement, Purchase Orders and Goods Receipts. ✅
- Step 04: add Asset registration, assignment, return and maintenance lifecycle. ✅
- Step 05: final hardening, reporting/dashboard cleanup and portfolio documentation.
