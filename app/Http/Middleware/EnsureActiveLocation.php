<?php

namespace App\Http\Middleware;

use App\Models\Location;
use App\Support\ActiveLocation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveLocation
{
    public function handle(Request $request, Closure $next): Response
    {
        if (ActiveLocation::shouldBypass()) {
            return $next($request);
        }

        if (! $request->user()) {
            return $next($request);
        }

        if (! session()->has('active_location_id') && $request->user()->active_location_id) {
            session(['active_location_id' => $request->user()->active_location_id]);
        }

        $locationId = ActiveLocation::id();
        if (! $locationId) {
            return redirect()
                ->route('locations.active')
                ->with('status', 'Pilih lokasi aktif terlebih dahulu.');
        }

        $isActive = Location::whereKey($locationId)->where('is_active', true)->exists();
        if (! $isActive) {
            session()->forget('active_location_id');

            return redirect()
                ->route('locations.active')
                ->withErrors(['active_location_id' => 'Lokasi aktif tidak valid atau nonaktif.']);
        }

        return $next($request);
    }
}
