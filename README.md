# AgriQueue: Agricultural Procurement & Digital Queue System

Laravel 11/12 + MySQL. Custom authentication, four roles, no npm build step (Tailwind and Chart.js load from CDN).
**This code was written without a PHP runtime available, so it has not been executed.** Follow the steps below and
expect to fix a typo or two on first run; every file is short and readable.

## 1. Requirements
PHP 8.2+, Composer, MySQL 8 (or MariaDB 10.6+). Enable PHP extensions: pdo_mysql, mbstring, openssl, xml, ctype, json.

## 2. Create the Laravel app and copy these files over it
```bash
composer create-project laravel/laravel agriqueue
cd agriqueue
# Copy the contents of this folder into the project, overwriting when asked:
#   app/  database/  resources/  routes/
```
(This replaces `app/Models/User.php`, `routes/web.php` and `database/seeders/DatabaseSeeder.php`.)
You may delete `resources/views/welcome.blade.php`.

## 3. Create the database
```sql
CREATE DATABASE agriqueue CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## 4. Edit `.env`
```
APP_NAME=AgriQueue
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=agriqueue
DB_USERNAME=root
DB_PASSWORD=your_password
```
No SQL server handy? Keep the default `DB_CONNECTION=sqlite` and skip step 3; everything still works.

## 5. Migrate, seed, run
```bash
php artisan migrate:fresh --seed
php artisan serve
```
Open http://localhost:8000

## Demo accounts (password: `password`)
| Role | Email |
|---|---|
| Farmer | farmer@agriqueue.test |
| Staff (Hyderabad) | staff@agriqueue.test |
| Inspector | inspector@agriqueue.test |
| Admin | admin@agriqueue.test |

The seeder creates a live day at Hyderabad with a farmer at every stage, plus 14 days of history for the charts.

## What maps to the brief
* Flow: Booked → Checked In → Waiting → Weighing → Quality Check → Unloading → Payment Pending → Completed, plus Cancelled, Missed, Rejected, Delayed.
* Smart slot allocation: `app/Services/SlotService.php` (per-slot and daily capacity, best-slot recommendation, past slots closed).
* Capacity protection: booking runs in a DB transaction with a row lock, so two farmers cannot take the last tons. Duplicate bookings (same farmer, crop, date, slot) are rejected.
* Roles: farmer, staff, inspector, admin via `RoleMiddleware` on route groups; public sign-up creates farmers only.
* Notifications (in-app) on booking, token, queue changes, weighing, quality, payment and cancellation.
* Dashboard + analytics (admin), search/filter (farmer bookings), activity logs, receipts, QR tokens, live public queue screen at `/board/{center_id}`, waiting-time estimate.
* Pricing: crop price per ton × grade factor (A 100%, B 95%, C 90%).

## Images
Photos load from loremflickr.com by keyword. If a photo does not load, the page still works (green fallback). For the final demo, save 5-6 good crop photos in `public/img/` and change the URLs in `home.blade.php` and the seeder.

## Not included (optional extras from the brief)
SMS gateway, digital scale integration, AI image grading, multilingual UI. In-app notifications are the delivery channel; swap `Notice::send()` for an SMS provider to extend.
