<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BillSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'name',
        'keywords',
        'chambers',
        'categories',
        'statuses',
        'urgent_only',
        'risk_level',
        'frequency',
        'include_ai_summary',
        'preferred_time',
        'active',
        'verified_at',
        'last_notified_at',
        'verification_token',
        'unsubscribe_token',
    ];

    protected $casts = [
        'keywords' => 'array',
        'chambers' => 'array',
        'categories' => 'array',
        'statuses' => 'array',
        'urgent_only' => 'boolean',
        'include_ai_summary' => 'boolean',
        'active' => 'boolean',
        'verified_at' => 'datetime',
        'last_notified_at' => 'datetime',
        'preferred_time' => 'datetime',
    ];

    /**
     * Boot method to generate tokens on creation
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subscription) {
            if (empty($subscription->verification_token)) {
                $subscription->verification_token = Str::random(64);
            }
            if (empty($subscription->unsubscribe_token)) {
                $subscription->unsubscribe_token = Str::random(64);
            }
        });
    }

    /**
     * Scope to get only verified subscriptions
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    /**
     * Scope to get only active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('active', true)->whereNotNull('verified_at');
    }

    /**
     * Scope to get subscriptions by frequency
     */
    public function scopeFrequency($query, $frequency)
    {
        return $query->where('frequency', $frequency);
    }

    /**
     * Scope to get subscriptions due for notification
     */
    public function scopeDueForNotification($query)
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('last_notified_at')
                  ->orWhere(function ($subq) {
                      // Daily: last notified more than 23 hours ago
                      $subq->where('frequency', 'daily')
                          ->where('last_notified_at', '<=', now()->subHours(23));
                  })
                  ->orWhere(function ($subq) {
                      // Weekly: last notified more than 6 days ago
                      $subq->where('frequency', 'weekly')
                          ->where('last_notified_at', '<=', now()->subDays(6));
                  });
            });
    }

    /**
     * Check if subscription matches a bill
     */
    public function matchesBill(LegislativeBill $bill): bool
    {
        // Check urgent_only filter
        if ($this->urgent_only && !$bill->urgency_status) {
            return false;
        }

        // Check chamber filter
        if (!empty($this->chambers) && !in_array($bill->chamber, $this->chambers)) {
            return false;
        }

        // Check status filter
        if (!empty($this->statuses) && !in_array($bill->status, $this->statuses)) {
            return false;
        }

        // Check risk level filter
        if ($this->risk_level) {
            $billRiskLevel = $bill->getHighestRiskLevel();
            if ($billRiskLevel !== $this->risk_level) {
                return false;
            }
        }

        // Check keywords - search in title and description
        if (!empty($this->keywords)) {
            $matchFound = false;
            $searchText = strtolower($bill->title . ' ' . $bill->description);

            foreach ($this->keywords as $keyword) {
                if (str_contains($searchText, strtolower($keyword))) {
                    $matchFound = true;
                    break;
                }
            }

            if (!$matchFound) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get matching bills for this subscription
     */
    public function getMatchingBills($limit = 50)
    {
        $query = LegislativeBill::query();

        // Apply filters
        if ($this->urgent_only) {
            $query->where('urgency_status', true);
        }

        if (!empty($this->chambers)) {
            $query->whereIn('chamber', $this->chambers);
        }

        if (!empty($this->statuses)) {
            $query->whereIn('status', $this->statuses);
        }

        if ($this->risk_level) {
            $query->whereHas('risks', function ($q) {
                $q->where('risk_level', $this->risk_level);
            });
        }

        // Keyword search
        if (!empty($this->keywords)) {
            $query->where(function ($q) {
                foreach ($this->keywords as $keyword) {
                    $q->orWhere('title', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%");
                }
            });
        }

        // Only bills created/updated since last notification
        if ($this->last_notified_at) {
            $query->where(function ($q) {
                $q->where('created_at', '>', $this->last_notified_at)
                  ->orWhere('updated_at', '>', $this->last_notified_at);
            });
        }

        return $query->with(['initiators', 'analysis', 'risks'])
                     ->orderBy('created_at', 'desc')
                     ->limit($limit)
                     ->get();
    }

    /**
     * Mark as verified
     */
    public function verify()
    {
        $this->update([
            'verified_at' => now(),
            'verification_token' => null,
        ]);
    }

    /**
     * Mark as notified
     */
    public function markAsNotified()
    {
        $this->update(['last_notified_at' => now()]);
    }

    /**
     * Deactivate subscription
     */
    public function deactivate()
    {
        $this->update(['active' => false]);
    }

    /**
     * Check if subscription is verified
     */
    public function isVerified(): bool
    {
        return !is_null($this->verified_at);
    }

    /**
     * Get verification URL
     */
    public function getVerificationUrl(): string
    {
        return route('subscriptions.verify', ['token' => $this->verification_token]);
    }

    /**
     * Get unsubscribe URL
     */
    public function getUnsubscribeUrl(): string
    {
        return route('subscriptions.unsubscribe', ['token' => $this->unsubscribe_token]);
    }

    /**
     * Get manage URL
     */
    public function getManageUrl(): string
    {
        return route('subscriptions.manage', ['token' => $this->unsubscribe_token]);
    }

    /**
     * Get human-readable frequency
     */
    public function getFrequencyLabel(): string
    {
        return match($this->frequency) {
            'instant' => 'Instant (la fiecare proiect)',
            'daily' => 'Zilnic',
            'weekly' => 'Săptămânal',
            default => ucfirst($this->frequency),
        };
    }

    /**
     * Get subscription summary
     */
    public function getSummary(): string
    {
        $parts = [];

        if (!empty($this->keywords)) {
            $parts[] = 'Cuvinte cheie: ' . implode(', ', $this->keywords);
        }

        if (!empty($this->chambers)) {
            $chambers = array_map(fn($c) => $c === 'cdep' ? 'CDEP' : 'Senat', $this->chambers);
            $parts[] = 'Camere: ' . implode(', ', $chambers);
        }

        if ($this->urgent_only) {
            $parts[] = 'Doar urgențe';
        }

        if ($this->risk_level) {
            $parts[] = 'Risc: ' . ucfirst($this->risk_level);
        }

        return !empty($parts) ? implode(' • ', $parts) : 'Toate proiectele';
    }
}
