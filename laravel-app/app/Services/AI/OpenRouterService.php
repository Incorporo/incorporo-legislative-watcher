<?php

namespace App\Services\AI;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenRouterService
{
    protected $apiKey;

    protected $baseUrl = 'https://openrouter.ai/api/v1';

    protected $model;

    protected $maxTokens;

    protected $temperature;

    public function __construct()
    {
        $this->apiKey = config('services.openrouter.api_key');
        $this->model = config('services.openrouter.model', 'anthropic/claude-3.5-sonnet');
        $this->maxTokens = config('services.openrouter.max_tokens', 4096);
        $this->temperature = config('services.openrouter.temperature', 0.7);

        // Don't throw exception during construction, check when actually used
        // This allows Laravel to bootstrap without API key configured
    }

    /**
     * Check if API key is configured
     */
    protected function ensureApiKeyConfigured(): void
    {
        if (! $this->apiKey) {
            throw new Exception('OpenRouter API key not configured. Please set OPENROUTER_API_KEY in .env');
        }
    }

    /**
     * Analyze a legislative bill and return structured assessment
     */
    public function analyzeBill(string $title, ?string $description = null, ?string $fullText = null): array
    {
        $this->ensureApiKeyConfigured();

        $startTime = microtime(true);

        try {
            // Build the prompt
            $prompt = $this->buildAnalysisPrompt($title, $description, $fullText);

            // Make API request
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'HTTP-Referer' => config('app.url'),
                'X-Title' => config('app.name', 'Legislative Watcher'),
                'Content-Type' => 'application/json',
            ])->timeout(120)->post($this->baseUrl.'/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a legal and legislative analysis expert for Romanian laws. Analyze bills objectively and provide structured assessments in JSON format.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
                'response_format' => ['type' => 'json_object'],
            ]);

            if (! $response->successful()) {
                throw new Exception('OpenRouter API request failed: '.$response->body());
            }

            $data = $response->json();

            // Extract the analysis from response
            $content = $data['choices'][0]['message']['content'] ?? null;

            if (! $content) {
                throw new Exception('No content in OpenRouter response');
            }

            // Parse JSON response
            $analysis = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Failed to parse OpenRouter JSON response: '.json_last_error_msg());
            }

            // Calculate metrics
            $processingTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
            $tokenCount = $data['usage']['total_tokens'] ?? 0;
            $cost = $this->calculateCost($data['usage'] ?? []);

            return [
                'success' => true,
                'analysis' => $analysis,
                'metadata' => [
                    'model' => $this->model,
                    'processing_time_ms' => round($processingTime),
                    'token_count' => $tokenCount,
                    'cost' => $cost,
                    'prompt_tokens' => $data['usage']['prompt_tokens'] ?? 0,
                    'completion_tokens' => $data['usage']['completion_tokens'] ?? 0,
                ],
            ];

        } catch (Exception $e) {
            Log::error('OpenRouter analysis failed', [
                'error' => $e->getMessage(),
                'title' => $title,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'metadata' => [
                    'processing_time_ms' => round((microtime(true) - $startTime) * 1000),
                ],
            ];
        }
    }

    /**
     * Build analysis prompt for the bill
     */
    protected function buildAnalysisPrompt(string $title, ?string $description, ?string $fullText): string
    {
        $text = $fullText ?: $description ?: $title;

        return <<<PROMPT
Analyze the following Romanian legislative bill and provide a comprehensive assessment in JSON format.

**Bill Title:** {$title}

**Bill Content:**
{$text}

**Instructions:**
Provide a detailed analysis in the following JSON structure:

{
  "summary": "Brief 2-3 sentence summary of what this bill does",
  "category": "Primary category (e.g., 'Economic', 'Social', 'Environmental', 'Justice', 'Administrative', 'Healthcare', 'Education', etc.)",
  "tags": ["keyword1", "keyword2", "keyword3"],
  "impact_assessment": {
    "scope": "Who/what is affected? (e.g., 'All citizens', 'Businesses', 'Specific sector')",
    "magnitude": "low|medium|high - Overall impact level",
    "timeframe": "short-term|medium-term|long-term - When effects will be felt"
  },
  "pros": [
    {
      "point": "Positive aspect 1",
      "explanation": "Why this is beneficial",
      "stakeholders": ["Who benefits"]
    },
    {
      "point": "Positive aspect 2",
      "explanation": "Why this is beneficial",
      "stakeholders": ["Who benefits"]
    }
  ],
  "cons": [
    {
      "point": "Negative aspect 1",
      "explanation": "Why this is problematic",
      "stakeholders": ["Who is negatively affected"]
    },
    {
      "point": "Negative aspect 2",
      "explanation": "Why this is problematic",
      "stakeholders": ["Who is negatively affected"]
    }
  ],
  "risks": [
    {
      "risk": "Potential risk 1",
      "severity": "low|medium|high|critical",
      "probability": "low|medium|high",
      "mitigation": "How this could be addressed"
    }
  ],
  "economic_impact": {
    "budget_required": "Estimated budget impact (or 'Unknown' if not specified)",
    "affected_sectors": ["Sector 1", "Sector 2"],
    "employment_impact": "positive|negative|neutral|unknown"
  },
  "compliance_considerations": [
    "Legal or regulatory consideration 1",
    "Legal or regulatory consideration 2"
  ],
  "recommendations": [
    "Recommendation 1",
    "Recommendation 2"
  ],
  "overall_assessment": "balanced|positive|negative|neutral",
  "confidence_score": 0.0-1.0
}

**Important:**
- Respond ONLY with valid JSON
- Be objective and balanced
- Base analysis on the provided content
- Use Romanian context and legal framework
- If information is unclear or missing, indicate "Unknown" or use appropriate default values
- Ensure all arrays have at least one item or are empty arrays []
PROMPT;
    }

    /**
     * Calculate estimated cost based on token usage
     * Pricing varies by model - using approximate values
     */
    protected function calculateCost(array $usage): float
    {
        $promptTokens = $usage['prompt_tokens'] ?? 0;
        $completionTokens = $usage['completion_tokens'] ?? 0;

        // Approximate pricing (cents per 1M tokens) - update based on actual model
        // Claude 3.5 Sonnet: $3/1M input, $15/1M output
        $inputCostPer1M = 3.0;
        $outputCostPer1M = 15.0;

        $inputCost = ($promptTokens / 1000000) * $inputCostPer1M;
        $outputCost = ($completionTokens / 1000000) * $outputCostPer1M;

        return round($inputCost + $outputCost, 6);
    }

    /**
     * Test API connectivity
     */
    public function testConnection(): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
            ])->timeout(10)->get($this->baseUrl.'/models');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Successfully connected to OpenRouter API',
                    'models_available' => count($response->json('data', [])),
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to connect: HTTP '.$response->status(),
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection error: '.$e->getMessage(),
            ];
        }
    }
}
