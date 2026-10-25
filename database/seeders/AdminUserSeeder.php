<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env("ADMIN_EMAIL", "admin@pixelstudio.test");
        $password = env("ADMIN_PASSWORD");

        if (! $password) {
            $password = bin2hex(random_bytes(8));
            $this->command?->warn("ADMIN_PASSWORD not set in .env — generated one-time password: {$password}");
        }

        $user = User::updateOrCreate(
            ["email" => $email],
            [
                "name" => "Administrator",
                "password" => Hash::make($password),
                "is_active" => true,
                "email_verified_at" => now(),
            ]
        );

        Role::firstOrCreate(["name" => "Administrator", "guard_name" => "web"]);
        $user->assignRole("Administrator");
    }
}