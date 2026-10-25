<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = \App\Models\Product::class;

    public function definition(): array
    {
        return [
            "sku" => strtoupper($this->faker->unique()->bothify("PRD-####")),
            "name" => $this->faker->words(2, true),
            "selling_price" => $this->faker->randomFloat(2, 5, 100),
            "cost_price" => $this->faker->randomFloat(2, 2, 40),
            "current_stock" => $this->faker->randomFloat(3, 0, 100),
            "min_stock_level" => 5,
            "unit" => "pcs",
            "is_active" => true,
        ];
    }
}
