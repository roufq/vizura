<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockItem>
 */
class StockItemFactory extends Factory
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
            'quantity_on_hand' => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}
