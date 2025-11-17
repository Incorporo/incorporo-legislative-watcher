<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillTimeline extends Model
{
    use HasFactory;

    protected $table = 'bill_timeline';

    protected $fillable = [
        'bill_id',
        'event_date',
        'event_type',
        'description',
        'details',
        'chamber',
        'committee',
        'votes_for',
        'votes_against',
        'votes_abstain',
        'vote_result',
        'deadline',
        'deadline_met',
        'metadata',
        'source_url',
    ];

    protected $casts = [
        'event_date' => 'date',
        'deadline' => 'date',
        'deadline_met' => 'boolean',
        'metadata' => 'array',
    ];

    public function bill()
    {
        return $this->belongsTo(LegislativeBill::class, 'bill_id');
    }
}
