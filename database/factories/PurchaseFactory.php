<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Purchase>
 */
class PurchaseFactory extends Factory
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
            'supplier_id' => Supplier::factory(),
            'reference_no' => 'PO-'.$this->faker->unique()->numerify('#####'),
            'status' => 'received',
            'subtotal' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => 0,
            'received_by' => null,
            'received_at' => now(),
        ];
    }
}
