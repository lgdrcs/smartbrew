# SmartBrew Cafè

A PHP web app for a cafe: customers register, browse a menu, add items to a cart, check
out (with a loyalty stamp/redeem system), and get a personal QR code. Admins log in
separately to view paginated transactions and generate PDF reports.

The app runs on plain PHP (no framework) and stores data in a [Supabase](https://supabase.com)
(Postgres) database.

## Prerequisites

- **PHP** (standalone, from [php.net](https://www.php.net/downloads)) with the `pdo_pgsql`
  and `pgsql` extensions enabled — in `php.ini`, uncomment:
  ```
  extension=pdo_pgsql
  extension=pgsql
  ```
  Verify with `php -m` (should list `pdo_pgsql`).
- **[Composer](https://getcomposer.org/)**
- A **Supabase** project (free tier is fine) — [supabase.com](https://supabase.com)

## Setup

1. Install PHP dependencies:
   ```
   composer install
   ```
2. Create the database schema: open your Supabase project → SQL Editor → paste and run
   the contents of `supabase_schema.sql`.
3. Configure credentials: copy `.env.example` to `.env` and fill in your Supabase database
   connection details (Supabase dashboard → Project Settings → Database → Connection info,
   **Session pooler**, port `5432`):
   ```
   cp .env.example .env
   ```
4. Serve the app locally with PHP's built-in server, from the project root:
   ```
   php -S localhost:8000
   ```
   Then open `http://localhost:8000/DashboardSB.php`.

## Admin access

There's no seeded admin account. Visit `Admin/AdminRegister.php` once to create one, then
log in at `Admin/Admin.php`.

## Project structure

- Root (`Login.php`, `Register.php`, `Menu.php`, `Cart.php`, `Checkout.php`, `QR.php`,
  `Order_Receipt.php`) — customer-facing pages.
- `Admin/` — admin login, dashboard, transactions list, and PDF report generation.
- `db.php` — shared database connection, reads config from `.env`.
- `supabase_schema.sql` — table definitions to run once in Supabase.
- `uploads/receipts/` — uploaded GCash/Maya payment screenshots (gitignored).

## Future ideas

- **Docker**: since config already lives in `.env`, containerizing later is just adding a
  `Dockerfile` + `docker-compose.yml` — no application code changes needed.
