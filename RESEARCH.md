# Romanian Legislative Monitoring System - Research Report

## Executive Summary

This document presents comprehensive research on automatically scraping and monitoring legislative projects from the Romanian Parliament (Chamber of Deputies - CDEP and Senate). The research covers website structure analysis, existing solutions, technical approaches, and recommendations for building an AI-powered legislative monitoring system.

**Key Finding: No official public APIs exist for Romanian legislative data. Web scraping is the only viable approach.**

---

## 1. Target Websites Overview

### 1.1 Chamber of Deputies (Camera Deputaților)

- **Website**: https://www.cdep.ro
- **Technology**: Oracle PL/SQL (evidence: `/pls/` paths in URLs)
- **Legislative Tracking Portal**: https://www.cdep.ro/pls/proiecte/upl_pck2015.home
- **Status**: Active, but experiences intermittent 503 errors (rate limiting or load issues)

### 1.2 Romanian Senate (Senatul României)

- **Website**: https://www.senat.ro
- **Technology**: ASP.NET WebForms (evidence: `__doPostBack`, ViewState)
- **Legislative Projects**: https://www.senat.ro/legiproiect.aspx
- **Status**: More stable than CDEP, consistent availability

---

## 2. API Availability Assessment

### 2.1 Official APIs: **NONE FOUND**

Extensive research including:
- Search of official documentation
- Analysis of data.gov.ro (Romanian Open Data Portal)
- Review of website source code
- International comparisons (EU Parliament, US Congress have APIs; Romania does not)

### 2.2 Open Data Portal (data.gov.ro)

- **URL**: https://data.gov.ro
- **API**: Yes (CKAN-based)
- **Endpoints**:
  - `https://data.gov.ro/api/3/action/package_search`
  - `https://data.gov.ro/api/3/action/package_list`
- **Legislative Data**: **NOT AVAILABLE**
  - Contains election results (CSV format)
  - Contains administrative/financial data
  - Does NOT contain real-time legislative tracking data

### 2.3 Legal Framework

Romania adopted **Law No. 179/2022** on open data (transposing EU Directive 1024/2019), but legislative tracking data is not yet published as open data.

---

## 3. URL Patterns and Data Structure

### 3.1 CDEP (Chamber of Deputies)

#### Individual Bill URL Pattern
```
https://www.cdep.ro/pls/proiecte/upl_pck2015.proiect?idp=[PROJECT_ID]&cam=[CHAMBER]
```

**Parameters:**
- `idp`: Project/bill ID (numeric, sequential)
- `cam`: Chamber identifier (1=Senate, 2=Chamber of Deputies)
- `idl`: Language (1=Romanian, 2=English)

**Examples:**
- https://www.cdep.ro/pls/proiecte/upl_pck2015.proiect?idp=21835
- https://www.cdep.ro/pls/proiecte/upl_pck2015.proiect?cam=2&idp=19101

#### Bill List URL Pattern
```
https://www.cdep.ro/pls/proiecte/upl_pck.home?cam=[1|2]
```

#### MP Profile URL Pattern
```
https://www.cdep.ro/pls/parlam/structura.mp?idm=[ID]&cam=[CHAMBER]&leg=[LEGISLATURE]
```

### 3.2 Senate (Senat)

#### Individual Bill URL Pattern
```
https://www.senat.ro/Legis/Lista.aspx?cod=[ID]&pos=0&NR=[number]&AN=[year]
```

**Example:**
- https://www.senat.ro/Legis/Lista.aspx?cod=27167&pos=0&NR=b514&AN=2025

**Parameters:**
- `cod`: Internal database ID
- `NR`: Bill registration number (e.g., b514)
- `AN`: Year
- `pos`: Position in list (0-indexed)

#### Bill List URL
```
https://www.senat.ro/legiproiect.aspx
```

---

## 4. Data Available for Scraping

### 4.1 Bill/Project Metadata

