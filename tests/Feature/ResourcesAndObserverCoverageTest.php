<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Filament\Resources\CompanyResource;
use App\Filament\Resources\DriverResource;
use App\Filament\Resources\VehicleResource;
use App\Filament\Resources\TripResource;
use App\Filament\Pages\ManagerAvailability;
use App\Models\Company;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Trip;
use Illuminate\Support\Facades\Cache;

class ResourcesAndObserverCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_resources_form_table_and_schema_methods()
    {
        // Ensure testableSchema exists
        $this->assertIsArray(CompanyResource::testableSchema());
        $this->assertIsArray(DriverResource::testableSchema());
        $this->assertIsArray(VehicleResource::testableSchema());
        $this->assertIsArray(TripResource::testableSchema());

        // Assert form keys exist in the testable schemas
        $this->assertArrayHasKey('form', CompanyResource::testableSchema());
        $this->assertArrayHasKey('form', DriverResource::testableSchema());
        $this->assertArrayHasKey('form', VehicleResource::testableSchema());
        $this->assertArrayHasKey('form', TripResource::testableSchema());
    }

    public function test_manager_availability_methods_return_collections()
    {
        $company = Company::create(['name' => 'Avail Co']);
        $drivers = collect([
            Driver::create(['name' => 'D1', 'company_id' => $company->id, 'license_number' => 1111]),
            Driver::create(['name' => 'D2', 'company_id' => $company->id, 'license_number' => 2222]),
        ]);

        $vehicles = collect([
            Vehicle::create(['model' => 'V1', 'plate_number' => 1111, 'company_id' => $company->id]),
            Vehicle::create(['model' => 'V2', 'plate_number' => 2222, 'company_id' => $company->id]),
        ]);

        $page = new ManagerAvailability();
        $page->start_time = '2025-09-10';
        $page->end_time = '2025-09-11';

        $availableDrivers = $page->getAvailableDrivers();
        $availableVehicles = $page->getAvailableVehicles();

        $this->assertIsIterable($availableDrivers);
        $this->assertIsIterable($availableVehicles);
    }

    public function test_observer_clears_cache_on_update_and_delete()
    {
        Cache::put('active_trips', 123);
        Cache::put('available_drivers', 5);
        Cache::put('completed_trips', 7);
        Cache::put('filament.active_trips_count', 9);

        $company = Company::create(['name' => 'Obs Co']);
        $driver = Driver::create(['name' => 'Obs D', 'company_id' => $company->id, 'license_number' => 9999]);
        $vehicle = Vehicle::create(['model' => 'Obs V', 'plate_number' => 9999, 'company_id' => $company->id]);

        $trip = Trip::create([
            'company_id' => $company->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'start_time' => '2025-09-15',
            'end_time' => '2025-09-16',
            'status' => 'active',
        ]);

        // cache keys should have been cleared by observer on created
        $this->assertNull(Cache::get('active_trips'));
        $this->assertNull(Cache::get('available_drivers'));

        // set again and update trip to trigger updated observer
        Cache::put('active_trips', 1);
        $trip->status = 'completed';
        $trip->save();

        $this->assertNull(Cache::get('active_trips'));

        // set again and delete to trigger deleted observer
        Cache::put('active_trips', 2);
        $trip->delete();

        $this->assertNull(Cache::get('active_trips'));
    }
}
