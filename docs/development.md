# Local Development

This document describes the local workflow implemented by the current repository.

## Requirements

Install:

- PHP 8.2+
- Composer
- Node.js and npm
- PHP `pdo_sqlite` for the default SQLite database

GitHub Actions currently runs PHP 8.3 and Node.js 20.

Verify the local tools:

```powershell
php --version
composer --version
node --version
npm --version
```

## Recommended Windows Startup

From the project root:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\dev.ps1
```

The launcher:

1. verifies PHP, Composer, Node.js, and npm;
2. installs Composer dependencies if `vendor` is missing;
3. installs npm dependencies if Vite or `concurrently` is missing;
4. creates `.env` from `.env.example` only if `.env` does not exist;
5. generates `APP_KEY` only if it is missing;
6. creates `database/database.sqlite` only when the SQLite database is missing;
7. applies pending migrations;
8. seeds demo data only when a new SQLite database is created;
9. opens separate Backend and Frontend PowerShell terminals for the development processes.

It does not overwrite an existing `.env` and does not run `migrate:fresh`.

## Application URLs

Application:

```text
http://127.0.0.1:8000
```

Vite development asset server:

```text
http://localhost:5173
```

Use port `8000` to access the Laravel application.

## Manual First-Time Setup

### Windows / PowerShell

```powershell
composer install
npm ci
Copy-Item .env.example .env
php artisan key:generate
New-Item database\database.sqlite -ItemType File -Force
php artisan migrate --seed
npm run build
```

### Linux / macOS

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm run build
```

Then start the development processes:

```bash
composer run dev
```

## Daily Development

### Windows

Use the repository launcher:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\dev.ps1
```

The launcher opens two PowerShell windows:

```text
Backend   -> php artisan serve + queue listener
Frontend  -> Vite on http://localhost:5173
```

The application itself is available at:

```text
http://127.0.0.1:8000
```

The launcher keeps Laravel and Vite on fixed ports. If `8000` or `5173` is already occupied, it exits with a clear message so an old development process can be stopped first. Close both spawned terminals, or press `Ctrl+C` in each one, to stop the local development environment.

To follow Laravel logs separately on Windows when needed:

```powershell
Get-Content .\storage\logs\laravel.log -Wait
```

### Environments with `pcntl`

The repository's Composer development command remains available:

```bash
composer run dev
```

That command also starts Laravel Pail. Pail requires the PHP `pcntl` extension, so it is not the default native Windows workflow.

## Database

The default `.env.example` uses SQLite.

Default database file:

```text
database/database.sqlite
```

Apply pending migrations:

```powershell
php artisan migrate
```

Seed demo/reference data:

```powershell
php artisan db:seed
```

Reset all local data only when intentional:

```powershell
php artisan migrate:fresh --seed
```

`migrate:fresh` drops all existing tables before recreating them.

## Demo Accounts

Local seed data uses the password from:

```text
DEMO_PASSWORD=password
```

The seeded accounts include:

```text
admin@example.com
manager@example.com
employee@example.com
hr@example.com
director@example.com
procurement@example.com
finance@example.com
asset@example.com
```

When `DEMO_MODE=true`, the seeder refuses the default password and requires a non-default password of at least 10 characters.

## Demo Safety

The public demo behavior comes from `config/demo.php`, `EnsurePublicDemoSafety`, and dynamic field validation.

When demo mode is enabled:

- normal role/policy authorization remains active;
- write requests are rate-limited;
- dynamic file fields are prohibited when demo uploads are disabled;
- the guarded `demo:reset` command is available for demo resets.

## Tests

Run all tests:

```powershell
php artisan test
```

The PHPUnit configuration uses:

```text
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
CACHE_STORE=array
QUEUE_CONNECTION=sync
SESSION_DRIVER=array
```

The repository test suite covers workflow approval, workflow versioning/lifecycle, dynamic field types, conditional fields, notification business context, security, authorization, procurement, inventory, asset lifecycle, UI rendering, and API behavior.

## Quality Checks

PHP formatting:

```powershell
vendor\bin\pint --test
```

Frontend production build:

```powershell
npm run build
```

Git whitespace check:

```powershell
git diff --check
```

Recommended pre-commit sequence:

```powershell
vendor\bin\pint --test
php artisan test
npm run build
git diff --check
```

### Focused checks for Dynamic Forms and notifications

When changing Dynamic Forms, workflow lifecycle, or notifications, run the focused feature tests before the full suite:

```powershell
php artisan test --filter=DynamicFieldTypeTest
php artisan test --filter=DynamicConditionalFieldTest
php artisan test --filter=WorkflowConfigurationVersioningTest
php artisan test --filter=WorkflowLifecycleTest
php artisan test --filter=WorkflowApprovalTest
```

Notification changes that touch Procurement/Asset hand-offs should also run:

```powershell
php artisan test --filter=PurchaseRequestOrderFlowTest
php artisan test --filter=GoodsReceiptFlowTest
```

The full suite remains the final regression gate.

## Common Issues

### `vite` is not recognized

Install frontend dependencies:

```powershell
npm ci
```

Then run:

```powershell
npm run dev
```

### `No application encryption key has been specified`

For the local application:

```powershell
php artisan key:generate
```

The test configuration already defines a dedicated non-secret test key.

### SQLite driver error

Confirm `pdo_sqlite` is enabled:

```powershell
php -m | Select-String sqlite
```

### Port 8000 or 5173 is already in use

Inspect the listeners:

```powershell
Get-NetTCPConnection -State Listen |
    Where-Object { $_.LocalPort -in 8000,5173 } |
    Select-Object LocalAddress, LocalPort, OwningProcess
```

Inspect the owning process before stopping it:

```powershell
Get-Process -Id <PID>
```

Replace `<PID>` with the numeric process ID returned by the first command. Stop only a confirmed stale Laravel/Node development process.

### Clear Laravel caches

```powershell
php artisan optimize:clear
```

## Files That Must Stay Local

Do not commit:

```text
.env
vendor/
node_modules/
database/*.sqlite
public/build/
```

These paths are already covered by `.gitignore`.
