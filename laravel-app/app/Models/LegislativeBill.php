<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegislativeBill extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'chamber',
        'bill_number',
        'year',
        'internal_id',
        'title',
        'type',
        'status',
        'urgency_status',
        'first_chamber',
        'decision_chamber',
        'registration_date',
        'description',
        'full_text',
        'content_hash',
        'url',
        'source_url',
        'metadata',
        'last_scraped_at',
        'last_changed_at',
        'scrape_count',
        'change_count',
        'analyzed',
        'analyzed_at',
        // Phase 2: AI Assessment fields
        'ai_assessed',
        'ai_assessed_at',
        'ai_assessment_priority',
        'ai_assessment_status',
        'ai_assessment_error',
        'stakeholder_impact',
        'conflict_analysis',
        'voting_predictions',
        'policy_recommendations',
        'ai_summary',
        'batch_assessment_attempts',
        'last_assessment_attempt',
    ];

    protected $casts = [
        'urgency_status' => 'boolean',
        'registration_date' => 'date',
        'metadata' => 'array',
        'last_scraped_at' => 'datetime',
        'last_changed_at' => 'datetime',
        'analyzed' => 'boolean',
        'analyzed_at' => 'datetime',
        // Phase 2: AI Assessment casts
        'ai_assessed' => 'boolean',
        'ai_assessed_at' => 'datetime',
        'stakeholder_impact' => 'array',
        'conflict_analysis' => 'array',
        'voting_predictions' => 'array',
        'policy_recommendations' => 'array',
        'last_assessment_attempt' => 'datetime',
    ];

    /**
     * Get the initiators of this bill
     */
    public function initiators()
    {
        return $this->hasMany(BillInitiator::class, 'bill_id');
    }

    /**
     * Get the timeline events for this bill
     */
    public function timeline()
    {
        return $this->hasMany(BillTimeline::class, 'bill_id')->orderBy('event_date', 'desc');
    }

    /**
     * Get the documents attached to this bill
     */
    public function documents()
    {
        return $this->hasMany(BillDocument::class, 'bill_id');
    }

    /**
     * Get the change history for this bill
     */
    public function changes()
    {
        return $this->hasMany(BillChange::class, 'bill_id')->orderBy('detected_at', 'desc');
    }

    /**
     * Get the AI analysis for this bill
     */
    public function analysis()
    {
        return $this->hasMany(BillAnalysis::class, 'bill_id');
    }

    /**
     * Get the risk flags for this bill
     */
    public function risks()
    {
        return $this->hasMany(BillRisk::class, 'bill_id');
    }

    /**
     * Get committee assignments
     */
    public function committeeAssignments()
    {
        return $this->hasMany(CommitteeAssignment::class, 'bill_id');
    }

    /**
     * Get committees through assignments
     */
    public function committees()
    {
        return $this->belongsToMany(Committee::class, 'committee_assignments')
            ->withPivot(['assigned_date', 'review_deadline', 'status', 'recommendation'])
            ->withTimestamps();
    }

    /**
     * Scope to filter by chamber
     */
    public function scopeChamber($query, $chamber)
    {
        return $query->where('chamber', $chamber);
    }

    /**
     * Scope to filter by year
     */
    public function scopeYear($query, $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Scope to filter urgent bills
     */
    public function scopeUrgent($query)
    {
        return $query->where('urgency_status', true);
    }

    /**
     * Scope to filter analyzed bills
     */
    public function scopeAnalyzed($query, $analyzed = true)
    {
        return $query->where('analyzed', $analyzed);
    }

    /**
     * Scope to filter by status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Calculate content hash for change detection
     */
    public function calculateContentHash()
    {
        $content = json_encode([
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'type' => $this->type,
        ]);

        return hash('sha256', $content);
    }

    /**
     * Check if content has changed
     */
    public function hasContentChanged($newData)
    {
        $newHash = hash('sha256', json_encode($newData));

        return $this->content_hash !== $newHash;
    }

    /**
     * Get the latest analysis of a specific type
     */
    public function getLatestAnalysis($type)
    {
        return $this->analysis()
            ->where('analysis_type', $type)
            ->orderBy('analyzed_at', 'desc')
            ->first();
    }

    /**
     * Get highest risk level
     */
    public function getHighestRiskLevel()
    {
        $risks = $this->risks()->pluck('risk_level')->toArray();

        $levels = ['critical', 'high', 'medium', 'low'];
        foreach ($levels as $level) {
            if (in_array($level, $risks)) {
                return $level;
            }
        }

        return null;
    }

    /**
     * Get display name (bill number/year)
     */
    public function getDisplayNameAttribute()
    {
        return "{$this->bill_number}/{$this->year}";
    }
}
