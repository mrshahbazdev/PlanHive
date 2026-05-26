# PlanHive — Shared Hosting Deployment Guide

## Prerequisites
- PHP 8.1+ with extensions: mbstring, xml, curl, zip, pdo, sqlite3/pgsql/mysql, bcmath, gd, intl
- Composer installed on server (or upload vendor/ folder)
- MySQL/MariaDB or PostgreSQL database

## Step-by-Step Deployment

### 1. Upload Files
Upload the **entire project** to your hosting account root:
```
/home/username/planhive/        ← All Laravel files go here
/home/username/public_html/     ← Your domain's document root
```

### 2. Option A: Symlink (Recommended)
If your host supports symlinks or you have SSH access:
```bash
# Remove default public_html contents
rm -rf /home/username/public_html/*

# Create symlink from public_html to Laravel's public directory
ln -s /home/username/planhive/public /home/username/public_html
```

### 2. Option B: Copy public/ to public_html
If you can't use symlinks:
```bash
# Copy public/ contents to public_html
cp -r /home/username/planhive/public/* /home/username/public_html/

# Edit public_html/index.php — update these paths:
# Change: require __DIR__.'/../vendor/autoload.php';
# To:     require '/home/username/planhive/vendor/autoload.php';

# Change: $app = require_once __DIR__.'/../bootstrap/app.php';
# To:     $app = require_once '/home/username/planhive/bootstrap/app.php';
```

### 2. Option C: Use Root .htaccess
If your domain points to the project root (not public/):
The included root `.htaccess` will redirect all requests to `public/` automatically.

### 3. Environment Configuration
```bash
cd /home/username/planhive
cp .env.example .env

# Edit .env with your production settings:
nano .env
```

Key `.env` settings for shared hosting:
```env
APP_NAME=PlanHive
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

### 4. Install Dependencies (SSH)
If you have SSH access:
```bash
cd /home/username/planhive
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

If you don't have SSH, upload the `vendor/` directory from your local machine.

### 5. Set Permissions
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

On shared hosting (cPanel), the user/group may differ:
```bash
chmod -R 775 storage bootstrap/cache
```

### 6. Database Setup
- Create a MySQL database via cPanel → MySQL Databases
- Create a database user and assign it to the database
- Update `.env` with the credentials
- Run migrations: `php artisan migrate --force`

## Troubleshooting

### 500 Error
- Check `storage/logs/laravel.log` for details
- Ensure `storage/` and `bootstrap/cache/` are writable (775)
- Verify `.env` file exists and has correct DB credentials

### Blank Page
- Set `APP_DEBUG=true` temporarily to see errors
- Check PHP version: `php -v` (needs 8.1+)

### Assets Not Loading (CSS/JS)
- Ensure `APP_URL` in `.env` matches your actual domain
- The `public/build/` directory must contain the compiled assets
- Check that `.htaccess` mod_rewrite is enabled

### Session/Cache Issues
- Use `file` driver for sessions and cache on shared hosting
- Clear caches: `php artisan cache:clear && php artisan config:clear`
