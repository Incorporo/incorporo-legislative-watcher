<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScrapingJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_type',
        'chamber',
        'scope',
        'status',
        'progress',
        'items_total',
        'items_processed',
        'items_created',
        'items_updated',
        'items_failed',
        'errors_count',
        'started_at',
        'completed_at',
        'duration_seconds',
        'error_log',
        'error_summary',
        'http_requests',
        'bytes_downloaded',
        'estimated_cost',
        'trigger',
        'triggered_by',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'error_summary' => 'array',
        'metadata' => 'array',
    ];

    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function markAsStarted()
    {
        $this->update([
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'duration_seconds' => now()->diffInSeconds($this->started_at),
            'progress' => 100,
        ]);
    }

    public function markAsFailed($errorMessage)
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'duration_seconds' => now()->diffInSeconds($this->started_at),
            'error_log' => $this->error_log . "\n" . $errorMessage,
            'errors_count' => $this->errors_count + 1,
        ]);
    }
}
