<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /** Resource => actions shown as columns in the permission matrix. */
    public const MATRIX = [
        'sales' => ['view', 'create', 'edit', 'delete'],
        'products' => ['view', 'create', 'edit', 'delete'],
        'customers' => ['view', 'create', 'edit', 'delete'],
        'suppliers' => ['view', 'create', 'edit', 'delete'],
        'expenses' => ['view', 'create', 'edit', 'delete'],
        'filament' => ['view', 'create', 'edit', 'delete'],
        'supplies' => ['view', 'create', 'edit', 'delete'],
        'purchases' => ['view', 'create', 'edit', 'delete'],
        'balance' => ['view', 'create', 'delete'],      // balance, transfers, deposits, shareholders
        'transaction' => ['view', 'create', 'delete'],
        'advance' => ['view', 'create', 'delete'],      // employee advances
        'employees' => ['view', 'create', 'delete'],
        'orders' => ['view', 'create', 'edit', 'delete'],
        'queue' => ['view'],
        'printers' => ['view'],
        'reports' => ['view'],
        'users' => ['view', 'create', 'edit'],
        'settings' => ['view', 'edit'],                 // App settings (edit also covers backup/restore)
    ];

    /** Standalone permissions rendered as single checkboxes (request field => permission). */
    public const EXTRAS = [
        'audit_view'    => 'audit.view',
    ];

    public function index()
    {
        $roles = Role::withCount('users', 'permissions')->orderBy('name')->get();

        return view('roles.index', compact('roles'));
    }

    public function show(Role $role)
    {
        $users = $role->users()->orderBy('name')->get();

        $permissionNames = $role->name === 'Administrator'
            ? Permission::pluck('name')->all()
            : $role->permissions->pluck('name')->all();

        $grouped = [];
        foreach ($permissionNames as $name) {
            [$resource, $action] = str_contains($name, '.') ? explode('.', $name, 2) : [$name, null];
            $grouped[$resource][] = $action;
        }
        ksort($grouped);

        return view('roles.show', compact('role', 'users', 'grouped'));
    }

    public function create()
    {
        return view('roles.create', ['matrix' => self::MATRIX, 'current' => []]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $role = Role::create(['name' => $data['name']]);
        $this->syncPermissions($role, $request, $data);

        return redirect()->route('roles.index')->with('success', "Role {$role->name} created.");
    }

    public function edit(Role $role)
    {
        return view('roles.edit', [
            'role' => $role,
            'matrix' => self::MATRIX,
            'current' => $role->permissions->pluck('name')->all(),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $isAdminRole = $role->name === 'Administrator';

        // Only the developer (super admin) may change the Administrator role.
        if ($isAdminRole && ! $request->user()->is_super_admin) {
            return back()->with('error', 'Only the developer (super admin) can edit the Administrator role.');
        }

        $data = $request->validate($this->rules($role));

        // The Administrator role keeps its name (code and the seeder look it up by name).
        if (! $isAdminRole) {
            $role->name = $data['name'];
            $role->save();
        }
        $this->syncPermissions($role, $request, $data);

        return redirect()->route('roles.index')->with('success', "Role {$role->name} updated.");
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'Administrator') {
            return back()->with('error', "The Administrator role can't be deleted.");
        }

        if ($role->users()->exists()) {
            return back()->with('error', "Can't delete {$role->name} — it still has users assigned.");
        }

        $role->delete();

        return back()->with('success', "Role {$role->name} deleted.");
    }

    private function rules(?Role $role = null): array
    {
        $rules = [
            // The Administrator name input is disabled, so it isn't submitted.
            'name' => ($role && $role->name === 'Administrator')
                ? 'nullable'
                : 'required|string|max:255|unique:roles,name' . ($role ? ',' . $role->id : ''),
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ];

        foreach (array_keys(self::EXTRAS) as $field) {
            $rules[$field] = 'sometimes|boolean';
        }

        return $rules;
    }

    private function syncPermissions(Role $role, Request $request, array $data): void
    {
        $permissions = $data['permissions'] ?? [];

        foreach (self::EXTRAS as $field => $permission) {
            if ($request->boolean($field)) {
                $permissions[] = $permission;
            }
        }

        $role->syncPermissions(array_values(array_unique($permissions)));
    }
}