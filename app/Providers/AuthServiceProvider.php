<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define("manageFinancials", fn (User $user) => $user->canManageFinancials());

        // Administrators only — managing users and business-wide settings is a
        // narrower permission than manageFinancials (which managers also hold).
        Gate::define("administer", fn (User $user) => $user->isAdministrator());
    }
}