<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            "Filament/material", "Electricity", "Printer maintenance", "Printer parts",
            "Tools", "Packaging", "Transportation", "Advertising", "Internet", "Rent",
            "Shop expenses", "Equipment", "Salaries", "Other",
        ] as $name) {
            ExpenseCategory::firstOrCreate(["name" => $name]);
        }
    }
}
