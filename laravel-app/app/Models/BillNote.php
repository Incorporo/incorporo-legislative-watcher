<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bill_id',
        'content',
        'is_private',
    ];

    protected $casts = [
        'is_private' => 'boolean',
    ];

    /**
     * Get the user that wrote this note
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the bill this note belongs to
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(LegislativeBill::class, 'bill_id');
    }

    /**
     * Scope to filter private notes
     */
    public function scopePrivate($query)
    {
        return $query->where('is_private', true);
    }

    /**
     * Scope to filter public notes
     */
    public function scopePublic($query)
    {
        return $query->where('is_private', false);
    }

    /**
     * Scope to filter by user and bill
     */
    public function scopeForBillAndUser($query, int $billId, int $userId)
    {
        return $query->where('bill_id', $billId)->where('user_id', $userId);
    }
}
