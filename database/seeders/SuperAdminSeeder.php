<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Creates (or updates) the one hidden super admin.
 *
 * Put these in .env, run the seeder once, then delete the password line:
 *   SUPERADMIN_EMAIL=owner@example.com
 *   SUPERADMIN_PASSWORD=a-long-random-password
 *
 *   php artisan db:seed --class=SuperAdminSeeder
 */
class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SUPERADMIN_EMAIL');
        $password = env('SUPERADMIN_PASSWORD');

        if (! $email || ! $password) {
            $this->command?->error('Set SUPERADMIN_EMAIL and SUPERADMIN_PASSWORD in .env first.');
            return;
        }

        $user = User::firstOrNew(['email' => $email]);
        $user->name = $user->name ?: 'System';
        $user->password = Hash::make($password);
        $user->email_verified_at = $user->email_verified_at ?? now();

        // Only set columns that exist in your users table.
        if (Schema::hasColumn('users', 'is_active')) {
            $user->is_active = true;
        }

        // Not mass-assignable on purpose, so it can never come from a request.
        $user->forceFill(['is_super_admin' => true])->save();

        $this->command?->info("Super admin ready: {$email}");
    }
}