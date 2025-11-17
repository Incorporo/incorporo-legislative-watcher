#!/bin/bash

###############################################################################
# Quick Fix Script for Composer Installation Issues
###############################################################################
# This script fixes common composer installation problems
# Usage: ./fix-composer.sh
###############################################################################

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

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

print_header "Composer Installation Fix Script"

# Check if we're in the right directory
if [ ! -f "composer.json" ]; then
    print_error "composer.json not found!"
    print_info "Please run this script from the laravel-app directory:"
    echo "  cd laravel-app"
    echo "  ../fix-composer.sh"
    exit 1
fi

# Step 1: Check PHP version
print_info "Checking PHP version..."
PHP_VERSION=$(php -r "echo PHP_VERSION;" | cut -d. -f1,2)
print_success "PHP $PHP_VERSION detected"

# Step 2: Check critical extensions
print_info "Checking critical PHP extensions..."
CRITICAL_EXTENSIONS=("xml" "dom" "mbstring" "pdo" "tokenizer")
MISSING=0

for ext in "${CRITICAL_EXTENSIONS[@]}"; do
    if php -m | grep -qi "^$ext$"; then
        print_success "$ext extension found"
    else
        print_error "$ext extension is MISSING!"
        MISSING=1
    fi
done

if [ $MISSING -eq 1 ]; then
    print_error "Critical extensions are missing!"
    print_info "Install them with:"
    echo ""
    echo "  # For PHP 8.4 (Debian/Ubuntu):"
    echo "  sudo apt-get install -y php8.4-xml php8.4-mbstring php8.4-curl"
    echo ""
    echo "  # For PHP 8.1-8.3 (Debian/Ubuntu):"
    echo "  sudo apt-get install -y php-xml php-mbstring php-curl"
    echo ""
    exit 1
fi

# Step 3: Clean up existing installation
print_header "Cleaning Up Previous Installation"

if [ -d "vendor" ]; then
    print_info "Removing vendor directory..."
    rm -rf vendor
    print_success "Vendor directory removed"
fi

if [ -f "composer.lock" ]; then
    print_info "Removing composer.lock..."
    rm -f composer.lock
    print_success "composer.lock removed"
fi

# Step 4: Clear Composer cache
print_info "Clearing Composer cache..."
composer clear-cache 2>/dev/null || true
print_success "Composer cache cleared"

# Step 5: Clean bootstrap cache
if [ -d "bootstrap/cache" ]; then
    print_info "Cleaning bootstrap cache..."
    rm -f bootstrap/cache/*.php 2>/dev/null || true
    print_success "Bootstrap cache cleaned"
fi

# Step 6: Install dependencies
print_header "Installing Composer Dependencies"
print_info "This may take several minutes..."

# Try normal install first
if composer install --no-interaction --prefer-dist --optimize-autoloader 2>&1 | tee /tmp/composer-output.log; then
    print_success "Composer dependencies installed successfully!"
else
    print_warning "Standard install failed, checking for platform requirement issues..."

    # Check if it's just warnings
    if grep -q "ext-xml" /tmp/composer-output.log; then
        print_info "Trying with --ignore-platform-req=ext-xml..."
        composer install --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-req=ext-xml
    else
        print_error "Installation failed. See /tmp/composer-output.log for details"
        exit 1
    fi
fi

# Step 7: Verify installation
print_header "Verifying Installation"

if [ -f "vendor/autoload.php" ]; then
    print_success "vendor/autoload.php exists"
else
    print_error "vendor/autoload.php not found!"
    exit 1
fi

# Step 8: Set up .env if needed
if [ ! -f ".env" ]; then
    print_info "Creating .env file..."
    if [ -f ".env.example" ]; then
        cp .env.example .env
        print_success ".env file created"
    else
        print_warning ".env.example not found, skipping..."
    fi
fi

# Step 9: Generate application key if needed
if [ -f ".env" ]; then
    if ! grep -q "APP_KEY=base64:" .env; then
        print_info "Generating application key..."
        php artisan key:generate --ansi
        print_success "Application key generated"
    else
        print_success "Application key already exists"
    fi
fi

# Step 10: Set permissions
print_info "Setting directory permissions..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || print_warning "Could not set permissions (run with sudo if needed)"

# Step 11: Test artisan
print_header "Testing Laravel Installation"

if php artisan --version >/dev/null 2>&1; then
    VERSION=$(php artisan --version)
    print_success "$VERSION is working!"
else
    print_error "Artisan command failed!"
    exit 1
fi

# Step 12: Clear all caches
print_info "Clearing all caches..."
php artisan config:clear >/dev/null 2>&1 || true
php artisan cache:clear >/dev/null 2>&1 || true
php artisan route:clear >/dev/null 2>&1 || true
print_success "All caches cleared"

# Final summary
print_header "Installation Complete!"
echo ""
print_success "Laravel is now properly installed and configured!"
echo ""
print_info "Next steps:"
echo "  1. Review your .env file: nano .env"
echo "  2. Run migrations: php artisan migrate"
echo "  3. Start dev server: php artisan serve"
echo ""
print_info "For more help, see TROUBLESHOOTING.md"
echo ""
