<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'LOC-'.$this->faker->unique()->numerify('###'),
            'name' => $this->faker->company(),
            'address' => $this->faker->streetAddress(),
            'phone' => $this->faker->phoneNumber(),
            'is_active' => true,
            'toko_pusat' => false,
        ];
    }
}
