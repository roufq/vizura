<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActiveLocation
{
    public static function id(): ?int
    {
        $sessionId = session('active_location_id');
        if ($sessionId !== null) {
            return (int) $sessionId;
        }

        $user = Auth::user();
        return $user?->active_location_id ? (int) $user->active_location_id : null;
    }

    public static function shouldBypass(): bool
    {
        $user = Auth::user();
        if (! $user || ! method_exists($user, 'hasRole')) {
            return false;
        }

        return $user->hasRole('Owner') && Request::boolean('bypass_location');
    }
}
