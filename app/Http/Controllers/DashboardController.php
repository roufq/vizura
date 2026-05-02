<?php

namespace App\Http\Controllers;

use App\Support\DashboardService;
use App\Support\LocationResolver;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        public DashboardService $dashboardService,
        public LocationResolver $locationResolver
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $canViewAll = $user?->hasRole('Owner') || $user?->hasRole('Super Admin') ?? false;
        $allowedLocationIds = $canViewAll ? ($user?->accessibleLocationIds() ?? []) : [];
        $locationId = $canViewAll
            ? $this->locationResolver->resolveFromRequest($request, $allowedLocationIds, $canViewAll)
            : \App\Support\ActiveLocation::id();

        return view('dashboard', $this->dashboardService->build(
            $user,
            $locationId,
            $allowedLocationIds,
            $canViewAll,
            $request->only(['location_id', 'all_locations'])
        ));
    }
}
