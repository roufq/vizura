<?php

namespace App\Models;

use App\Models\Concerns\LocationScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class StockItem extends Model
{
    /** @use HasFactory<\Database\Factories\StockItemFactory> */
    use HasFactory, LocationScoped;

    protected $fillable = [
        'location_id',
        'product_id',
        'quantity_on_hand',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity_on_hand' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (StockItem $stockItem): void {
            $stockItem->ensureNotNegative();
        });
    }

    public function adjustQuantity(float $delta): void
    {
        $newQuantity = (float) $this->quantity_on_hand + $delta;
        $this->setQuantity($newQuantity);
    }

    public function setQuantity(float $quantity): void
    {
        if ($quantity < 0) {
            throw ValidationException::withMessages([
                'quantity_on_hand' => 'Stok tidak boleh minus.',
            ]);
        }

        $this->quantity_on_hand = $quantity;
        $this->save();
    }

    protected function ensureNotNegative(): void
    {
        if ((float) $this->quantity_on_hand < 0) {
            throw ValidationException::withMessages([
                'quantity_on_hand' => 'Stok tidak boleh minus.',
            ]);
        }
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
