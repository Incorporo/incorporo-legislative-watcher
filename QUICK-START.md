# Quick Start Guide

This is a streamlined guide to get the Romanian Legislative Watcher up and running quickly.

## Prerequisites

Before you begin, install these required packages:

```bash
# For Debian/Ubuntu (PHP 8.4)
sudo apt-get update
sudo apt-get install -y \
    php8.4-cli \
    php8.4-common \
    php8.4-xml \
    php8.4-mbstring \
    php8.4-curl \
    php8.4-zip \
    php8.4-sqlite3 \
    php8.4-gd \
    composer \
    git
```

## Option 1: Automated Setup (Recommended)

```bash
# Clone the repository
git clone https://github.com/Incorporo/incorporo-legislative-watcher.git
cd incorporo-legislative-watcher

# Run the setup script
./setup.sh

# Start the server
cd laravel-app
DATABASE_URL="" php artisan serve
```

Visit **http://localhost:8000**

## Option 2: Database-Only Setup

If you already have the vendor directory but need to initialize the database:

```bash
cd incorporo-legislative-watcher/laravel-app
../init-database.sh
```

## Option 3: Manual Setup

```bash
# 1. Clone and enter directory
git clone https://github.com/Incorporo/incorporo-legislative-watcher.git
cd incorporo-legislative-watcher/laravel-app

# 2. Install dependencies
composer install --no-interaction --prefer-dist

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Create database
touch database/database.sqlite
chmod 664 database/database.sqlite

# 5. Run migrations
DATABASE_URL="" php artisan migrate --force

# 6. Start server
DATABASE_URL="" php artisan serve
```

## Important: DATABASE_URL Environment Variable

If you have `DATABASE_URL` set in your system environment (common on servers with multiple projects), you **must** prefix all artisan commands with `DATABASE_URL=""` or create an alias:

```bash
# Check if DATABASE_URL is set
env | grep DATABASE_URL

# Option A: Prefix every command
DATABASE_URL="" php artisan migrate
DATABASE_URL="" php artisan serve

# Option B: Create a permanent alias (recommended)
echo 'alias artisan="DATABASE_URL=\"\" php artisan"' >> ~/.bashrc
source ~/.bashrc

# Now use artisan normally
artisan migrate
artisan serve
```

## Common Issues

### Missing PDO SQLite Extension

**Error:** `could not find driver`

**Fix:**
```bash
# Install the extension
sudo apt-get install -y php8.4-sqlite3

# Verify
php -m | grep pdo_sqlite

# Restart PHP-FPM if using it
sudo systemctl restart php8.4-fpm
```

### PostgreSQL Connection Error (when configured for SQLite)

**Error:** `connection to server at "localhost" (127.0.0.1), port 5432 failed`

**Cause:** DATABASE_URL environment variable is set

**Fix:**
```bash
# Add to .env file
DATABASE_URL=

# Or use the init-database.sh script
../init-database.sh
```

### Composer Warnings on PHP 8.4

**Issue:** Many deprecation notices during `composer install`

**Fix:** These are harmless warnings from Composer itself. They don't affect functionality. To hide them:
```bash
composer install 2>&1 | grep -v "Deprecation Notice"
```

## Next Steps

After installation:

1. **Test the scraper:**
   ```bash
   DATABASE_URL="" php artisan scrape:bills --chamber=all --limit=10
   ```

2. **View the data:**
   ```bash
   DATABASE_URL="" php artisan tinker
   >>> \App\Models\LegislativeBill::count()
   >>> \App\Models\LegislativeBill::latest()->first()
   ```

3. **Run tests:**
   ```bash
   DATABASE_URL="" php artisan test
   ```

4. **Setup cron for automatic scraping:**
   See [CRON-SETUP.md](./CRON-SETUP.md)

## Getting Help

- **Troubleshooting:** See [TROUBLESHOOTING.md](./TROUBLESHOOTING.md)
- **Full Documentation:** See [README.md](./README.md)
- **Architecture Details:** See [ARCHITECTURE.md](./ARCHITECTURE.md)

## Useful Commands

```bash
# Start development server
DATABASE_URL="" php artisan serve

# Run migrations
DATABASE_URL="" php artisan migrate

# Seed database
DATABASE_URL="" php artisan db:seed

# Clear caches
DATABASE_URL="" php artisan config:clear
DATABASE_URL="" php artisan cache:clear

# Check application status
DATABASE_URL="" php artisan about

# List all routes
DATABASE_URL="" php artisan route:list

# Scrape bills
DATABASE_URL="" php artisan scrape:bills --chamber=all --limit=10

# Interactive PHP shell
DATABASE_URL="" php artisan tinker
```

## Development Workflow

```bash
# After pulling new changes
git pull
composer install
DATABASE_URL="" php artisan migrate
DATABASE_URL="" php artisan config:clear

# Before committing
DATABASE_URL="" php artisan test
DATABASE_URL="" php artisan pint  # Code formatting
```

## Production Deployment

See [NEXT-STEPS.md](./NEXT-STEPS.md) for production deployment guidelines.
