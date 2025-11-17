<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillChange extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'field_name',
        'old_value',
        'new_value',
        'change_type',
        'importance',
        'detected_at',
        'detection_method',
        'notification_sent',
        'notification_sent_at',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'notification_sent' => 'boolean',
        'notification_sent_at' => 'datetime',
    ];

    public function bill()
    {
        return $this->belongsTo(LegislativeBill::class, 'bill_id');
    }
}
