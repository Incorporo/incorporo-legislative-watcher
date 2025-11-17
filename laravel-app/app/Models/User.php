<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'organization',
        'role_type',
        'bio',
        'avatar_url',
        'notification_preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notification_preferences' => 'array',
            'last_activity_at' => 'datetime',
        ];
    }

    /**
     * Get the watchlist entries for the user
     */
    public function watchlist(): HasMany
    {
        return $this->hasMany(Watchlist::class);
    }

    /**
     * Get all bills the user is watching
     */
    public function watchedBills()
    {
        return $this->hasManyThrough(
            LegislativeBill::class,
            Watchlist::class,
            'user_id',
            'id',
            'id',
            'bill_id'
        );
    }

    /**
     * Get the user's custom tags
     */
    public function tags(): HasMany
    {
        return $this->hasMany(UserTag::class);
    }

    /**
     * Get the user's bill notes
     */
    public function notes(): HasMany
    {
        return $this->hasMany(BillNote::class);
    }

    /**
     * Get the user's saved searches
     */
    public function savedSearches(): HasMany
    {
        return $this->hasMany(SavedSearch::class);
    }

    /**
     * Get the user's default saved search
     */
    public function defaultSearch()
    {
        return $this->hasOne(SavedSearch::class)->where('is_default', true);
    }

    /**
     * Get the user's dashboard preferences
     */
    public function dashboardPreferences(): HasOne
    {
        return $this->hasOne(DashboardPreference::class);
    }

    /**
     * Get the user's bill subscriptions
     */
    public function billSubscriptions(): HasMany
    {
        return $this->hasMany(BillSubscription::class);
    }

    /**
     * Get teams owned by the user (Phase 3)
     */
    public function ownedTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    /**
     * Get teams the user is a member of (Phase 3)
     */
    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_members')
            ->withPivot(['role', 'permissions', 'joined_at', 'notifications_enabled'])
            ->withTimestamps();
    }

    /**
     * Get team memberships (Phase 3)
     */
    public function teamMemberships(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    /**
     * Get discussions created by the user (Phase 3)
     */
    public function discussions(): HasMany
    {
        return $this->hasMany(BillDiscussion::class);
    }

    /**
     * Get comments by the user (Phase 3)
     */
    public function comments(): HasMany
    {
        return $this->hasMany(DiscussionComment::class);
    }

    /**
     * Get tasks assigned to the user (Phase 3)
     */
    public function assignedTasks(): HasMany
    {
        return $this->hasMany(TeamTask::class, 'assigned_to');
    }

    /**
     * Get tasks created by the user (Phase 3)
     */
    public function createdTasks(): HasMany
    {
        return $this->hasMany(TeamTask::class, 'created_by');
    }

    /**
     * Update the user's last activity timestamp
     */
    public function updateLastActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }

    /**
     * Get the user's notification preference for a specific type
     */
    public function getNotificationPreference(string $type, bool $default = true): bool
    {
        $preferences = $this->notification_preferences ?? [];
        return $preferences[$type] ?? $default;
    }

    /**
     * Set a notification preference
     */
    public function setNotificationPreference(string $type, bool $enabled): void
    {
        $preferences = $this->notification_preferences ?? [];
        $preferences[$type] = $enabled;
        $this->update(['notification_preferences' => $preferences]);
    }

    /**
     * Check if user is actively watching a specific bill
     */
    public function isWatching(int $billId): bool
    {
        return $this->watchlist()->where('bill_id', $billId)->exists();
    }

    /**
     * Add a bill to the user's watchlist
     */
    public function addToWatchlist(int $billId, array $options = []): Watchlist
    {
        return $this->watchlist()->create(array_merge([
            'bill_id' => $billId,
            'notifications_enabled' => true,
            'priority' => 'normal',
        ], $options));
    }

    /**
     * Remove a bill from the user's watchlist
     */
    public function removeFromWatchlist(int $billId): bool
    {
        return $this->watchlist()->where('bill_id', $billId)->delete() > 0;
    }
}
