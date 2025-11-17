<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'user_id',
        'role',
        'permissions',
        'joined_at',
        'last_active_at',
        'notifications_enabled',
    ];

    protected $casts = [
        'permissions' => 'array',
        'joined_at' => 'datetime',
        'last_active_at' => 'datetime',
        'notifications_enabled' => 'boolean',
    ];

    /**
     * Get the team
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by role
     */
    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Check if member has permission
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'owner') {
            return true;
        }

        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * Update last activity timestamp
     */
    public function updateActivity(): void
    {
        $this->update(['last_active_at' => now()]);
    }
}
