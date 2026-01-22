<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LocationScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_location_scoped_models_only_return_active_location_records(): void
    {
        $locationA = Location::create([
            'code' => 'LOC-A',
            'name' => 'Lokasi A',
            'is_active' => true,
        ]);

        $locationB = Location::create([
            'code' => 'LOC-B',
            'name' => 'Lokasi B',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'active_location_id' => $locationA->id,
        ]);

        $this->actingAs($user);
        session(['active_location_id' => $locationA->id]);

        AuditLog::create([
            'user_id' => $user->id,
            'location_id' => $locationA->id,
            'action' => 'login',
            'occurred_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'location_id' => $locationB->id,
            'action' => 'login',
            'occurred_at' => now(),
        ]);

        $logs = AuditLog::all();

        $this->assertCount(1, $logs);
        $this->assertSame($locationA->id, $logs->first()->location_id);

        $logs = AuditLog::query()
            ->withoutGlobalScope('active_location')
            ->forActiveLocation()
            ->get();

        $this->assertCount(1, $logs);
        $this->assertSame($locationA->id, $logs->first()->location_id);
    }

    public function test_location_scope_bypass_requires_owner_and_flag(): void
    {
        $locationA = Location::create([
            'code' => 'LOC-A',
            'name' => 'Lokasi A',
            'is_active' => true,
        ]);

        $locationB = Location::create([
            'code' => 'LOC-B',
            'name' => 'Lokasi B',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'active_location_id' => $locationA->id,
        ]);

        $this->actingAs($user);
        session(['active_location_id' => $locationA->id]);

        AuditLog::create([
            'user_id' => $user->id,
            'location_id' => $locationA->id,
            'action' => 'login',
            'occurred_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'location_id' => $locationB->id,
            'action' => 'login',
            'occurred_at' => now(),
        ]);

        request()->merge(['bypass_location' => '1']);

        $this->assertCount(1, AuditLog::all());

        Role::firstOrCreate(['name' => 'Owner']);
        $user->assignRole('Owner');

        $this->assertCount(2, AuditLog::all());

        request()->replace([]);
    }
}
