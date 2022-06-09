<?php

namespace Database\Factories\Inventory;

use App\Models\Inventory\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventory\Actor>
 */
class ActorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'document_id' => Document::factory(),
            'document' => $this->faker->numerify('##########'),
            'client' => $this->faker->randomElement([0, 1]),
            'provider' => $this->faker->randomElement([0, 1]),
            'name' => $this->faker->name,
            'address' => $this->faker->address(),
            'email' => $this->faker->email(),
            'cellphone' => $this->faker->phoneNumber(),
            'phone' => $this->faker->phoneNumber()
        ];
    }
}
