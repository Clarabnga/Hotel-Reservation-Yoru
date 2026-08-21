# Render Free Docker deployment

This guide prepares Yoru Hotel for a demo deployment on a Render Free Web Service backed by Supabase PostgreSQL. It does not perform the deployment.

## 1. Prepare Supabase

1. Create or select a Supabase project.
2. Open **Connect** and copy the **Session pooler** connection string. This is the practical option for a persistent application on an IPv4-only network; the direct endpoint requires IPv6 unless the Supabase IPv4 add-on is enabled.
3. Replace the password placeholder in the connection string with the database password.
4. Keep SSL enabled with `DB_SSLMODE=require`.

Use either one URL:

```text
DB_CONNECTION=pgsql
DATABASE_URL=postgresql://postgres.PROJECT_REF:PASSWORD@aws-REGION.pooler.supabase.com:5432/postgres
DB_SSLMODE=require
```

Or the equivalent individual values:

```text
DB_CONNECTION=pgsql
DB_HOST=aws-REGION.pooler.supabase.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.PROJECT_REF
DB_PASSWORD=replace-me
DB_SSLMODE=require
```

Do not set `DB_CONNECTION=sqlite` on Render. Do not commit either connection form.

## 2. Generate the application key

Generate the key locally and copy only the output into Render:

```bash
php artisan key:generate --show
```

Never put the generated value in `.env.example`, the Dockerfile, or Git.

## 3. Create the Render service

In Render, choose **New > Web Service**, connect the repository, and use these exact settings:

| Setting | Value |
| --- | --- |
| Runtime | Docker |
| Branch | Select the reviewed deployment branch explicitly (currently `dev/render-deploy`) |
| Root directory | Leave blank |
| Dockerfile path | `./Dockerfile` |
| Instance type | Free |
| Docker build context | `.` |
| Docker command | Leave blank; use the image `ENTRYPOINT` |
| Health check path | `/health` |
| Auto-deploy | Off for the first deployment; enable later only if desired |

The container listens on `0.0.0.0:$PORT`; Render supplies `PORT` (normally `10000`). The startup script runs the idempotent `php artisan migrate --force`, builds Laravel caches, and then starts FrankenPHP/Caddy. It never runs `migrate:fresh` or seeders.

Render's pre-deploy command is not available to a Free Web Service. On a future paid service, move `php artisan migrate --force` to the pre-deploy command and remove it from `docker/start-container.sh` if migrations must run separately from startup.

## 4. Environment variables

Set these in **Render > Service > Environment**:

```text
APP_NAME=Yoru Hotel
APP_ENV=production
APP_KEY=base64:generated-value
APP_DEBUG=false
APP_URL=https://YOUR-SERVICE.onrender.com
APP_TIMEZONE=Asia/Jakarta
LOG_CHANNEL=stderr
LOG_LEVEL=warning

DB_CONNECTION=pgsql
DATABASE_URL=your-supabase-session-pooler-url
DB_SSLMODE=require

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
```

`DATABASE_URL` may be replaced by the individual `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` variables shown earlier. Keep `APP_DEBUG=false`; the image also defaults it to false. Redis configuration remains available for a future paid worker/cache setup by changing the relevant drivers and supplying the existing `REDIS_*` variables.

## 5. Health and migrations

- Health check: `GET /health`
- Expected response: HTTP 200 with `{"status":"ok"}`
- The endpoint is public, does not query the database, and exposes no configuration.
- Check the first startup logs for successful migration output before treating the service as ready.

## Watchdog and queues

Watchdog is unchanged. Its normal production architecture needs both the scheduler and a persistent queue worker. A Render Free Web Service supplies neither a permanent background worker nor a Free background-worker service, and it may spin down after inactivity. Database queue records can be stored, but they will not process themselves.

For a real deployment, add a paid Render Background Worker running the existing queue command and a scheduler process/cron using the existing Laravel commands. For a demo-only walkthrough, run the existing scheduler and worker locally against a disposable demo database. Combining the web server, scheduler, and worker in one Free container is intentionally not implemented because process restarts and spin-down can silently interrupt jobs.

## Upload persistence

Render Free storage is ephemeral. Room images uploaded through the admin interface can disappear after a restart, spin-down, or deployment. Local upload behavior remains unchanged and missing images fall back to the Yoru placeholder.

Persistent production uploads require object storage such as S3-compatible storage or Supabase Storage, followed by configuring Laravel's filesystem disk. A persistent Render disk is not available on the Free instance type.

## Pre-deploy checklist

```bash
php artisan test
npm run build
composer validate
composer audit
npm audit
./vendor/bin/pint --test
git diff --check
```

After deployment, confirm `/health`, the home page, login, registration, and a read-only room availability search before enabling auto-deploy.
