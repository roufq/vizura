<?php

namespace App\Models;

use App\Models\Concerns\AuditLoggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use \App\Models\Concerns\BelongsToTenant, AuditLoggable, HasFactory, SoftDeletes;

    protected $fillable = [
        'sku',
        'barcode',
        'name',
        'category_id',
        'unit_id',
        'sale_price',
        'cost_price',
        'is_taxable',
        'is_active',
        'block_when_out_of_stock',
        'batch_code',
        'expires_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sale_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'is_taxable' => 'boolean',
            'is_active' => 'boolean',
            'block_when_out_of_stock' => 'boolean',
            'expires_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Product $product): void {
            $locations = Location::query()->pluck('id');

            if ($locations->isEmpty()) {
                return;
            }

            $payload = $locations
                ->map(fn (int $locationId): array => [
                    'location_id' => $locationId,
                    'quantity_on_hand' => 0,
                ])
                ->all();

            $product->stockItems()->createMany($payload);
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function stockItems(): HasMany
    {
        return $this->hasMany(StockItem::class);
    }
}
