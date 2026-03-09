# SQL Lead Management System – Laravel

PHP Laravel application for the SQL Sales Management System. Serves the full UI (login, admin and dealer dashboards) and the REST API.

## Requirements

- PHP 8.2+
- Composer
- PostgreSQL (same database as your existing schema)

## Setup

1. **Copy environment and configure database**
   ```bash
   cp .env.example .env
   ```
   Edit `.env`: set `DB_CONNECTION=pgsql` and either `DB_URL=postgres://user:password@host:5432/dbname` or individual `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.

2. **Install dependencies and generate key**
   ```bash
   composer install
   php artisan key:generate
   ```

## Run

```bash
php artisan serve
```

Then open **http://localhost:8000** in your browser.

- **Login:** Use **ADMIN** / **ADMIN** or **DEALER** / **DEALER** for demo, or real user credentials from the database.
- **Admin:** Dashboard, Inquiries, Dealers, Rewards, Reports, History, Full Database.
- **Dealer:** Dashboard, Demo Schedule.

## API (optional)

The same app exposes REST endpoints under `/api/*` (e.g. `/api/status`, `/api/login`, `/api/products`, etc.) for use by other clients if needed.

## Project layout

- `app/Http/Controllers/` – AuthController (login/logout), AdminController, DealerController, ApiController
- `routes/web.php` – Web routes (login, admin/*, dealer/*)
- `routes/api.php` – REST API routes
- `resources/views/` – Blade templates (auth, admin, dealer, layouts, partials)
- `public/css/app.css` – App styles
- `public/` – Images (sql-logo.png, etc.)
