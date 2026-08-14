# Enterprise Workflow & Operations Management System

A Laravel 12 portfolio project for **internal enterprise operations**. The application uses a configurable Workflow Engine as its core and connects approval-driven Procurement with Inventory and Asset Management.

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

The project intentionally focuses on **internal workflows and operations**. Legacy Sales / CRM concepts were removed so the domain tells one coherent enterprise story.

## Main modules

### Organization & access control

- Users, departments and roles.
- Demo roles: Admin, Employee, Manager, HR, Procurement, Finance, Director and Asset Manager.
- Coarse-grained route protection through role middleware.
- Model-level policies for purchase requests, employee workflow requests and asset lifecycle actions.
- Dashboard data is scoped to the authenticated user's responsibilities instead of exposing every workflow request.

### Configurable Workflow Engine

- Dynamic form templates and fields.
- Workflow templates with ordered approval steps.
- Approver resolution by user, role or department.
- Approve, reject and return-for-edit actions.
- Approval history, notifications and audit logs.
- Generic workflow requests remain independent from Procurement.
- `WorkflowTransitionDispatcher` invokes domain-specific completion handlers without putting Procurement logic inside `ApprovalService`.

### Procurement

- Supplier master data.
- Structured Purchase Requests linked one-to-one with the generic workflow request.
- Purchase Request item snapshots preserve SKU, name, unit, quantity and estimated cost.
- Purchase Orders preserve supplier/warehouse context and procurement item snapshots.
- Draft → Issued → Partially Received → Received lifecycle.
- Cancelled POs remain in history and may be replaced by a new active PO.
- Partial receiving is supported.
- Over-receipt is rejected transactionally.
- Goods Receipt date cannot precede PO issue time.

### Inventory

- `Item` / `ItemCategory` master data for internal materials and equipment.
- Warehouses and stock positions by warehouse + item.
- Append-only inventory movement ledger.
- Low-stock detection using reorder levels.
- Manual receipt for non-asset-tracked materials.
- Service-level validation protects stock mutations even when operations are called outside a controller.
- Procurement receipts, asset assignments and asset returns all create traceable stock movements.

### Asset Management

- Item master can mark equipment as `is_asset_trackable`.
- Goods Receipt creates one `Asset` record per received physical unit.
- Each asset keeps acquisition source, warehouse, cost, condition and optional serial number.
- Assignment records employee custody and removes one physical unit from warehouse stock.
- Return restores stock to a selected warehouse.
- Returned equipment may enter Maintenance before becoming Available again.
- Duplicate assignment, fractional tracked-item receiving and invalid lifecycle dates are rejected.

### Dashboard & reporting

The dashboard is role-aware:

- Admin: organization and global workflow overview.
- Employees / approvers: own requests and approvals requiring their action.
- Procurement / Admin: Purchase Request and Purchase Order operational metrics.
- Inventory roles: active items, warehouses, stock positions, low-stock alerts and recent movements.
- Asset Manager / Admin: total, available, assigned and maintenance asset counts.

## Architecture

```text
HTTP Request
    │
    ▼
Form Request validation
    │
    ▼
Controller
    │
    ├── Gate / Policy authorization
    │
    ▼
Application / Domain Service
    │
    ├── DB transaction
    ├── row-level locking where concurrent writes matter
    ├── domain validation
    ├── audit logging
    └── workflow / inventory / asset integrations
    │
    ▼
Eloquent Models + Database
```

Important application boundaries:

```text
app/Services/Workflow/
app/Services/Procurement/
app/Services/Inventory/
app/Services/Asset/
app/Policies/
app/Support/Navigation/
```

Controllers coordinate HTTP concerns. Transactional business logic lives in services. Models focus on persistence, relationships and casts. Blade templates render state rather than owning business rules.

## Important business invariants

- A workflow request can only be acted on by the current configured approver.
- Approvers cannot open unrelated approval records through a guessed URL.
- An employee cannot view another employee's private request.
- A Purchase Order cannot be created until its Purchase Request has completed approval.
- Only one non-cancelled PO can be active for a Purchase Request at a time.
- Goods Receipt cannot exceed outstanding PO quantity.
- Goods Receipt cannot predate PO issue time.
- Stock cannot become negative through an inventory issue operation.
- Asset-tracked items cannot be manually received through the quick stock-receipt flow.
- Asset-tracked Goods Receipt quantities must be whole physical units.
- An asset cannot have two active assignments.
- Assignment cannot predate asset acquisition.
- Return cannot predate assignment.
- Maintenance assets cannot be assigned until released back to Available.

## Internal API v1

Session-authenticated endpoints intended for internal integrations:

```text
GET /internal-api/v1/items
GET /internal-api/v1/inventory-stocks
```

## Demo accounts

After `php artisan migrate:fresh --seed`, the seeded accounts use password `password`:

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

The default project configuration can use SQLite for a quick local demo. Configure MySQL/PostgreSQL in `.env` when required.

## Quality checks

```bash
php artisan optimize:clear
php artisan migrate:fresh --seed
php artisan test
```

The feature suite covers workflow authorization and transitions, Procurement ordering/receiving rules, Inventory side effects, Asset lifecycle behavior, role access and UI rendering.

## Portfolio demo scenario

A concise end-to-end scenario for an interview or screen recording:

1. Employee submits a Purchase Request for two laptops.
2. Manager, Procurement, Finance and Director approve the request through the generic workflow engine.
3. Procurement creates and issues a Purchase Order.
4. Supplier delivery is recorded with a Goods Receipt.
5. Warehouse stock increases and two individual Asset records are created automatically.
6. Asset Manager assigns one laptop to an employee; warehouse stock decreases by one.
7. The laptop is returned; warehouse stock increases again.
8. If the returned condition requires maintenance, the asset stays blocked until maintenance is completed.
9. Admin can inspect the related audit trail and operational dashboard.

## Refactor milestones

- Step 01 — Remove legacy Sales / CRM. ✅
- Step 02 — Normalize Product → Item / ItemCategory and Inventory terminology. ✅
- Step 03 — Add workflow-driven Procurement, Purchase Orders and Goods Receipts. ✅
- Step 04 — Add Asset registration, assignment, return and maintenance lifecycle. ✅
- Step 05 — Authorization hardening, role-aware dashboard, lifecycle validation, audit-label cleanup and portfolio documentation. ✅
