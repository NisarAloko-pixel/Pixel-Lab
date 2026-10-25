<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            "Keychains", "Decorations", "Lamps", "Phone Cases", "Logos",
            "Wall Clocks", "Trophies", "Car Parts", "Machine Parts",
            "Robotic Parts", "RC Parts", "Drone Parts", "Custom Products", "Other",
        ] as $name) {
            Category::firstOrCreate(["name" => $name], ["type" => "product"]);
        }
    }
}
