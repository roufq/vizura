<?php

namespace App\Models;

use App\Models\Concerns\LocationScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    /** @use HasFactory<\Database\Factories\SaleFactory> */
    use HasFactory, LocationScoped;

    protected $fillable = [
        'location_id',
        'cashier_id',
        'customer_id',
        'customer_name',
        'customer_phone',
        'notes',
        'original_sale_id',
        'reference_no',
        'type',
        'status',
        'subtotal',
        'order_discount',
        'tax_amount',
        'is_tax_inclusive',
        'total',
        'paid_total',
        'change_due',
        'receivable_balance',
        'payment_status',
        'posted_at',
        'voided_by',
        'voided_at',
        'void_reason',
        'returned_by',
        'returned_at',
        'return_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'order_discount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'is_tax_inclusive' => 'boolean',
            'total' => 'decimal:2',
            'paid_total' => 'decimal:2',
            'change_due' => 'decimal:2',
            'receivable_balance' => 'decimal:2',
            'posted_at' => 'datetime',
            'voided_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function cashier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function originalSale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'original_sale_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(SalePaymentSettlement::class);
    }
}
