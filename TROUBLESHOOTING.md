# Troubleshooting Guide

This guide helps you resolve common issues when setting up the Romanian Legislative Watcher application.

## Table of Contents

- [PHP Extension Issues](#php-extension-issues)
- [Composer Installation Problems](#composer-installation-problems)
- [Laravel Artisan Errors](#laravel-artisan-errors)
- [Database Issues](#database-issues)
- [Permission Problems](#permission-problems)

---

## PHP Extension Issues

### Error: "ext-xml is missing"

**Problem:** Composer reports that the XML extension is missing even though it might be installed.

**Solution:**

1. **Verify extensions are actually installed:**
   ```bash
   php -m | grep xml
   php -m | grep dom
   ```

2. **If extensions are missing, install them:**

   **For Debian/Ubuntu (PHP 8.4):**
   ```bash
   sudo apt-get update
   sudo apt-get install -y php8.4-xml php8.4-dom php8.4-mbstring php8.4-curl php8.4-zip
   ```

   **For Debian/Ubuntu (PHP 8.1-8.3):**
   ```bash
   sudo apt-get update
   sudo apt-get install -y php-xml php-dom php-mbstring php-curl php-zip
   ```

   **For CentOS/RHEL:**
   ```bash
   sudo yum install -y php-xml php-dom php-mbstring php-curl php-zip
   ```

3. **Verify installation:**
   ```bash
   php -m | grep -E "(xml|dom|mbstring|curl|zip)"
   ```

4. **Clear Composer cache and reinstall:**
   ```bash
   cd laravel-app
   rm -rf vendor composer.lock
   composer clear-cache
   composer install
   ```

### Complete Laravel PHP Extensions List

For a full Laravel installation, install these extensions:

```bash
# For PHP 8.4
sudo apt-get install -y \
    php8.4-cli \
    php8.4-common \
    php8.4-xml \
    php8.4-dom \
    php8.4-mbstring \
    php8.4-curl \
    php8.4-zip \
    php8.4-mysql \
    php8.4-pgsql \
    php8.4-sqlite3 \
    php8.4-gd \
    php8.4-bcmath \
    php8.4-intl \
    php8.4-opcache
```

---

## Composer Installation Problems

### Error: "vendor/autoload.php not found"

**Problem:** The vendor directory doesn't exist or is incomplete.

**Solution:**

```bash
cd laravel-app
composer install --no-interaction --prefer-dist --optimize-autoloader
```

### Composer Deprecation Warnings

**Problem:** Seeing many deprecation notices from Composer on PHP 8.4.

**Solution:** These are harmless warnings about Composer's internal code. They don't affect functionality. To hide them temporarily:

```bash
composer install 2>&1 | grep -v "Deprecation Notice"
```

Or update Composer to the latest version:
```bash
composer self-update
```

### Composer running slowly / "PHP curl extension not enabled"

**Problem:** Composer warns about missing curl extension.

**Solution:**

```bash
# Install curl extension
sudo apt-get install -y php8.4-curl

# Verify it's loaded
php -m | grep curl

# Restart PHP-FPM if using it
sudo systemctl restart php8.4-fpm
```

---

## Laravel Artisan Errors

### Error: "Could not open input file: artisan"

**Problem:** Running artisan from wrong directory.

**Solution:**

```bash
cd laravel-app
php artisan --version
```

### Error: "View path not found"

**Problem:** Missing `config/view.php` configuration file.

**Solution:**

This has been fixed in the latest version. Pull the latest changes:

```bash
git pull origin claude/fix-laravel-initialization-012GoBtcbDeHrP1cmgw8ZvBz
```

Or manually create the file:

```bash
cat > laravel-app/config/view.php << 'EOF'
<?php

return [
    'paths' => [
        resource_path('views'),
    ],
    'compiled' => env(
        'VIEW_COMPILED_PATH',
        realpath(storage_path('framework/views'))
    ),
];
EOF
```

### Error: "No application encryption key has been specified"

**Problem:** Missing APP_KEY in .env file.

**Solution:**

```bash
cd laravel-app
cp .env.example .env
php artisan key:generate
```

---

## Database Issues

### Error: "Database file not found"

**Problem:** SQLite database file doesn't exist.

**Solution:**

```bash
cd laravel-app
touch database/database.sqlite
chmod 664 database/database.sqlite
php artisan migrate --force
```

### Error: "Connection refused" (MySQL/PostgreSQL)

**Problem:** Database server not running or wrong credentials.

**Solution:**

1. **Check database is running:**
   ```bash
   # MySQL
   sudo systemctl status mysql

   # PostgreSQL
   sudo systemctl status postgresql
   ```

2. **Update .env with correct credentials:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

3. **Clear config cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

---

## Permission Problems

### Error: "Permission denied" on storage directories

**Problem:** Web server can't write to storage or cache directories.

**Solution:**

```bash
cd laravel-app

# Set proper ownership (if using www-data)
sudo chown -R www-data:www-data storage bootstrap/cache

# Or make writable by all
chmod -R 775 storage bootstrap/cache

# For development only (not recommended for production)
chmod -R 777 storage bootstrap/cache
```

### SELinux Issues (CentOS/RHEL)

**Problem:** SELinux blocking file access.

**Solution:**

```bash
# Allow Apache to write to storage
sudo chcon -R -t httpd_sys_rw_content_t laravel-app/storage
sudo chcon -R -t httpd_sys_rw_content_t laravel-app/bootstrap/cache

# Or temporarily disable SELinux (development only)
sudo setenforce 0
```

---

## Fresh Installation Steps

If you're experiencing multiple issues, try a completely fresh installation:

```bash
# 1. Navigate to project root
cd /path/to/incorporo-legislative-watcher

# 2. Pull latest changes
git pull

# 3. Navigate to Laravel app
cd laravel-app

# 4. Remove old files
rm -rf vendor composer.lock node_modules package-lock.json bootstrap/cache/*.php

# 5. Clear composer cache
composer clear-cache

# 6. Install dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader

# 7. Set up environment
cp .env.example .env
php artisan key:generate

# 8. Set permissions
chmod -R 775 storage bootstrap/cache

# 9. Set up database
touch database/database.sqlite
php artisan migrate --force

# 10. Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# 11. Test installation
php artisan about
```

---

## Getting Help

If you continue to experience issues:

1. Check the application logs:
   ```bash
   tail -f laravel-app/storage/logs/laravel.log
   ```

2. Enable debug mode in `.env`:
   ```env
   APP_DEBUG=true
   ```

3. Run Laravel diagnostics:
   ```bash
   php artisan about
   php artisan config:show
   ```

4. Check PHP configuration:
   ```bash
   php -i | grep -E "(extension_dir|Configuration File)"
   php -m
   ```

5. Open an issue on GitHub with:
   - PHP version (`php -v`)
   - Composer version (`composer --version`)
   - OS/Distribution
   - Full error message
   - Steps to reproduce
