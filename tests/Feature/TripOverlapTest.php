<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Trip;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Company;
use Carbon\Carbon;

class TripOverlapTest extends TestCase
{
    use RefreshDatabase;

    public function test_has_overlap_detects_overlapping_trips()
    {
        $company = Company::create(['name' => 'Test Co']);
        $driver = Driver::create(['name' => 'Driver 1', 'company_id' => $company->id, 'license_number' => 12345]);
        $vehicle = Vehicle::create(['model' => 'Model X', 'plate_number' => 1234, 'company_id' => $company->id]);

        // Use date-only values to match the migrations which use date columns
        $existing = Trip::create([
            'company_id' => $company->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'start_time' => '2025-09-10',
            'end_time' => '2025-09-12',
            'status' => 'active',
        ]);

        // overlapping start inside
        $this->assertTrue(\App\Filament\Resources\TripResource::hasOverlap('driver_id', $driver->id, '2025-09-11', '2025-09-13'));

        // non-overlapping before
        $this->assertFalse(\App\Filament\Resources\TripResource::hasOverlap('driver_id', $driver->id, '2025-09-07', '2025-09-09'));

        // contained within existing
        $this->assertTrue(\App\Filament\Resources\TripResource::hasOverlap('driver_id', $driver->id, '2025-09-10', '2025-09-11'));

        // different driver should not overlap
        $otherDriver = Driver::create(['name' => 'Driver 2', 'company_id' => $company->id, 'license_number' => 54321]);
        $this->assertFalse(\App\Filament\Resources\TripResource::hasOverlap('driver_id', $otherDriver->id, '2025-09-10 11:00:00', '2025-09-10 13:00:00'));
    }
}
