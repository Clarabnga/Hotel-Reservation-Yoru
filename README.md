# Yoru Hotel

Laravel 12 hotel reservations with Watchdog V2 priority scheduling.

## Local development

Requires PHP 8.2+ (production target: 8.5), Composer, Node.js 22+, npm, and SQLite.

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm ci
npm run build
php artisan storage:link
composer dev
```

Run checks:

```bash
php artisan test --exclude-group postgres
npm run build
composer validate --strict
vendor/bin/pint --test app routes database tests
```

See [Production infrastructure](docs/production-infrastructure.md) for PostgreSQL, Redis, queues, scheduler, mail, CI, environment variables, and the separate concurrency test.
