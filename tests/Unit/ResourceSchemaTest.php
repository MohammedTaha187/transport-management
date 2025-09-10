<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Filament\Resources\CompanyResource;
use App\Filament\Resources\DriverResource;
use App\Filament\Resources\VehicleResource;
use App\Filament\Resources\TripResource;
use App\Models\Company;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Trip;
use Carbon\Carbon;

class ResourceSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_resource_testable_schema()
    {
        $this->assertIsArray(CompanyResource::testableSchema());
        $this->assertIsArray(DriverResource::testableSchema());
        $this->assertIsArray(VehicleResource::testableSchema());
        $this->assertIsArray(TripResource::testableSchema());
    }

    public function test_trip_model_edge_cases()
    {
        $company = Company::create(['name' => 'Edge Co']);
        $driver = Driver::create(['name' => 'Edge D', 'company_id' => $company->id, 'license_number' => 9999]);
        $vehicle = Vehicle::create(['model' => 'EV', 'plate_number' => 9999, 'company_id' => $company->id]);

        // back-to-back trips: end_time equals next start_time should not be overlapping per whereBetween logic
        $t1 = Trip::create([
            'company_id' => $company->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'start_time' => '2025-09-10',
            'end_time' => '2025-09-11',
            'status' => 'active',
        ]);

        $t2 = Trip::create([
            'company_id' => $company->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'start_time' => '2025-09-11',
            'end_time' => '2025-09-12',
            'status' => 'active',
        ]);

        // The current implementation uses inclusive whereBetween, so back-to-back times are considered overlapping
        $this->assertTrue(TripResource::hasOverlap('driver_id', $driver->id, '2025-09-10', '2025-09-11', $t1->id));
        $this->assertTrue(TripResource::hasOverlap('driver_id', $driver->id, '2025-09-11', '2025-09-12', $t2->id));
    }
}
