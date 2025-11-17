<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardPreference extends Model
{
    use HasFactory;

    protected $table = 'user_dashboard_preferences';

    protected $fillable = [
        'user_id',
        'widget_layout',
        'visible_widgets',
        'theme',
        'chart_preferences',
    ];

    protected $casts = [
        'widget_layout' => 'array',
        'visible_widgets' => 'array',
        'chart_preferences' => 'array',
    ];

    /**
     * Get the user that owns these preferences
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the default widget layout
     */
    public static function getDefaultLayout(): array
    {
        return [
            ['widget' => 'recent_bills', 'position' => 1, 'size' => 'large'],
            ['widget' => 'watchlist_summary', 'position' => 2, 'size' => 'medium'],
            ['widget' => 'ai_insights', 'position' => 3, 'size' => 'medium'],
            ['widget' => 'activity_feed', 'position' => 4, 'size' => 'small'],
        ];
    }

    /**
     * Get the default visible widgets
     */
    public static function getDefaultVisibleWidgets(): array
    {
        return [
            'recent_bills',
            'watchlist_summary',
            'ai_insights',
            'activity_feed',
        ];
    }
}