**Both chambers provide:**
- Registration number and date
- Title and description
- Type (bill, legislative proposal, emergency ordinance, etc.)
- Initiators/sponsors (MPs, Government, citizens' initiative)
- Current status in legislative process
- Chamber competency (first chamber, decision chamber)
- Urgency procedure flag
- Legislative timeline (dates of submission, committee review, votes)

### 4.2 Senate-Specific Data (from actual bill B514/2025)

```
Registration: B514/2025
Title: "Propunere legislativă privind amenajarea de locuri de joacă pentru copii în aeroporturi şi gări"
Initiators: 21 signatories (5 senators, 16 deputies)
Status: "înregistrat la Senat pt. dezbatere" (registered at Senate for debate)
Timeline:
  - 21-10-2025: Registered at Senate
  - 27-10-2025: Sent for opinions (deadline: 26-11-2025)
Associated Documents: 5 PDFs
  - Legislative initiative address
  - Form of initiator
  - Explanatory memorandum
  - Opinion request letters
  - De-seizure address
```

### 4.3 Document Attachments

- **Format**: PDF files
- **Types**:
  - Full text of bills
  - Explanatory memorandums
  - Committee reports
  - Amendments
  - Voting records
  - Official correspondence

### 4.4 Parliamentary Activity

- MP questions and interpellations
- Committee schedules and agendas
- Plenary session schedules
- Voting records
- Parliamentary group information

---

## 5. Existing Open Source Solutions

### 5.1 MPTracker by Alex Morega (mgax)

- **Repository**: https://github.com/mgax/mptracker
- **Status**: Archived (Feb 2023)
- **Technology**: Python 3.3, Flask, PostgreSQL, SQLAlchemy
- **Features**:
  - Scraped MP questions from CDEP
  - Database of MPs and their activity
  - Web interface at parlament.openpolitics.ro (possibly defunct)
- **Parsing**: libxml2, libxslt for HTML parsing
- **Limitations**: Focused only on MP questions, not full legislative tracking

### 5.2 CDEP Committees Scraper (mgax)

- **Platform**: morph.io (mgax/cdep-committees)
- **Coverage**: Legislatures 1992-2016
- **Scope**: Senate, Chamber of Deputies, and cross-chamber committees
- **Status**: Last successful run in 2019 (likely outdated)

### 5.3 Parlamentul by briatte

- **Repository**: https://github.com/briatte/parlamentul
- **Technology**: R scripts
- **Purpose**: Bill cosponsorship network analysis
- **Data Sources**: Both www.cdep.ro and www.senat.ro
- **Scraping Approach**:
  - XPath parsing with R's XML package
  - Two-level iteration (chambers → years → bills)
  - Conditional downloads with resume capability
  - URL pattern: `http://www.cdep.ro/pls/proiecte/[href]`

**Key scraping patterns from data.r:**
```r
# Bill list extraction
"//a[contains(@href, '&anp') or contains(@href, '&anb') or contains(@href, '&anl') or contains(@href, '&ans')]/@href"

# Individual bills
"//a[contains(@href, 'idp=')]"

# Sponsor profiles
"//a[contains(@href, 'structura.mp?idm=')]/@href"

# MP biographical data
"//td[@class='menuoff']"
"//b[contains(text(), 'dep.') or contains(text(), 'sen.')]"
```

### 5.4 ro_parliament by r-parvulescu

- **Repository**: https://github.com/r-parvulescu/ro_parliament
- **Technology**: Python (100%)
- **Focus**: Scraping digital archives, processing speeches, organizing legislator metadata
- **Structure**: Python scripts + SQL schemas
- **Note**: Limited documentation visible

---

## 6. Technical Challenges and Considerations

### 6.1 Rate Limiting

- **CDEP**: Experiences 503 errors, suggesting aggressive rate limiting or resource constraints
- **Recommendation**: Implement respectful scraping with delays (2-5 seconds between requests)
- **Strategy**: Rotating user agents, session management, exponential backoff on failures

### 6.2 Website Technologies

**CDEP (Oracle PL/SQL):**
- Server-rendered HTML
- Dynamic content via Oracle Application Server
- Potential session management
- May use cookies for tracking

**Senate (ASP.NET):**
- ViewState parameters (must be maintained across requests)
- `__doPostBack` JavaScript callbacks
- AJAX-loaded content (DataTables plugin)
- Form-based navigation

### 6.3 Data Consistency

- No standardized data format across chambers
- Different field names and structures
- Inconsistent date formats
- Party name variations require normalization
- Legislature references span multiple years

### 6.4 Document Processing

- PDF extraction required for full legislative text
- OCR may be needed for older scanned documents
- Multiple languages (Romanian, English for some content)
- Attachments require separate downloads

### 6.5 Update Frequency

- Bills can be updated multiple times per day
- Status changes during plenary sessions
- New bills registered daily
- Amendments added during committee review
- **Recommendation**: Polling every 1-6 hours depending on priority

---

## 7. Proposed Technical Architecture

### 7.1 Technology Stack Recommendation

**Backend Framework: Laravel (PHP)**
- Excellent choice for rapid development
- Rich ecosystem for web scraping (Goutte, DomCrawler)
- Built-in queue system (for async scraping jobs)
- Robust ORM (Eloquent) for database management
- Easy deployment on shared hosting or VPS

**Alternative Considerations:**
- Python + Scrapy (more mature scraping ecosystem, but you prefer Laravel)
- Node.js + Puppeteer (for JavaScript-heavy pages, not needed here)

### 7.2 Core Components

#### A. Scraper Service
```
Laravel Components:
- Console Commands (artisan) for scheduled scraping
- Jobs & Queues (Redis/Database) for async processing
- HTTP Client (Guzzle) for making requests
- DOM Parser (symfony/dom-crawler or fabpot/goutte)
```

**Scraping Strategy:**
1. **Bill Discovery**
   - Scrape list pages from both chambers
   - Extract bill IDs and basic metadata
   - Store in `legislative_bills` table

2. **Detail Extraction**
   - Queue jobs for each bill ID
   - Fetch full bill page
   - Parse all fields and relationships
   - Download PDF attachments
   - Store in normalized database

3. **Change Detection**
   - Hash bill content
   - Compare with previous version
   - Track changes in `bill_history` table
   - Generate change notifications

#### B. Database Schema (Proposed)

```sql
-- Bills/Projects
CREATE TABLE legislative_bills (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    chamber ENUM('cdep', 'senate'),
    bill_number VARCHAR(50),
    year INT,
    internal_id VARCHAR(50), -- idp for CDEP, cod for Senate
    title TEXT,
    type VARCHAR(100), -- law, legislative proposal, emergency ordinance
    status VARCHAR(100),
    urgency_status BOOLEAN,
    first_chamber VARCHAR(50),
    decision_chamber VARCHAR(50),
    registration_date DATE,
    content_hash VARCHAR(64), -- SHA256 of content for change detection
    url TEXT,
    last_scraped_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE KEY unique_bill (chamber, internal_id)
);

-- Initiators/Sponsors
CREATE TABLE bill_initiators (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    bill_id BIGINT,
    name VARCHAR(255),
    type ENUM('mp', 'government', 'citizens'),
    party VARCHAR(100),
    chamber VARCHAR(50),
    FOREIGN KEY (bill_id) REFERENCES legislative_bills(id)
);

-- Legislative Timeline
CREATE TABLE bill_timeline (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    bill_id BIGINT,
    event_date DATE,
    event_type VARCHAR(100), -- registered, committee_review, vote, etc.
    description TEXT,
    metadata JSON, -- flexible field for varying data
    FOREIGN KEY (bill_id) REFERENCES legislative_bills(id)
);

-- Documents/Attachments
CREATE TABLE bill_documents (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    bill_id BIGINT,
    document_type VARCHAR(100),
    title VARCHAR(255),
    url TEXT,
    local_path VARCHAR(255),
    file_hash VARCHAR(64),
    file_size INT,
    downloaded_at TIMESTAMP,
    FOREIGN KEY (bill_id) REFERENCES legislative_bills(id)
);

-- Change History (for tracking updates)
CREATE TABLE bill_changes (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    bill_id BIGINT,
    field_name VARCHAR(100),
    old_value TEXT,
    new_value TEXT,
    detected_at TIMESTAMP,
    FOREIGN KEY (bill_id) REFERENCES legislative_bills(id)
);

-- AI Analysis Results
CREATE TABLE bill_analysis (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    bill_id BIGINT,
    analysis_type VARCHAR(50), -- risk_assessment, summary, impact_analysis
    analysis_result JSON, -- flexible structure for AI outputs
    confidence_score DECIMAL(3,2),
    analyzed_at TIMESTAMP,
    model_version VARCHAR(50),
    FOREIGN KEY (bill_id) REFERENCES legislative_bills(id)
);

-- Risk Flags
CREATE TABLE bill_risks (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    bill_id BIGINT,
    risk_category VARCHAR(100), -- privacy, business, constitutional, etc.
    risk_level ENUM('low', 'medium', 'high', 'critical'),
    description TEXT,
    justification TEXT, -- AI explanation
    flagged_at TIMESTAMP,
    FOREIGN KEY (bill_id) REFERENCES legislative_bills(id)
);

-- Scraping Jobs Log
CREATE TABLE scraping_jobs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    job_type VARCHAR(50), -- full_sync, incremental, single_bill
    chamber VARCHAR(50),
    status ENUM('pending', 'running', 'completed', 'failed'),
    items_processed INT,
    errors_count INT,
    started_at TIMESTAMP,
    completed_at TIMESTAMP,
    error_log TEXT
);
```

#### C. AI Analysis Engine

**Integration with LLMs:**
- OpenAI GPT-4 / Claude API for text analysis
- Local models (Llama, Mistral) for cost efficiency on simple tasks
- Prompt engineering for risk identification

**Analysis Types:**
1. **Automatic Summarization**
   - Extract key points from lengthy bills
   - Generate plain-language summaries
   - Identify affected sectors/populations

2. **Risk Detection**
   - Privacy implications (GDPR violations)
   - Business impact (new regulations, taxes)
   - Constitutional concerns
   - Democratic process irregularities (rushed legislation)
   - Transparency issues (vague language, broad powers)

3. **Trend Analysis**
   - Legislative patterns by party
   - Correlation with political events
   - Success rates by type/sponsor
   - Topic clustering

**Example AI Prompt Structure:**
```
You are analyzing Romanian legislative bill [NUMBER]/[YEAR].

Title: [TITLE]
Initiators: [INITIATORS]
Status: [STATUS]

Full text: [EXTRACTED_TEXT]

Analyze this bill and identify:
1. Primary objective and key provisions
2. Potential risks in these categories:
   - Privacy and data protection
   - Business/economic impact
   - Individual rights and freedoms
   - Democratic process concerns
   - Implementation challenges
3. Affected stakeholders
4. Urgency/importance level
5. Controversial elements

Provide structured JSON output with risk levels (low/medium/high/critical) and justifications.
```

#### D. Frontend Dashboard

**Features:**
- Bill listing with filters (chamber, status, date range, risk level)
- Individual bill detail pages
- AI-generated summaries and risk assessments
- Timeline visualization
- Search functionality (full-text across bills and documents)
- Alert subscriptions (keywords, topics, risk levels)
- RSS/email notifications
- Public API for third-party integrations

**Technology:**
- Laravel Blade templates (server-side rendering)
- Vue.js or Alpine.js for interactivity
- Tailwind CSS for styling
- Charts.js for visualizations

### 7.3 Deployment Options

#### Option 1: Traditional VPS (Recommended for MVP)
- **Provider**: DigitalOcean, Hetzner, Vultr
- **Cost**: ~$5-20/month
- **Stack**: Ubuntu + Nginx + PHP 8.2 + MySQL 8 + Redis
- **Pros**: Full control, predictable costs, easy to manage
- **Cons**: Requires some sysadmin knowledge

#### Option 2: Shared Hosting (Budget-friendly)
- **Providers**: Compatible with most cPanel hosts
- **Limitations**: Cron jobs may be limited, queue workers may need workarounds
- **Cost**: $3-10/month

#### Option 3: Laravel Forge + Cloud VPS
- **Cost**: $12/month (Forge) + $5/month (VPS)
- **Pros**: Zero-downtime deployments, easy SSL, automated backups
- **Best for**: Professional/production deployments

#### Option 4: PaaS (Platform as a Service)
- **Options**: Laravel Vapor (AWS), Heroku, Platform.sh
- **Pros**: Auto-scaling, managed infrastructure
- **Cons**: Higher cost (~$25-100/month)

---

## 8. Implementation Roadmap

### Phase 1: MVP (2-4 weeks)

**Week 1: Foundation**
- [ ] Set up Laravel project
- [ ] Design and create database schema
- [ ] Configure queue system (database or Redis)
- [ ] Create Bill and BillDocument models

**Week 2: Basic Scraping**
- [ ] Implement CDEP bill list scraper
- [ ] Implement CDEP individual bill scraper
- [ ] Implement Senate bill list scraper
- [ ] Implement Senate individual bill scraper
- [ ] Add rate limiting and error handling
- [ ] Create artisan command: `php artisan scrape:bills`

**Week 3: Data Processing**
- [ ] PDF download functionality
- [ ] Change detection system
- [ ] Data normalization (party names, dates, etc.)
- [ ] Basic deduplication logic
- [ ] Create artisan command: `php artisan scrape:sync`

**Week 4: Basic Interface**
- [ ] Bill listing page
- [ ] Bill detail page
- [ ] Basic search functionality
- [ ] Simple admin panel
- [ ] Deploy to VPS

**MVP Deliverable:**
- Working scraper for both chambers
- Database with legislative bills
- Basic web interface to browse bills
- Scheduled scraping (cron job running every 6 hours)

### Phase 2: AI Integration (2-3 weeks)

**Week 5-6: AI Analysis**
- [ ] Integrate OpenAI/Claude API
- [ ] Implement bill summarization
- [ ] Implement risk detection
- [ ] Store analysis results
- [ ] Create artisan command: `php artisan analyze:bills`

**Week 7: Enhanced Interface**
- [ ] Display AI summaries on bill pages
- [ ] Risk indicators and badges
- [ ] Category filtering by risk type
- [ ] Improved search (including AI-generated content)

**Phase 2 Deliverable:**
- AI-powered bill analysis
- Risk flagging system
- Enhanced user interface

### Phase 3: Advanced Features (3-4 weeks)

**Week 8-9: Monitoring & Alerts**
- [ ] User accounts and authentication
- [ ] Keyword alert subscriptions
- [ ] Email notifications
- [ ] RSS feeds
- [ ] Webhook support for integrations

**Week 10-11: Analytics & Reporting**
- [ ] Dashboard with statistics
- [ ] Trend analysis
- [ ] Data visualizations (charts, graphs)
- [ ] Export functionality (CSV, JSON, PDF reports)

**Week 12: Public API**
- [ ] RESTful API with Laravel Passport/Sanctum
- [ ] API documentation
- [ ] Rate limiting for API
- [ ] Public documentation site

**Phase 3 Deliverable:**
- Full-featured legislative monitoring platform
- User subscriptions and alerts
- Public API for developers
- Analytics and reporting

### Phase 4: Optimization & Scale (Ongoing)

- [ ] Performance optimization (caching, database indexing)
- [ ] Enhanced error recovery
- [ ] Historical data backfilling
- [ ] Multi-language support (EN translations)
- [ ] Mobile-responsive improvements
- [ ] Advanced AI models (trend prediction, influence analysis)
- [ ] Integration with social media for public sentiment

---

## 9. Cost Estimates

### Development Costs (if outsourcing)
- MVP (Phase 1): 80-120 hours → €2,400 - €6,000
- AI Integration (Phase 2): 60-80 hours → €1,800 - €4,000
- Advanced Features (Phase 3): 80-120 hours → €2,400 - €6,000
- **Total Development**: €6,600 - €16,000 (at €30-50/hour)

### Monthly Operating Costs

**Minimal Setup (MVP):**
- VPS hosting: $10/month
- Domain: $12/year (~$1/month)
- SSL certificate: Free (Let's Encrypt)
- **Total**: ~$11/month

**Production Setup with AI:**
- VPS (4GB RAM, 2 CPU): $20/month
- AI API costs (OpenAI GPT-4):
  - ~500 bills/month × $0.02/bill = $10/month
- Storage for PDFs (Object storage): $5/month
- Email service (SendGrid/Mailgun): $0-15/month
- Monitoring (optional): $0-10/month
- **Total**: ~$50-60/month

**Scale-Up (High Traffic):**
- Larger VPS or multiple servers: $50-100/month
- CDN (Cloudflare): $0-20/month
- Database hosting: $15-30/month
- Increased AI usage: $50-200/month
- **Total**: ~$115-350/month

---

## 10. Legal and Ethical Considerations

### 10.1 Terms of Service Review

- **Action Required**: Review CDEP and Senate websites' Terms of Service
- **robots.txt**: Check for scraping restrictions
- **Recommendation**: Implement respectful scraping practices regardless

### 10.2 Data Usage Rights

- Legislative data is **public domain** in Romania
- PDFs and official documents can be republished
- No copyright restrictions on laws and parliamentary proceedings
- **Best Practice**: Attribute source (link back to original bill pages)

### 10.3 Privacy Considerations

- MPs' names and activities are public figures → no privacy issues
- Avoid scraping personal contact information
- GDPR compliance for user accounts on your platform

### 10.4 Rate Limiting Ethics

- Respect server resources (2-5 second delays)
- Scrape during off-peak hours when possible
- Implement exponential backoff on errors
- Consider reaching out to Parliament IT departments
- Monitor for IP bans and adjust accordingly

---

## 11. Risk Assessment for the Project

### Technical Risks

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| Website structure changes | High | High | Implement flexible parsers, monitor for failures, quick update process |
| Rate limiting/IP bans | Medium | Medium | Rotating proxies, respectful delays, contact Parliament IT |
| PDF extraction failures | Medium | Low | Multiple extraction libraries, OCR fallback, manual review queue |
| AI API costs exceed budget | Medium | Medium | Local models for simple tasks, caching results, usage limits |
| Server downtime (CDEP/Senate) | Medium | Low | Retry logic, queue system, alert monitoring |

### Business/Product Risks

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| Low user adoption | Medium | Medium | Strong marketing, partner with NGOs, provide clear value |
| Competitors emerge | Low | Low | First-mover advantage, superior AI analysis, open-source community |
| Funding for operations | Medium | High | Freemium model, institutional partnerships, grants (civic tech) |

### Legal Risks

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| Scraping prohibited | Low | High | Legal review, request official data access, advocate for open data |
| Copyright claims on content | Very Low | Low | Only publish summaries, link to originals, fair use doctrine |

---

## 12. Competitive Landscape

### 12.1 Existing Solutions

**None found** that provide:
- Real-time legislative monitoring for Romania
- AI-powered risk analysis
- Comprehensive coverage of both chambers
- Public access to structured legislative data

### 12.2 Similar International Projects

- **Govtrack.us** (USA): Congressional bill tracking
- **TheyWorkForYou** (UK): Parliament monitoring
- **NosDéputés.fr** (France): Parliamentary activity tracking
- **Parltrack** (EU): European Parliament monitoring

**Opportunity**: Romania lacks a modern, AI-powered legislative monitoring platform

---

## 13. Recommendations

### 13.1 Immediate Next Steps

1. **Validate the Concept**
   - Build a minimal scraper (1-2 days)
   - Extract 50-100 bills from both chambers
   - Test AI analysis on sample bills
   - Assess quality and feasibility

2. **Legal Clearance**
   - Review Terms of Service for both websites
   - Consult with a Romanian lawyer (optional but recommended)
   - Consider reaching out to Parliament's IT departments

3. **Technical Prototype**
   - Set up Laravel project
   - Implement scrapers for both chambers
   - Create basic database
   - Test stability over 1 week

### 13.2 Strategic Approach

**Start Small, Scale Smart:**
- MVP with basic scraping and display
- Add AI analysis incrementally
- Gather user feedback early
- Iterate based on actual usage patterns

**Community Building:**
- Open-source the scraping components
- Engage with civic tech community (Code4Romania)
- Partner with transparency NGOs
- Seek feedback from legal experts and journalists

**Sustainability:**
- Explore grant funding (civic tech grants, EU digital democracy initiatives)
- Freemium model (free public access, premium alerts/API for businesses)
- Institutional subscriptions (law firms, corporations, advocacy groups)

### 13.3 Technology Choice Validation

**Laravel is an excellent choice because:**
- Rapid development (built-in auth, queue system, ORM)
- Strong community and packages for scraping
- Easy deployment (shared hosting to enterprise)
- Maintainable codebase for long-term project
- PHP hosting is cheap and ubiquitous

**Alternatives to consider:**
- Python + Django/FastAPI (stronger ML ecosystem, but you prefer Laravel)
- Ruby on Rails (similar to Laravel, smaller community)

---

## 14. Conclusion

Building an automated legislative monitoring system for Romania is **technically feasible** and **strategically valuable**. While no official APIs exist, web scraping both chambers is straightforward with clear URL patterns and stable structures.

**Key Success Factors:**
1. Robust scraping with change detection
2. Meaningful AI analysis that provides real value
3. User-friendly interface for non-technical users
4. Reliable notifications and alerting
5. Sustainable business model or funding

**Timeline to Launch:**
- MVP: 2-4 weeks
- Production-Ready: 2-3 months
- Full-Featured Platform: 4-6 months

**Estimated Investment:**
- Development: €6,600 - €16,000 (or DIY if you code)
- Monthly Operations: €50-60/month
- First Year Total: ~€10,000 - €20,000

**Expected Impact:**
- First comprehensive legislative tracker for Romania
- Increased transparency in lawmaking process
- Valuable tool for journalists, activists, businesses, and citizens
- Potential to influence policy through public awareness

---

## 15. Appendix: Technical Resources

### Useful Libraries for Laravel

**Web Scraping:**
- `fabpot/goutte` - Web scraper and crawler
- `symfony/dom-crawler` - DOM navigation
- `symfony/css-selector` - CSS selector support
- `guzzlehttp/guzzle` - HTTP client

**PDF Processing:**
- `smalot/pdfparser` - PHP PDF parser
- `spatie/pdf-to-text` - Wrapper for pdftotext
- OCR: `thiagoalessio/tesseract_ocr` if needed

**Queue & Jobs:**
- Laravel built-in queue system
- Redis for queue driver (recommended)
- `laravel/horizon` for queue monitoring

**AI Integration:**
- `openai-php/client` - OpenAI PHP client
- `anthropic-ai/anthropic-sdk-php` - Claude API client (if available)
- Generic HTTP clients for other LLM APIs

**Database & ORM:**
- Laravel Eloquent (built-in)
- `doctrine/dbal` for advanced DB operations
- `laravel/scout` for full-text search

### Example Scraping Code Structure

```php
<?php
namespace App\Services\Scrapers;

use Goutte\Client;
use App\Models\LegislativeBill;

class CDEPScraper
{
    protected $client;
    protected $baseUrl = 'https://www.cdep.ro';

    public function __construct()
    {
        $this->client = new Client();
    }

    public function scrapeBillList($chamber = 2)
    {
        $url = "{$this->baseUrl}/pls/proiecte/upl_pck.home?cam={$chamber}";
        $crawler = $this->client->request('GET', $url);

        // Parse bill links
        $bills = $crawler->filter('a[href*="idp="]')->each(function ($node) {
            $href = $node->attr('href');
            preg_match('/idp=(\d+)/', $href, $matches);
            return [
                'idp' => $matches[1] ?? null,
                'title' => $node->text(),
                'url' => $this->baseUrl . $href,
            ];
        });

        return $bills;
    }

    public function scrapeBillDetail($idp)
    {
        $url = "{$this->baseUrl}/pls/proiecte/upl_pck2015.proiect?idp={$idp}";
        $crawler = $this->client->request('GET', $url);

        // Extract bill details
        $data = [
            'internal_id' => $idp,
            'chamber' => 'cdep',
            'title' => $crawler->filter('h1.title')->text(),
            'status' => $crawler->filter('.status')->text(),
            // ... more fields
        ];

        // Extract documents
        $documents = $crawler->filter('a[href*=".pdf"]')->each(function ($node) {
            return [
                'url' => $node->attr('href'),
                'title' => $node->text(),
            ];
        });

        $data['documents'] = $documents;

        return $data;
    }

    public function saveBill($data)
    {
        return LegislativeBill::updateOrCreate(
            [
                'chamber' => $data['chamber'],
                'internal_id' => $data['internal_id'],
            ],
            $data
        );
    }
}
```

### Example Artisan Command

```php
<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Scrapers\CDEPScraper;
use App\Services\Scrapers\SenateScraper;

class ScrapeBills extends Command
{
    protected $signature = 'scrape:bills {--chamber=all} {--full}';
    protected $description = 'Scrape legislative bills from Romanian Parliament';

    public function handle()
    {
        $chamber = $this->option('chamber');
        $full = $this->option('full');

        if (in_array($chamber, ['all', 'cdep'])) {
            $this->info('Scraping CDEP...');
            $scraper = new CDEPScraper();
            $bills = $scraper->scrapeBillList();

            $this->info('Found ' . count($bills) . ' bills');

            $bar = $this->output->createProgressBar(count($bills));

            foreach ($bills as $bill) {
                sleep(2); // Rate limiting
                $details = $scraper->scrapeBillDetail($bill['idp']);
                $scraper->saveBill($details);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }

        // Similar for Senate...

        $this->info('Scraping completed!');
    }
}
```

---

**Document Version**: 1.0
**Last Updated**: 2025-11-16
**Author**: Claude (Anthropic) - Research for Incorporo Legislative Watcher Project
**Status**: Initial Research Complete

