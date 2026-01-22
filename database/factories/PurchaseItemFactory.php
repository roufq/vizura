<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseItem>
 */
class PurchaseItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->randomFloat(2, 1, 10);
        $unitCost = $this->faker->randomFloat(2, 1000, 5000);

        return [
            'purchase_id' => Purchase::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'line_total' => $quantity * $unitCost,
        ];
    }
}
