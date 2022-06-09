<?php

namespace Database\Factories\Inventory;

use App\Models\Inventory\CategoryProduct;
use App\Models\Inventory\Tax;
use App\Models\Role_User\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventory\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'category_product_id' => CategoryProduct::factory(),
            'tax_id' => Tax::factory(),
            'code' => $this->faker->unique()->name,
            'name' => $this->faker->unique()->name,
            'image' => null,
            'price' => $this->faker->numberBetween(1, 568),
            'stock' => $this->faker->numberBetween(0, 10000),
            'sales' => $this->faker->numberBetween(0, 100000)
        ];
    }
}
