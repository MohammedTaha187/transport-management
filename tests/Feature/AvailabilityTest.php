<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Trip;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Company;

class AvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_available_drivers_and_vehicles()
    {
        $company = Company::create(['name' => 'Test Co']);

        $drivers = collect([
            Driver::create(['name' => 'D1', 'company_id' => $company->id, 'license_number' => 1111]),
            Driver::create(['name' => 'D2', 'company_id' => $company->id, 'license_number' => 2222]),
            Driver::create(['name' => 'D3', 'company_id' => $company->id, 'license_number' => 3333]),
        ]);

        $vehicles = collect([
            Vehicle::create(['model' => 'V1', 'plate_number' => 1111, 'company_id' => $company->id]),
            Vehicle::create(['model' => 'V2', 'plate_number' => 2222, 'company_id' => $company->id]),
            Vehicle::create(['model' => 'V3', 'plate_number' => 3333, 'company_id' => $company->id]),
        ]);

        // create a trip that occupies driver 1 and vehicle 1
        Trip::create([
            'company_id' => $company->id,
            'driver_id' => $drivers[0]->id,
            'vehicle_id' => $vehicles[0]->id,
            'start_time' => '2025-09-10',
            'end_time' => '2025-09-12',
            'status' => 'active',
        ]);

        $page = new \App\Filament\Pages\ManagerAvailability();
        $page->start_time = '2025-09-11';
        $page->end_time = '2025-09-11';

        $availableDrivers = $page->getAvailableDrivers();
        $availableVehicles = $page->getAvailableVehicles();

        $this->assertCount(2, $availableDrivers);
        $this->assertFalse($availableDrivers->contains('id', $drivers[0]->id));

        $this->assertCount(2, $availableVehicles);
        $this->assertFalse($availableVehicles->contains('id', $vehicles[0]->id));
    }
}
