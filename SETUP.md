# 🛠 Pixel Lab: Full Setup Guide

This guide walks you through installing **Pixel Lab** from zero, on your own computer or on a live server.

**Jump to:**
[Requirements](#1-requirements) •
[Windows](#2-install-on-windows-wamp-or-plain-php) •
[Linux / macOS](#3-install-on-linux--macos) •
[Configuration](#4-configure-the-env-file) •
[First login](#5-first-login) •
[Production](#6-deploy-to-a-live-server) •
[Backups](#7-backups) •
[Updating](#8-updating-the-app) •
[Troubleshooting](#9-troubleshooting)

---

## 1. Requirements

| Tool | Version | Check with |
|---|---|---|
| PHP | 8.3 or newer | `php -v` |
| Composer | 2.x | `composer -V` |
| Node.js | 18 or newer | `node -v` |
| npm | comes with Node | `npm -v` |
| Git | any recent version | `git --version` |

**Required PHP extensions:** `sqlite3`, `pdo_sqlite`, `mbstring`, `xml`, `zip`, `gd`
(plus the usual Laravel ones: `openssl`, `fileinfo`, `curl`, `tokenizer`, `ctype`, `json`, `bcmath`)

Check which extensions are active:

```bash
php -m
```

> 💡 No MySQL or other database server is needed. Pixel Lab stores everything in one SQLite file.

---

## 2. Install on Windows (WAMP or plain PHP)

### Step 1: Install the tools

1. **PHP 8.3+**: install [WampServer](https://www.wampserver.com/) (includes PHP and Apache), or download PHP from <https://windows.php.net/download>.
2. **Composer**: <https://getcomposer.org/download/> (run the Windows installer).
3. **Node.js LTS**: <https://nodejs.org/>
4. **Git**: <https://git-scm.com/download/win>

### Step 2: Enable PHP extensions

Open `php.ini` (in WAMP: tray icon → PHP → `php.ini`) and make sure these lines **do not** start with `;`:

```ini
extension=gd
extension=mbstring
extension=pdo_sqlite
extension=sqlite3
extension=zip
extension=fileinfo
extension=openssl
extension=curl
```

Restart WAMP after saving.

> ⚠️ **WAMP tip:** the PHP used in the command line can be different from the one used by Apache. Run `php -v` in PowerShell and confirm it says **8.3 or newer**. If not, add the correct PHP folder to your Windows `PATH`.

### Step 3: Download the project

```powershell
cd C:\wamp64\www
git clone https://github.com/NisarAloko-pixel/Pixel-Lab.git
cd Pixel-Lab
```

### Step 4: Install dependencies

```powershell
composer install
npm install
```

### Step 5: Create the environment file

```powershell
copy .env.example .env
php artisan key:generate
```

### Step 6: Create the database file

```powershell
New-Item database\database.sqlite -ItemType File
```

### Step 7: Set the administrator login

Open `.env` in a text editor and set (see [Configuration](#4-configure-the-env-file)):

```env
ADMIN_EMAIL=you@example.com
ADMIN_PASSWORD=choose-a-strong-password
```

### Step 8: Build the database

```powershell
php artisan migrate --seed
```

This creates all tables, the administrator account, categories, permissions and default settings.

### Step 9: Link the storage folder

```powershell
php artisan storage:link
```

### Step 10: Build assets and run

```powershell
npm run build
php artisan serve
```

Open **<http://127.0.0.1:8000>** 🎉

### Optional: run it through WAMP/Apache instead of `php artisan serve`

Point a virtual host at the project's **`public`** folder, for example:

```apache
<VirtualHost *:80>
    ServerName pixellab.local
    DocumentRoot "C:/wamp64/www/Pixel-Lab/public"
    <Directory "C:/wamp64/www/Pixel-Lab/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Add `127.0.0.1 pixellab.local` to `C:\Windows\System32\drivers\etc\hosts`, restart Apache, and set `APP_URL=http://pixellab.local` in `.env`.

---

## 3. Install on Linux / macOS

### Ubuntu / Debian: install the tools

```bash
sudo apt update
sudo apt install -y git unzip curl \
  php8.3-cli php8.3-sqlite3 php8.3-mbstring php8.3-xml php8.3-zip php8.3-gd php8.3-curl php8.3-bcmath

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

> If `php8.3` packages are not found, add the PHP repository first: `sudo add-apt-repository ppa:ondrej/php`

### macOS (Homebrew)

```bash
brew install php composer node git
```

### Install the app

```bash
git clone https://github.com/NisarAloko-pixel/Pixel-Lab.git
cd Pixel-Lab

composer install
npm install

cp .env.example .env
php artisan key:generate

touch database/database.sqlite

# edit .env and set ADMIN_EMAIL and ADMIN_PASSWORD first
php artisan migrate --seed
php artisan storage:link

npm run build
php artisan serve
```

Open **<http://127.0.0.1:8000>**.

---

## 4. Configure the `.env` file

Open `.env` and review these values.

### Required

```env
APP_NAME="Pixel Lab"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=sqlite

ADMIN_EMAIL=you@example.com
ADMIN_PASSWORD=choose-a-strong-password
```

| Key | Meaning |
|---|---|
| `APP_ENV` | `local` while developing, `production` on a live server |
| `APP_DEBUG` | `true` only while developing. **Must be `false` in production** |
| `APP_URL` | The address you open the app at |
| `DB_CONNECTION` | Keep as `sqlite` |
| `ADMIN_EMAIL` / `ADMIN_PASSWORD` | The first administrator account, created by `migrate --seed` |

> ⚠️ Set `ADMIN_EMAIL` and `ADMIN_PASSWORD` **before** running `php artisan migrate --seed`. Use a strong password.

### Optional: email (password reset & emailed reports)

Pixel Lab can send password-reset and report emails. Configure SMTP if you want these:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

For testing without sending real mail, use `MAIL_MAILER=log` (emails are written to `storage/logs/laravel.log`).

After changing `.env`, clear the cache:

```bash
php artisan optimize:clear
```

---

## 5. First login

1. Open the app in your browser.
2. Sign in with `ADMIN_EMAIL` and `ADMIN_PASSWORD`.
3. Go to **Settings** and fill in your business details, **currency** and **time zone**.
4. Go to **Users & Roles** to create staff accounts and choose what each role can access.
5. Change your administrator password to something unique if you haven't already.

### Suggested order to start using the system

1. **Settings**: business name, currency, time zone
2. **Balance**: create your accounts (cash, bank, etc.)
3. **Suppliers** and **Customers**
4. **Filament Inventory** and **Supplies**
5. **Products**: link filament, print time and extras so profit is calculated correctly
6. **Purchases**: record stock arriving
7. **Sales**: start invoicing

---

## 6. Deploy to a live server

### Server requirements

Any Linux server with PHP 8.3+ (and the extensions above), Composer, and a web server (Nginx or Apache). Node.js is only needed to build assets. You can build on your own computer and upload `public/build`.

### Steps

```bash
git clone https://github.com/NisarAloko-pixel/Pixel-Lab.git
cd Pixel-Lab

composer install --no-dev --optimize-autoloader
npm install && npm run build

cp .env.example .env
php artisan key:generate
touch database/database.sqlite
```

Edit `.env` for production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
ADMIN_EMAIL=you@your-domain.com
ADMIN_PASSWORD=a-long-unique-password
```

Then:

```bash
php artisan migrate --seed --force
php artisan storage:link

# Speed up the app
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### File permissions

The web server user (often `www-data`) must be able to write to these:

```bash
sudo chown -R www-data:www-data storage bootstrap/cache database
sudo chmod -R 775 storage bootstrap/cache
sudo chmod 664 database/database.sqlite
sudo chmod 775 database
```

> SQLite needs write access to the **folder** containing the database file, not just the file.

### Nginx example

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/Pixel-Lab/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Apache

The project includes `public/.htaccess`. Point `DocumentRoot` at the **`public`** folder and enable `AllowOverride All` and `mod_rewrite`.

### HTTPS

Install a free certificate with Let's Encrypt:

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
```

### Security checklist

- [ ] `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Web server points to `public/` only
- [ ] HTTPS enabled
- [ ] Strong administrator password
- [ ] `.env` and `database/database.sqlite` are **not** publicly accessible and **not** in Git
- [ ] Regular backups (see below)

---

## 7. Backups

Your whole business lives in **one file**: `database/database.sqlite`.

- Use the **Backup & Restore** page inside the app to create and restore backups.
- Also keep your own copies. For example, copy `database/database.sqlite` and `.env` to an external drive or cloud storage on a schedule.
- Always test restoring a backup before you need it.

Example automatic daily copy on Linux (cron, runs at 2:00 AM):

```bash
0 2 * * * cp /var/www/Pixel-Lab/database/database.sqlite /var/backups/pixel-lab-$(date +\%F).sqlite
```

---

## 8. Updating the app

```bash
cd Pixel-Lab

# 1. Back up first!
cp database/database.sqlite database/database.sqlite.bak

# 2. Get the latest code
git pull

# 3. Update dependencies and database
composer install --no-dev --optimize-autoloader
npm install && npm run build
php artisan migrate --force

# 4. Clear caches
php artisan optimize:clear
```

> Use `php artisan migrate` (without `--seed`) when updating, so existing data is never overwritten.

---

## 9. Troubleshooting

| Problem | Fix |
|---|---|
| **`could not find driver`** | The `pdo_sqlite` / `sqlite3` extension is off. Enable it in `php.ini` and restart. |
| **`Your PHP version does not satisfy...`** | Install PHP 8.3+ and confirm with `php -v`. |
| **`No application encryption key has been specified`** | Run `php artisan key:generate`. |
| **Blank page / 500 error** | Set `APP_DEBUG=true` temporarily, or read `storage/logs/laravel.log`. |
| **Page has no styling** | Run `npm run build` (or `npm run dev` while developing). |
| **`Vite manifest not found`** | Same as above: run `npm run build`. |
| **`Database file ... does not exist`** | Create it: `touch database/database.sqlite` (Windows: `New-Item database\database.sqlite`). |
| **`attempt to write a readonly database`** | Fix permissions on the `database/` folder and file (see [File permissions](#file-permissions)). |
| **Can't log in after install** | You may have changed `.env` after seeding. Reset the password via `php artisan tinker` or re-run on a fresh database. |
| **Images / avatars not showing** | Run `php artisan storage:link`. |
| **PDF/Excel export fails** | Make sure the `gd`, `zip` and `mbstring` extensions are enabled. |
| **Changes to `.env` ignored** | Run `php artisan optimize:clear`. |
| **404 on every page except home** | The web server isn't rewriting URLs. Enable `mod_rewrite` (Apache) or use the Nginx `try_files` rule. |
| **`composer install` is very slow or fails** | Check your internet connection, and make sure the `zip` extension is enabled. |

### Reset everything (development only) ⚠️

This **deletes all data** and rebuilds the database:

```bash
php artisan migrate:fresh --seed
```

---

## 💬 Need help?

- 📧 **Email:** [nisaraymal@gmail.com](mailto:nisaraymal@gmail.com)
- 💬 **WhatsApp:** [+93 76 4000 785](https://wa.me/93764000785)

Professional deployment, desktop or mobile packaging and multi-computer setups are available on request.
