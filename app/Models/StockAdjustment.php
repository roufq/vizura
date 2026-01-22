<?php

namespace App\Models;

use App\Models\Concerns\LocationScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    /** @use HasFactory<\Database\Factories\StockAdjustmentFactory> */
    use HasFactory, LocationScoped;

    protected $fillable = [
        'location_id',
        'product_id',
        'requested_by',
        'approved_by',
        'quantity_delta',
        'reason',
        'evidence_path',
        'status',
        'approved_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity_delta' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
