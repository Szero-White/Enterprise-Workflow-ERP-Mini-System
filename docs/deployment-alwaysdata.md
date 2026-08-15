# Alwaysdata Public Demo Deployment

This runbook deploys the same `Enterprise-Workflow-ERP-Mini-System` repository used for development. `DEMO_MODE` is a safety profile inside the application, not a separate demo codebase.

## Deployment model

- Hosting: alwaysdata managed PHP hosting.
- Web root: `/home/workflow-erp/Enterprise-Workflow-ERP-Mini-System/public/`.
- Application runtime: PHP 8.2+.
- Database: alwaysdata MariaDB/MySQL service through Laravel's `mysql` driver.
- Frontend: Vite production assets under `public/build`.
- Public URL: `https://workflow-erp.alwaysdata.net`.
- Realtime notification bridge: disabled unless `NODE_NOTIFICATION_URL` is configured, so a queue worker is not required for the default public demo.

Do not describe this hosting setup as a self-managed Nginx/PHP-FPM production stack. alwaysdata provides the managed HTTP/PHP runtime.

## 1. Verify the release locally

From PowerShell:

```powershell
cd "D:\Study\Laravel\Enterprise Workflow And ERP Mini System"

git status
php artisan optimize:clear
php artisan migrate:fresh --seed
php artisan test
npm ci
npm run build
```

Smoke-test login, dashboard, Purchase Request, approval, Purchase Order, Goods Receipt, inventory and asset lifecycle pages before committing the release.

## 2. Merge the verified release

Use a feature branch and merge only after all checks pass.

```powershell
git add .
git commit -m "refactor(core): prepare public demo and production deployment"
git push -u origin feat/public-demo-production-readiness

git switch main
git pull origin main
git merge --ff-only feat/public-demo-production-readiness
git push origin main
```

Do not deploy an unverified working tree.

## 3. Prepare alwaysdata access

In the alwaysdata administration interface:

1. Confirm `Web > Sites` still points to `/home/workflow-erp/Enterprise-Workflow-ERP-Mini-System/public/`.
2. Confirm PHP is 8.2 or newer.
3. Under `Databases > MySQL`, note the exact database name, user, password and server.
4. Under `Remote access > SSH/SFTP`, ensure SSH access works.
5. Prefer SSH key authentication once the initial connection is working.

Typical SSH host for this account is:

```text
ssh-workflow-erp.alwaysdata.net
```

## 4. Back up the current public demo before replacement

Connect from PowerShell:

```powershell
ssh workflow-erp@ssh-workflow-erp.alwaysdata.net
```

On the server, create a manual pre-release database backup before the one intentional `migrate:fresh` used to replace the obsolete demo schema:

```bash
mkdir -p ~/release-backups
mysqldump -h MYSQL_HOST -u MYSQL_USER -p MYSQL_DATABASE | gzip > ~/release-backups/pre-public-release-$(date +%Y%m%d-%H%M%S).sql.gz
```

Replace `MYSQL_HOST`, `MYSQL_USER` and `MYSQL_DATABASE` with the exact values from the alwaysdata panel. Do not put the password in shell history; let `-p` prompt for it.

## 5. Prepare a clean release directory

Build the new release beside the currently served directory so the old demo remains available while dependencies compile.

```bash
cd ~
rm -rf Enterprise-Workflow-ERP-Mini-System.next
git clone https://github.com/Szero-White/Enterprise-Workflow-ERP-Mini-System.git Enterprise-Workflow-ERP-Mini-System.next
cd Enterprise-Workflow-ERP-Mini-System.next
```

Create the production environment file:

```bash
cp .env.production.example .env
nano .env
```

Set at minimum:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://workflow-erp.alwaysdata.net

LOG_CHANNEL=daily
LOG_LEVEL=warning
LOG_DAILY_DAYS=7

DB_HOST=MYSQL_HOST_FROM_ALWAYSDATA
DB_PORT=3306
DB_DATABASE=MYSQL_DATABASE_FROM_ALWAYSDATA
DB_USERNAME=MYSQL_USER_FROM_ALWAYSDATA
DB_PASSWORD=MYSQL_PASSWORD_FROM_ALWAYSDATA

DEMO_MODE=true
DEMO_PASSWORD=USE_A_NON_DEFAULT_10_PLUS_CHARACTER_PASSWORD
DEMO_UPLOADS_ENABLED=false
DEMO_MAX_WRITES_PER_MINUTE=15
DEMO_MAX_WRITES_PER_HOUR=60

SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

SECURITY_HSTS_ENABLED=true
SECURITY_HSTS_MAX_AGE=31536000
SECURITY_HSTS_INCLUDE_SUBDOMAINS=false

