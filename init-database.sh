#!/bin/bash

###############################################################################
# Database Initialization Script
###############################################################################
# This script handles database setup with proper environment variable handling
# Usage: ./init-database.sh
###############################################################################

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_header() {
    echo -e "${BLUE}=========================================================================="
    echo "$1"
    echo -e "==========================================================================${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

print_header "Database Initialization"

# Check if we're in the laravel-app directory
if [ ! -f "artisan" ]; then
    print_error "This script must be run from the laravel-app directory!"
    print_info "Usage: cd laravel-app && ../init-database.sh"
    exit 1
fi

# Step 1: Check PDO SQLite extension
print_info "Checking for PDO SQLite extension..."
if php -m | grep -qi "pdo_sqlite"; then
    print_success "PDO SQLite extension is installed"
else
    print_error "PDO SQLite extension is NOT installed!"
    print_info "Install it with:"
    echo ""
    echo "  # For PHP 8.4 (Debian/Ubuntu):"
    echo "  sudo apt-get install -y php8.4-sqlite3"
    echo ""
    echo "  # For PHP 8.1-8.3 (Debian/Ubuntu):"
    echo "  sudo apt-get install -y php-sqlite3"
    echo ""
    echo "  # For CentOS/RHEL:"
    echo "  sudo yum install -y php-pdo"
    echo ""
    print_info "After installing, restart PHP-FPM if using it:"
    echo "  sudo systemctl restart php8.4-fpm"
    echo ""
    exit 1
fi

# Step 2: Check for DATABASE_URL environment variable
print_info "Checking for DATABASE_URL conflicts..."
if [ -n "$DATABASE_URL" ]; then
    print_warning "DATABASE_URL is set in your environment: $DATABASE_URL"
    print_info "This will be overridden by .env file"
fi

# Step 3: Ensure .env exists
if [ ! -f ".env" ]; then
    print_info "Creating .env from .env.example..."
    cp .env.example .env
    print_success ".env file created"
fi

# Step 4: Check .env has DATABASE_URL override
if ! grep -q "^DATABASE_URL=" .env; then
    print_info "Adding DATABASE_URL override to .env..."
    sed -i '/^DB_DATABASE=/a\\n# IMPORTANT: Override any system DATABASE_URL with empty value for SQLite\nDATABASE_URL=' .env
    print_success "DATABASE_URL override added"
fi

# Step 5: Create database directory if needed
print_info "Ensuring database directory exists..."
mkdir -p database
print_success "Database directory ready"

# Step 6: Create SQLite database file
if [ ! -f "database/database.sqlite" ]; then
    print_info "Creating SQLite database file..."
    touch database/database.sqlite
    chmod 664 database/database.sqlite
    print_success "Database file created"
else
    print_success "Database file already exists"
fi

# Step 7: Clear all caches
print_info "Clearing Laravel caches..."
rm -f bootstrap/cache/config.php 2>/dev/null || true
DATABASE_URL="" php artisan config:clear >/dev/null 2>&1 || true
DATABASE_URL="" php artisan cache:clear >/dev/null 2>&1 || true
print_success "Caches cleared"

# Step 8: Create migrations table
print_header "Setting Up Database"
print_info "Creating migrations table..."

if DATABASE_URL="" php artisan migrate:install; then
    print_success "Migrations table created"
else
    print_error "Failed to create migrations table"
    exit 1
fi

# Step 9: Run migrations
print_info "Running migrations..."
if DATABASE_URL="" php artisan migrate --force; then
    print_success "All migrations completed"
else
    print_error "Migrations failed"
    exit 1
fi

# Step 10: Check migration status
print_info "Verifying migrations..."
DATABASE_URL="" php artisan migrate:status

# Final message
print_header "Database Setup Complete!"
echo ""
print_success "Your database is ready to use!"
echo ""
print_info "Available commands:"
echo "  • Run migrations: DATABASE_URL=\"\" php artisan migrate"
echo "  • Seed database: DATABASE_URL=\"\" php artisan db:seed"
echo "  • Check status: DATABASE_URL=\"\" php artisan migrate:status"
echo ""
print_warning "IMPORTANT: Always prefix artisan commands with DATABASE_URL=\"\" to avoid conflicts"
print_info "Or add this to your ~/.bashrc or ~/.zshrc:"
echo "  alias artisan='DATABASE_URL=\"\" php artisan'"
echo ""
