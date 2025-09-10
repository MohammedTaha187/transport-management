<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Vehicle;
use App\Models\Company;

class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition()
    {
        return [
            'model' => $this->faker->companySuffix() . ' ' . $this->faker->word(),
            'plate_number' => $this->faker->unique()->numberBetween(1000, 999999),
            'company_id' => Company::factory(),
        ];
    }
}
