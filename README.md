# LINKVIO

LINKVIO is a Laravel-based SEO publishing marketplace. The current foundation includes a Blade public homepage, advertiser/publisher registration, email verification and password recovery, role-protected workspaces, account settings, publisher website submissions, admin approval/rejection, and an approved-only publisher directory with validated filters. Orders, payments, payouts, and the remaining administration workflows are not implemented yet.

## Requirements

- PHP 8.2 or newer with `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, and `zip`
- Composer
- MySQL 8 or compatible
- Node.js and npm

The frontend uses Blade, Bootstrap 5 CSS, custom CSS, and vanilla JavaScript. No SPA framework or Bootstrap JavaScript is used.

## Local setup with XAMPP

1. Start Apache and MySQL from the XAMPP Control Panel.
2. Create a MySQL database named `linkmetrix` with `utf8mb4` character set and collation (or run the SQL below in phpMyAdmin):

   ```sql
   CREATE DATABASE linkmetrix CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. Copy `.env.example` to `.env`. Set `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` to match your local MySQL account. The example values use XAMPP's common `root` account with no password; do not use those credentials in production.
4. Install dependencies and initialize the app:

   ```powershell
   composer install
   php artisan key:generate
   npm install
   php artisan migrate
   npm run build
   ```

5. Start the Laravel development server:

   ```powershell
   php artisan serve
   ```

   Open `http://127.0.0.1:8000`. Use `npm run dev` in a separate terminal when changing CSS or JavaScript.

Local mail uses Laravel's `log` mailer by default. Verification and reset links are written to `storage/logs/laravel.log`. Configure a trusted SMTP provider in `.env` before enabling real email delivery.

## Roles and access

Public registration permits only `advertiser` and `publisher` roles. Admin is never selectable from registration. A trusted operator must assign admin access to a verified account through a controlled deployment/maintenance process. Role-specific dashboards require authentication and verified email; suspended accounts are logged out of protected areas.

Advertiser order processing, checkout, payment verification, payouts, and most admin management screens are future phases.

## Marketplace data

Only active websites with `status = approved` appear in public search or on the homepage. The initial database includes publisher and advertiser profiles, website categories, and website listings. SEO metrics are nullable and display as “Not available” until imported from a real provider. Do not seed invented metrics or use sample listings as live inventory.

## Tests and checks

Tests use an in-memory SQLite database, separate from the configured MySQL database:

```powershell
php artisan test
npm run build
npm audit
```

## Production deployment

- Use PHP/MySQL versions and extensions supported by the deployed Laravel 12 application.
- Set `APP_ENV=production`, `APP_DEBUG=false`, a production `APP_URL`, and a unique `APP_KEY`.
- Use a dedicated least-privilege MySQL user and secret values stored outside source control.
- Configure production mail, queue, cache, and session services; do not use the local `log` mailer or development defaults.
- Build frontend assets with `npm ci && npm run build`.
- Run `php artisan migrate --force`, then `php artisan optimize` as part of deployment.
- Serve Laravel through the `public` directory, enable HTTPS, and establish monitored database/file backups.
- Run queue workers under a process supervisor when queued jobs are introduced.
