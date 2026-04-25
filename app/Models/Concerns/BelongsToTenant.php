<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::creating(function ($model) {
            $manager = app(\App\Support\TenantManager::class);
            if ($manager->hasTenant()) {
                $model->tenant_id = $manager->getTenantId();
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            if (app()->runningInConsole()) return;

            $manager = app(\App\Support\TenantManager::class);
            if ($manager->hasTenant()) {
                $builder->where($builder->getQuery()->from . '.tenant_id', '=', $manager->getTenantId());
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
