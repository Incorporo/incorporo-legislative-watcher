<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillAnalysis extends Model
{
    use HasFactory;

    protected $table = 'bill_analysis';

    protected $fillable = [
        'bill_id',
        'analysis_type',
        'analysis_result',
        'confidence_score',
        'token_count',
        'analysis_cost',
        'model_version',
        'prompt_version',
        'processing_time_ms',
        'analyzed_at',
        'human_reviewed',
        'approved',
        'review_notes',
        'reviewed_at',
    ];

    protected $casts = [
        'analysis_result' => 'array',
        'analyzed_at' => 'datetime',
        'human_reviewed' => 'boolean',
        'approved' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    public function bill()
    {
        return $this->belongsTo(LegislativeBill::class, 'bill_id');
    }

    public function risks()
    {
        return $this->hasMany(BillRisk::class, 'analysis_id');
    }
}
