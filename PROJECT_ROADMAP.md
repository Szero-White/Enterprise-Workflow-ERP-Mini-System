# Project Roadmap — Fresher → Junior → Middle

This roadmap prioritizes features that improve both the business story and the engineering story. New work should continue to be added as focused vertical slices with migration + model + request + service + controller + tests, not as giant controllers.

## Implemented now — strong Fresher / Junior portfolio core

- Authentication and role middleware.
- Dynamic internal forms and multi-step workflow approval.
- Audit logs and database notifications.
- Product category and product catalog.
- Customer management.
- Multiple warehouses.
- Current inventory balance and movement history.
- Stock receipt.
- Sales orders with multiple lines.
- Backend price calculation.
- Draft / confirm / cancel order state transition.
- Database transaction and row locking for stock changes.
- Compensating stock restoration on cancellation.
- Queue job for optional realtime notification delivery after commit.
- Dashboard analytics.
- Search / filter / pagination.
- Session-authenticated read-only Internal REST API v1 with Laravel API Resources.
- Order-line product identity and price snapshots for historical accuracy.
- Server-side enforcement of catalog sale price.
- Feature tests.
- Docker Compose.
- GitHub Actions CI.

## Next — Junior target

### 1. Supplier + Purchase Order

Add:

- `Supplier`
- `PurchaseOrder`
- `PurchaseOrderItem`
- statuses: draft / approved / received / cancelled
- receive PO into inventory

This connects purchasing to the existing inventory ledger and makes the ERP story more complete.

### 2. Laravel Policies

Move object-level authorization from scattered checks toward:

- `SalesOrderPolicy`
- `WorkflowRequestPolicy`
- `ProductPolicy`

Keep role middleware for module-level access.

### 3. External REST API with token authentication

Promote the existing internal read API into a separate `/api/v1` integration surface with Sanctum/JWT/OAuth-style token authentication, OpenAPI documentation, consistent error responses and write endpoints for:

- products
- customers
- sales orders
- stock availability

Do not expose an unauthenticated ERP API just to say the project has REST.

### 4. Reporting / export

Add CSV/XLSX exports for:

- revenue by date
- top products
- stock on hand
- inventory movement ledger

Use queued exports when the dataset becomes large.

## Next — Junior+ / Middle talking points

### 5. Redis + Horizon

Move queue/cache from database to Redis, then add Horizon for queue visibility.

### 6. Inventory reservation

For high concurrency, split stock into:

- on hand
- reserved
- available

Add reservation expiry and idempotent order confirmation.

### 7. Returns and refunds

Create explicit return documents and inventory movements instead of editing completed orders.

### 8. API idempotency

For order creation/confirmation endpoints, accept an idempotency key to avoid duplicate processing when clients retry.

### 9. Static analysis and quality gates

Add CI steps for:

- Laravel Pint
- PHPStan / Larastan
- test coverage threshold for critical domain services

### 10. Observability

Add structured logs and correlation IDs around:

- sales order confirmation
- stock mutation
- queued jobs
- external notification calls

## Features intentionally not added yet

Avoid adding microservices, Kafka, Elasticsearch or Kubernetes only as keywords. They are valuable when a real requirement justifies them, but premature infrastructure makes this portfolio harder to run and harder to explain.
