<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscussionComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'discussion_id',
        'user_id',
        'parent_id',
        'content',
        'is_edited',
        'edited_at',
        'likes_count',
    ];

    protected $casts = [
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
    ];

    /**
     * Get the discussion
     */
    public function discussion(): BelongsTo
    {
        return $this->belongsTo(BillDiscussion::class, 'discussion_id');
    }

    /**
     * Get the comment author
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get parent comment (for replies)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(DiscussionComment::class, 'parent_id');
    }

    /**
     * Get replies to this comment
     */
    public function replies(): HasMany
    {
        return $this->hasMany(DiscussionComment::class, 'parent_id')
            ->orderBy('created_at', 'asc');
    }

    /**
     * Get users who liked this comment
     */
    public function likes()
    {
        return $this->belongsToMany(User::class, 'comment_likes', 'comment_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Check if user liked this comment
     */
    public function isLikedBy(int $userId): bool
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    /**
     * Toggle like from a user
     */
    public function toggleLike(int $userId): bool
    {
        if ($this->isLikedBy($userId)) {
            $this->likes()->detach($userId);
            $this->decrement('likes_count');
            return false;
        } else {
            $this->likes()->attach($userId);
            $this->increment('likes_count');
            return true;
        }
    }

    /**
     * Mark comment as edited
     */
    public function markAsEdited(): void
    {
        $this->update([
            'is_edited' => true,
            'edited_at' => now(),
        ]);
    }
}
