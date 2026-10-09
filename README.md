# LINKVIO

LINKVIO is a Laravel-based SEO publishing marketplace. The current foundation includes a Blade public homepage, advertiser/publisher registration, email verification and password recovery, role-protected workspaces, account settings, publisher website submissions, admin approval/rejection, and an approved-only publisher directory with validated filters. Orders, payments, payouts, and the remaining administration workflows are not implemented yet.

Administrators can create and manage SEO blog posts in the protected admin workspace. Published posts are listed at `/blog`, individual articles have canonical URLs and Article structured data, and `/blog/sitemap.xml` lists published articles.

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

Public registration permits only `advertiser` and `publisher` roles. Privileged roles are never selectable from registration. A trusted operator grants super administrator access to a verified account through a controlled deployment/maintenance process. Existing `admin` role records remain a backward-compatible full-access alias. Role-specific dashboards require authentication and verified email; suspended accounts are logged out of protected areas.

To grant local super administrator access, create an account normally, verify its email, then run `php artisan app:make-admin your-email@example.com` and confirm the prompt. Sign in at `/login` with that account's own email and password, then open `/admin/dashboard`. There are no built-in administrator credentials; do not add shared default passwords or publish credentials in the repository.

Advertiser order processing, checkout, payment verification, payouts, and most other admin management screens are future phases.

## Marketplace data

Only active websites with `status = approved` appear in public search or on the homepage. The initial database includes publisher and advertiser profiles, website categories, and website listings. SEO metrics are nullable and display as “Not available” until imported from a real provider. Do not seed invented metrics or use sample listings as live inventory.

## Publisher workspace

Verified publishers can edit their business profile, submit and update website listings, see moderation notes, and pause or resume an approved listing. They can also submit support requests and see only their own requests. Publisher order acceptance, content-file access, delivery verification, earnings, and payouts are not active until their order and financial workflows are implemented.

## Content writer workflow

Run migrations and seed the starter article categories with `php artisan migrate` and `php artisan db:seed --class=Database\Seeders\ArticleCategorySeeder`. A super administrator can create SEO manager and content writer accounts at `/admin/writers`; Laravel sends the normal email verification notification, and each person signs in through the existing `/login` page. SEO managers create briefs, assign writers, manage keywords and metadata, and approve or request changes. Writers can only edit their assigned draft or changes-requested articles and submit them for review. Only a super administrator (including existing legacy `admin` accounts) can publish an SEO-approved article. Rich text is sanitized on save and render; featured images are stored on Laravel's public disk, so configure `php artisan storage:link` for local image URLs.

## Local demo accounts

To populate a local development database with demo admin, publisher, and seller (advertiser) accounts plus sample listings, set `DEMO_ACCOUNT_PASSWORD` in `.env` to a strong password of at least 12 characters, then run:

```powershell
php artisan db:seed --class=Database\Seeders\DemoDataSeeder
```

All five accounts use the password configured in `DEMO_ACCOUNT_PASSWORD` and have verified email addresses. The current local demo password is `04463DC98F6D6639C1FF5FC991899807D8DA694F`:

| Role | Email | Password |
|---|---|---|
| Super Admin | `demo-admin@linkvio.test` | `04463DC98F6D6639C1FF5FC991899807D8DA694F` |
| SEO Manager | `demo-seo-manager@linkvio.test` | `04463DC98F6D6639C1FF5FC991899807D8DA694F` |
| Content Writer | `demo-content-writer@linkvio.test` | `04463DC98F6D6639C1FF5FC991899807D8DA694F` |
| Publisher | `demo-publisher@linkvio.test` | `04463DC98F6D6639C1FF5FC991899807D8DA694F` |
| Seller (Advertiser) | `demo-seller@linkvio.test` | `04463DC98F6D6639C1FF5FC991899807D8DA694F` |

Sign in at `/login`. The password column reflects the current local seed data; if you change `DEMO_ACCOUNT_PASSWORD`, reseed the demo data to update the accounts. These credentials are for local development only—do not use them in production or share them publicly. This seeder is restricted to local and testing environments. Its sample websites use reserved `.test` domains, remain pending admin review, and contain no fabricated SEO metrics; they are not exposed as approved public inventory.

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
