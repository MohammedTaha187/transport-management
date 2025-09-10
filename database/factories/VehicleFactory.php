<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Vehicle;

class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition()
    {
        return [
            'model' => $this->faker->word,
            'plate_number' => $this->faker->unique()->numberBetween(1000, 9999),
        ];
    }
}
