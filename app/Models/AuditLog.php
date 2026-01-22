<?php

namespace App\Models;

use App\Models\Concerns\LocationScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory, LocationScoped;

    protected $fillable = [
        'user_id',
        'location_id',
        'action',
        'metadata',
        'ip_address',
        'user_agent',
        'occurred_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];
}
