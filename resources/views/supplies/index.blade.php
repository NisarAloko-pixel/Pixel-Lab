
<x-layouts.pixel>
<style>
    :root { --btn-dark: #21262F; }
    [data-theme="dark"] { --btn-dark: #3A4050; }
</style>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-6xl mx-auto px-6 py-10" x-data="{
        deleteModalOpen: false,
        deleteForm: null,
        deleteReason: '',
        deleteItemLabel: '',
        openDeleteModal(form, label) {
            this.deleteForm = form;
            this.deleteItemLabel = label;
            this.deleteReason = '';
            this.deleteModalOpen = true;
            $nextTick(() => $refs.deleteReasonInput?.focus());
        },
        confirmDelete() {
            if (!this.deleteReason.trim()) return;
            this.deleteForm.querySelector('input[name=cancellation_reason]').value = this.deleteReason.trim();
            this.deleteForm.submit();
        },

        // Delete-supply popup
        supplyModalOpen: false,
        supplyForm: null,
        supplyLabel: '',
        openSupplyDelete(form, label) {
            this.supplyForm = form;
            this.supplyLabel = label;
            this.supplyModalOpen = true;
        },
        confirmSupplyDelete() {
            this.supplyForm.submit();
        }
    }">

        <div class="flex justify-between items-end mb-6">
            <div>
                <h1 class="ledger-font text-3xl" style="color:var(--ink);">Supplies</h1>
                <p class="text-sm mt-1" style="color:var(--ink-soft);">Wire, glue, bulbs, keyrings — everything else the lab runs on.</p>
            </div>
            
        </div>

        @if ($pendingPurchases->isNotEmpty())
            <div class="rounded-lg p-5 mb-8" style="background:var(--warn-soft); border:1px solid var(--warn-line, var(--warn));">
                <h2 class="text-sm font-semibold mb-3" style="color:var(--warn);">
                    Arrived — not yet added ({{ $pendingPurchases->count() }})
                </h2>
                <p class="text-xs mb-4" style="color:var(--ink-soft);">
                    These purchases physically showed up but aren't in your Supplies list yet. Review and add them.
                </p>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
    @foreach ($pendingPurchases as $purchase)
        <div class="rounded-md p-3 flex flex-col gap-2" style="background:var(--surface); border:1px solid var(--line);">
            <div class="text-sm font-medium truncate">
                {{ $purchase->supply->name ?? $purchase->name ?: 'Unnamed item' }}
            </div>
            <div class="text-xs" style="color:var(--ink-soft);">
                {{ number_format($purchase->quantity, 2) }} {{ $purchase->unit ?? $purchase->supply->unit ?? '' }}<br>
                {{ $purchase->currency }} {{ number_format($purchase->total_price, 2) }}
                @if ($purchase->supplier)
                    <br>{{ $purchase->supplier->name }}
                @endif
                <br>arrived {{ $purchase->arrived_at?->format('Y-m-d') }}
            </div>

            <div class="mt-auto flex items-center gap-2">
               @if ($purchase->supply_id)
    <form method="POST" action="{{ route('supplies.applyPurchase', $purchase) }}" class="flex-1">
        @csrf
        <button type="submit" class="w-full text-xs font-semibold px-2 py-1.5 rounded-md"
            style="background:var(--accent); color:#FFF;">
            Add to Stock
        </button>
    </form>
@else
    <a href="{{ route('supplies.create', ['purchase_id' => $purchase->id]) }}"
        class="flex-1 text-center text-xs font-semibold px-2 py-1.5 rounded-md"
        style="background:var(--accent); color:#FFF;">
        Create Supply
    </a>
@endif

                <form method="POST" action="{{ route('purchases.destroy', $purchase) }}"
                    x-ref="deleteForm{{ $purchase->id }}"
                    class="{{ $purchase->supply_id ? '' : 'flex-1' }}">
                    @csrf @method('DELETE')
                    <input type="hidden" name="cancellation_reason">
                    <button type="button"
                        @click="openDeleteModal($refs.deleteForm{{ $purchase->id }}, @js(trim(($purchase->supply->name ?? $purchase->name) ?: 'this purchase')))"
                        class="{{ $purchase->supply_id ? 'shrink-0 w-9 h-9 flex items-center justify-center' : 'w-full text-xs font-semibold px-2 py-1.5' }} rounded-md"
                        style="border:1px solid var(--line); color:var(--danger);"
                        title="Cancel purchase">
                        @if ($purchase->supply_id)
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 6L6 18"/>
                                <path d="M6 6l12 12"/>
                            </svg>
                        @else
                            Cancel
                        @endif
                    </button>
                </form>
            </div>
        </div>
    @endforeach
</div>
            </div>
        @endif

        <form method="GET" data-live-search class="rounded-lg p-5 mb-8 grid grid-cols-1 md:grid-cols-4 gap-4 items-end"
            style="background:var(--surface); border:1px solid var(--line);">
            <div class="md:col-span-2">
                <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Search</label>
                <input name="search" value="{{ request('search') }}" placeholder="Search by name..." class="field w-full">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Category</label>
                <select name="category" class="field w-full">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 text-sm" style="color:var(--ink-soft);">
                    <input type="checkbox" name="low_stock" value="1" @checked(request('low_stock'))>
                    Low stock only
                </label>
                <button class="text-sm font-semibold px-4 py-2.5 rounded-md" style="background:var(--btn-dark); color:#FFF;">
                    Filter
                </button>
            </div>
        </form>


        <div data-live-results>
<div class="rounded-lg overflow-hidden" style="background:var(--surface); border:1px solid var(--line);">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left" style="background:var(--paper); border-bottom:1px solid var(--line);">
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Name</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Category</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Brand</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Remaining/Purchased</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Damaged</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Cost/unit</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Supplier</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Purchased</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($supplies as $supply)
                        <tr style="border-bottom:1px solid var(--line); {{ $supply->isLow() ? 'background:var(--warn-soft);' : '' }}">
                            <td class="px-4 py-3 font-medium">{{ $supply->name }}</td>
                            <td class="px-4 py-3" style="color:var(--ink-soft);">{{ $supply->category ?: '—' }}</td>
                            <td class="px-4 py-3" style="color:var(--ink-soft);">{{ $supply->brand ?: '—' }}</td>
                            <td class="px-4 py-3 text-right num">
    {{ number_format($supply->quantity_remaining, 2) }} / {{ number_format($supply->quantity_purchased, 2) }} {{ $supply->unit }}
    @if ($supply->quantity_remaining <= 0)
        <span class="badge ml-1" style="background:var(--danger-soft); color:var(--danger);">Out</span>
    @elseif ($supply->quantity_remaining < 5)
        <span class="badge ml-1" style="background:var(--warn-soft); color:var(--warn);">Low</span>
    @endif
</td>
                            <td class="px-4 py-3 text-right num">
    @if ($supply->hasDamagedStock())
        <span class="badge" style="background:var(--warn-soft); color:var(--warn);">
            {{ number_format($supply->damaged_stock, 2) }} {{ $supply->unit }}
        </span>
    @else
        <span style="color:var(--ink-soft);">—</span>
    @endif
</td>
                            <td class="px-4 py-3 text-right num font-medium">
                                {{ number_format($supply->cost_per_unit, $supply->cost_per_unit < 0.01 ? 4 : 2) }}
                            </td>
                            <td class="px-4 py-3" style="color:var(--ink-soft);">{{ $supply->supplier->name ?? '—' }}</td>
                            <td class="px-4 py-3 num" style="color:var(--ink-soft);">{{ $supply->purchase_date?->format('Y-m-d') ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end items-center gap-1">
                                    <a href="{{ route('supplies.show', $supply) }}" class="icon-btn" style="color:var(--ink-soft);" title="View details">
                                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>
                                    
                                    <a href="{{ route('supplies.edit', $supply) }}" class="icon-btn" style="color:var(--accent-ink);" title="Edit">
                                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('supplies.destroy', $supply) }}"
                                        x-ref="supplyDeleteForm{{ $supply->id }}">
                                        @csrf @method('DELETE')
                                        <button type="button"
                                            @click="openSupplyDelete($refs.supplyDeleteForm{{ $supply->id }}, @js($supply->name))"
                                            class="icon-btn" style="color:var(--danger);" title="Delete">
                                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                            <td colspan="9" class="px-4 py-10 text-center text-sm" style="color:var(--ink-soft);">
                                No supplies recorded yet — add your first one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $supplies->links() }}</div>
