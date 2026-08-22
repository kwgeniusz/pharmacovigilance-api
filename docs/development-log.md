# Development Log

This document records the implementation in Conventional Commit groups. Commits were created locally with explicit file staging; no blanket `git add .` command was used.

Shared files can appear in more than one group when a later feature extends an earlier implementation; they should be staged from the working tree in this sequence.

## 1. `chore: scaffold Laravel API`

**Purpose:** Create the Laravel 13 application with Pest and initialize its independent Git repository.

**Official commands executed:**

```bash
composer global require laravel/installer
laravel new pharmacovigilance-api --database=mysql --pest --no-node --no-boost --no-authentication
php artisan install:api --no-interaction
git init -b main
```

**Files created:** `.editorconfig`, `.gitattributes`, `.gitignore`, `.npmrc`, `artisan`, `package.json`, `phpunit.xml`, `vite.config.js`, `app/Http/Controllers/Controller.php`, `app/Providers/AppServiceProvider.php`, `bootstrap/cache/.gitignore`, `bootstrap/providers.php`, `config/app.php`, `config/auth.php`, `config/cache.php`, `config/database.php`, `config/filesystems.php`, `config/logging.php`, `config/mail.php`, `config/queue.php`, `config/services.php`, `config/session.php`, `database/.gitignore`, `database/migrations/0001_01_01_000001_create_cache_table.php`, `database/migrations/0001_01_01_000002_create_jobs_table.php`, `public/.htaccess`, `public/favicon.ico`, `public/index.php`, `public/robots.txt`, `resources/css/app.css`, `resources/js/app.js`, `resources/views/welcome.blade.php`, `routes/console.php`, `routes/web.php`, `storage/app/.gitignore`, `storage/app/private/.gitignore`, `storage/app/public/.gitignore`, `storage/framework/.gitignore`, `storage/framework/cache/.gitignore`, `storage/framework/cache/data/.gitignore`, `storage/framework/sessions/.gitignore`, `storage/framework/testing/.gitignore`, `storage/framework/views/.gitignore`, `storage/logs/.gitignore`, `tests/Pest.php`, and `tests/TestCase.php`.

**Files modified:** None; this is the generated baseline.

**Verification commands and results:** `php artisan --version` reported Laravel Framework 13; dependency installation completed; `git branch --show-current` reported `main`. The initial migration invoked by `install:api` was intentionally deferred until Sail MySQL was running.

**Status:** Created as `bdd595a`.

## 2. `chore: configure Laravel Sail environment`

**Purpose:** Provide reproducible PHP 8.5, MySQL 8.4, and Mailpit services and environment defaults.

**Official commands executed:**

```bash
composer require laravel/sail --dev --no-interaction
php artisan sail:install --with=mysql,mailpit --no-interaction
php artisan sail:publish --no-interaction
```

**Files created and retained:** `compose.yaml`, `docker/8.5/Dockerfile`, `docker/8.5/php.ini`, `docker/8.5/start-container`, `docker/8.5/supervisord.conf`, and `docker/mysql/create-testing-database.sh`. Sail also published PHP 8.0–8.4, MariaDB, and PostgreSQL assets; they are not referenced by `compose.yaml` and were intentionally excluded from the commit.

**Files modified:** `.env.example`, `bootstrap/app.php`, `composer.json`, `composer.lock`, and `docker/8.5/Dockerfile`. The published PHP 8.5 runtime was narrowed to backend dependencies; Node, Playwright, FFmpeg, non-MySQL clients, and unrelated PHP extensions were removed because the Vue application belongs to a separate repository. `bootstrap/app.php` was included here because it was omitted from the initial scaffold commit.

**Verification commands and results:** `WWWUSER=1000 WWWGROUP=1000 docker compose up -d --build` built `sail-8.5/app`; `docker compose ps` reported the application on `8000` and healthy MySQL 8.4 and Mailpit services; `php --version` inside the application container reported PHP 8.5.9. MySQL is published on host port `3308` because an unrelated existing container already owns `3306`; port `5173` remains free for the separate Vue repository.

**Status:** Created as `6cc9843`.

## 3. `feat: add pharmacovigilance data model`

**Purpose:** Add users, customers, medications, orders, order items, relationships, factories, and deterministic sample data.

