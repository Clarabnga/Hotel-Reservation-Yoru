# Production infrastructure

This prepares Yoru Hotel for production without deploying it. Store values based on `.env.production.example` in the deployment platform's secret manager; never commit populated secrets.

## Topology

- Laravel 12 / PHP 8.5 HTTP application
- PostgreSQL for application data and all persistent Watchdog jobs/events
- Redis for queues, cache, distributed locks, sessions, and cache-backed rate limiting
- Separate long-running queue worker and scheduler runtime

The HTTP layer may be serverless. `queue:work` and `schedule:work` must run on a VPS/container/worker platform, not inside Vercel request execution.

The production PHP runtime needs the normal Laravel extensions plus `pdo_pgsql` and `redis` (phpredis). The SQLite development/test runtime needs `pdo_sqlite`.

## PostgreSQL and concurrency tests

Set `DB_CONNECTION=pgsql`, the `DB_*` credentials, and the provider-required `DB_SSLMODE` (normally `require`). SQLite remains supported for development and normal tests.

Booking locks same-type room rows in ascending ID order with `SELECT ... FOR UPDATE`. PostgreSQL holds these locks through transaction completion, serializing competing allocation attempts; the later transaction then observes the committed reservation and rejects or reallocates the overlap.

Run the separate test against a disposable PostgreSQL database:

```bash
DB_CONNECTION=pgsql DB_HOST=127.0.0.1 DB_PORT=5432 \
DB_DATABASE=yoru_test DB_USERNAME=yoru DB_PASSWORD=secret DB_SSLMODE=disable \
php artisan test tests/Integration/PostgresBookingLockTest.php
```

Normal tests use `php artisan test --exclude-group postgres`. CI has separate SQLite and PostgreSQL 17 jobs.

## Redis, queues, and locks

Use `CACHE_STORE=redis`, `SESSION_DRIVER=redis`, and `QUEUE_CONNECTION=redis`. Laravel unique-job locks and login rate limiting consequently use Redis. Redis never contains authoritative Watchdog lifecycle or audit history.

Run a supervised worker:

```bash
php artisan queue:work redis --queue=watchdog,default --sleep=1 --tries=1 --timeout=120 --max-time=3600
```

Keep `REDIS_QUEUE_RETRY_AFTER` greater than the worker `--timeout` (the supplied production example uses 180 seconds versus 120 seconds). Run `php artisan queue:restart` after releases. `ExecuteWatchdogJob` is unique by Watchdog job ID and reports actual execution results to PostgreSQL.

## Scheduler

Run exactly one scheduler process, or one cron call per minute:

```bash
php artisan schedule:work
# or: * * * * * cd /app && php artisan schedule:run >> /dev/null 2>&1
```

The scheduler revives due `retry_wait` jobs before claiming work. `withoutOverlapping()` protects scheduler invocations; workers perform actual jobs.

## HTTPS, environment, logging, and mail

Production requires `APP_ENV=production`, `APP_DEBUG=false`, a real HTTPS `APP_URL`, encrypted secure HTTP-only cookies, and correct proxy HTTPS forwarding. Use warning-level daily logs or a managed log drain. Configure real provider credentials under `MAIL_*` and verify the sending domain.

Log Viewer page and API requests are protected by an application callback requiring an authenticated `admin` role.

## Release/startup checklist

1. Inject secrets and validate production configuration.
2. Build with `composer install --no-dev --optimize-autoloader` and `npm ci && npm run build`.
3. Run `php artisan migrate --force` from one controlled release task.
4. Configure persistent/shared uploaded-file storage and run `php artisan storage:link` where applicable.
5. Cache configuration, routes, and views after environment injection.
6. Start HTTP, worker, and scheduler as separate processes.
7. Verify PostgreSQL/Redis backups, worker health, mail, `/up`, and admin-only Log Viewer access.
