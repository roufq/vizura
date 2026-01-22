<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductPrice>
 */
class ProductPriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'location_id' => Location::factory(),
            'product_id' => Product::factory(),
            'sale_price' => $this->faker->randomFloat(2, 1000, 50000),
            'cost_price' => $this->faker->randomFloat(2, 500, 30000),
        ];
    }
}