</div>

        {{-- Delete-purchase reason modal --}}
        <div x-show="deleteModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center px-4"
            style="background:rgba(0,0,0,0.4);"
            @keydown.escape.window="deleteModalOpen = false">
            <div class="rounded-lg w-full max-w-sm p-6"
                style="background:var(--surface); border:1px solid var(--line);"
                @click.outside="deleteModalOpen = false">

                <h2 class="ledger-font text-lg mb-1" style="color:var(--ink);">Cancel purchase</h2>
                <p class="text-sm mb-4" style="color:var(--ink-soft);">
                    Cancelling <span style="color:var(--ink); font-weight:500;" x-text="deleteItemLabel"></span> keeps the record but marks it cancelled. Tell us why:
                </p>

                <textarea x-model="deleteReason" x-ref="deleteReasonInput" rows="3" required
                    placeholder="e.g. duplicate entry, ordered by mistake, wrong item logged..."
                    class="field w-full mb-1"></textarea>
                <p class="text-xs mb-5" style="color:var(--ink-soft);">A reason is required.</p>

                <div class="flex justify-end gap-2">
                    <button type="button" @click="deleteModalOpen = false"
                        class="text-sm font-semibold px-4 py-2 rounded-md"
                        style="background:var(--paper); color:var(--ink-soft); border:1px solid var(--line);">
                        Back
                    </button>
                    <button type="button" @click="confirmDelete()" :disabled="!deleteReason.trim()"
                        class="text-sm font-semibold px-4 py-2 rounded-md"
                        :style="deleteReason.trim() ? 'background:var(--danger); color:#FFF;' : 'background:var(--line); color:var(--ink-soft); cursor:not-allowed;'">
                        Cancel Purchase
                    </button>
                </div>
            </div>
        </div>

        {{-- Delete-supply confirm modal --}}
        <div x-show="supplyModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center px-4"
            style="background:rgba(0,0,0,0.4);"
            @keydown.escape.window="supplyModalOpen = false">
            <div class="rounded-lg w-full max-w-sm p-6"
                style="background:var(--surface); border:1px solid var(--line);"
                @click.outside="supplyModalOpen = false">

                <h2 class="ledger-font text-lg mb-1" style="color:var(--ink);">Delete supply</h2>
                <p class="text-sm mb-5" style="color:var(--ink-soft);">
                    Delete <span style="color:var(--ink); font-weight:500;" x-text="supplyLabel"></span>?
                    It will be removed from your supplies list.
                </p>

                <div class="flex justify-end gap-2">
                    <button type="button" @click="supplyModalOpen = false"
                        class="text-sm font-semibold px-4 py-2 rounded-md"
                        style="background:var(--paper); color:var(--ink-soft); border:1px solid var(--line);">
                        Back
                    </button>
                    <button type="button" @click="confirmSupplyDelete()"
                        class="text-sm font-semibold px-4 py-2 rounded-md"
                        style="background:var(--danger); color:#FFF;">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

</x-layouts.pixel>