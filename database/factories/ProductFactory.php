<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => strtoupper($this->faker->unique()->bothify('SKU-#####')),
            'barcode' => $this->faker->unique()->ean13(),
            'name' => ucfirst($this->faker->words(2, true)),
            'category_id' => Category::factory(),
            'unit_id' => Unit::factory(),
            'sale_price' => $this->faker->randomFloat(2, 1000, 50000),
            'cost_price' => $this->faker->randomFloat(2, 500, 30000),
            'is_taxable' => $this->faker->boolean(20),
            'is_active' => true,
            'block_when_out_of_stock' => $this->faker->boolean(10),
            'batch_code' => null,
            'expires_at' => null,
        ];
    }
}
