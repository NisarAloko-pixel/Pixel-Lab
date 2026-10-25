<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-4xl mx-auto px-6 py-10">

        <div class="flex justify-between items-end mb-6">
            <div>
                <h1 class="ledger-font text-3xl">Users</h1>
                <p class="text-sm mt-1" style="color:var(--ink-soft);">Manage staff accounts and roles.</p>
            </div>
            <a href="{{ route('users.create') }}" class="text-sm font-semibold px-4 py-2.5 rounded-md" style="background:var(--accent); color:#FFF;">
                New user
            </a>
        </div>

        <div class="rounded-lg overflow-hidden" style="background:var(--surface); border:1px solid var(--line);">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left" style="background:var(--paper); border-bottom:1px solid var(--line);">
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Name</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Email</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Role</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Status</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr style="border-bottom:1px solid var(--line);">
                            <td class="px-4 py-3 font-medium">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-8 h-8 rounded-full object-cover shrink-0" style="border:1px solid var(--line); background:var(--accent-soft);">
                                    {{ $user->name }}
                                </div>
                            </td>
                            <td class="px-4 py-3" style="color:var(--ink-soft);">{{ $user->email }}</td>
                            <td class="px-4 py-3">
                                <span class="badge" style="background:var(--accent-soft); color:var(--accent-ink);">{{ $user->getRoleNames()->first() ?? '—' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if ($user->is_active)
                                    <span class="badge" style="background:var(--success-soft); color:var(--success);">Active</span>
                                @else
                                    <span class="badge" style="background:var(--danger-soft); color:var(--danger);">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end items-center gap-2">
                                    <a href="{{ route('users.edit', $user) }}" title="Edit"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-md"
                                        style="color:var(--accent-ink); background:var(--accent-soft);">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4Z"/>
                                        </svg>
                                    </a>
                                    @unless ($user->id === auth()->id())
                                        <form method="POST" action="{{ route('users.toggle-active', $user) }}"
                                            onsubmit="return confirm('{{ $user->is_active ? 'Deactivate' : 'Reactivate' }} {{ $user->name }}?');">
                                            @csrf @method('PATCH')
                                            <button type="submit" title="{{ $user->is_active ? 'Deactivate' : 'Reactivate' }}"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-md"
                                                style="color:{{ $user->is_active ? 'var(--danger)' : 'var(--success)' }}; background:{{ $user->is_active ? 'var(--danger-soft)' : 'var(--success-soft)' }};">
                                                @if ($user->is_active)
                                                    {{-- user-x icon --}}
                                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                                        <circle cx="8.5" cy="7" r="4"/>
                                                        <line x1="18" y1="8" x2="23" y2="13"/>
                                                        <line x1="23" y1="8" x2="18" y2="13"/>
                                                    </svg>
                                                @else
                                                    {{-- user-check icon --}}
                                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                                        <circle cx="8.5" cy="7" r="4"/>
                                                        <polyline points="17 11 19 13 23 9"/>
                                                    </svg>
                                                @endif
                                            </button>
                                        </form>

                                        @can('users.delete')
                                            <form method="POST" action="{{ route('users.destroy', $user) }}"
                                                onsubmit="return confirm({{ \Illuminate\Support\Js::from('Delete ' . $user->name . '? This cannot be undone.') }});">
                                                @csrf @method('DELETE')
                                                <button type="submit" title="Delete"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-md"
                                                    style="color:var(--danger); background:var(--danger-soft);">
                                                    {{-- trash icon --}}
                                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <polyline points="3 6 5 6 21 6"/>
                                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                                        <path d="M10 11v6"/><path d="M14 11v6"/>
                                                        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endcan
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $users->links() }}</div>
    </div>
</div>
</x-layouts.pixel>