<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    /** @use HasFactory<\Database\Factories\AccountFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'is_cash',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_cash' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }
}
