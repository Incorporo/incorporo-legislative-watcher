# Incorporo Legislative Watcher

An AI-powered legislative monitoring system for the Romanian Parliament (Chamber of Deputies and Senate).

## 🚀 Quick Start

**New to this project? Get up and running in 5 minutes:**

```bash
# Clone the repository
git clone https://github.com/Incorporo/incorporo-legislative-watcher.git
cd incorporo-legislative-watcher

# Run the automated setup script
./setup.sh

# Start the development server
cd laravel-app
php artisan serve
```

Then visit **http://localhost:8000** in your browser!

### Requirements

- **PHP 8.1+** with extensions: `pdo`, `sqlite3`, `mbstring`, `xml`, `curl`
- **Composer** (https://getcomposer.org)
- **Node.js 16+** and NPM (optional, for frontend assets)
- **Git**

### Manual Setup (Alternative)

If you prefer manual setup or the script doesn't work:

```bash
cd laravel-app

# Install PHP dependencies
composer install

# Install JavaScript dependencies (optional)
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Create database
touch database/database.sqlite

# Run migrations
php artisan migrate

# Start server
php artisan serve
```

### Testing the Scraper

```bash
# Scrape 10 bills from both chambers (test mode)
php artisan scrape:bills --chamber=all --limit=10

# View the scraped data
php artisan tinker
>>> \App\Models\LegislativeBill::count()
>>> \App\Models\LegislativeBill::latest()->first()
```

### Running Tests

```bash
cd laravel-app
php artisan test
```

---

## Project Overview

This project aims to automatically scrape, analyze, and monitor legislative projects from:
- **CDEP** (Camera Deputaților - Chamber of Deputies): https://www.cdep.ro
- **Senate** (Senatul României): https://www.senat.ro

The system will use AI to automatically analyze legislative processes and identify potential risks based on reading all documentation.

## Key Features (Planned)

- **Automated Scraping**: Continuous monitoring of both parliamentary chambers
- **AI Analysis**: Automatic bill summarization and risk assessment
- **Risk Detection**: Identify privacy, business, constitutional, and democratic concerns
- **Real-time Alerts**: Notifications for bills matching specific criteria
- **Public API**: Access to legislative data for developers and researchers
- **Search & Filter**: Comprehensive search across bills and AI-generated content

## Documentation

- **[RESEARCH.md](./RESEARCH.md)**: Comprehensive research on Romanian legislative websites, technical architecture, and implementation strategies (15,000+ words)
- **[NEXT-STEPS.md](./NEXT-STEPS.md)**: Actionable roadmap from POC to production deployment

## Quick Facts

### Current Status
- ✅ Research phase completed
- ⏳ Development not yet started
- 📋 Ready for implementation

### Key Research Findings

1. **No Official APIs**: Neither CDEP nor Senate provide public APIs
2. **Web Scraping Required**: All data must be extracted via web scraping
3. **Existing Projects**: Several open-source scrapers exist (mgax/mptracker, briatte/parlamentul)
4. **Feasibility**: ✅ Technically viable with clear URL patterns and stable structures

### Technology Stack (Recommended)

- **Backend**: Laravel (PHP)
- **Database**: MySQL/PostgreSQL
- **Queue System**: Redis
- **Scraping**: Goutte, Symfony DomCrawler
- **AI**: OpenAI GPT-4 or Anthropic Claude
- **Deployment**: VPS (DigitalOcean, Hetzner) or Laravel Forge

### URL Patterns Discovered

#### CDEP (Chamber of Deputies)
```
Bill List: https://www.cdep.ro/pls/proiecte/upl_pck2015.home
Bill Detail: https://www.cdep.ro/pls/proiecte/upl_pck2015.proiect?idp=[ID]
```

#### Senate
```
Bill List: https://www.senat.ro/legiproiect.aspx
Bill Detail: https://www.senat.ro/Legis/Lista.aspx?cod=[ID]&NR=[number]&AN=[year]
```

## Implementation Timeline

- **POC (Proof of Concept)**: 1-2 days
- **MVP**: 2-4 weeks
- **AI Integration**: 2-3 weeks
- **Advanced Features**: 3-4 weeks
- **Total to Launch**: 2-3 months

## Estimated Costs

### Development
- **DIY**: 2-3 months of your time
- **Outsource**: €8,000-18,000

### Operating Costs (Monthly)
- **Hosting**: $10-20/month
- **AI API**: $10-50/month
- **Email Service**: $0-15/month
- **Total**: ~$50-85/month

## Getting Started

### 1. Read the Research
Start with [RESEARCH.md](./RESEARCH.md) to understand:
- Website structures and scraping strategies
- Database schema recommendations
- AI analysis approaches
- Legal and ethical considerations

### 2. Follow the Roadmap
Check [NEXT-STEPS.md](./NEXT-STEPS.md) for:
- Phase-by-phase implementation guide
- Code examples and commands
- Success metrics and milestones
- Risk mitigation strategies

### 3. Build the POC
The fastest way to validate this concept:

```bash
# Create Laravel project
composer create-project laravel/laravel legislative-watcher

# Install dependencies
composer require fabpot/goutte

# Test scraping 50 bills
# (See NEXT-STEPS.md for detailed code)
```

## Project Goals

### Short-term
1. Validate scraping feasibility with POC
2. Build MVP with basic bill tracking
3. Deploy to production server

### Medium-term
4. Integrate AI for automated analysis
5. Launch public beta
6. Partner with NGOs and media outlets

### Long-term
7. Become the definitive legislative tracker for Romania
8. Expand to municipal/local legislation
9. Provide analytics and trend insights
10. Build sustainable business model

## Why This Matters

Romania currently lacks a modern, comprehensive legislative monitoring platform. This project will:

- **Increase Transparency**: Make parliamentary activity accessible to all citizens
- **Enable Accountability**: Allow tracking of MPs' legislative work
- **Prevent Harmful Legislation**: Early warning system for problematic bills
- **Support Democracy**: Empower journalists, activists, and engaged citizens
- **Provide Data**: Structured legislative data for research and analysis

## Competitive Advantage

Unlike existing solutions:
- ✅ **AI-Powered**: Automated risk analysis and summarization
- ✅ **Comprehensive**: Both chambers, all bill types
- ✅ **Real-time**: Continuous monitoring with alerts
- ✅ **Modern Stack**: Built with latest technologies
- ✅ **Open Data**: Public API for third-party integrations

## Similar Projects (International)

- **GovTrack.us** (USA): Congressional bill tracking
- **TheyWorkForYou** (UK): Parliament monitoring
- **NosDéputés.fr** (France): Parliamentary activity
- **Parltrack** (EU): European Parliament

**Romania has no equivalent.** This is an opportunity to fill that gap.

## Contributing

This project is in the research phase. Once development begins, contributions will be welcome:

- **Developers**: Help build scrapers and UI
- **Legal Experts**: Advise on compliance and data usage
- **Domain Experts**: Improve AI prompts for risk detection
- **Users**: Provide feedback and feature requests

## License

To be determined (likely open-source: MIT or GPL)

## Contact

**Project Owner**: Incorporo
**Research Phase**: Completed November 2025
**Status**: Ready for development

---

## Next Steps

1. ✅ Research completed (see RESEARCH.md)
2. ⏭️ Decide on solo vs. team approach
3. ⏭️ Build POC (1-2 days)
4. ⏭️ Validate feasibility
5. ⏭️ Start MVP development

**Ready to begin?** Check [NEXT-STEPS.md](./NEXT-STEPS.md) for detailed guidance.

---

**Last Updated**: 2025-11-16
**Version**: 0.1.0 (Research Phase)
