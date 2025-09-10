<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Driver;
use App\Models\Company;

class DriverFactory extends Factory
{
    protected $model = Driver::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'license_number' => $this->faker->unique()->numberBetween(1000, 999999),
            'company_id' => Company::factory(),
        ];
    }
}