NODE_NOTIFICATION_URL=
```

Never commit this `.env` file.

## 6. Install backend and build frontend assets

Composer is available on alwaysdata and Node/npm can be selected in the account Environment settings.

```bash
composer install --no-dev --optimize-autoloader --no-interaction
php artisan key:generate
npm ci
npm run build
rm -rf node_modules
```

Verify that Vite produced its manifest:

```bash
test -f public/build/manifest.json && echo "Vite manifest OK"
```

Do not continue if `public/build/manifest.json` is missing.

## 7. Put the prepared release into maintenance mode and swap directories

Still inside the `.next` directory:

```bash
php artisan down
cd ~
```

Keep one short-lived source backup for rollback:

```bash
rm -rf Enterprise-Workflow-ERP-Mini-System.previous
mv Enterprise-Workflow-ERP-Mini-System Enterprise-Workflow-ERP-Mini-System.previous
mv Enterprise-Workflow-ERP-Mini-System.next Enterprise-Workflow-ERP-Mini-System
cd Enterprise-Workflow-ERP-Mini-System
```

The site configuration does not need to change because the final directory name and `/public` path are unchanged.

## 8. Replace the obsolete demo database once

This project has changed domain/schema substantially from the old public demo. After the manual backup, the first replacement deployment may intentionally rebuild this disposable demo database:

```bash
php artisan optimize:clear
php artisan migrate:fresh --seed --force
```

For every normal deployment after this initial replacement, use only:

```bash
php artisan migrate --force
```

Never use `migrate:fresh` for data that must be preserved.

## 9. Warm production caches and bring the application up

```bash
php artisan config:cache
php artisan view:cache
php artisan up
```

`route:cache` is intentionally not part of this runbook: run it only after verifying that every route in the current release is cache-compatible.

## 10. Public-demo reset

The application provides a guarded command:

```bash
php artisan demo:reset --force
```

It only runs when `DEMO_MODE=true`, deletes private workflow demo attachments and rebuilds/seed the disposable demo database.

For a public recruiter demo, register this directly in alwaysdata `Scheduled Tasks` at an off-hours daily time chosen for the account:

```bash
cd /home/workflow-erp/Enterprise-Workflow-ERP-Mini-System && php artisan demo:reset --force
```

Do not create a Laravel scheduler worker merely to check a portfolio checkbox when there are no other application schedules.

## 11. Queue policy

With the provided production template:

```dotenv
NODE_NOTIFICATION_URL=
```

`NotificationService` does not dispatch `SendRealtimeNotification`, so no queue service is required for the default public demo.

If a realtime notification endpoint is enabled later, register a foreground alwaysdata Service such as:

```bash
cd /home/workflow-erp/Enterprise-Workflow-ERP-Mini-System && php artisan queue:work --queue=notifications --sleep=2 --tries=3 --timeout=60
```

Only add that service when there is an actual queued workload.

## 12. Smoke-test the live release

Check `https://workflow-erp.alwaysdata.net/up` first, then run the complete recruiter story:

1. Login as Employee and create a Purchase Request.
2. Login as Manager, Procurement, Finance and Director and approve each step.
3. Create and issue a Purchase Order.
4. Record a Goods Receipt and confirm inventory increases.
5. Confirm asset-trackable goods create individual Assets.
6. Assign and return an Asset and verify stock movement.
7. Login as Admin and verify configuration can be viewed but mutation/create/edit routes are blocked in public demo mode.
8. Verify workflow file upload controls are disabled.
9. Verify incorrect destructive requests receive demo protection and excessive writes receive HTTP 429.
10. Inspect `storage/logs/laravel.log` and the alwaysdata HTTP/PHP logs for new errors.

Check response headers from your own machine:

```powershell
curl.exe -I https://workflow-erp.alwaysdata.net/login
```

Expected baseline headers include `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, and—after HTTPS is confirmed—`Strict-Transport-Security`.

## 13. Rollback

If the new code is unusable, keep the application in maintenance mode and restore the previous source directory plus the pre-release database backup. alwaysdata also maintains provider-side daily backups, but a manual release backup gives a clear rollback point for this migration.

After the live release has been stable and the provider backup window covers it, delete the short-lived `.previous` source copy to reclaim disk space.

## 14. After the deployment is green

Only then update the repository presentation with:

- Live Demo link.
- CI badge.
- Release/version badge.
- 8–9 recruiter-oriented screenshots.
- Short "Quick Recruiter Demo" credentials/flow section.

At that point, stop adding business modules and spend the remaining effort on interview explanation and applications.
