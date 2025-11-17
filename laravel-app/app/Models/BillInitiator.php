<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillInitiator extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'legislator_id',
        'name',
        'type',
        'party',
        'chamber',
        'role',
        'position',
    ];

    public function bill()
    {
        return $this->belongsTo(LegislativeBill::class, 'bill_id');
    }

    public function legislator()
    {
        return $this->belongsTo(Legislator::class, 'legislator_id');
    }
}
