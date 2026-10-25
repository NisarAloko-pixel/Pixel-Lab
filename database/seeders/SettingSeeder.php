<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            "business_name" => "Pixel Studio",
            "currency" => "AFN",
            "invoice_prefix" => "PS-",
            "low_stock_threshold" => "5",
            "default_electricity_cost" => "0.00",
            "default_labor_cost" => "0.00",
            "tax_enabled" => "false",
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(["key" => $key], ["value" => $value]);
        }
    }
}
