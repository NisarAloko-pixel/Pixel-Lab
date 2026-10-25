<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-xl mx-auto px-6 py-10">
        <a href="{{ route('users.index') }}" class="inline-flex items-center gap-1 text-sm font-medium mb-4" style="color:var(--accent-ink);">
            &larr; Back to users
        </a>
        <h1 class="ledger-font text-3xl mb-6">Edit User</h1>

        @if ($errors->any())
            <div class="rounded-md p-4 mb-6 text-sm" style="background:var(--danger-soft); border:1px solid var(--line); color:var(--danger);">
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('users.update', $user) }}" class="rounded-lg p-6 space-y-4" style="background:var(--surface); border:1px solid var(--line);">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium mb-1" style="color:var(--ink-soft);">Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                    class="w-full rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2" style="border:1px solid var(--line); background:var(--paper); --tw-ring-color:var(--accent);">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1" style="color:var(--ink-soft);">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                    class="w-full rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2" style="border:1px solid var(--line); background:var(--paper); --tw-ring-color:var(--accent);">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1" style="color:var(--ink-soft);">New password</label>
                    <input type="password" name="password" placeholder="Leave blank to keep current"
                        class="w-full rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2" style="border:1px solid var(--line); background:var(--paper); --tw-ring-color:var(--accent);">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" style="color:var(--ink-soft);">Confirm password</label>
                    <input type="password" name="password_confirmation"
                        class="w-full rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2" style="border:1px solid var(--line); background:var(--paper); --tw-ring-color:var(--accent);">
                </div>
            </div>

            <div>
    <label class="block text-sm font-medium mb-1" style="color:var(--ink-soft);">Role</label>
    <select name="role" class="w-full rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2" style="border:1px solid var(--line); background:var(--paper); --tw-ring-color:var(--accent);">
        @foreach ($roles as $role)
            <option value="{{ $role->name }}" @selected(old('role', $currentRole) === $role->name)>{{ $role->name }}</option>
        @endforeach
    </select>
</div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active))>
                Active
            </label>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="text-sm font-semibold px-4 py-2.5 rounded-md" style="background:var(--accent); color:#FFF;">Save changes</button>
                <a href="{{ route('users.index') }}" class="text-sm font-medium px-4 py-2.5" style="color:var(--ink-soft);">Cancel</a>
            </div>
        </form>
    </div>
</div>
</x-layouts.pixel>