**Official commands executed:**

```bash
php artisan make:model Customer --migration --factory --seed --no-interaction
php artisan make:model Medication --migration --factory --seed --no-interaction
php artisan make:model Order --migration --factory --seed --no-interaction
php artisan make:model OrderItem --migration --factory --seed --no-interaction
php artisan make:test DatabaseModelTest --pest --no-interaction
```

**Files created:** `app/Models/Customer.php`, `app/Models/Medication.php`, `app/Models/Order.php`, `app/Models/OrderItem.php`, `database/factories/CustomerFactory.php`, `database/factories/MedicationFactory.php`, `database/factories/OrderFactory.php`, `database/factories/OrderItemFactory.php`, `database/migrations/2026_08_21_192308_create_customers_table.php`, `database/migrations/2026_08_21_192309_create_medications_table.php`, `database/migrations/2026_08_21_192310_create_orders_table.php`, `database/migrations/2026_08_21_192311_create_order_items_table.php`, `database/seeders/CustomerSeeder.php`, `database/seeders/MedicationSeeder.php`, `database/seeders/OrderSeeder.php`, `database/seeders/OrderItemSeeder.php`, and `tests/Feature/DatabaseModelTest.php`.

**Files modified:** `app/Models/User.php`, `database/factories/UserFactory.php`, `database/migrations/0001_01_01_000000_create_users_table.php`, and `database/seeders/DatabaseSeeder.php`.

**Verification commands and results:** `./vendor/bin/sail artisan migrate:fresh --seed --no-interaction` completed all eight migrations and four entity seeders against MySQL 8.4. The database/relationship tests passed as part of the complete Pest run.

**Status:** Created as `b3a9d33`.

## 4. `feat: add session authentication endpoints`

**Purpose:** Add Sanctum stateful login, logout, current-user, CSRF, CORS, and unauthenticated behavior.

**Official commands executed:**

```bash
php artisan config:publish cors
php artisan make:controller Api/AuthController --no-interaction
php artisan make:request LoginRequest --no-interaction
php artisan make:resource UserResource --no-interaction
php artisan make:test AuthenticationTest --pest --no-interaction
```

**Files created:** `config/cors.php`, `config/sanctum.php`, `database/migrations/2026_08_21_192157_create_personal_access_tokens_table.php`, `app/Http/Controllers/Api/AuthController.php`, `app/Http/Requests/LoginRequest.php`, `app/Http/Resources/UserResource.php`, and `tests/Feature/AuthenticationTest.php`.

**Files modified:** `bootstrap/app.php`.

**Verification commands and results:** `./vendor/bin/sail artisan test` passed login, invalid credentials, profile, logout, and unauthenticated request coverage as part of 18 passing tests.

**Status:** Created as `afb751a`.

## 5. `feat: add medication and order search`

**Purpose:** Add exact lot lookup, inclusive rolling date filtering, newest-first pagination, and native Laravel Resources.

**Official commands executed:**

```bash
php artisan make:controller Api/MedicationController --no-interaction
php artisan make:controller Api/OrderController --no-interaction
php artisan make:request SearchMedicationRequest --no-interaction
php artisan make:request SearchOrderRequest --no-interaction
php artisan make:resource MedicationResource --no-interaction
php artisan make:resource OrderResource --no-interaction
php artisan make:resource OrderCollection --collection --no-interaction
php artisan make:resource OrderItemResource --no-interaction
php artisan make:resource CustomerResource --no-interaction
php artisan make:test MedicationSearchTest --pest --no-interaction
php artisan make:test OrderSearchTest --pest --no-interaction
```

**Files created:** `app/Http/Controllers/Api/MedicationController.php`, `app/Http/Controllers/Api/OrderController.php`, `app/Http/Requests/SearchMedicationRequest.php`, `app/Http/Requests/SearchOrderRequest.php`, `app/Http/Resources/MedicationResource.php`, `app/Http/Resources/OrderResource.php`, `app/Http/Resources/OrderCollection.php`, `app/Http/Resources/OrderItemResource.php`, `app/Http/Resources/CustomerResource.php`, `app/Queries/Orders/SearchOrders.php`, `tests/Feature/MedicationSearchTest.php`, and `tests/Feature/OrderSearchTest.php`.

