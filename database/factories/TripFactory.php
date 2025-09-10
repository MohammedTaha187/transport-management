<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Trip;
use App\Models\Company;
use App\Models\Driver;
use App\Models\Vehicle;
use Carbon\Carbon;

class TripFactory extends Factory
{
    protected $model = Trip::class;

    public function definition()
    {
        $start = Carbon::now()->addDays($this->faker->numberBetween(-10, 10));
        $end = (clone $start)->addHours($this->faker->numberBetween(1, 5));

        $company = Company::create(['name' => $this->faker->company]);
        $driver = Driver::create(['name' => $this->faker->name, 'company_id' => $company->id, 'license_number' => $this->faker->unique()->numberBetween(1000, 9999)]);
        $vehicle = Vehicle::create(['model' => $this->faker->word, 'plate_number' => $this->faker->unique()->numberBetween(1000, 9999), 'company_id' => $company->id]);

        return [
            'company_id' => $company->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'start_time' => $start->toDateString(),
            'end_time' => $end->toDateString(),
            'status' => $this->faker->randomElement(['active','completed','canceled']),
        ];
    }
}
