# Scraper Setup Guide

This guide explains how to configure the legislative bill scrapers to bypass anti-bot protection from Romanian Parliament websites.

## Problem

Both www.cdep.ro and www.senat.ro return HTTP 403 Forbidden errors when accessed via standard HTTP requests. This is due to WAF (Web Application Firewall) and bot detection systems.

## Solutions

We've implemented **three** approaches to bypass this protection. Choose the one that works best for your environment.

---

## Option 1: Residential Proxies (Recommended for Production)

**Pros:**
- Most reliable
- No browser installation needed
- Works in containerized environments

**Cons:**
- Requires paid proxy service
- Monthly cost ($20-100/month)

### Setup

1. Sign up for a residential proxy service:
   - Bright Data (https://brightdata.com)
   - Smartproxy (https://smartproxy.com)
   - Oxylabs (https://oxylabs.io)

2. Add proxy configuration to `.env`:

```env
SCRAPER_PROXY_ENABLED=true
SCRAPER_PROXY="http://username:password@proxy1.example.com:8080,http://username:password@proxy2.example.com:8080"
SCRAPER_PROXY_ROTATION=true
```

3. Test the configuration:

```bash
php artisan test:scrapers
```

**Multiple Proxies:**
Separate multiple proxy URLs with commas for automatic rotation. The scraper will cycle through them on each request.

---

## Option 2: Selenium WebDriver (Best for Development)

**Pros:**
- Most effective bypass
- Controls real browser
- Can handle JavaScript-heavy sites

**Cons:**
- Requires Chrome/Chromium installation
- Higher resource usage
- Slower than HTTP requests

### Setup

1. **Install Chromium Browser:**

```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install chromium-browser

# Or use snap
sudo snap install chromium
```

2. **Verify ChromeDriver is available:**

```bash
which chromedriver
# Should output: /opt/node22/bin/chromedriver (or similar)
```

3. **Enable Selenium in `.env`:**

```env
SELENIUM_ENABLED=true
SELENIUM_URL=http://localhost:4444
SELENIUM_HEADLESS=true
```

4. **Test Selenium:**

```bash
php artisan test:selenium
php artisan test:selenium https://www.senat.ro
```

**Screenshot for Debugging:**

```bash
php artisan test:selenium --screenshot=/tmp/cdep.png
```

5. **Update Scrapers to Use Selenium:**

Modify `app/Services/Scrapers/CDEPScraper.php` and `SenateScraper.php` to use `SeleniumScraper` instead of HTTP requests for bypassing protection.

---

## Option 3: Official API Access (Best Long-term Solution)

**Pros:**
- No blocking issues
- Officially supported
- Most reliable

**Cons:**
- Requires approval process
- May take time to get access
- Unknown if APIs are available

### Steps

1. Contact Romanian Parliament IT departments:
   - CDEP: https://www.cdep.ro/contact
   - Senate: https://www.senat.ro/Contact.aspx

2. Request:
   - API access for legislative bill data
   - Or IP whitelist for automated scraping
   - Explain purpose: civic transparency & legislative monitoring

3. Await response and follow their integration process.

---

## Testing Scrapers

### Test with Current Configuration

```bash
php artisan test:scrapers
```

This will attempt to scrape both CDEP and Senate websites and report results.

### Test Individual Website

```bash
# Test CDEP
php artisan test:selenium https://www.cdep.ro/pls/proiecte/upl_pck2015.home

# Test Senate
php artisan test:selenium https://www.senat.ro
```

---

## Scraper Commands

### Scrape Bills

```bash
# Scrape from both chambers
php artisan scrape:bills

# Scrape specific chamber
php artisan scrape:bills --chamber=cdep
php artisan scrape:bills --chamber=senate

# Limit number of bills
php artisan scrape:bills --limit=50

# Scrape specific year
php artisan scrape:bills --year=2025
```

### Monitor Scraping

```bash
# Watch logs in real-time
tail -f storage/logs/laravel.log | grep -i scraper
```

---

## Configuration Reference

All scraper settings are in `config/scraper.php` and `.env`:

```env
# Basic Settings
SCRAPER_USER_AGENT="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
SCRAPER_DELAY_SECONDS=3
SCRAPER_TIMEOUT_SECONDS=30
SCRAPER_MAX_RETRIES=3

# Proxy Settings
SCRAPER_PROXY_ENABLED=false
SCRAPER_PROXY=
SCRAPER_PROXY_ROTATION=true

# Selenium Settings
SELENIUM_ENABLED=false
SELENIUM_URL=http://localhost:4444
SELENIUM_HEADLESS=true

# Website URLs
CDEP_BASE_URL=https://www.cdep.ro
SENATE_BASE_URL=https://www.senat.ro
```

---

## Troubleshooting

### HTTP 403 Forbidden

**Cause:** Anti-bot protection blocking requests

**Solutions:**
1. Enable residential proxies
2. Use Selenium WebDriver
3. Contact Parliament for API access

### Selenium: "Chrome not found"

**Cause:** Chromium/Chrome browser not installed

**Solution:**
```bash
sudo apt-get install chromium-browser
```

### Selenium: "ChromeDriver not found"

**Cause:** ChromeDriver binary missing

**Solution:**
Download from https://chromedriver.chromium.org/ and place in `/usr/local/bin/`

### Slow Scraping

**Cause:** Rate limiting (default 3s delay between requests)

**Solution:** Adjust in `.env`:
```env
SCRAPER_DELAY_SECONDS=1
```

**Warning:** Too aggressive scraping may result in IP bans.

### Proxy Connection Failed

**Causes:**
- Invalid proxy credentials
- Proxy IP not whitelisted
- Proxy service down

**Debug:**
```bash
# Test proxy manually
curl -x "http://user:pass@proxy.com:8080" https://www.cdep.ro
```

---

## Performance Considerations

### HTTP Requests (with Proxies)
- **Speed:** ~1-2 seconds per page
- **Resources:** Low CPU, low memory
- **Reliability:** 95%+ (with good proxies)

### Selenium WebDriver
- **Speed:** ~5-10 seconds per page
- **Resources:** High CPU, high memory (~200MB per browser)
- **Reliability:** 99%+

### Recommendations

- **Development:** Use Selenium for testing
- **Production:** Use residential proxies for scraping
- **Long-term:** Request official API access

---

## Next Steps

1. Choose your bypass method (proxies recommended)
2. Configure `.env` accordingly
3. Test with `php artisan test:scrapers`
4. Run initial scrape: `php artisan scrape:bills --limit=100`
5. Set up cron for regular scraping:

```bash
# Add to crontab
0 */6 * * * cd /path/to/project && php artisan scrape:bills
```

---

## Need Help?

- Check logs: `storage/logs/laravel.log`
- Review scraper code: `app/Services/Scrapers/`
- Test individual components with artisan commands
- File issues with detailed error messages
