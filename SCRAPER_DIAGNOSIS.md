# Bill Scraper Diagnosis Report
**Date**: 2025-11-18
**Status**: CRITICAL - Scraper returning 0 bills

## Problems Identified

### 1. Proxy DNS Resolution Failure ⚠️ CRITICAL
**Issue**: The proxy server `p.webshare.io` cannot be resolved by DNS
**Evidence**:
```
CURL Error: Could not resolve proxy: p.webshare.io
```

**Possible Causes**:
- Proxy service may be down or terminated
- DNS resolution blocked in this environment
- Proxy domain changed
- Firewall blocking DNS lookups

**Testing**:
```bash
# Test DNS resolution
nslookup p.webshare.io
dig p.webshare.io

# Test proxy connectivity
curl -x http://dzkjcikr-rotate:heh59hhzv7la@p.webshare.io:80 https://ipinfo.io
```

### 2. Proxy Not Enabled in Config ⚠️ HIGH
**Issue**: Even with `SCRAPER_PROXY` set, the proxy is disabled
**Location**: `laravel-app/config/scraper.php:38`

**Current Config**:
```php
'proxy_enabled' => env('SCRAPER_PROXY_ENABLED', false),  // ← Defaults to FALSE
'proxy' => env('SCRAPER_PROXY', null),
```

**Solution**: Set environment variable:
```bash
SCRAPER_PROXY_ENABLED=true
```

### 3. Missing .env File ⚠️ HIGH
**Issue**: No `.env` file found, configuration using defaults
**Impact**: Environment variables not loaded

**Solution**: Create `.env` file based on `.env.example`

### 4. Anti-Bot Protection ⚠️ CRITICAL
**Issue**: Both parliament websites have bot detection

**CDEP**:
- Returns 503 Service Unavailable
- TLS handshake failures
- Likely Cloudflare or similar protection

**Senate**:
- Cloudflare detected in HTML response
- JavaScript/AJAX-loaded content
- ViewState requirements (ASP.NET)

**Evidence from tests**:
- Direct curl to CDEP: `upstream connect error or disconnect/reset before headers`
- Senate response: Contains `cloudflare` strings

### 5. Dynamic Content Loading ⚠️ HIGH
**Issue**: Bill lists loaded via JavaScript, not in initial HTML

**Senate Analysis**:
- Page loads successfully (HTTP 200)
- HTML contains 181,347 bytes
- But 0 bill links found with `cod=` pattern
- Likely uses DataTables or AJAX to populate bills

## Solutions

### Immediate Fixes

#### Option 1: Fix Proxy Configuration (If proxy is valid)
1. Test if proxy is accessible from your production server
2. Set environment variables:
```bash
SCRAPER_PROXY_ENABLED=true
SCRAPER_PROXY=http://dzkjcikr-rotate:heh59hhzv7la@p.webshare.io:80
```

#### Option 2: Use Different Proxy Service
If webshare.io proxy is down, try alternatives:
- Bright Data (formerly Luminati)
- Oxylabs
- Smartproxy
- ScraperAPI (has built-in JavaScript rendering)

#### Option 3: Use Selenium/Headless Browser (RECOMMENDED)
Since both sites have anti-bot protection and JavaScript content:

1. Enable Selenium in config:
```bash
SELENIUM_ENABLED=true
SELENIUM_URL=http://localhost:4444
```

2. Deploy Selenium Grid with Chrome/Firefox
3. Update scrapers to use SeleniumScraper instead of BaseScraper

### Long-term Solutions

#### 1. Implement Selenium-Based Scraping
- Renders JavaScript content
- Bypasses many anti-bot protections
- Can handle dynamic page interactions
- Supports proxy rotation

**Implementation**: Already started in `app/Services/Scrapers/SeleniumScraper.php`

#### 2. Use ScraperAPI or Similar Service
- Handles JavaScript rendering
- Automatic proxy rotation
- Built-in anti-bot bypass
- Pay-per-request model

**Cost**: ~$0.001-0.01 per request

#### 3. Request Official Data Access
Contact Romanian Parliament IT departments:
- CDEP: informatica@cdep.ro
- Senate: webmaster@senat.ro

Request:
- Official API access
- Data dumps
- Exemption from rate limiting

#### 4. Combination Approach
- Use direct HTTP for pages that work
- Fall back to Selenium for protected pages
- Implement intelligent retry logic

## Recommended Action Plan

### Step 1: Environment Setup (30 minutes)
1. Create `.env` file
2. Set proxy configuration
3. Test proxy connectivity
4. Clear config cache

### Step 2: Test Without Proxy (15 minutes)
Determine which sites work without proxy:
```bash
SCRAPER_PROXY_ENABLED=false php artisan scrape:bills --limit=10
```

### Step 3: Deploy Selenium (2 hours)
If proxy fails or sites block HTTP:
1. Set up Selenium Grid with Docker
2. Configure Chrome with stealth plugins
3. Update scrapers to use Selenium
4. Test scraping

### Step 4: Production Testing (1 hour)
1. Run full scrape with logging
2. Monitor for errors
3. Verify data quality
4. Set up monitoring alerts

## Technical Details

### Working URLs (from research)
**CDEP**:
- Bill list: `https://www.cdep.ro/pls/proiecte/upl_pck2015.home?cam=2`
- Individual bill: `https://www.cdep.ro/pls/proiecte/upl_pck2015.proiect?idp={ID}&cam=2`

**Senate**:
- Bill list: `https://www.senat.ro/LegiProiect.aspx` (case sensitive!)
- Individual bill: `https://www.senat.ro/Legis/Lista.aspx?cod={ID}`

### Current vs Research URLs
**Mismatch found**:
- SenateScraper uses: `/legiproiect.aspx` (lowercase)
- Research shows: `/LegiProiect.aspx` (CamelCase)
- ASP.NET is case-sensitive on some servers!

## Next Steps

1. **IMMEDIATE**: Test proxy connectivity from production environment
2. **HIGH PRIORITY**: Create .env file with proper configuration
3. **HIGH PRIORITY**: Fix URL casing in SenateScraper
4. **MEDIUM**: Consider Selenium deployment
5. **LOW**: Contact Parliament for official data access

## Files to Review/Modify

1. `laravel-app/.env` - Create and configure
2. `laravel-app/app/Services/Scrapers/SenateScraper.php:27` - Fix URL casing
3. `laravel-app/app/Services/Scrapers/BaseScraper.php:30-45` - Proxy configuration loading
4. `laravel-app/config/scraper.php` - Verify configuration

## Test Commands

```bash
# Test proxy directly
curl -x http://dzkjcikr-rotate:heh59hhzv7la@p.webshare.io:80 https://ipinfo.io

# Test CDEP accessibility
curl -I https://www.cdep.ro/pls/proiecte/upl_pck2015.home?cam=2

# Test Senate accessibility
curl -I https://www.senat.ro/LegiProiect.aspx

# Run debug script
php simple-scraper-test.php

# Run scraper with debugging
php artisan scrape:bills --chamber=senate --limit=5 -vvv
```

## Monitoring

Set up monitoring for:
- Proxy connectivity
- HTTP response codes
- Bills found per scrape
- Error rates
- Scrape duration

Use Laravel logging and consider:
- Sentry for error tracking
- Uptime Robot for availability monitoring
- Custom dashboard for scraping metrics
