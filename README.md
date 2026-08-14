# Enterprise Commerce ERP & Workflow System

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![CI](https://img.shields.io/badge/CI-GitHub_Actions-2088FF?logo=githubactions&logoColor=white)](#quality-and-testing)

A Laravel monolith that combines **sales operations, product catalog, customer management, multi-warehouse inventory and configurable internal approval workflows**.

The project is intentionally designed as a portfolio system that is still easy to explain in an interview: controllers stay thin, validation lives in Form Requests, business transactions are coordinated by focused services, and important state changes are covered by audit logs and feature tests.

## Current business modules

### Sales order management

- Create sales orders as `draft`.
- Add multiple product lines with immutable SKU/name/unit/price snapshots.
- Recalculate catalog price, subtotal, discount and order total on the backend; client-supplied prices are ignored.
- Confirm an order only when sufficient stock is available.
- Deduct stock inside a database transaction.
- Cancel a confirmed order and restore stock.
- Search, filter and paginate sales orders.

### Product catalog

- Product categories.
- SKU-based product management.
- Cost price, sale price, unit and reorder level.
- Active/inactive lifecycle instead of deleting products that already have business history.

### Customer / CRM basics

- Customer code and contact information.
- Company and tax information.
- Order count and search.
- Safe delete rules when business transactions already exist.

### Multi-warehouse inventory

- Warehouse management.
- Stock quantity per `warehouse + product`.
- Stock receipt flow.
- Inventory movement ledger.
- Low-stock warning based on product reorder level.
- Pessimistic row locking during sales confirmation to reduce overselling risk.

### Enterprise workflow

The original configurable workflow engine remains a separate module and supports:

- Dynamic form templates and dynamic fields.
- Sequential multi-step approval.
- Role / department / user based approvers.
- Approve, reject, return and resubmit flows.
- Approval history.
- Audit log.
- Database notifications.

### Dashboard

Admin and Manager roles get an operations dashboard with:

- Confirmed revenue.
- Sales order count.
- Active customer and product counts.
- Low-stock alerts.
- Seven-day revenue chart.
- Recent sales orders.
- Workflow summary and recent internal requests.

The UI includes a redesigned enterprise sidebar, card/table system, responsive layouts and light/dark theme toggle.

### Internal REST API v1

The authenticated admin/manager area also exposes read-only JSON endpoints for same-origin/internal integrations:

- `GET /internal-api/v1/products`
- `GET /internal-api/v1/inventory-stocks`
- `GET /internal-api/v1/sales-orders`
- `GET /internal-api/v1/sales-orders/{order}`

The API uses dedicated Laravel API Resources, pagination and a versioned namespace. It intentionally reuses the existing session authentication because it is an internal API. External/mobile consumers should use a separate `/api/v1` surface with Sanctum/JWT/OAuth-style token authentication in a later iteration.

## Architecture

```text
HTTP Request
    ↓
Route + Role Middleware
    ↓
Controller
    ↓
FormRequest
    ↓
Domain-focused Service
    ↓
Eloquent Models
    ↓
Database Transaction / Row Lock where required
    ↓
Audit Log + Queueable side effects
```

Examples:

```text
SalesOrderController
    → SalesOrderStoreRequest
    → SalesOrderService
        → InventoryStockService
        → SalesOrder / SalesOrderItem
        → InventoryStock / InventoryMovement
        → AuditLogService
```

```text
NotificationService
    → persist database notification
    → dispatch SendRealtimeNotification after commit
    → queue worker
    → optional external realtime service
```

See [ARCHITECTURE.md](ARCHITECTURE.md) for design decisions and transaction boundaries.

## Tech stack

**Backend**

- PHP 8.2+
- Laravel 12
- Eloquent ORM
- Form Requests
- Service layer
- PHP backed enums
- Database transactions
- Pessimistic locking (`lockForUpdate`)
- Database queue
- Audit logging

**Frontend**

- Blade
- Bootstrap 5
- Bootstrap Icons
- Chart.js
- Vanilla JavaScript
- Responsive admin dashboard
- Light / dark theme

**Database**

- SQLite for local quick start and automated tests
- MySQL supported and included in Docker Compose

**Engineering tooling**

- PHPUnit feature tests
- Laravel Pint compatible structure
- GitHub Actions CI
- Docker / Docker Compose
- Vite / npm build

## Demo accounts

| Role | Email | Password |
|---|---|---|
| Admin | `admin@example.com` | `password` |
| Manager | `manager@example.com` | `password` |
| Employee | `employee@example.com` | `password` |
| HR | `hr@example.com` | `password` |
| Director | `director@example.com` | `password` |

The seeder also creates sample products, customers, warehouses, inventory balances and confirmed sales orders so the business dashboard is populated immediately.

## Local quick start

```bash
cp .env.example .env
composer install
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
php artisan serve
```

In a second terminal, run the queue worker:

```bash
php artisan queue:work --queue=notifications,default
```

Then open `http://127.0.0.1:8000`.

## Docker quick start

```bash
docker compose up -d --build
docker compose exec app php artisan migrate --seed
```

Application: `http://localhost:8000`

MySQL is exposed on host port `3307` for database tools.

## Quality and testing

Run:

```bash
php artisan test
```

Important feature-test scenarios include:

- Employee workflow submission and approval authorization.
- Approval history and audit logging.
- Notification ownership.
- Sales order draft → confirmation.
- Inventory deduction.
- Insufficient-stock rollback.
- Confirmed-order cancellation and stock restoration.
- Role protection for sales modules.
- Internal REST API authorization and resource response shape.
- Server-side catalog price enforcement and order-item snapshot history.

GitHub Actions installs PHP/npm dependencies, builds frontend assets and runs the Laravel test suite on pushes and pull requests.

## Business consistency rules worth discussing in interviews

1. A sales order starts as a draft, so creating it does not mutate stock.
2. Confirmation locks the sales order and each stock row inside one transaction.
3. If one item lacks stock, the entire confirmation fails and no product is partially deducted.
4. Cancelling a confirmed order creates compensating inventory movements and restores stock.
5. Historical order SKU, name, unit and price are snapshotted on order items instead of depending on mutable product master data.
6. Products, customers and warehouses with transaction history are generally deactivated instead of hard deleted.
7. External realtime notification work is queued after the database transaction commits.

## Suggested next iterations

The current branch focuses on a coherent sales + inventory core instead of adding unrelated CRUD screens. The next valuable increments are documented in [PROJECT_ROADMAP.md](PROJECT_ROADMAP.md), especially:

- Purchase orders and suppliers.
- Laravel Policy/Gate authorization.
- External `/api/v1` with token authentication, OpenAPI documentation and write endpoints.
- Redis cache / queue and Horizon.
- Inventory reservation for concurrent checkout flows.
- Returns / refunds.
- Reporting export.
- CI quality gates such as Pint and static analysis.

## Current hosted demo

The existing deployment is:

`https://workflow-erp.alwaysdata.net`

After pulling this upgraded branch, redeploy and run the new migrations/seeder so the hosted demo matches the commerce modules described above.
