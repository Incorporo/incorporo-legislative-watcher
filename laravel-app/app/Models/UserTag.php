<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UserTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'color',
        'description',
    ];

    /**
     * Get the user that owns this tag
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all bills tagged with this tag
     */
    public function bills(): BelongsToMany
    {
        return $this->belongsToMany(LegislativeBill::class, 'bill_tag', 'user_tag_id', 'bill_id')
            ->withTimestamps();
    }

    /**
     * Scope to filter tags by user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
