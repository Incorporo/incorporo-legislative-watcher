<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedSearch extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'filters',
        'is_default',
        'use_count',
        'last_used_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'is_default' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    /**
     * Get the user that owns this saved search
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Increment the use count and update last used timestamp
     */
    public function recordUse(): void
    {
        $this->increment('use_count');
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Scope to get default search for a user
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope to order by most recently used
     */
    public function scopeRecentlyUsed($query)
    {
        return $query->orderBy('last_used_at', 'desc');
    }

    /**
     * Scope to order by most frequently used
     */
    public function scopeMostUsed($query)
    {
        return $query->orderBy('use_count', 'desc');
    }
}
