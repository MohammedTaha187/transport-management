<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Trip;
use Carbon\Carbon;

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

        // Create deterministic, spaced trips per driver to avoid overlap conflicts during seeding.
        $base = Carbon::now()->startOfDay();

        foreach ($companies as $company) {
            $companyDrivers = $drivers->where('company_id', $company->id)->values();
            $companyVehicles = $vehicles->where('company_id', $company->id)->values();

            foreach ($companyDrivers as $dIndex => $driver) {
                // create a few non-overlapping trips for this driver
                for ($i = 0; $i < 3; $i++) {
                    $start = $base->copy()->addDays($dIndex + $i)->addHours($i * 4 + ($dIndex % 3));
                    $end = (clone $start)->addHours(2);

                    $vehicle = $companyVehicles->count() ? $companyVehicles[($dIndex + $i) % $companyVehicles->count()] : $vehicles->random();

                    Trip::create([
                        'company_id' => $company->id,
                        'driver_id' => $driver->id,
                        'vehicle_id' => $vehicle->id,
                        'start_time' => $start->toDateTimeString(),
                        'end_time' => $end->toDateTimeString(),
                        'status' => 'active',
                    ]);
                }
            }
        }

        // --- Additional targeted trips for dashboard demo ---
        // Create a few trips that are active right now (start <= now <= end)
        $now = Carbon::now();
        $activeNowCount = 5;

        for ($i = 0; $i < $activeNowCount; $i++) {
            // window: started 30 minutes ago, ends in 30 minutes
            $s = $now->copy()->subMinutes(30 + $i * 10);
            $e = $now->copy()->addMinutes(30 + $i * 10);

            $busyDrivers = Trip::where('start_time', '<=', $e)
                ->where('end_time', '>=', $s)
                ->pluck('driver_id')
                ->filter()
                ->unique();

            $busyVehicles = Trip::where('start_time', '<=', $e)
                ->where('end_time', '>=', $s)
                ->pluck('vehicle_id')
                ->filter()
                ->unique();

            $availableDriver = Driver::whereNotIn('id', $busyDrivers)->inRandomOrder()->first();
            $availableVehicle = Vehicle::whereNotIn('id', $busyVehicles)->inRandomOrder()->first();

            if ($availableDriver && $availableVehicle) {
                Trip::create([
                    'company_id' => $availableDriver->company_id ?? $companies->first()->id,
                    'driver_id' => $availableDriver->id,
                    'vehicle_id' => $availableVehicle->id,
                    'start_time' => $s->toDateTimeString(),
                    'end_time' => $e->toDateTimeString(),
                    'status' => 'active',
                ]);
            }
        }

        // Create a few completed trips within the current month
        $completedCount = 6;

        for ($i = 0; $i < $completedCount; $i++) {
            // choose an end time within this month but in the past
            $end = Carbon::now()->subDays(2 + $i);
            $start = (clone $end)->subHours(2 + ($i % 3));

            $busyDrivers = Trip::where('start_time', '<=', $end)
                ->where('end_time', '>=', $start)
                ->pluck('driver_id')
                ->filter()
                ->unique();

            $busyVehicles = Trip::where('start_time', '<=', $end)
                ->where('end_time', '>=', $start)
                ->pluck('vehicle_id')
                ->filter()
                ->unique();

            $availableDriver = Driver::whereNotIn('id', $busyDrivers)->inRandomOrder()->first();
            $availableVehicle = Vehicle::whereNotIn('id', $busyVehicles)->inRandomOrder()->first();

            if ($availableDriver && $availableVehicle) {
                Trip::create([
                    'company_id' => $availableDriver->company_id ?? $companies->first()->id,
                    'driver_id' => $availableDriver->id,
                    'vehicle_id' => $availableVehicle->id,
                    'start_time' => $start->toDateTimeString(),
                    'end_time' => $end->toDateTimeString(),
                    'status' => 'completed',
                ]);
            }
        }
    }
}
