<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-4xl mx-auto px-6 py-10">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="ledger-font text-3xl">Roles &amp; Permissions</h1>
                <p class="text-sm mt-1" style="color:var(--ink-soft);">Manage staff roles and what each can access.</p>
            </div>
            <a href="{{ route('roles.create') }}" class="text-sm font-semibold px-4 py-2.5 rounded-md" style="background:var(--accent); color:#FFF;"> New role</a>
        </div>

        @if (session('error'))
            <div class="rounded-md p-3 mb-4 text-sm" style="background:var(--danger-soft); color:var(--danger);">{{ session('error') }}</div>
        @endif

        <div class="rounded-lg overflow-hidden card-3d">
            <table class="w-full text-sm">
                <thead>
                    <tr style="background:var(--paper); border-bottom:1px solid var(--line);">
                        <th class="text-left px-5 py-3 font-medium" style="color:var(--ink-soft);">Role</th>
                        <th class="text-left px-5 py-3 font-medium" style="color:var(--ink-soft);">Users</th>
                        <th class="text-left px-5 py-3 font-medium" style="color:var(--ink-soft);">Permissions</th>
                        <th class="text-right px-5 py-3 font-medium" style="color:var(--ink-soft);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr style="border-bottom:1px solid var(--line);">
                            <td class="px-5 py-4 font-semibold">{{ $role->name }}</td>
                            <td class="px-5 py-4" style="color:var(--ink-soft);">
                                {{ $role->users_count }} user{{ $role->users_count === 1 ? '' : 's' }}
                            </td>
                            <td class="px-5 py-4" style="color:var(--ink-soft);">
                                {{ $role->name === 'Administrator' ? 'All' : $role->permissions_count . ' permission' . ($role->permissions_count === 1 ? '' : 's') }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('roles.show', $role) }}" class="icon-btn" style="background:var(--blue-soft); color:var(--blue);" title="View users in this role">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('roles.edit', $role) }}" class="icon-btn" style="background:var(--accent-soft); color:var(--accent-ink);" title="Edit role">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </a>
                                    @if ($role->name !== 'Administrator')
                                        <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('Delete this role?')">
                                            @csrf @method('DELETE')
                                           <button type="submit" class="icon-btn" style="background:var(--danger-soft) !important; color:var(--danger) !important; padding:0 !important; width:30px !important; height:30px !important; border-radius:8px !important;" title="Delete role">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
    </svg>
</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
</x-layouts.pixel>
