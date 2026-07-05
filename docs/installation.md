# Installation Guide

## Requirements
- PHP 8.3+ with extensions: `pdo, mbstring, openssl, tokenizer, json, curl, fileinfo, gd`
- Composer 2
- Node 20+ / npm (admin frontend build)
- MySQL 8 / MariaDB 10.6+ (or PostgreSQL 14+)
- Redis (recommended for cache + queue in production)
- A web server (Nginx/Apache) + PHP-FPM
- (Optional) Flutter SDK to build the mobile app

## Backend (Laravel 12)
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
# configure DB_*, CACHE_STORE, QUEUE_CONNECTION, MAIL_*, FILESYSTEM_DISK in .env
php artisan migrate --force
php artisan storage:link
php artisan system:doctor        # verify configuration + health
```
Serve behind Nginx → PHP-FPM, or `php artisan serve` for local development.

## Admin frontend (React)
```bash
cd frontend
npm install
npm run build                    # outputs dist/ (serve as static assets)
```
Configure the API base URL in the frontend environment before building.

## Public website (static)
The `website/` folder is served as static HTML/CSS/JS. Set the API base + school id
in `website/assets/js/cms-config.js`. It reads published content from the read-only
`/api/v1/cms/public/*` endpoints.

## Mobile app (Flutter)
```bash
cd mobile
flutter pub get
flutter build apk        # or ios
```

## Queue worker + scheduler (production)
```bash
php artisan queue:work --queue=default --tries=3     # supervised (systemd/supervisor)
* * * * * cd /path/to/backend && php artisan schedule:run >> /dev/null 2>&1
```

## First login
Seeded super-admin credentials are environment-specific. Rotate them immediately
and create real roles/users under Administration.
