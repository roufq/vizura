<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockTransfer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockTransferItem>
 */
class StockTransferItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stock_transfer_id' => StockTransfer::factory(),
            'product_id' => Product::factory(),
            'quantity' => $this->faker->randomFloat(2, 1, 10),
        ];
    }
}
