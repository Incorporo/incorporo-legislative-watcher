# CRON Setup Guide for Romanian Legislative Watcher

This guide explains how to set up automated scraping using CRON jobs for the Romanian Legislative Watcher application.

## Table of Contents

1. [Overview](#overview)
2. [Prerequisites](#prerequisites)
3. [CRON Job Configuration](#cron-job-configuration)
4. [Available Commands](#available-commands)
5. [Recommended Schedule](#recommended-schedule)
6. [Monitoring](#monitoring)
7. [Troubleshooting](#troubleshooting)

---

## Overview

The system uses Laravel Artisan commands to automate:
- **Bill List Scraping**: Discover new legislative bills
- **Incremental Updates**: Update existing bills with latest status
- **Document Downloads**: Fetch PDF documents automatically
- **AI Analysis**: (Future) Analyze bills for risks and summaries

All commands are designed to be **self-contained**, **resumable**, and **CRON-friendly**.

---

## Prerequisites

### 1. Laravel Scheduler (Recommended)

Laravel includes a built-in task scheduler that requires only a single CRON entry:

```bash
* * * * * cd /path/to/legislative-watcher && php artisan schedule:run >> /dev/null 2>&1
```

**Define your schedule in `app/Console/Kernel.php`:**

```php
protected function schedule(Schedule $schedule)
{
    // Incremental scraping every 6 hours
    $schedule->command('scrape:incremental --hours=6')
             ->everySixHours()
             ->withoutOverlapping()
             ->runInBackground();

    // Document downloads every 4 hours (off-peak)
    $schedule->command('scrape:documents --limit=100')
             ->cron('0 */4 * * *')
             ->withoutOverlapping()
             ->runInBackground();

    // Full scrape weekly (Sundays at 3 AM)
    $schedule->command('scrape:bills --chamber=all --full')
             ->weeklyOn(0, '3:00')
             ->withoutOverlapping();
}
```

### 2. Direct CRON Jobs (Alternative)

If you prefer direct CRON configuration:

```bash
# Open crontab editor
crontab -e
```

Add entries (see [Recommended Schedule](#recommended-schedule) below).

---

## Available Commands

### 1. **`scrape:bills`** - Full Bill Scraping

Scrapes bill lists and optionally their full details.

**Usage:**
```bash
php artisan scrape:bills [options]
```

**Options:**
- `--chamber=all|cdep|senate` : Which chamber to scrape (default: all)
- `--year=2025` : Filter by year
- `--limit=100` : Maximum bills to scrape
- `--full` : Scrape full details for each bill (slower but comprehensive)
- `--force` : Force rescraping even if recently scraped

**Examples:**
```bash
# Scrape all recent bills (list only)
php artisan scrape:bills

# Full scrape of CDEP for 2025
php artisan scrape:bills --chamber=cdep --year=2025 --full

# Quick scrape of latest 50 Senate bills
php artisan scrape:bills --chamber=senate --limit=50
```

**When to use:**
- Initial data seeding
- Weekly full sync
- After system maintenance

---

### 2. **`scrape:incremental`** - Incremental Updates

Updates bills that haven't been scraped recently. **CRON-optimized**.

**Usage:**
```bash
php artisan scrape:incremental [options]
```

**Options:**
- `--chamber=all|cdep|senate` : Which chamber (default: all)
- `--hours=6` : Only scrape bills older than N hours (default: 6)

**Examples:**
```bash
# Update bills not scraped in last 6 hours
php artisan scrape:incremental

# Update CDEP bills older than 12 hours
php artisan scrape:incremental --chamber=cdep --hours=12
```

**When to use:**
- Regular CRON jobs (every 4-6 hours)
- Keeping data fresh without overloading servers
- Respecting rate limits

**Smart Prioritization:**
- Urgent bills (urgency_status = true)
- Recently changed bills
- Never-scraped bills
- Oldest scraped bills

---

### 3. **`scrape:documents`** - Document Downloads

Downloads PDF documents attached to bills.

**Usage:**
```bash
php artisan scrape:documents [options]
```

**Options:**
- `--limit=50` : Maximum documents to download (default: 50)
- `--force` : Re-download existing documents

**Examples:**
```bash
# Download up to 50 pending documents
php artisan scrape:documents

# Download up to 200 documents
php artisan scrape:documents --limit=200

# Force re-download all documents
php artisan scrape:documents --force --limit=1000
```

**When to use:**
- After bill scraping
- During off-peak hours (night time)
- When preparing for AI analysis

---

## Recommended Schedule

### Option A: Using Laravel Scheduler (Recommended)

**Single CRON entry:**
```bash
* * * * * cd /var/www/legislative-watcher && php artisan schedule:run >> /dev/null 2>&1
```

**Schedule definition in `app/Console/Kernel.php`:**
```php
protected function schedule(Schedule $schedule)
{
    // Every 6 hours: incremental bill updates
    $schedule->command('scrape:incremental --hours=6')
             ->cron('0 */6 * * *')
             ->withoutOverlapping()
             ->emailOutputOnFailure('admin@example.com');

    // Every 4 hours (offset): document downloads
    $schedule->command('scrape:documents --limit=100')
             ->cron('30 */4 * * *')
             ->withoutOverlapping();

    // Weekly full sync: Sunday 3 AM
    $schedule->command('scrape:bills --chamber=all --full --limit=500')
             ->weeklyOn(0, '3:00')
             ->withoutOverlapping();

    // Daily urgent bill check: Every day at 9 AM and 5 PM
    $schedule->command('scrape:incremental --hours=2')
             ->twiceDaily(9, 17);
}
```

### Option B: Direct CRON Entries

```bash
# Incremental scrape every 6 hours
0 */6 * * * cd /var/www/legislative-watcher && php artisan scrape:incremental --hours=6 >> /var/log/legislative-watcher/scraper.log 2>&1

# Document downloads every 4 hours (offset by 30 minutes)
30 */4 * * * cd /var/www/legislative-watcher && php artisan scrape:documents --limit=100 >> /var/log/legislative-watcher/documents.log 2>&1

# Full sync every Sunday at 3 AM
0 3 * * 0 cd /var/www/legislative-watcher && php artisan scrape:bills --chamber=all --full >> /var/log/legislative-watcher/full-sync.log 2>&1

# Quick urgent check twice daily (9 AM and 5 PM)
0 9,17 * * * cd /var/www/legislative-watcher && php artisan scrape:incremental --hours=2 >> /var/log/legislative-watcher/urgent.log 2>&1
```

---

## Setup Instructions

### Step 1: Create Log Directory

```bash
sudo mkdir -p /var/log/legislative-watcher
sudo chown www-data:www-data /var/log/legislative-watcher
```

### Step 2: Create Wrapper Script (Optional)

Create `/usr/local/bin/legislative-scraper.sh`:

```bash
#!/bin/bash

# Configuration
APP_PATH="/var/www/legislative-watcher"
PHP_BIN="/usr/bin/php"
LOG_DIR="/var/log/legislative-watcher"

# Change to application directory
cd "$APP_PATH" || exit 1

# Execute command with logging
"$PHP_BIN" artisan "$@" >> "$LOG_DIR/scraper-$(date +\%Y\%m\%d).log" 2>&1

# Rotate logs older than 30 days
find "$LOG_DIR" -name "scraper-*.log" -mtime +30 -delete
```

Make it executable:
```bash
chmod +x /usr/local/bin/legislative-scraper.sh
```

**Use in CRON:**
```bash
0 */6 * * * /usr/local/bin/legislative-scraper.sh scrape:incremental --hours=6
```

### Step 3: Configure Laravel Scheduler

Edit `app/Console/Kernel.php` (see [Option A](#option-a-using-laravel-scheduler-recommended) above).

Add single CRON entry:
```bash
crontab -e
```

```bash
* * * * * cd /var/www/legislative-watcher && php artisan schedule:run >> /dev/null 2>&1
```

### Step 4: Test Commands

```bash
# Test incremental scrape
php artisan scrape:incremental --hours=24

# Check scraping job status
php artisan tinker
>>> App\Models\ScrapingJob::latest()->first()

# View recent logs
tail -f storage/logs/laravel.log
```

---

## Monitoring

### 1. Database Monitoring

Check scraping job status:

```sql
-- Recent scraping jobs
SELECT * FROM scraping_jobs
ORDER BY created_at DESC
LIMIT 10;

-- Failed jobs
SELECT * FROM scraping_jobs
WHERE status = 'failed'
ORDER BY created_at DESC;

-- Success rate
SELECT
    status,
    COUNT(*) as count,
    ROUND(AVG(duration_seconds), 2) as avg_duration,
    SUM(items_processed) as total_items
FROM scraping_jobs
WHERE created_at > NOW() - INTERVAL 7 DAY
GROUP BY status;
```

### 2. Laravel Telescope (Recommended)

Install Telescope for debugging:

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

Access at: `https://your-domain.com/telescope`

### 3. Log Monitoring

```bash
# Watch real-time logs
tail -f storage/logs/laravel.log

# Check CRON logs
tail -f /var/log/legislative-watcher/scraper.log

# Search for errors
grep -i "error\|exception" storage/logs/laravel.log
```

### 4. Health Check Endpoint

Create a health check route in `routes/web.php`:

```php
Route::get('/health/scraper', function () {
    $lastJob = \App\Models\ScrapingJob::latest()->first();

    if (!$lastJob) {
        return response()->json(['status' => 'warning', 'message' => 'No scraping jobs found'], 503);
    }

    $hoursSinceLastRun = now()->diffInHours($lastJob->created_at);

    if ($hoursSinceLastRun > 12) {
        return response()->json([
            'status' => 'error',
            'message' => 'Scraper has not run in over 12 hours',
            'last_run' => $lastJob->created_at,
        ], 503);
    }

    return response()->json([
        'status' => 'ok',
        'last_job' => $lastJob->only(['status', 'created_at', 'items_processed']),
        'hours_since_last_run' => $hoursSinceLastRun,
    ]);
});
```

Monitor with cron:
```bash
*/30 * * * * curl -f https://your-domain.com/health/scraper || echo "Scraper health check failed" | mail -s "Alert: Scraper Down" admin@example.com
```

---

## Error Handling

### Automatic Retry Logic

All scrapers include:
- **3 retries** with exponential backoff
- **Rate limiting** (3 seconds between requests)
- **503 handling** (waits 10 seconds on rate limit)
- **Error logging** to database and Laravel logs

### Manual Retry

If a job fails:

```bash
# Check failed job
php artisan tinker
>>> $job = App\Models\ScrapingJob::where('status', 'failed')->latest()->first()
>>> $job->error_log

# Retry manually
php artisan scrape:incremental --force
```

---

## Troubleshooting

### Issue: CRON not running

**Check:**
```bash
# Verify CRON service is running
systemctl status cron

# Check crontab is configured
crontab -l

# Check CRON logs
tail -f /var/log/syslog | grep CRON
```

**Solution:**
```bash
# Restart CRON
sudo systemctl restart cron
```

### Issue: Permission denied

**Symptoms:** CRON runs but fails with permission errors

**Solution:**
```bash
# Fix ownership
sudo chown -R www-data:www-data /var/www/legislative-watcher

# Fix permissions
sudo chmod -R 755 /var/www/legislative-watcher
sudo chmod -R 775 /var/www/legislative-watcher/storage
sudo chmod -R 775 /var/www/legislative-watcher/bootstrap/cache
```

### Issue: Commands not found

**Symptoms:** `php artisan command not found`

**Solution:**
```bash
# Verify PHP path
which php

# Update CRON to use full path
0 */6 * * * cd /var/www/legislative-watcher && /usr/bin/php artisan scrape:incremental
```

### Issue: Rate limiting / IP bans

**Symptoms:** Lots of 503 errors in logs

**Solutions:**
1. **Increase delays:**
   Edit `BaseScraper.php`, change `$delaySeconds = 5;`

2. **Reduce frequency:**
   Change CRON from every 6 hours to every 12 hours

3. **Rotate IPs:**
   Use proxy rotation (advanced)

### Issue: Memory exhaustion

**Symptoms:** `Allowed memory size exhausted`

**Solution:**
```bash
# Increase PHP memory limit
php artisan scrape:bills -d memory_limit=512M

# Or edit php.ini
sudo nano /etc/php/8.2/cli/php.ini
# Set: memory_limit = 512M
```

---

## Performance Optimization

### 1. Use Queues for Parallel Processing

Configure Redis or database queue:

```bash
# Install Redis
sudo apt install redis-server
composer require predis/predis

# Update .env
QUEUE_CONNECTION=redis

# Run queue worker
php artisan queue:work --tries=3 --timeout=300
```

Convert scraping to queued jobs for better performance.

### 2. Enable Caching

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Database Indexing

Indexes are already included in migrations. Ensure they're applied:

```bash
php artisan migrate
```

---

## Security Best Practices

1. **Never run CRON as root** - Use www-data or dedicated user
2. **Restrict file permissions** - 755 for directories, 644 for files
3. **Rotate logs** - Prevent disk space exhaustion
4. **Monitor for failures** - Set up email alerts
5. **Use HTTPS** - Encrypt all outgoing requests

---

## Example: Complete Production Setup

```bash
#!/bin/bash
# Production setup script

APP_PATH="/var/www/legislative-watcher"
LOG_DIR="/var/log/legislative-watcher"

# 1. Create log directory
sudo mkdir -p "$LOG_DIR"
sudo chown www-data:www-data "$LOG_DIR"

# 2. Install Laravel scheduler
echo "* * * * * cd $APP_PATH && php artisan schedule:run >> /dev/null 2>&1" | sudo -u www-data crontab -

# 3. Set up log rotation
sudo tee /etc/logrotate.d/legislative-watcher <<EOF
$LOG_DIR/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
EOF

# 4. Create systemd service for queue worker
sudo tee /etc/systemd/system/legislative-watcher-queue.service <<EOF
[Unit]
Description=Legislative Watcher Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=$APP_PATH
ExecStart=/usr/bin/php $APP_PATH/artisan queue:work --tries=3 --timeout=300
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF

# 5. Enable and start queue worker
sudo systemctl enable legislative-watcher-queue
sudo systemctl start legislative-watcher-queue

echo "✅ Production CRON setup complete!"
```

---

## Support

For issues or questions:
- Check logs: `storage/logs/laravel.log`
- Review scraping jobs: `SELECT * FROM scraping_jobs ORDER BY created_at DESC;`
- GitHub Issues: https://github.com/your-repo/issues

---

**Last Updated:** 2025-11-17
**Version:** 1.0
