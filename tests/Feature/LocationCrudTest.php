<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LocationCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_location_with_unique_code_and_status(): void
    {
        $owner = $this->makeOwner();

        $response = $this
            ->actingAs($owner)
            ->withSession(['active_location_id' => $owner->active_location_id])
            ->post(route('locations.store', absolute: false), [
            'code' => 'LOC-1',
            'name' => 'Lokasi Baru',
            'address' => 'Jalan Contoh 123',
            'phone' => '081234567890',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('locations.index', absolute: false));

        $location = Location::where('code', 'LOC-1')->first();
        $this->assertNotNull($location);
        $this->assertTrue($location->is_active);
    }

    public function test_owner_cannot_create_location_with_duplicate_code(): void
    {
        $owner = $this->makeOwner();

        Location::create([
            'code' => 'LOC-1',
            'name' => 'Lokasi Awal',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($owner)
            ->withSession(['active_location_id' => $owner->active_location_id])
            ->post(route('locations.store', absolute: false), [
            'code' => 'LOC-1',
            'name' => 'Lokasi Duplikat',
            'is_active' => '1',
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    public function test_owner_can_update_location_status_to_inactive(): void
    {
        $owner = $this->makeOwner();

        $location = Location::create([
            'code' => 'LOC-2',
            'name' => 'Lokasi Update',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($owner)
            ->withSession(['active_location_id' => $owner->active_location_id])
            ->put(route('locations.update', $location, absolute: false), [
            'code' => 'LOC-2',
            'name' => 'Lokasi Update',
            'is_active' => '0',
        ]);

        $response->assertRedirect(route('locations.index', absolute: false));

        $this->assertFalse($location->fresh()->is_active);
    }

    public function test_user_can_select_active_location_and_session_is_set(): void
    {
        $owner = $this->makeOwner();
        $location = Location::create([
            'code' => 'LOC-3',
            'name' => 'Lokasi Aktif',
            'is_active' => true,
        ]);

        $response = $this->actingAs($owner)->post(route('locations.active.update', absolute: false), [
            'location_id' => $location->id,
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $response->assertSessionHas('active_location_id', $location->id);
        $this->assertSame($location->id, $owner->fresh()->active_location_id);
    }

    public function test_user_cannot_select_inactive_location(): void
    {
        $owner = $this->makeOwner();
        $location = Location::create([
            'code' => 'LOC-4',
            'name' => 'Lokasi Nonaktif',
            'is_active' => false,
        ]);

        $response = $this->actingAs($owner)->post(route('locations.active.update', absolute: false), [
            'location_id' => $location->id,
        ]);

        $response->assertNotFound();
    }

    private function makeOwner(): User
    {
        Role::firstOrCreate(['name' => 'Owner']);

        $location = Location::create([
            'code' => 'OWNER-LOC',
            'name' => 'Lokasi Owner',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'active_location_id' => $location->id,
        ]);
        $user->assignRole('Owner');

        return $user;
    }
}
