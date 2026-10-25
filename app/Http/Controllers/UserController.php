<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
   public function index()
{
    $users = User::visible()->orderBy("name")->paginate(20);   // CHANGED: added ->visible()

    return view("users.index", compact("users"));
}

    public function create()
    {
        $roles = Role::orderBy('name')->get();

        return view("users.create", compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "name" => "required|string|max:255",
            "email" => "required|email|unique:users,email",
            "password" => "required|string|min:8|confirmed",
            "role" => ["required", Rule::exists('roles', 'name')],
            "is_active" => "sometimes|boolean",
        ]);

        $user = User::create([
            "name" => $data["name"],
            "email" => $data["email"],
            "password" => Hash::make($data["password"]),
            "is_active" => $request->boolean("is_active", true),
        ]);

        $user->assignRole($data["role"]);

        return redirect()->route("users.index")->with("success", "User {$data['name']} created.");
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        $currentRole = $user->getRoleNames()->first();

        return view("users.edit", compact('user', 'roles', 'currentRole'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            "name" => "required|string|max:255",
            "email" => ["required", "email", Rule::unique("users", "email")->ignore($user->id)],
            "password" => "nullable|string|min:8|confirmed",
            "role" => ["required", Rule::exists('roles', 'name')],
            "is_active" => "sometimes|boolean",
        ]);

        // Don't allow demoting/deactivating the last remaining administrator —
        // that would lock everyone out of user management and settings.
        if ($user->hasRole('Administrator')) {
            $remainingAdmins = User::role('Administrator')->where("id", "!=", $user->id)->where("is_active", true)->count();
            $stillAdmin = $data["role"] === 'Administrator' && $request->boolean("is_active", true);

            if ($remainingAdmins === 0 && !$stillAdmin) {
                return back()->withInput()->with("error", "At least one active administrator must remain.");
            }
        }

        $user->name = $data["name"];
        $user->email = $data["email"];
        $user->is_active = $request->boolean("is_active");

        if (!empty($data["password"])) {
            $user->password = Hash::make($data["password"]);
        }

        $user->save();
        $user->syncRoles([$data["role"]]);

        return redirect()->route("users.index")->with("success", "User {$user->name} updated.");
    }

    public function toggleActive(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with("error", "You can't deactivate your own account.");
        }

       if ($user->hasRole('Administrator') && $user->is_active) {
            $remainingAdmins = User::role('Administrator')->where("id", "!=", $user->id)->where("is_active", true)->count();
            if ($remainingAdmins === 0) {
                return back()->with("error", "At least one active administrator must remain.");
            }
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $verb = $user->is_active ? "reactivated" : "deactivated";

        return back()->with("success", "User {$user->name} {$verb}.");
    }

        public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with("error", "You can't delete your own account.");
        }

        if ($user->is_super_admin) {
            return back()->with("error", "This account can't be deleted.");
        }

        // Never remove the last active administrator
        if ($user->hasRole('Administrator') && $user->is_active) {
            $remainingAdmins = User::role('Administrator')
                ->where("id", "!=", $user->id)
                ->where("is_active", true)
                ->count();

            if ($remainingAdmins === 0) {
                return back()->with("error", "At least one active administrator must remain.");
            }
        }

        $name = $user->name;

        try {
            $user->delete();
        } catch (\Throwable $e) {
            // e.g. the user is linked to sales or other records that can't be removed
            return back()->with("error", "{$name} can't be deleted because they have related records. Deactivate the account instead.");
        }

        return back()->with("success", "User {$name} deleted.");
    }
}