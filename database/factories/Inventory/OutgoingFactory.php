<?php

namespace Database\Factories\Inventory;

use App\Models\Inventory\Actor;
use App\Models\Inventory\Product;
use App\Models\Inventory\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventory\Outgoing>
 */
class OutgoingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'transaction_id' => Transaction::factory(),
            'actor_id' => Actor::factory(),
            'products' => [
                [
                    'product_id' => Product::factory(),
                    'quantity' => 10,
                    'price' => 50,
                ],
                [
                    'product_id' => Product::factory(),
                    'quantity' => 10,
                    'price' => 60,
                ],
            ],
            'subtotal' => 1100,
            'taxes' => 132,
            'total' => 1232
        ];
    }
}
