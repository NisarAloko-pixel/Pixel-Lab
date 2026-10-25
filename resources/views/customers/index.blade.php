<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-6xl mx-auto px-6 py-10">

        <div class="flex justify-between items-end mb-6">
            <div>
                <h1 class="ledger-font text-3xl" style="color:var(--ink);">Customers</h1>
                <p class="text-sm mt-1" style="color:var(--ink-soft);">Everyone who's bought from you, and what they still owe.</p>
            </div>
            <a href="{{ route('customers.create') }}"
                class="text-sm font-semibold px-4 py-2.5 rounded-md"
                style="background:var(--accent); color:#FFF;">
                + New Customer
            </a>
        </div>

        {{-- Search bar --}}
        <form method="GET" data-live-search class="rounded-lg p-4 mb-6 flex flex-wrap items-end gap-3" style="background:var(--surface); border:1px solid var(--line);">
            <div class="flex-1 min-w-[220px]">
                <label class="block text-[11px] font-medium mb-1" style="color:var(--ink-soft);">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name..." class="field w-full">
            </div>
            <button class="text-sm font-medium px-4 py-2 rounded-md" style="background:var(--ink); color:var(--paper);">
                Search
            </button>
            @if (request('search'))
                <a href="{{ route('customers.index') }}" class="text-sm font-medium px-2 py-2" style="color:var(--ink-soft);">
                    Clear
                </a>
            @endif
        </form>

        {{-- Table --}}
        <div data-live-results>
<div class="rounded-lg overflow-hidden" style="background:var(--surface); border:1px solid var(--line);">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left" style="background:var(--paper); border-bottom:1px solid var(--line);">
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Name</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Phone</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Email</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Outstanding Balance</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        @php
                            $owing = (float) $customer->outstanding_balance > 0;
                            $badgeStyle = $owing ? 'background:var(--warn-soft); color:var(--warn);' : 'background:var(--success-soft); color:var(--success);';
                        @endphp
                        <tr style="border-bottom:1px solid var(--line);">
                            <td class="px-4 py-3 font-medium">{{ $customer->name }}</td>
                            <td class="px-4 py-3" style="color:var(--ink-soft);">{{ $customer->phone ?: '—' }}</td>
                            <td class="px-4 py-3" style="color:var(--ink-soft);">{{ $customer->email ?: '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <span class="num font-medium" style="color:{{ $owing ? 'var(--danger)' : 'var(--ink)' }};">
                                    {{ number_format($customer->outstanding_balance, 2) }}
                                </span>
                                <span class="badge ml-2" style="{{ $badgeStyle }}">{{ $owing ? 'Owing' : 'Clear' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end items-center gap-1">
                                    <a href="{{ route('customers.show', $customer) }}" class="icon-btn" style="color:var(--ink-soft);" title="View">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('customers.edit', $customer) }}" class="icon-btn" style="color:var(--accent-ink);" title="Edit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('customers.destroy', $customer) }}"
                                        data-confirm="Delete {{ $customer->name }}?"
data-confirm-text="This cannot be undone."
data-confirm-button="Delete">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="icon-btn" style="color:var(--danger);" title="Delete">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 6h18"/>
                                                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                                                <line x1="10" y1="11" x2="10" y2="17"/>
                                                <line x1="14" y1="11" x2="14" y2="17"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm" style="color:var(--ink-soft);">
                                No customers yet — add your first one above.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $customers->links() }}</div>
</div>
    </div>
</div>
</x-layouts.pixel>
