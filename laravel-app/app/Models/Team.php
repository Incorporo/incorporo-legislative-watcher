<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'owner_id',
        'type',
        'avatar_url',
        'settings',
        'max_members',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the team owner
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get all team members
     */
    public function members(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    /**
     * Get all users in this team
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'team_members')
            ->withPivot(['role', 'permissions', 'joined_at', 'notifications_enabled'])
            ->withTimestamps();
    }

    /**
     * Get bill collections for this team
     */
    public function billCollections(): HasMany
    {
        return $this->hasMany(TeamBillCollection::class);
    }

    /**
     * Get discussions for this team
     */
    public function discussions(): HasMany
    {
        return $this->hasMany(BillDiscussion::class);
    }

    /**
     * Get tasks for this team
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(TeamTask::class);
    }

    /**
     * Scope to filter active teams
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Check if user is a member of this team
     */
    public function hasMember(int $userId): bool
    {
        return $this->members()->where('user_id', $userId)->exists();
    }

    /**
     * Check if user is owner or admin
     */
    public function isAdministrator(int $userId): bool
    {
        if ($this->owner_id === $userId) {
            return true;
        }

        return $this->members()
            ->where('user_id', $userId)
            ->whereIn('role', ['admin', 'owner'])
            ->exists();
    }

    /**
     * Get member count
     */
    public function getMemberCountAttribute(): int
    {
        return $this->members()->count();
    }

    /**
     * Check if team is at capacity
     */
    public function isAtCapacity(): bool
    {
        return $this->member_count >= $this->max_members;
    }
}
