# Yoru Hotel

Laravel 12 hotel reservations with Watchdog V2 priority scheduling.

Public room products are modeled as `RoomType` records backed by physical `Room` inventory. Reservation allocation remains synchronous and locks physical rooms transactionally. Business inquiries persist for admin follow-up, while authenticated guests can submit service requests that enter Watchdog at their membership priority.

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

Watchdog dispatches wrappers onto the dedicated `watchdog` queue. Run its worker explicitly:

```bash
php artisan queue:work --queue=watchdog
```

## Hotel KPI definitions

- Occupancy: occupied room nights / active room nights in the current month.
- ADR: recognized room revenue / occupied room nights.
- RevPAR: recognized room revenue / active room nights.
- Cancellation rate: cancelled reservations / all reservations.
- Average stay: nights across confirmed/completed reservations / those stays.
- Booking lead time: average days between reservation creation and check-in.

Revenue includes confirmed and completed reservations and excludes cancelled stays. Every division safely returns zero when its denominator is zero.

Run checks:

```bash
php artisan test --exclude-group postgres
npm run build
composer validate --strict
vendor/bin/pint --test app routes database tests
```

See [Production infrastructure](docs/production-infrastructure.md) for PostgreSQL, Redis, queues, scheduler, mail, CI, environment variables, and the separate concurrency test.
