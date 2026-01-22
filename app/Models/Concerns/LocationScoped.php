<?php

namespace App\Models\Concerns;

use App\Support\ActiveLocation;
use Illuminate\Database\Eloquent\Builder;

trait LocationScoped
{
    protected static function bootLocationScoped(): void
    {
        static::addGlobalScope('active_location', function (Builder $builder): void {
            $locationId = ActiveLocation::id();

            if ($locationId !== null && ! ActiveLocation::shouldBypass()) {
                $builder->where($builder->getModel()->getTable().'.location_id', $locationId);
            }
        });
    }

    public function scopeForActiveLocation(Builder $builder): Builder
    {
        $locationId = ActiveLocation::id();

        if ($locationId !== null && ! ActiveLocation::shouldBypass()) {
            $builder->where($builder->getModel()->getTable().'.location_id', $locationId);
        }

        return $builder;
    }
}
