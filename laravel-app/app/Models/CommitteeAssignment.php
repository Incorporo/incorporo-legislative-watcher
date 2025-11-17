<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommitteeAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'committee_id',
        'bill_id',
        'assigned_date',
        'review_deadline',
        'report_date',
        'status',
        'recommendation',
        'report_summary',
        'report_url',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'review_deadline' => 'date',
        'report_date' => 'date',
    ];

    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }

    public function bill()
    {
        return $this->belongsTo(LegislativeBill::class);
    }
}
