<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommitteeMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'committee_id',
        'legislator_id',
        'role',
        'joined_date',
        'left_date',
        'active',
    ];

    protected $casts = [
        'joined_date' => 'date',
        'left_date' => 'date',
        'active' => 'boolean',
    ];

    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }

    public function legislator()
    {
        return $this->belongsTo(Legislator::class);
    }
}
