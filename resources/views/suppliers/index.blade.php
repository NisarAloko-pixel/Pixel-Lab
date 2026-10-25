{{-- resources/views/suppliers/index.blade.php --}}
<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-6xl mx-auto px-6 py-10">


        <div class="flex justify-between items-end mb-6">
            <div>
                <h1 class="ledger-font text-3xl" style="color:var(--ink);">Suppliers</h1>
                <p class="text-sm mt-1" style="color:var(--ink-soft);">Who you buy from, and what you still owe them.</p>
            </div>
            <a href="{{ route('suppliers.create') }}"
                class="text-sm font-semibold px-4 py-2.5 rounded-md"
                style="background:var(--accent); color:#FFF;">
                + New Supplier
            </a>
        </div>

        <div class="rounded-lg overflow-hidden" style="background:var(--surface); border:1px solid var(--line);">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left" style="background:var(--paper); border-bottom:1px solid var(--line);">
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Name</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Contact</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Phone</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Outstanding</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        @php
                            $bal = (float) $supplier->outstanding_balance;
                            if ($bal > 0) {
                                $label = 'Owing';   $badgeStyle = 'background:var(--warn-soft); color:var(--warn);';
                            } elseif ($bal < 0) {
                                $label = 'Prepaid'; $badgeStyle = 'background:var(--success-soft); color:var(--success);';
                            } else {
                                $label = 'Clear';   $badgeStyle = 'background:var(--success-soft); color:var(--success);';
                            }
                        @endphp
                        <tr style="border-bottom:1px solid var(--line);">
                            <td class="px-4 py-3 font-medium">
                                <a href="{{ route('suppliers.show', $supplier) }}" class="hover:underline">{{ $supplier->name }}</a>
                            </td>
                            <td class="px-4 py-3" style="color:var(--ink-soft);">{{ $supplier->contact_person ?: '—' }}</td>
                            <td class="px-4 py-3" style="color:var(--ink-soft);">{{ $supplier->phone ?: '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <span class="num font-medium" style="color:{{ $bal > 0 ? 'var(--danger)' : 'var(--ink)' }};">
                                    {{ number_format(abs($bal), 2) }}
                                </span>
                                <span class="badge ml-2" style="{{ $badgeStyle }}">{{ $label }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end items-center gap-1">
                                    <a href="{{ route('suppliers.show', $supplier) }}" class="icon-btn" style="color:var(--ink-soft);" title="View">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('suppliers.edit', $supplier) }}" class="icon-btn" style="color:var(--accent-ink);" title="Edit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}"
                                        onsubmit="return confirm('Delete {{ addslashes($supplier->name) }}? This cannot be undone.');">
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
                                No suppliers yet — add your first one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $suppliers->links() }}</div>
    </div>
</div>
</x-layouts.pixel>