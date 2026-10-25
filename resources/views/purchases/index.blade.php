<x-layouts.pixel>
<style>
    :root { --btn-dark: #21262F; }
    [data-theme="dark"] { --btn-dark: #3A4050; }
    [data-theme="dark"] select.field { color-scheme: dark; }
</style>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-6xl mx-auto px-6 py-10">

        <div class="flex justify-between items-end mb-6">
            <div>
                <h1 class="ledger-font text-3xl" style="color:var(--ink);">Purchases</h1>
                <p class="text-sm mt-1" style="color:var(--ink-soft);">Everything on order, everything that's landed.</p>
            </div>
            <a href="{{ route('purchases.create') }}"
                class="text-sm font-semibold px-4 py-2.5 rounded-md"
                style="background:var(--accent); color:#FFF;">
                Purchase
            </a>
        </div>

        @if (session('error'))
            <div class="rounded-lg p-3 mb-6 text-sm" style="background:var(--danger-soft); color:var(--danger);">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg p-3 mb-6 text-sm" style="background:var(--danger-soft); color:var(--danger);">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="flex flex-wrap items-center gap-3 mb-6">

            {{-- Status tabs (instant, no page reload) --}}
            <div class="flex gap-2" data-live-tabs>
                <a data-live-link href="{{ route('purchases.index') }}" class="text-sm px-3 py-1.5 rounded-md"
                    style="{{ request('status') ? 'color:var(--ink-soft); border:1px solid var(--line);' : 'background:var(--btn-dark); color:#FFF;' }}">All</a>
                <a data-live-link href="{{ route('purchases.index', ['status' => 'in_transit']) }}" class="text-sm px-3 py-1.5 rounded-md"
                    style="{{ request('status') === 'in_transit' ? 'background:var(--btn-dark); color:#FFF;' : 'color:var(--ink-soft); border:1px solid var(--line);' }}">In Transit</a>
                <a data-live-link href="{{ route('purchases.index', ['status' => 'arrived']) }}" class="text-sm px-3 py-1.5 rounded-md"
                    style="{{ request('status') === 'arrived' ? 'background:var(--btn-dark); color:#FFF;' : 'color:var(--ink-soft); border:1px solid var(--line);' }}">Arrived</a>
                <a data-live-link href="{{ route('purchases.index', ['status' => 'cancelled']) }}" class="text-sm px-3 py-1.5 rounded-md"
                    style="{{ request('status') === 'cancelled' ? 'background:var(--btn-dark); color:#FFF;' : 'color:var(--ink-soft); border:1px solid var(--line);' }}">Cancelled</a>
            </div>

            {{-- Live search: same line as the tabs --}}
            <form method="GET" data-live-search class="flex flex-1 items-center gap-3" style="min-width:280px;">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <input name="search" value="{{ request('search') }}" placeholder="Search by item, supplier or ID..." class="field flex-1">
                <select name="type" class="field" style="width:9rem;">
                    <option value="">All types</option>
                    <option value="filament" @selected(request('type') === 'filament')>Filament</option>
                    <option value="supply" @selected(request('type') === 'supply')>Supply</option>
                </select>
            </form>
        </div>

        <div data-live-results>
        <div class="rounded-lg overflow-hidden" style="background:var(--surface); border:1px solid var(--line);">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left" style="background:var(--paper); border-bottom:1px solid var(--line);">
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">ID</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Item</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Type</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Quantity</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Price (Purchase Currency)</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Price ({{ $baseCurrency }})</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Supplier</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Order Date</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Status</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($purchases as $purchase)
                        <tr style="border-bottom:1px solid var(--line);">
                            <td class="px-4 py-3 num" style="color:var(--ink-soft);">#{{ $purchase->id }}</td>
                            <td class="px-4 py-3 font-medium">{{ $purchase->displayName() }}</td>
                            <td class="px-4 py-3" style="color:var(--ink-soft);">{{ $purchase->item_type === 'filament' ? 'Filament' : 'Supply' }}</td>
                            <td class="px-4 py-3 text-right num">
                                {{ number_format($purchase->quantity, 2) }}{{ $purchase->item_type === 'filament' ? 'g' : '' }}
                            </td>
                            <td class="px-4 py-3 text-right num">
                                {{ $purchase->currency }} {{ number_format($purchase->total_price, 2) }}
                                @if ($purchase->currency !== $baseCurrency)
                                    <div class="text-xs" style="color:var(--ink-soft);">rate {{ number_format($purchase->exchange_rate, 4) }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right num">
                                {{ $baseCurrency }} {{ number_format($purchase->total_price * $purchase->exchange_rate, 2) }}
                            </td>
                            <td class="px-4 py-3" style="color:var(--ink-soft);">{{ $purchase->supplier->name ?? '—' }}</td>
                            <td class="px-4 py-3 num" style="color:var(--ink-soft);">{{ $purchase->order_date?->format('Y-m-d') ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if ($purchase->isArrived())
                                    <span class="badge" style="background:var(--success-soft); color:var(--success);">
                                        Arrived @ {{ $purchase->arrived_location }}
                                    </span>
                                @elseif ($purchase->isCancelled())
                                    <span class="badge" style="background:var(--danger-soft); color:var(--danger);">
                                        Cancelled
                                    </span>
                                @else
                                    <span class="badge" style="background:var(--warn-soft); color:var(--warn);">In Transit</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end items-center gap-1.5">
                                    <a href="{{ route('purchases.show', $purchase) }}" title="View details"
                                        class="p-2 rounded-md inline-flex items-center justify-center"
                                        style="color:var(--ink-soft); border:1px solid var(--line);">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                    </a>

                                    @unless ($purchase->isArrived() || $purchase->isCancelled())
                                        <button type="button" title="Mark Arrived"
                                            class="p-2 rounded-md inline-flex items-center justify-center"
                                            style="background:var(--accent); color:#FFF;"
                                            onclick="document.getElementById('arrive-{{ $purchase->id }}').classList.toggle('hidden')">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M20 8h-3V4H4a2 2 0 0 0-2 2v10h2" />
                                                <path d="M14 17H9" />
                                                <path d="M20 17h2v-3.34a4 4 0 0 0-1.17-2.83L19 9h-5v8h1" />
                                                <circle cx="7" cy="17" r="2" />
                                                <circle cx="17" cy="17" r="2" />
                                            </svg>
                                        </button>
                                        <a href="{{ route('purchases.edit', $purchase) }}" title="Edit"
                                            class="p-2 rounded-md inline-flex items-center justify-center"
                                            style="color:var(--ink-soft); border:1px solid var(--line);">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                                <path d="m15 5 4 4" />
                                            </svg>
                                        </a>
                                    @endunless

                                    {{-- Delete: opens the confirm popup. Hidden when the controller would refuse. --}}
                                    @unless ($purchase->stock_applied)
                                        <button type="button" title="Delete purchase"
                                            class="p-2 rounded-md inline-flex items-center justify-center"
                                            style="color:var(--danger); border:1px solid var(--danger-line, var(--line));"
                                            data-delete-open
                                            data-delete-url="{{ route('purchases.destroy', $purchase) }}"
                                            data-delete-label="#{{ $purchase->id }} {{ $purchase->displayName() }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 6h18" />
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                                <line x1="10" y1="11" x2="10" y2="17" />
                                                <line x1="14" y1="11" x2="14" y2="17" />
                                            </svg>
                                        </button>
                                    @endunless
                                </div>
                            </td>
                        </tr>

                        @unless ($purchase->isArrived() || $purchase->isCancelled())
                            <tr id="arrive-{{ $purchase->id }}" class="hidden" style="border-bottom:1px solid var(--line); background:var(--paper);">
                                <td colspan="10" class="px-4 py-4">
                                    <form method="POST" action="{{ route('purchases.arrive', $purchase) }}" class="flex flex-wrap items-end gap-3">
                                        @csrf
                                        <div>
                                            <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Arrived at</label>
                                            <select name="arrived_location" required class="field">
                                                @foreach ($locations as $location)
                                                    <option value="{{ $location }}">{{ $location }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button class="text-sm font-semibold px-4 py-2.5 rounded-md" style="background:var(--btn-dark); color:#FFF;">
                                            Confirm Arrival
                                        </button>
                                        <span class="text-xs" style="color:var(--ink-soft);">This will update stock and cost on the item's page.</span>
                                    </form>
                                </td>
                            </tr>
                        @endunless

                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-10 text-center text-sm" style="color:var(--ink-soft);">
                                No purchases logged yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $purchases->links() }}</div>
        </div>
    </div>
</div>
{{-- Delete purchase popup: one shared dialog, the trash buttons fill in which purchase it targets. --}}
<style>
    #delete-dialog {
        margin: auto;
        padding: 0;
        border: 1px solid var(--line);
        border-radius: 0.75rem;
        background: var(--surface);
        color: var(--ink);
        width: min(26rem, calc(100vw - 2rem));
    }
    #delete-dialog::backdrop { background: rgba(0, 0, 0, 0.55); }
</style>

<dialog id="delete-dialog">
    <form method="POST" id="delete-form" class="p-6">
        @csrf
        @method('DELETE')

        <h2 class="ledger-font text-xl" style="color:var(--ink);">Delete purchase?</h2>
        <p id="delete-label" class="text-sm mt-1 font-medium" style="color:var(--ink);"></p>
        <p class="text-sm mt-3" style="color:var(--ink-soft);">
            This permanently removes the purchase record. Any payment made on it is returned to the account. This can't be undone.
        </p>

        <div class="flex justify-end gap-2 mt-6">
            <button type="button" data-delete-close class="text-sm px-4 py-2.5 rounded-md"
                style="color:var(--ink-soft); border:1px solid var(--line);">
                Keep it
            </button>
            <button type="submit" class="text-sm font-semibold px-4 py-2.5 rounded-md"
                style="background:var(--danger); color:#FFF;">
                Delete purchase
            </button>
        </div>
    </form>
</dialog>

<script>
(function () {
    var dialog = document.getElementById('delete-dialog');
    var form   = document.getElementById('delete-form');
    var label  = document.getElementById('delete-label');
    if (!dialog) return;

    // Delegated, so it keeps working after the live search swaps the table.
    document.addEventListener('click', function (e) {
        var opener = e.target.closest('[data-delete-open]');
        if (opener) {
            form.action = opener.dataset.deleteUrl;
            label.textContent = opener.dataset.deleteLabel;
            dialog.showModal();
            return;
        }

        // "Keep it" button, or a click on the dimmed backdrop.
        if (e.target.closest('[data-delete-close]') || e.target === dialog) {
            dialog.close();
        }
    });
})();
</script>
</x-layouts.pixel>