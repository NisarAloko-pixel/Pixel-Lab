<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
       public function up(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        $resources = [
            'sales', 'products', 'customers', 'suppliers', 'expenses', 'filament',
            'supplies', 'purchases', 'balance', 'transaction', 'advance', 'employees',
            'users', 'settings', 'printers', 'orders', 'queue', 'reports', 'audit',
        ];

        foreach ($resources as $resource) {
            foreach (['view', 'create', 'edit', 'delete'] as $action) {
                Permission::findOrCreate("{$resource}.{$action}", 'web');
            }
        }

        Permission::findOrCreate('roles.manage', 'web');

        try {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        } catch (\Throwable $e) {
            // cache table not ready yet: nothing cached to clear
        }
    }
};