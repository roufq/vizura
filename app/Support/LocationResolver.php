<?php

namespace App\Support;

class LocationResolver
{
    /**
     * @param  array<string, mixed>  $filters
     * @param  array<int>  $allowedLocationIds
     */
    public function resolveFromFilters(array $filters, array $allowedLocationIds, bool $canViewAll): ?int
    {
        if ($canViewAll && ($filters['all_locations'] ?? false)) {
            return null;
        }

        if (! empty($filters['location_id'])) {
            $requested = (int) $filters['location_id'];
            if ($allowedLocationIds === [] || in_array($requested, $allowedLocationIds, true)) {
                return $requested;
            }
        }

        $activeLocationId = ActiveLocation::id();
        if ($activeLocationId && ($allowedLocationIds === [] || in_array($activeLocationId, $allowedLocationIds, true))) {
            return $activeLocationId;
        }

        return $allowedLocationIds[0] ?? null;
    }

    /**
     * @param  array<int>  $allowedLocationIds
     */
    public function resolveFromRequest($request, array $allowedLocationIds, bool $canViewAll): ?int
    {
        return $this->resolveFromFilters($request->all(), $allowedLocationIds, $canViewAll);
    }
}
