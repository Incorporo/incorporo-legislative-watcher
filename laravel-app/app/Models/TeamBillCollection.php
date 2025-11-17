<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TeamBillCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'name',
        'description',
        'created_by',
        'color',
        'bill_count',
        'is_shared',
    ];

    protected $casts = [
        'is_shared' => 'boolean',
    ];

    /**
     * Get the team
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
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all bills in this collection
     */
    public function bills(): BelongsToMany
    {
        return $this->belongsToMany(LegislativeBill::class, 'collection_bills', 'collection_id', 'bill_id')
            ->withPivot(['added_by', 'note', 'position'])
            ->withTimestamps()
            ->orderBy('collection_bills.position');
    }

    /**
     * Scope to filter shared collections
     */
    public function scopeShared($query)
    {
        return $query->where('is_shared', true);
    }

    /**
     * Add bill to collection
     */
    public function addBill(int $billId, int $addedBy, ?string $note = null): void
    {
        $position = $this->bills()->max('collection_bills.position') + 1;

        $this->bills()->attach($billId, [
            'added_by' => $addedBy,
            'note' => $note,
            'position' => $position,
        ]);

        $this->increment('bill_count');
    }

    /**
     * Remove bill from collection
     */
    public function removeBill(int $billId): void
    {
        $this->bills()->detach($billId);
        $this->decrement('bill_count');
    }
}
