<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Trip;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate([
            'email' => 'test@example.com'
        ], [
            'name' => 'Test User',
            'password' => 'password'
        ]);

        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Filament Admin', 'password' => '12345678']
        );

        $companies = Company::factory()->count(2)->create();

        foreach ($companies as $company) {
            Driver::factory()->count(5)->create([ 'company_id' => $company->id ]);
            Vehicle::factory()->count(5)->create([ 'company_id' => $company->id ]);
        }

        $drivers = Driver::all();
        $vehicles = Vehicle::all();

        Trip::factory()->count(20)->make()->each(function ($trip) use ($companies, $drivers, $vehicles) {
            $company = $companies->random();

            $companyDrivers = $drivers->where('company_id', $company->id);
            $companyVehicles = $vehicles->where('company_id', $company->id);

            $driver = $companyDrivers->isNotEmpty() ? $companyDrivers->random() : $drivers->random();
            $vehicle = $companyVehicles->isNotEmpty() ? $companyVehicles->random() : $vehicles->random();

            $trip->company_id = $company->id;
            $trip->driver_id = $driver->id;
            $trip->vehicle_id = $vehicle->id;
            $trip->save();
        });
    }
}
