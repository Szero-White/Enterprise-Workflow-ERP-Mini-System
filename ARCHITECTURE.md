# Architecture

## 1. System shape

The application is a Laravel monolith split by business responsibility rather than by arbitrary technical folders alone.

```text
Route
  → Middleware
  → Controller
  → FormRequest
  → Application / Domain Service
  → Eloquent Models
  → Database
```

The goal is to keep HTTP concerns, validation and business state transitions separate while avoiding unnecessary enterprise patterns for a portfolio-sized system.

## 2. Module boundaries

### Catalog

Owns product categories and products.

Key entities:

- `ProductCategory`
- `Product`

Product records contain master data such as SKU, unit, cost price, sale price and reorder level. Transaction history never depends on mutable product master data; a sales order item stores SKU, product name, unit and unit-price snapshots.

### CRM

Owns customer master data.

Key entity:

- `Customer`

A customer with sales history cannot be hard deleted through the UI. The safer lifecycle is to set `is_active = false`.

### Inventory

Owns warehouses, current stock balances and the append-style movement ledger.

Key entities:

- `Warehouse`
- `InventoryStock`
- `InventoryMovement`

`inventory_stocks` has a unique pair of `warehouse_id + product_id`. It stores the current balance for fast reads.

`inventory_movements` records why a balance changed. The movement and current balance serve different purposes: fast operational reads versus traceability.

### Sales

Owns sales order state and order lines.

Key entities:

- `SalesOrder`
- `SalesOrderItem`

State machine:

```text
draft ──confirm──> confirmed ──cancel──> cancelled
  └────────────────cancel────────────────> cancelled
```

### Workflow

The existing dynamic workflow engine remains independent from sales. It is suitable for leave requests, purchase approval, payment requests, document approval and other internal processes.

## 3. Sales order transaction boundary

### Create draft

`SalesOrderService::createDraft()`:

1. Validates product IDs from the Form Request.
2. Loads active products.
3. Calculates line totals on the backend.
4. Validates discount against subtotal.
5. Creates `sales_orders`.
6. Creates `sales_order_items`.
7. Writes an audit record.

No stock is changed at this step.

### Confirm order

`SalesOrderService::confirm()` performs one database transaction:

1. Locks the order row.
2. Confirms the order is still `draft`.
3. Loads warehouse and order items.
4. For each item, `InventoryStockService` locks the relevant stock row.
5. Rejects the whole operation if any item is short of stock.
6. Deducts stock.
7. Adds inventory movement records.
8. Changes order status to `confirmed`.
9. Writes the audit record.

This prevents a partial-success case such as item A being deducted while item B fails.

### Cancel confirmed order

Cancellation is compensating business logic rather than deleting history:

1. Lock the order.
2. Restore each item to its warehouse balance.
3. Create `sale_cancellation` movements.
4. Mark the order `cancelled`.
5. Keep the original order and its lines for traceability.

## 4. Concurrency and stock integrity

Stock deduction uses `lockForUpdate()` on the row representing a product in a warehouse. This is a pragmatic first protection against two requests reading the same available stock and both trying to consume it.

For a higher-throughput system, the next design step would add stock reservation, idempotency keys and retry handling around deadlocks.

## 5. Validation responsibilities

Form Requests validate HTTP payload shape and simple relational constraints:

- required fields
- numeric ranges
- database existence
- unique SKU / code
- distinct products in order lines

Services validate business rules that require current state:

- active product requirement
- discount cannot exceed subtotal
- only draft orders can be confirmed
- stock must be sufficient at confirmation time
- cancelled order cannot be cancelled twice

## 6. Queue and side effects

Database notifications are persisted synchronously because they are part of the product experience.

Optional realtime delivery is asynchronous:

```text
NotificationService
  → save notification
  → SendRealtimeNotification::dispatch(...)->afterCommit()
  → queue worker
  → RealtimeNotificationService
```

This avoids holding an approval or sales transaction open while waiting for an external HTTP service.

## 7. Authorization

Current access control uses role middleware:

- `admin`: all system management plus business modules
- `manager`: business modules and approval center
- `employee`: internal request portal
- `hr`, `director`: approval center

The next authorization upgrade should introduce Laravel Policies for resource-specific rules, while role middleware remains useful for broad module access.

## 8. Dashboard query separation

`DashboardController` delegates aggregation to `DashboardDataService` instead of collecting many unrelated database queries inside the controller.

The service provides:

- business summary
- seven-day sales chart data
- recent orders
- low-stock products
- recent workflow requests

This keeps controller code focused on request/response orchestration.

## 9. Internal REST API boundary

The project includes a read-only, versioned JSON surface under `/internal-api/v1`. It is intentionally protected by the existing session authentication plus `admin,manager` module authorization because it serves same-origin/internal consumers.

```text
/internal-api/v1/*
  → auth + role middleware
  → Controllers/Api/V1
  → Eloquent query
  → Resources/Api/V1
  → paginated JSON
```

Dedicated API Resources keep transport formatting separate from Eloquent models. A future external `/api/v1` should add token authentication and OpenAPI documentation instead of weakening authentication on the current endpoints.

## 10. File naming conventions

Names intentionally include the domain responsibility:

```text
Services/Inventory/InventoryStockService.php
Services/Sales/SalesOrderService.php
Services/Dashboard/DashboardDataService.php
Controllers/Inventory/InventoryController.php
Controllers/Sales/SalesOrderController.php
```

Avoid generic names such as `CommonService`, `Helper`, `Manager`, or one service that handles catalog + orders + stock + notifications.

## 11. Testing strategy

Feature tests focus on state transitions and authorization rather than only checking that pages return HTTP 200.

Important test cases:

- workflow authorization
- workflow transition history
- database notification ownership
- sales draft creation
- confirmation stock deduction
- insufficient-stock rollback
- cancellation stock restoration
- role restrictions
- internal API authentication, filtering and resource shape
- immutable order-item product snapshots

## 12. Deployment and delivery

- SQLite remains convenient for a zero-setup local demo and test environment.
- MySQL is supported for deployment and Docker development.
- Database queue is the default so queue concepts work without additional infrastructure.
- Docker Compose separates web app, queue worker and MySQL services.
- GitHub Actions is included as a basic CI gate.
