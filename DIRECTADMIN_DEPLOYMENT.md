# Paperglow SaaS — Shujaa Host DirectAdmin Production Deployment Guide

This guide details the complete deployment process for running the **Paperglow Multi-Tenant SaaS platform** on **Shujaa Host DirectAdmin** using **PHP 8.2+ / PHP 8.3**, **Apache**, and **MariaDB / MySQL**.

The production stack is **100% PHP 8.2+ / 8.3 + Laravel 11 + Blade + Alpine.js + Tailwind CSS** with **Zero Node.js runtime required** in production.

---

## 1. Prerequisites on Shujaa Host DirectAdmin

1. **PHP Version**: Ensure **PHP 8.3** (or 8.2+) is active for your domain in DirectAdmin (**Account Manager &rarr; PHP Version Selector** &rarr; select **PHP 8.3**).
2. **Required PHP Extensions** (standard on Shujaa Host):
   - `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `curl`, `zip`.
3. **MariaDB Database**:
   - Go to DirectAdmin &rarr; **MySQL Management** &rarr; **Create New Database**.
   - Note your Database Name (e.g., `papergl1_saas`), Username (e.g., `papergl1_user`), and Password.

---

## 2. Directory Layout & Document Root Setup

In DirectAdmin, standard web files reside in `/home/<username>/domains/paperglow.co.ke/public_html`.

Because Laravel uses a `public/` directory for front-controller security, use one of the two standard setups:

### Method A: Point Document Root to `public` (Recommended)
In DirectAdmin under **Custom HTTPD Configurations** (or by asking Shujaa Host support), set Document Root to:
```
/home/<username>/domains/paperglow.co.ke/public_html/public
```

### Method B: Symlink / Root .htaccess
If document root cannot be changed, create an `.htaccess` in `public_html/`:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```
All Laravel application files (`app/`, `bootstrap/`, `config/`, `database/`, `resources/`, `routes/`, `storage/`, `vendor/`) reside in `public_html/`, and incoming traffic routes cleanly to `public_html/public/index.php`.

---

## 3. Production `.env` Configuration

Create `.env` by copying `.env.example`:

```bash
cp .env.example .env
php artisan key:generate
```

Configure your MariaDB credentials in `.env`:

```ini
APP_NAME=Paperglow
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://paperglow.co.ke

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=papergl1_saas
DB_USERNAME=papergl1_user
DB_PASSWORD=YOUR_STRONG_MARIADB_PASSWORD

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false

QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_HOST=mail.paperglow.co.ke
MAIL_PORT=465
MAIL_USERNAME=noreply@paperglow.co.ke
MAIL_PASSWORD=YOUR_EMAIL_PASSWORD
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="noreply@paperglow.co.ke"
MAIL_FROM_NAME="${APP_NAME}"
```

> **Security Note**: Never commit a production `APP_KEY` into source control. Always run `php artisan key:generate` on the production server.

---

## 4. SSH Terminal Commands (or DirectAdmin Terminal)

Log into your Shujaa Host account via SSH or the DirectAdmin Terminal app and run:

```bash
cd /home/<username>/domains/paperglow.co.ke/public_html

# 1. Install production dependencies
composer install --no-dev --optimize-autoloader

# 2. Generate application key
php artisan key:generate --force

# 3. Set folder permissions for Laravel writable directories
chmod -R 775 storage bootstrap/cache

# 4. Run Database Migrations to create all SaaS tables
php artisan migrate --force

# 5. Seed default roles, initial applications catalog, and superadmin
php artisan db:seed --force

# 6. Cache configuration and routes for peak PHP 8.3 performance
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 5. Alternative Database Setup (via phpMyAdmin)

If you prefer using phpMyAdmin instead of CLI migrations:
1. Open DirectAdmin &rarr; **phpMyAdmin**.
2. Select your MariaDB database (`papergl1_saas`).
3. Click **Import**.
4. Choose the bundled schema file: `database/paperglow_directadmin_mysql_schema.sql`.
5. Click **Import / Go**.

---

## 6. Automated Background Tasks (Cron Job)

Set up Laravel's task scheduler in DirectAdmin (**Advanced Features &rarr; Cron Jobs**):
- **Minute**: `*`
- **Hour**: `*`
- **Day of Month**: `*`
- **Month**: `*`
- **Day of Week**: `*`
- **Command**:
  ```bash
  * * * * * cd /home/<username>/domains/paperglow.co.ke/public_html && php artisan schedule:run >> /dev/null 2>&1
  ```

---

## 7. Initial Admin Credentials

- **URL**: `https://paperglow.co.ke/login`
- **Email**: `admin@paperglow.co.ke`
- **Password**: The initial superadmin password is dynamically generated during `php artisan db:seed` and printed securely to the terminal (or can be pre-configured using `ADMIN_INITIAL_PASSWORD` in `.env`).

*Remember to change the administrator password after initial sign in!*

---

## 8. Verification Checklist

- [x] PHP 8.2+ / 8.3 CLI and FPM enabled
- [x] MariaDB persistence active (all data stored in tables with `organization_id` tenant isolation)
- [x] No Node.js process / PM2 needed in production
- [x] All 14 applications operational via Laravel Blade & Alpine.js
- [x] Paperglow Red branding (`#dc2626`) and modern responsive UI
