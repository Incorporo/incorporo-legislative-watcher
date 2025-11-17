<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillRisk extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'analysis_id',
        'risk_category',
        'risk_level',
        'risk_score',
        'description',
        'justification',
        'affected_parties',
        'recommended_action',
        'evidence',
        'related_risks',
        'status',
        'flagged_at',
        'resolved_at',
        'resolution_notes',
        'public',
        'alert_sent',
        'alert_sent_at',
        'verified',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'evidence' => 'array',
        'related_risks' => 'array',
        'flagged_at' => 'datetime',
        'resolved_at' => 'datetime',
        'public' => 'boolean',
        'alert_sent' => 'boolean',
        'alert_sent_at' => 'datetime',
        'verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function bill()
    {
        return $this->belongsTo(LegislativeBill::class, 'bill_id');
    }

    public function analysis()
    {
        return $this->belongsTo(BillAnalysis::class, 'analysis_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePublic($query)
    {
        return $query->where('public', true);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('risk_level', $level);
    }
}
