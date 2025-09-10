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
        $start = Carbon::now()->addDays($this->faker->numberBetween(-30, 30));
        $end = (clone $start)->addHours($this->faker->numberBetween(1, 72));

        return [
            'company_id' => Company::factory(),
            'driver_id' => Driver::factory(),
            'vehicle_id' => Vehicle::factory(),
            'start_time' => $start->toDateString(),
            'end_time' => $end->toDateString(),
            'status' => $this->faker->randomElement(['active','completed','canceled']),
        ];
    }
}
