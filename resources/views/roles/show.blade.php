<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-3xl mx-auto px-6 py-10">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="ledger-font text-3xl">{{ $role->name }}</h1>
                <p class="text-sm mt-1" style="color:var(--ink-soft);">
                    {{ $users->count() }} user{{ $users->count() === 1 ? '' : 's' }} with this role
                </p>
            </div>
            <a href="{{ route('roles.index') }}" class="text-sm font-medium px-4 py-2.5" style="color:var(--accent-ink);">&larr; Back to roles</a>
        </div>

        <div class="rounded-lg overflow-hidden card-3d mb-6">
            <div class="px-5 py-3" style="border-bottom:1px solid var(--line);">
                <h2 class="font-semibold text-sm">Permissions</h2>
            </div>
            <div class="px-5 py-4 space-y-3">
                @forelse ($grouped as $resource => $actions)
                    <div class="flex items-start gap-4">
                        <div class="w-28 shrink-0 font-medium text-sm capitalize">{{ $resource }}</div>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($actions as $action)
                                <span class="badge" style="background:var(--accent-soft); color:var(--accent-ink);">
                                    {{ $action ? ucfirst($action) : 'Enabled' }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-sm" style="color:var(--ink-soft);">No permissions assigned.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-lg overflow-hidden card-3d">
            <div class="px-5 py-3" style="border-bottom:1px solid var(--line);">
                <h2 class="font-semibold text-sm">Users</h2>
            </div>
            @forelse ($users as $user)
                <div class="flex items-center gap-3 px-5 py-4" style="border-bottom:1px solid var(--line);">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-9 h-9 rounded-full object-cover" style="border:1px solid var(--line); background:var(--accent-soft);">
                    <div class="flex-1">
                        <div class="font-medium">{{ $user->name }}</div>
                        <div class="text-xs" style="color:var(--ink-soft);">{{ $user->email }}</div>
                    </div>
                    <span class="badge" style="background:{{ $user->is_active ? 'var(--success-soft)' : 'var(--danger-soft)' }}; color:{{ $user->is_active ? 'var(--success)' : 'var(--danger)' }};">
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    <a href="{{ route('users.edit', $user) }}" class="icon-btn" style="background:var(--accent-soft); color:var(--accent-ink);" title="Edit user">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                    </a>
                </div>
            @empty
                <div class="px-5 py-8 text-center text-sm" style="color:var(--ink-soft);">No users have this role yet.</div>
            @endforelse
        </div>
    </div>
</div>
</x-layouts.pixel>
