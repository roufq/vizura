<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActiveLocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_active_location_for_dashboard(): void
    {
        $user = User::factory()->create([
            'active_location_id' => null,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('locations.active', absolute: false));
    }

    public function test_allows_dashboard_when_active_location_is_set(): void
    {
        $location = Location::create([
            'code' => 'LOC-1',
            'name' => 'Lokasi Test',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'active_location_id' => $location->id,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
    }
}
