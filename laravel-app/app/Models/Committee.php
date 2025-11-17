<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Committee extends Model
{
    use HasFactory;

    protected $fillable = [
        'internal_id',
        'name',
        'name_short',
        'chamber',
        'type',
        'description',
        'jurisdiction',
        'chair_id',
        'leadership',
        'active',
        'established_date',
        'dissolved_date',
        'email',
        'phone',
        'website_url',
        'member_count',
        'bills_reviewed',
        'meetings_held',
        'metadata',
    ];

    protected $casts = [
        'leadership' => 'array',
        'active' => 'boolean',
        'established_date' => 'date',
        'dissolved_date' => 'date',
        'metadata' => 'array',
    ];

    public function chair()
    {
        return $this->belongsTo(Legislator::class, 'chair_id');
    }

    public function members()
    {
        return $this->belongsToMany(Legislator::class, 'committee_members')
            ->withPivot(['role', 'joined_date', 'left_date', 'active'])
            ->withTimestamps();
    }

    public function bills()
    {
        return $this->belongsToMany(LegislativeBill::class, 'committee_assignments')
            ->withPivot(['assigned_date', 'review_deadline', 'status', 'recommendation'])
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
