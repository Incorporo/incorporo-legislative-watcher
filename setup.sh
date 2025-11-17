#!/bin/bash

###############################################################################
# Romanian Legislative Watcher - Automated Setup Script
###############################################################################
# This script will set up your Laravel application with all dependencies
# Usage: ./setup.sh
###############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Functions
print_header() {
    echo -e "${BLUE}"
    echo "=========================================================================="
    echo "$1"
    echo "=========================================================================="
    echo -e "${NC}"
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

# Check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

print_header "Romanian Legislative Watcher - Setup"

# Step 1: Check Prerequisites
print_info "Checking prerequisites..."

if ! command_exists php; then
    print_error "PHP is not installed. Please install PHP 8.1 or higher."
    exit 1
fi

PHP_VERSION=$(php -r "echo PHP_VERSION;" | cut -d. -f1,2)
print_success "PHP $PHP_VERSION found"

if ! command_exists composer; then
    print_error "Composer is not installed. Please install Composer first."
    print_info "Visit: https://getcomposer.org/download/"
    exit 1
fi
print_success "Composer found"

if ! command_exists node; then
    print_warning "Node.js is not installed. Frontend assets won't be built."
    print_info "You can install Node.js later from: https://nodejs.org/"
    SKIP_NODE=true
else
    print_success "Node.js found"
    SKIP_NODE=false
fi

# Check PHP Extensions
print_info "Checking required PHP extensions..."
REQUIRED_EXTENSIONS=("xml" "dom" "mbstring" "pdo" "tokenizer" "curl" "zip" "fileinfo")
MISSING_EXTENSIONS=()

for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if php -m | grep -qi "^$ext$"; then
        print_success "Extension $ext is installed"
    else
        MISSING_EXTENSIONS+=("$ext")
        print_error "Extension $ext is MISSING"
    fi
done

if [ ${#MISSING_EXTENSIONS[@]} -ne 0 ]; then
    print_error "Missing PHP extensions: ${MISSING_EXTENSIONS[*]}"
    print_info "Please install the missing extensions. On Debian/Ubuntu, run:"
    echo ""
    for ext in "${MISSING_EXTENSIONS[@]}"; do
        echo "  sudo apt-get install php8.4-$ext"
    done
    echo ""
    print_info "Or install all common Laravel extensions at once:"
    echo "  sudo apt-get install php8.4-cli php8.4-common php8.4-xml php8.4-mbstring php8.4-curl php8.4-zip php8.4-mysql php8.4-pgsql php8.4-sqlite3 php8.4-gd"
    echo ""
    print_warning "Continuing anyway - composer may fail..."
    sleep 2
fi

# Step 2: Navigate to Laravel directory
cd laravel-app

# Step 3: Install Composer Dependencies
print_header "Installing Composer Dependencies"
print_info "This may take a few minutes..."

if [ ! -d "vendor" ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
    print_success "Composer dependencies installed"
else
    print_warning "Vendor directory already exists. Running composer update..."
    composer update --no-interaction --prefer-dist --optimize-autoloader
    print_success "Composer dependencies updated"
fi

# Step 4: Install NPM Dependencies (if Node.js is available)
if [ "$SKIP_NODE" = false ]; then
    print_header "Installing NPM Dependencies"

    if [ ! -d "node_modules" ]; then
        npm install
        print_success "NPM dependencies installed"
    else
        print_warning "Node modules already exist. Skipping npm install."
    fi
fi

# Step 5: Environment Configuration
print_header "Setting up Environment Configuration"

if [ ! -f ".env" ]; then
    cp .env.example .env
    print_success "Created .env file from .env.example"
else
    print_warning ".env file already exists. Skipping..."
fi

# Step 6: Generate Application Key
print_header "Generating Application Key"
php artisan key:generate --ansi
print_success "Application key generated"

# Step 7: Create SQLite Database
print_header "Setting up Database"

if [ ! -d "database" ]; then
    mkdir -p database
fi

if [ ! -f "database/database.sqlite" ]; then
    touch database/database.sqlite
    print_success "Created SQLite database file"
else
    print_warning "Database file already exists"
fi

# Step 8: Run Migrations
print_header "Running Database Migrations"
DATABASE_URL="" php artisan migrate --force --ansi
print_success "Database migrations completed"

# Step 9: Create Storage Link
print_header "Creating Storage Symlink"
php artisan storage:link 2>/dev/null || print_warning "Storage link already exists or failed"

# Step 10: Set Permissions
print_header "Setting Directory Permissions"

chmod -R 775 storage bootstrap/cache 2>/dev/null || print_warning "Could not set permissions (you may need to do this manually)"
print_info "Ensured storage and bootstrap/cache are writable"

# Step 11: Clear and Cache Config
print_header "Optimizing Application"
DATABASE_URL="" php artisan config:clear
DATABASE_URL="" php artisan cache:clear
DATABASE_URL="" php artisan view:clear
DATABASE_URL="" php artisan route:clear
print_success "Cleared all caches"

# Optional: Build Frontend Assets
if [ "$SKIP_NODE" = false ]; then
    print_header "Building Frontend Assets"
    print_info "Do you want to build frontend assets now? (y/n)"
    read -r BUILD_ASSETS

    if [ "$BUILD_ASSETS" = "y" ] || [ "$BUILD_ASSETS" = "Y" ]; then
        npm run build
        print_success "Frontend assets built"
    else
        print_info "You can build assets later with: npm run build"
    fi
fi

# Final Summary
print_header "Setup Complete!"

echo ""
print_success "Your Laravel application is ready!"
echo ""
print_info "Next steps:"
echo "  1. Review your .env file for any custom configuration"
echo "  2. Start the development server:"
echo ""
echo -e "     ${GREEN}cd laravel-app${NC}"
echo -e "     ${GREEN}php artisan serve${NC}"
echo ""
echo "  3. Visit: http://localhost:8000"
echo ""
print_info "To scrape legislative data, run:"
echo -e "     ${GREEN}php artisan scrape:bills --chamber=all --limit=10${NC}"
echo ""
print_info "To run tests:"
echo -e "     ${GREEN}php artisan test${NC}"
echo ""
print_header "Happy Coding!"
