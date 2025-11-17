<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillDiscussion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bill_id',
        'team_id',
        'user_id',
        'title',
        'content',
        'discussion_type',
        'is_pinned',
        'is_locked',
        'view_count',
        'comment_count',
        'last_activity_at',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_locked' => 'boolean',
        'last_activity_at' => 'datetime',
    ];

    /**
     * Get the bill
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(LegislativeBill::class, 'bill_id');
    }

    /**
     * Get the team (nullable for public discussions)
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all comments
     */
    public function comments(): HasMany
    {
        return $this->hasMany(DiscussionComment::class, 'discussion_id');
    }

    /**
     * Get top-level comments (no parent)
     */
    public function topLevelComments(): HasMany
    {
        return $this->hasMany(DiscussionComment::class, 'discussion_id')
            ->whereNull('parent_id')
            ->orderBy('created_at', 'asc');
    }

    /**
     * Scope to filter pinned discussions
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Scope to filter by type
     */
    public function scopeType($query, string $type)
    {
        return $query->where('discussion_type', $type);
    }

    /**
     * Scope to filter public discussions
     */
    public function scopePublic($query)
    {
        return $query->whereNull('team_id');
    }

    /**
     * Increment view count
     */
    public function incrementViews(): void
    {
        $this->increment('view_count');
    }

    /**
     * Update activity timestamp
     */
    public function updateActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }
}
