<?php

namespace App\Policies;

use App\Models\User;

/**
 * Registered in AuthServiceProvider as Gate::define("manageFinancials", ...).
 * Administrators and Managers can touch expenses, suppliers, purchases and reports;
 * Sales users are limited to customers, sales and invoices per the spec (section 18).
 */
class FinancialPolicy
{
    public function manageFinancials(User $user): bool
    {
        return $user->canManageFinancials();
    }
}