**Files modified:** None.

**Verification commands and results:** `./vendor/bin/sail artisan test` passed exact `lot_number` search, required input, rolling 30-day defaults, inclusive boundaries, reversed-range validation, matching-item filtering, newest-first ordering, and pagination coverage.

**Status:** Created as `c485b58`.

## 6. `feat: add order and customer details`

**Purpose:** Expose authenticated order and customer detail endpoints with eager-loaded relationships and native missing-model responses.

**Official commands executed:**

```bash
php artisan make:controller Api/CustomerController --no-interaction
php artisan make:test OrderDetailsTest --pest --no-interaction
php artisan make:test CustomerDetailsTest --pest --no-interaction
```

**Files created:** `app/Http/Controllers/Api/CustomerController.php`, `tests/Feature/OrderDetailsTest.php`, and `tests/Feature/CustomerDetailsTest.php`.

**Files modified:** `app/Http/Controllers/Api/OrderController.php`, `app/Http/Resources/CustomerResource.php`, and `app/Http/Resources/OrderResource.php`.

**Verification commands and results:** `./vendor/bin/sail artisan test` passed order/customer detail, relationship serialization, authentication, and native `404` coverage.

**Status:** Created as `d797a10`.

## 7. `feat: add buyer email alerts`

**Purpose:** Resolve alert data from MySQL, enforce order-lot membership, and deliver the English recall warning through configurable SMTP.

**Official commands executed:**

```bash
php artisan make:controller Api/AlertController --no-interaction
php artisan make:request SendAlertRequest --no-interaction
php artisan make:mail MedicationRecallAlert --markdown=mail.medication-recall-alert --no-interaction
php artisan make:test AlertDeliveryTest --pest --no-interaction
```

**Files created:** `app/Actions/Alerts/SendMedicationRecallAlert.php`, `app/Http/Controllers/Api/AlertController.php`, `app/Http/Requests/SendAlertRequest.php`, `app/Mail/MedicationRecallAlert.php`, `resources/views/mail/medication-recall-alert.blade.php`, `tests/Feature/AlertDeliveryTest.php`, and `routes/api.php`.

**Files modified:** None.

**Verification commands and results:** `./vendor/bin/sail artisan test` passed fake-mail recipient, medication, lot, and rejection assertions. A seeded order was sent through the real SMTP configuration; Mailpit `/api/v1/messages` returned one message to `alice@example.test` with subject `Important Medication Recall Notice - Lot 951357` and the expected order/lot snippet.

**Status:** Created as `b327848`.

## 8. `docs: document API setup and architecture`

**Purpose:** Document Sail setup, architecture, credentials, stateful authentication, endpoints, examples, assumptions, tests, and commit preparation.

**Official commands executed:** None; documentation was authored directly.

**Files created:** `docs/development-log.md` and `docs/pharmacovigilance-api.postman_collection.json`.

**Files modified:** `README.md`.

**Verification commands and results:** `./vendor/bin/sail pint --test` passed; `./vendor/bin/sail artisan test` passed 18 tests with 66 assertions; `./vendor/bin/sail artisan route:list --path=api` showed all eight API routes; the Postman Collection v2.1 JSON passed syntax validation and covers authentication, pharmacovigilance, validation, email, and logout flows; `git status --short --untracked-files=all` showed only untracked implementation files and no staged entries.

**Status:** Created as the final documentation commit in this sequence; see `git log` for its resulting hash.

## Final verification summary

| Command | Result |
| --- | --- |
| `WWWUSER=1000 WWWGROUP=1000 docker compose up -d --build` | Sail PHP 8.5 image built; application, MySQL, and Mailpit started. |
| `./vendor/bin/sail artisan migrate:fresh --seed --no-interaction` | Eight migrations and deterministic seed data completed on MySQL 8.4. |
| `./vendor/bin/sail pint --test` | Passed. |
| `./vendor/bin/sail artisan test` | 18 passed, 66 assertions. |
| `./vendor/bin/sail artisan route:list --path=api` | Eight API routes registered. |
| Mailpit API check | One real seeded warning email received with the expected recipient, subject, lot, and order content. |
| `git status --short --untracked-files=all` | All application and documentation files are committed; only unused assets produced by `sail:publish` remain outside Git. |
