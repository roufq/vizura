<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'current_balance',
        'loyalty_points',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'current_balance' => 'decimal:2',
            'loyalty_points' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function sales(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
