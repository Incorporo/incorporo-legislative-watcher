<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillCommittee extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'timeline_event_id',
        'committee_name',
        'committee_id',
        'committee_link',
        'chamber',
        'legislature',
        'assignment_type',
        'report_received',
        'report_date',
        'report_number',
        'report_url',
        'report_result',
        'deadline_amendments',
        'deadline_report',
        'metadata',
    ];

    protected $casts = [
        'report_received' => 'boolean',
        'report_date' => 'date',
        'deadline_amendments' => 'date',
        'deadline_report' => 'date',
        'metadata' => 'array',
    ];

    /**
     * Get the bill this committee assignment belongs to
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(LegislativeBill::class, 'bill_id');
    }

    /**
     * Get the timeline event this committee assignment is associated with
     */
    public function timelineEvent(): BelongsTo
    {
        return $this->belongsTo(BillTimeline::class, 'timeline_event_id');
    }

    /**
     * Check if this is the main report committee
     */
    public function isRaportCommittee(): bool
    {
        return $this->assignment_type === 'raport';
    }

    /**
     * Check if this is an advisory committee
     */
    public function isAvizCommittee(): bool
    {
        return $this->assignment_type === 'aviz';
    }

    /**
     * Check if the committee has submitted their report
     */
    public function hasSubmittedReport(): bool
    {
        return $this->report_received === true;
    }

    /**
     * Check if deadline is approaching (within 3 days)
     */
    public function isDeadlineApproaching(): bool
    {
        if ($this->deadline_report) {
            $daysUntilDeadline = now()->diffInDays($this->deadline_report, false);

            return $daysUntilDeadline >= 0 && $daysUntilDeadline <= 3;
        }

        return false;
    }

    /**
     * Check if deadline has passed without report
     */
    public function isOverdue(): bool
    {
        if ($this->deadline_report && ! $this->report_received) {
            return now()->isAfter($this->deadline_report);
        }

        return false;
    }
}
