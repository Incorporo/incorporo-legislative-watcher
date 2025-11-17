<?php

namespace App\Services;

use App\Models\LegislativeBill;
use App\Services\AI\OpenRouterService;
use Illuminate\Support\Facades\Log;

class AIAssessmentService
{
    protected $openRouter;

    public function __construct(OpenRouterService $openRouter)
    {
        $this->openRouter = $openRouter;
    }

    /**
     * Comprehensive AI assessment of a bill
     * Includes: stakeholder impact, conflict analysis, voting predictions, policy recommendations
     */
    public function assessBillComprehensive(LegislativeBill $bill): ?array
    {
        try {
            $prompt = $this->buildComprehensivePrompt($bill);

            $response = $this->openRouter->chat([
                [
                    'role' => 'system',
                    'content' => 'You are a legislative analysis expert specializing in Romanian Parliament bills. Provide comprehensive, structured analysis in JSON format.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ]);

            if (!$response) {
                return null;
            }

            // Parse the JSON response
            $analysis = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("Failed to parse AI response for bill {$bill->id}: " . json_last_error_msg());
                return null;
            }

            return $analysis;

        } catch (\Exception $e) {
            Log::error("AI Assessment failed for bill {$bill->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Build comprehensive prompt for AI analysis
     */
    protected function buildComprehensivePrompt(LegislativeBill $bill): string
    {
        return <<<PROMPT
Analyze the following Romanian legislative bill and provide a comprehensive JSON assessment:

**Bill Information:**
- Number: {$bill->bill_number}
- Title: {$bill->title}
- Chamber: {$bill->chamber}
- Status: {$bill->status}
- Urgency: {$bill->urgency_status}
- Summary: {$bill->summary}

Provide analysis in the following JSON structure:

```json
{
  "summary": "Brief 2-3 sentence executive summary of the bill",

  "stakeholder_impact": {
    "citizens": {
      "impact_level": "high|medium|low",
      "description": "How this affects everyday citizens",
      "affected_groups": ["list", "of", "groups"]
    },
    "businesses": {
      "impact_level": "high|medium|low",
      "description": "Business sector implications",
      "affected_sectors": ["list", "of", "sectors"]
    },
    "government": {
      "impact_level": "high|medium|low",
      "description": "Government operational changes",
      "affected_agencies": ["list", "of", "agencies"]
    },
    "civil_society": {
      "impact_level": "high|medium|low",
      "description": "NGO and advocacy group implications"
    }
  },

  "conflict_analysis": {
    "conflicting_bills": [
      {
        "likelihood": "high|medium|low",
        "description": "Potential conflicts with existing legislation",
        "areas": ["specific conflict areas"]
      }
    ],
    "legal_challenges": [
      {
        "type": "constitutional|procedural|implementation",
        "severity": "high|medium|low",
        "description": "Potential legal issues"
      }
    ],
    "political_opposition": {
      "expected_level": "high|medium|low",
      "reasoning": "Why opposition might occur",
      "key_opponents": ["likely opposing parties/groups"]
    }
  },

  "voting_predictions": {
    "passage_likelihood": "high|medium|low",
    "confidence": "0-100 percentage",
    "key_factors": ["factors affecting passage"],
    "amendments_likely": true|false,
    "timeline_estimate": "estimated timeline for passage"
  },

  "policy_recommendations": {
    "supporters": [
      {
        "action": "specific action to support",
        "audience": "who should do this",
        "impact": "expected outcome"
      }
    ],
    "opponents": [
      {
        "action": "specific action to oppose",
        "audience": "who should do this",
        "focus_areas": ["areas to challenge"]
      }
    ],
    "improvements": [
      {
        "suggestion": "amendment or improvement",
        "benefit": "why this would help"
      }
    ]
  },

  "key_provisions": [
    "Most important provision 1",
    "Most important provision 2",
    "Most important provision 3"
  ],

  "long_term_implications": "Long-term societal/economic/political effects",

  "urgency_justification": "Assessment of whether urgency status is warranted"
}
```

Provide ONLY the JSON response, no additional text.
PROMPT;
    }

    /**
     * Generate stakeholder impact matrix
     */
    public function generateStakeholderImpact(LegislativeBill $bill): ?array
    {
        // This is extracted from comprehensive assessment, but can be called separately
        $assessment = $this->assessBillComprehensive($bill);
        return $assessment['stakeholder_impact'] ?? null;
    }

    /**
     * Analyze conflicts with other bills
     */
    public function analyzeConflicts(LegislativeBill $bill): ?array
    {
        $assessment = $this->assessBillComprehensive($bill);
        return $assessment['conflict_analysis'] ?? null;
    }

    /**
     * Predict voting outcome
     */
    public function predictVotingOutcome(LegislativeBill $bill): ?array
    {
        $assessment = $this->assessBillComprehensive($bill);
        return $assessment['voting_predictions'] ?? null;
    }

    /**
     * Generate policy recommendations
     */
    public function generatePolicyRecommendations(LegislativeBill $bill): ?array
    {
        $assessment = $this->assessBillComprehensive($bill);
        return $assessment['policy_recommendations'] ?? null;
    }

    /**
     * Generate executive summary
     */
    public function generateSummary(LegislativeBill $bill): ?string
    {
        $assessment = $this->assessBillComprehensive($bill);
        return $assessment['summary'] ?? null;
    }
}
