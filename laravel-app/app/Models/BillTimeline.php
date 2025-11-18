<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillTimeline extends Model
{
    use HasFactory;

    protected $table = 'bill_timeline';

    protected $fillable = [
        'bill_id',
        'sequence_order',
        'event_date',
        'event_type',
        'description',
        'details',
        'chamber',
        'chamber_round',
        'committee',
        'votes_for',
        'votes_against',
        'votes_abstain',
        'vote_result',
        'is_adoption',
        'is_final',
        'vote_details',
        'deadline',
        'deadline_type',
        'deadline_met',
        'stenogram_link',
        'video_link',
        'committees',
        'documents',
        'metadata',
        'source_url',
    ];

    protected $casts = [
        'event_date' => 'date',
        'deadline' => 'date',
        'deadline_met' => 'boolean',
        'is_adoption' => 'boolean',
        'is_final' => 'boolean',
        'vote_details' => 'array',
        'committees' => 'array',
        'documents' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the bill this timeline event belongs to
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(LegislativeBill::class, 'bill_id');
    }

    /**
     * Get the committee assignments associated with this timeline event
     */
    public function committeeAssignments(): HasMany
    {
        return $this->hasMany(BillCommittee::class, 'timeline_event_id');
    }

    /**
     * Get the documents associated with this timeline event
     */
    public function timelineDocuments(): HasMany
    {
        return $this->hasMany(BillDocument::class, 'timeline_event_id');
    }

    /**
     * Scope to get events in timeline order
     */
    public function scopeInOrder($query)
    {
        return $query->orderBy('sequence_order')->orderBy('event_date');
    }

    /**
     * Scope to get events by chamber
     */
    public function scopeByChamber($query, $chamber)
    {
        return $query->where('chamber', $chamber);
    }

    /**
     * Scope to get adoption/vote events
     */
    public function scopeAdoptions($query)
    {
        return $query->where('is_adoption', true);
    }

    /**
     * Scope to get final events (publications)
     */
    public function scopeFinal($query)
    {
        return $query->where('is_final', true);
    }

    /**
     * Check if this is a vote event
     */
    public function isVote(): bool
    {
        return $this->is_adoption === true;
    }

    /**
     * Check if this is the final publication event
     */
    public function isFinal(): bool
    {
        return $this->is_final === true;
    }

    /**
     * Get chamber display name
     */
    public function getChamberName(): string
    {
        return match ($this->chamber) {
            'senate' => 'Senat',
            'cdep' => 'Camera Deputaților',
            'presidential' => 'Președinte/Parlament',
            default => $this->chamber ?? 'Unknown',
        };
    }

    /**
     * Get chamber code for display
     */
    public function getChamberCode(): string
    {
        return match ($this->chamber) {
            'senate' => 'SE',
            'cdep' => 'CD',
            'presidential' => 'PA',
            default => '',
        };
    }
}
