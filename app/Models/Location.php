<?php

namespace App\Models;

use App\Models\Concerns\AuditLoggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use \App\Models\Concerns\BelongsToTenant, AuditLoggable, HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
        'is_active',
        'toko_pusat',
        'tenant_id',
        'receipt_header',
        'receipt_footer',
        'receipt_tagline',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'toko_pusat' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::created(function (Location $location): void {
            $productIds = Product::query()->pluck('id');

            if ($productIds->isEmpty()) {
                return;
            }

            $payload = $productIds
                ->map(fn (int $productId): array => [
                    'product_id' => $productId,
                    'quantity_on_hand' => 0,
                ])
                ->all();

            $location->stockItems()->createMany($payload);
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isHeadOffice(): bool
    {
        return (bool) $this->toko_pusat;
    }

    public function stockItems(): HasMany
    {
        return $this->hasMany(StockItem::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_locations')->withTimestamps();
    }
}
