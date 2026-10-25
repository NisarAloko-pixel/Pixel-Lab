# Pixel Lab

**3D Printing Management System**: one centralized web application for managing sales, customers, suppliers, filament and product inventory, expenses and finances for a 3D printing business.

Built with Laravel. Runs on a standard PHP server with a local SQLite database, so there are no external services to set up.

<!--
Add screenshots here, for example:
![Dashboard](docs/screenshots/dashboard.png)
-->

## Features

| Module | What it does |
|---|---|
| **Dashboard** | Sales, profit, expenses and receivables at a glance, with period filters (today, week, month, year) and charts |
| **Sales** | Create, track and invoice sales, payments and cancellations, including free items |
| **Customers** | Customer details, history and outstanding balances |
| **Suppliers** | Supplier records and what you owe them |
| **Purchases** | Purchase orders, arrivals and payments, with multi-currency support |
| **Filament Inventory** | Filament stock, usage and cost per gram |
| **Product Inventory** | Products, stock levels, damaged items and inventory value |
| **Supplies** | Supply management; apply purchases to stock |
| **Expenses** | Operational expenses by category |
| **Balance & Shareholders** | Account balances, deposits, transfers and shareholder transactions |
| **Transactions** | A clear record of all financial transactions |
| **Employees & Advances** | Employees and their advance payments |
| **Reports & Export** | Sales, profit, expense and purchase reports with PDF, Excel and CSV export |
| **Users & Roles** | Access control with users, roles and permissions |
| **Audit Logs** | Who did what across the system |
| **Backup & Restore** | Back up your data and restore it when needed |
| **Settings** | Business details, currency, time zone and backup options |

The interface supports light and dark themes and is responsive for desktop and mobile browsers.

## Tech stack

- **Backend:** Laravel (PHP 8.3+)
- **Database:** SQLite
- **Frontend:** Blade, Alpine.js, Tailwind CSS, Vite
- **Access control:** [spatie/laravel-permission](https://github.com/spatie/laravel-permission)

## Requirements

- PHP 8.3 or newer, with the `sqlite3`, `pdo_sqlite`, `mbstring`, `xml`, `zip` and `gd` extensions
- [Composer](https://getcomposer.org/)
- Node.js 18+ and npm

## Installation

```bash
# 1. Clone the repository
git clone https://github.com/YOUR_USER/YOUR_REPO.git
cd YOUR_REPO

# 2. Install dependencies
composer install
npm install

# 3. Environment file
cp .env.example .env
php artisan key:generate

# 4. Create the SQLite database
touch database/database.sqlite        # Windows PowerShell: New-Item database\database.sqlite

# 5. Set your administrator login in .env (see below), then build the database
php artisan migrate --seed

# 6. Build assets and start the app
npm run build
php artisan serve
```

Open <http://127.0.0.1:8000> and sign in with the administrator account you set in `.env`.

### Administrator account

Set these values in `.env` **before** running `php artisan migrate --seed`:

```env
ADMIN_EMAIL=admin@pixelstudio.com
ADMIN_PASSWORD=12345678
```



## Development

```bash
npm run dev          # Vite dev server with hot reload
php artisan serve    # PHP development server
```

Useful commands:

```bash
php artisan optimize:clear   # clear config, route, view and cache files
php artisan route:list       # list all routes
php artisan migrate:status   # check pending migrations
```

## Project structure

```
app/                  Controllers, models, services, exports
database/migrations/  Database schema
database/seeders/     Admin user, categories and default settings
resources/views/      Blade templates
routes/               Web and auth routes
```

## Security notes

- Never commit your `.env` file or any `.sqlite` database. Both are listed in `.gitignore`.
- Use `APP_ENV=production` and `APP_DEBUG=false` on a live server.
- Serve the app over HTTPS and point the web server at the `public/` directory only.

## Support

Found a problem or something isn't working correctly?

- **Email:** nisaraymal@gmail.com
- **WhatsApp:** +93 76 4000 785

Professional deployment, desktop or mobile packaging and multi-computer setups are available on request.

## Author

Built by **NiSaR Aloko**: practical, reliable and modern business management solutions.
