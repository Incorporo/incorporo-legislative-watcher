# AI Bill Assessment Feature

## Overview

The Legislative Watcher now includes AI-powered bill assessment using OpenRouter AI. This feature automatically analyzes legislative bills and provides comprehensive assessments including pros, cons, risks, economic impact, and recommendations.

## Features

### Comprehensive Analysis
For each bill, the AI provides:

- **Summary** - Brief overview of what the bill does
- **Category & Tags** - Classification and keywords
- **Impact Assessment** - Scope, magnitude, and timeframe
- **Pros** - Positive aspects with explanations and affected stakeholders
- **Cons** - Negative aspects with explanations and affected stakeholders
- **Risks** - Potential risks with severity, probability, and mitigation
- **Economic Impact** - Budget requirements, affected sectors, employment impact
- **Compliance Considerations** - Legal and regulatory considerations
- **Recommendations** - Actionable recommendations
- **Overall Assessment** - Balanced/positive/negative/neutral rating
- **Confidence Score** - AI's confidence in the analysis (0.0-1.0)

### Cost Tracking
The system tracks:
- Token usage per analysis
- Estimated cost per analysis
- Processing time
- Model version used

## Setup

### 1. Get OpenRouter API Key

1. Visit [https://openrouter.ai](https://openrouter.ai)
2. Sign up for an account
3. Navigate to [https://openrouter.ai/keys](https://openrouter.ai/keys)
4. Generate a new API key
5. Add credits to your account (recommended: start with $5)

### 2. Configure Laravel

Edit `/laravel-app/.env` and add your API key:

```bash
OPENROUTER_API_KEY=sk-or-v1-your-api-key-here
OPENROUTER_MODEL=anthropic/claude-3.5-sonnet
OPENROUTER_MAX_TOKENS=4096
OPENROUTER_TEMPERATURE=0.7
```

### 3. Test Connection

```bash
cd laravel-app
php artisan assess:bills --test
```

Expected output:
```
✓ Successfully connected to OpenRouter API
✓ 100+ models available
OpenRouter is configured correctly!
```

## Usage

### Assess All Unanalyzed Bills

```bash
php artisan assess:bills
```

This will:
- Find all bills where `analyzed = false`
- Analyze each bill using AI
- Save results to `bill_analysis` table
- Update bill's `analyzed` flag

### Assess with Limit

```bash
php artisan assess:bills --limit=10
```

Process only the first 10 unanalyzed bills.

### Assess Specific Bill

```bash
php artisan assess:bills --bill=5
```

Analyze a specific bill by ID.

### Re-assess Already Analyzed Bills

```bash
php artisan assess:bills --force
```

Re-analyze all bills, even if they were previously analyzed.

### Combined Options

```bash
php artisan assess:bills --force --limit=5
```

Re-analyze the first 5 bills.

## Output Example

```
=================================
Legislative Bill AI Assessment
=================================

Found 15 bills to assess

 3/15 [=====>----------------------] 20% Assessing Bill #12: Legea privind...

=================================
ASSESSMENT SUMMARY
=================================
┌─────────────────────────┬──────────┐
│ Metric                  │ Value    │
├─────────────────────────┼──────────┤
│ Total Bills             │ 15       │
│ Successfully Assessed   │ 15       │
│ Failed                  │ 0        │
│ Total Tokens Used       │ 45,230   │
│ Total Cost              │ $0.2150  │
│ Average Time per Bill   │ 3,450ms  │
│ Total Processing Time   │ 51.75s   │
└─────────────────────────┴──────────┘

✓ Bill assessments have been saved to the database
✓ You can view them on the dashboard or bills list
```

## Database Schema

### bill_analysis Table

```sql
CREATE TABLE bill_analysis (
    id BIGINT PRIMARY KEY,
    bill_id BIGINT NOT NULL,
    analysis_type VARCHAR(255),          -- e.g., 'ai_assessment'
    analysis_result JSON,                 -- Full JSON analysis
    confidence_score DECIMAL(3,2),        -- 0.00 to 1.00
    token_count INT,                      -- Tokens used
    analysis_cost DECIMAL(10,6),          -- Cost in USD
    model_version VARCHAR(255),           -- e.g., 'anthropic/claude-3.5-sonnet'
    prompt_version VARCHAR(50),           -- e.g., '1.0'
    processing_time_ms INT,               -- Processing time in ms
    analyzed_at TIMESTAMP,
    human_reviewed BOOLEAN,
    approved BOOLEAN,
    review_notes TEXT,
    reviewed_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Analysis Result JSON Structure

```json
{
  "summary": "Brief 2-3 sentence summary",
  "category": "Economic",
  "tags": ["taxation", "budget", "reform"],
  "impact_assessment": {
    "scope": "All businesses",
    "magnitude": "high",
    "timeframe": "medium-term"
  },
  "pros": [
    {
      "point": "Increases government revenue",
      "explanation": "Additional tax revenue for public services",
      "stakeholders": ["Government", "Public sector"]
    }
  ],
  "cons": [
    {
      "point": "Increases business costs",
      "explanation": "Small businesses may struggle with compliance",
      "stakeholders": ["Small businesses", "Entrepreneurs"]
    }
  ],
  "risks": [
    {
      "risk": "Economic slowdown in affected sectors",
      "severity": "medium",
      "probability": "medium",
      "mitigation": "Gradual implementation with support programs"
    }
  ],
  "economic_impact": {
    "budget_required": "10 million EUR implementation costs",
    "affected_sectors": ["Retail", "Manufacturing", "Services"],
    "employment_impact": "neutral"
  },
  "compliance_considerations": [
    "Requires updates to tax reporting systems",
    "May conflict with existing EU regulations"
  ],
  "recommendations": [
    "Consider gradual rollout over 2-3 years",
    "Provide compliance support for small businesses"
  ],
  "overall_assessment": "balanced",
  "confidence_score": 0.85
}
```

## Cost Estimates

Based on Claude 3.5 Sonnet pricing:
- **Input**: $3 per 1M tokens
- **Output**: $15 per 1M tokens

Average costs per bill:
- Short bill (500 words): ~$0.01 - $0.02
- Medium bill (2000 words): ~$0.03 - $0.05
- Long bill (5000 words): ~$0.08 - $0.15

For 1000 bills: approximately **$30-$80** depending on bill length.

## Scheduled Assessment

To automatically assess new bills, add to your crontab:

```bash
# Run every hour
0 * * * * cd /path/to/laravel-app && php artisan assess:bills --limit=50 >> /var/log/bill-assessment.log 2>&1
```

Or use Laravel's scheduler in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('assess:bills --limit=50')
             ->hourly()
             ->withoutOverlapping();
}
```

Then run the scheduler:
```bash
php artisan schedule:work
```

## Viewing Assessments

### Via Database

```php
use App\Models\LegislativeBill;

$bill = LegislativeBill::find(1);
$analysis = $bill->analysis()->latest()->first();

echo $analysis->analysis_result['summary'];
// "This bill proposes..."

echo $analysis->analysis_result['overall_assessment'];
// "positive"
```

### Via Dashboard

The dashboard automatically shows:
- Analysis status (analyzed/unanalyzed)
- Overall assessment badges
- Risk indicators
- Latest analysis results

## Troubleshooting

### "OpenRouter API key not configured"

Make sure `OPENROUTER_API_KEY` is set in `.env` file.

### "Request failed with status code 402"

Your OpenRouter account has insufficient credits. Add more at [https://openrouter.ai/credits](https://openrouter.ai/credits).

### "Request failed with status code 429"

You've hit the rate limit. The command includes 1-second delays between requests, but you may need to increase this if processing many bills.

### High Costs

- Use `--limit` to process bills in batches
- Monitor token usage with `--test` mode first
- Consider using a cheaper model for initial screening
- Adjust `OPENROUTER_MAX_TOKENS` to limit response length

## Alternative Models

Edit `.env` to try different models:

```bash
# Cheaper option (GPT-4 Turbo)
OPENROUTER_MODEL=openai/gpt-4-turbo

# Most capable (Claude 3 Opus)
OPENROUTER_MODEL=anthropic/claude-3-opus

# Budget option (GPT-3.5)
OPENROUTER_MODEL=openai/gpt-3.5-turbo

# Open source option (Llama 3)
OPENROUTER_MODEL=meta-llama/llama-3-70b-instruct
```

See all available models at [https://openrouter.ai/models](https://openrouter.ai/models).

## Security Considerations

1. **API Key**: Store in `.env`, never commit to Git
2. **Cost Control**: Set budget limits in OpenRouter dashboard
3. **Rate Limiting**: Built-in 1-second delays prevent abuse
4. **Validation**: Always review AI assessments before public display
5. **Privacy**: Bill content is sent to OpenRouter - ensure compliance with data policies

## Future Enhancements

Potential improvements:
- Multi-language support (Romanian + English)
- Stakeholder-specific assessments
- Comparison with similar bills
- Sentiment analysis of public comments
- Automated risk alerting
- PDF document analysis
- Committee recommendation predictions

## Support

For issues or questions:
1. Check OpenRouter status: [https://status.openrouter.ai](https://status.openrouter.ai)
2. Review Laravel logs: `storage/logs/laravel.log`
3. Test connectivity: `php artisan assess:bills --test`
4. Open an issue on GitHub

## License

Same as the main Legislative Watcher project.
