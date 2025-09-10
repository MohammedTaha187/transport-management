<?php

namespace Tests\Feature;

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

class ResourcesCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_resources_expose_pages_and_queries()
    {
        $company = Company::create(['name' => 'Coverage Co']);
        $driver = Driver::create(['name' => 'Cov Driver','company_id' => $company->id, 'license_number' => 7777]);
        $vehicle = Vehicle::create(['model' => 'V-Cov', 'plate_number' => 7777, 'company_id' => $company->id]);

        // Create a trip so query has something to load
        Trip::create([
            'company_id' => $company->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'start_time' => Carbon::now()->toDateString(),
            'end_time' => Carbon::now()->addDay()->toDateString(),
            'status' => 'active',
        ]);

        // Resources should at least return pages arrays
        $this->assertIsArray(CompanyResource::getPages());
        $this->assertIsArray(DriverResource::getPages());
        $this->assertIsArray(VehicleResource::getPages());
        $this->assertIsArray(TripResource::getPages());

        // getEloquentQuery should return a Builder instance
        $tripQuery = TripResource::getEloquentQuery();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $tripQuery);

        $companyQuery = CompanyResource::getEloquentQuery();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $companyQuery);
    }

    public function test_model_relationships()
    {
        $company = Company::create(['name' => 'Relations Co']);
        $d1 = Driver::create(['name' => 'R1', 'company_id' => $company->id, 'license_number' => 1010]);
        $v1 = Vehicle::create(['model' => 'RV', 'plate_number' => 2020, 'company_id' => $company->id]);

        $t = Trip::create([
            'company_id' => $company->id,
            'driver_id' => $d1->id,
            'vehicle_id' => $v1->id,
            'start_time' => Carbon::now()->toDateString(),
            'end_time' => Carbon::now()->addDay()->toDateString(),
            'status' => 'active',
        ]);

        // relations should be accessible
        $this->assertEquals(1, $company->drivers()->count());
        $this->assertEquals(1, $company->vehicles()->count());
        $this->assertEquals(1, $company->trips()->count());

        $this->assertEquals($company->id, $d1->company->id);
        $this->assertEquals($company->id, $v1->company->id);
        $this->assertEquals($d1->id, $t->driver->id);
    }

    public function test_trip_overlap_ignore_id()
    {
        $company = Company::create(['name' => 'Ignore Co']);
        $driver = Driver::create(['name' => 'Ig Driver','company_id' => $company->id, 'license_number' => 3333]);
        $vehicle = Vehicle::create(['model' => 'VI', 'plate_number' => 3333, 'company_id' => $company->id]);

        $existing = Trip::create([
            'company_id' => $company->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'start_time' => '2025-09-10',
            'end_time' => '2025-09-12',
            'status' => 'active',
        ]);

        // hasOverlap should ignore the provided id
        $this->assertFalse(TripResource::hasOverlap('driver_id', $driver->id, '2025-09-10', '2025-09-12', $existing->id));
    }
}
