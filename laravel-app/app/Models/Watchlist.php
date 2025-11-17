<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Watchlist extends Model
{
    use HasFactory;

    protected $table = 'user_watchlists';

    protected $fillable = [
        'user_id',
        'bill_id',
        'notifications_enabled',
        'priority',
        'personal_note',
    ];

    protected $casts = [
        'notifications_enabled' => 'boolean',
    ];

    /**
     * Get the user that owns this watchlist entry
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the bill being watched
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(LegislativeBill::class, 'bill_id');
    }

    /**
     * Scope to filter by priority
     */
    public function scopePriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope to filter by notification status
     */
    public function scopeWithNotifications($query)
    {
        return $query->where('notifications_enabled', true);
    }
}
