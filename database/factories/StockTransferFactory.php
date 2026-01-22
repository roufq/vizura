<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockTransfer>
 */
class StockTransferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference_no' => 'TRF-'.$this->faker->unique()->numerify('#####'),
            'source_location_id' => Location::factory(),
            'destination_location_id' => Location::factory(),
            'status' => 'draft',
            'requested_by' => null,
            'sent_by' => null,
            'received_by' => null,
            'sent_at' => null,
            'received_at' => null,
        ];
    }
}
