<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Legislator extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'internal_id',
        'name',
        'first_name',
        'last_name',
        'chamber',
        'party',
        'party_normalized',
        'constituency',
        'legislature',
        'mandate_start',
        'mandate_end',
        'active',
        'email',
        'phone',
        'profile_url',
        'photo_url',
        'biography',
        'birth_date',
        'education',
        'profession',
        'committees',
        'bills_initiated',
        'bills_co_sponsored',
        'questions_asked',
        'speeches_given',
        'metadata',
    ];

    protected $casts = [
        'mandate_start' => 'date',
        'mandate_end' => 'date',
        'active' => 'boolean',
        'birth_date' => 'date',
        'committees' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get bills initiated by this legislator
     */
    public function initiatedBills()
    {
        return $this->hasMany(BillInitiator::class, 'legislator_id')
            ->where('type', 'mp')
            ->where('role', 'primary');
    }

    /**
     * Get bills co-sponsored by this legislator
     */
    public function coSponsoredBills()
    {
        return $this->hasMany(BillInitiator::class, 'legislator_id')
            ->where('type', 'mp')
            ->where('role', 'co_sponsor');
    }

    /**
     * Get all bills this legislator is associated with
     */
    public function bills()
    {
        return $this->belongsToMany(LegislativeBill::class, 'bill_initiators')
            ->withPivot(['type', 'role', 'position'])
            ->withTimestamps();
    }

    /**
     * Get committee memberships
     */
    public function committeeMemberships()
    {
        return $this->hasMany(CommitteeMember::class, 'legislator_id');
    }

    /**
     * Get active committees
     */
    public function activeCommittees()
    {
        return $this->belongsToMany(Committee::class, 'committee_members')
            ->wherePivot('active', true)
            ->withPivot(['role', 'joined_date'])
            ->withTimestamps();
    }

    /**
     * Get committees chaired by this legislator
     */
    public function chairedCommittees()
    {
        return $this->hasMany(Committee::class, 'chair_id');
    }

    /**
     * Scope active legislators
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope by chamber
     */
    public function scopeChamber($query, $chamber)
    {
        return $query->where('chamber', $chamber);
    }

    /**
     * Scope by party
     */
    public function scopeParty($query, $party)
    {
        return $query->where('party_normalized', $party);
    }

    /**
     * Get full name
     */
    public function getFullNameAttribute()
    {
        if ($this->first_name && $this->last_name) {
            return "{$this->first_name} {$this->last_name}";
        }
        return $this->name;
    }

    /**
     * Update statistics
     */
    public function updateStatistics()
    {
        $this->bills_initiated = $this->initiatedBills()->count();
        $this->bills_co_sponsored = $this->coSponsoredBills()->count();
        $this->save();
    }
}
