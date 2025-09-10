<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Trip;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Company;

class ObserverOverlapTest extends TestCase
{
    use RefreshDatabase;

    public function test_observer_blocks_overlapping_trip_creation()
    {
        $company = Company::create(['name' => 'Test Co']);
        $driver = Driver::create(['name' => 'Driver 1', 'company_id' => $company->id, 'license_number' => 12345]);
        $vehicle = Vehicle::create(['model' => 'Model X', 'plate_number' => 1234, 'company_id' => $company->id]);

        $existing = Trip::create([
            'company_id' => $company->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'start_time' => '2025-09-10',
            'end_time' => '2025-09-12',
            'status' => 'active',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Driver is already booked');

        Trip::create([
            'company_id' => $company->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'start_time' => '2025-09-11',
            'end_time' => '2025-09-13',
            'status' => 'active',
        ]);
    }

    public function test_observer_allows_non_overlapping_trip_creation()
    {
        $company = Company::create(['name' => 'Test Co']);
        $driver = Driver::create(['name' => 'Driver 1', 'company_id' => $company->id, 'license_number' => 12345]);
        $vehicle = Vehicle::create(['model' => 'Model X', 'plate_number' => 1234, 'company_id' => $company->id]);

        $existing = Trip::create([
            'company_id' => $company->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'start_time' => '2025-09-10',
            'end_time' => '2025-09-12',
            'status' => 'active',
        ]);

        $trip = Trip::create([
            'company_id' => $company->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'start_time' => '2025-09-13',
            'end_time' => '2025-09-14',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('trips', ['id' => $trip->id]);
    }
}
