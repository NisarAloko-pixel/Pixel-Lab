<?php

namespace Database\Seeders;

use App\Http\Controllers\RoleController;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Every matrix permission (resource.action), taken from the role editor
        // so the two can never drift apart.
        foreach (RoleController::MATRIX as $resource => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$resource}.{$action}"]);
            }
        }

        // Standalone permissions with their own checkbox (settings.edit, audit.view).
        foreach (RoleController::EXTRAS as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Only ever granted to Administrator (see below) — not exposed as a checkbox
        // in the role editor, since it controls who can grant every other permission.
        Permission::firstOrCreate(['name' => 'roles.manage']);

        // One-off upgrade: carry old permissions over to the new split ones, then drop them.
        foreach (Role::with('permissions')->get() as $r) {
            $has = $r->permissions->pluck('name');
            foreach (['view', 'create', 'delete'] as $action) {
                if ($has->contains("finance.{$action}")) {
                    $r->givePermissionTo(["balance.{$action}", "transaction.{$action}", "advance.{$action}"]);
                }
            }
            if ($has->contains('settings.edit')) {
                $r->givePermissionTo('settings.view');
            }
        }
        Permission::whereIn('name', ['finance.view', 'finance.create', 'finance.delete'])->delete();

        $everything = Permission::pluck('name')->all();

        // NOTE: syncPermissions() resets these roles to the lists below every time
        // the seeder runs, so re-running it discards edits made in the role editor.
        $salesPermissions = [
            'sales.view', 'sales.create',
            'products.view', 'products.create', 'products.edit', 'products.delete',
            'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
            'filament.view', 'filament.create', 'filament.edit', 'filament.delete',
            // Pages that used to be open to everyone:
            'supplies.view', 'orders.view', 'queue.view', 'printers.view',
        ];
        Role::firstOrCreate(['name' => 'Sales'])->syncPermissions($salesPermissions);

        $managerPermissions = array_merge($salesPermissions, [
            'sales.edit', 'sales.delete',
            'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
            'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete',
            'reports.view',
            // Pages that used to be open to everyone:
            'supplies.create', 'supplies.edit', 'supplies.delete',
            'purchases.view', 'purchases.create', 'purchases.edit', 'purchases.delete',
            'orders.create', 'orders.edit', 'orders.delete',
        ]);
        Role::firstOrCreate(['name' => 'Manager'])->syncPermissions($managerPermissions);

        // Administrator only ever GAINS permissions here (never loses any), because the
        // developer may have removed some on purpose via the role editor.
        // Finance and employees are left to Administrator by default.
        Role::firstOrCreate(['name' => 'Administrator'])->givePermissionTo($everything);

        // Migrate existing users off the old `role` string column onto the new roles.
        $map = ['administrator' => 'Administrator', 'manager' => 'Manager', 'sales' => 'Sales'];
        foreach (DB::table('users')->select('id', 'role')->get() as $row) {
            if (isset($map[$row->role])) {
                User::find($row->id)?->syncRoles([$map[$row->role]]);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}