<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'owner_id',
        'plan',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    public function canAccess(string $feature): bool
    {
        if (!$this->isActive()) return false;

        $plans = [
            'starter' => [
                'pos_basics', 'ajax_search', 'barcode_scanner', 'draft_sales', 
                'cash_payment', 'qris_payment', 'basic_piutang', 'discounts', 
                'tax_system', 'void_retur', 'stock_card', 'stock_adjustment', 
                'stock_alert', 'product_uom', 'basic_accounting', 'sales_report_csv', 
                'cash_up', 'customer_db', 'supplier_db', 'dark_mode'
            ],
            'business' => [
                // Inherit Starter +
                'pos_basics', 'ajax_search', 'barcode_scanner', 'draft_sales', 
                'cash_payment', 'qris_payment', 'basic_piutang', 'discounts', 
                'tax_system', 'void_retur', 'stock_card', 'stock_adjustment', 
                'stock_alert', 'product_uom', 'basic_accounting', 'sales_report_csv', 
                'cash_up', 'customer_db', 'supplier_db', 'dark_mode',
                
                'ar_management', 'ap_management', 'stock_transfer', 'location_pricing', 
                'multi_location_session', 'full_accounting', 'p_l_report', 'cashflow_report', 
                'expense_management', 'rbac_manager_head', 'soft_deletes'
            ],
            'enterprise' => [
                // Inherit Business +
                'pos_basics', 'ajax_search', 'barcode_scanner', 'draft_sales', 
                'cash_payment', 'qris_payment', 'basic_piutang', 'discounts', 
                'tax_system', 'void_retur', 'stock_card', 'stock_adjustment', 
                'stock_alert', 'product_uom', 'basic_accounting', 'sales_report_csv', 
                'cash_up', 'customer_db', 'supplier_db', 'dark_mode',
                'ar_management', 'ap_management', 'stock_transfer', 'location_pricing', 
                'multi_location_session', 'full_accounting', 'p_l_report', 'cashflow_report', 
                'expense_management', 'rbac_manager_head', 'soft_deletes',

                'audit_trail', 'custom_receipt', 'consolidated_reports', 'loyalty_analysis', 
                'unlimited_outlets', 'unlimited_users', 'db_optimization'
            ]
        ];

        return in_array($feature, $plans[$this->plan] ?? []);
    }

    public function maxOutlets(): int
    {
        return match($this->plan) {
            'starter' => 1,
            'business' => 5,
            'enterprise' => 9999,
            default => 0
        };
    }

    public function maxUsers(): int
    {
        return match($this->plan) {
            'starter' => 3,
            'business' => 15,
            'enterprise' => 9999,
            default => 0
        };
    }
}
