<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseCategoryFactory extends Factory
{
    protected $model = \App\Models\ExpenseCategory::class;

    public function definition(): array
    {
        return ["name" => $this->faker->unique()->word(), "is_active" => true];
    }
}
