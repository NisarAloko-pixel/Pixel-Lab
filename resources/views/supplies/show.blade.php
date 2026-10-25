@php
    // Latest arrived purchase linked to this supply (source of the arrival date)
    $arrivedPurchase = \App\Models\Purchase::where('supply_id', $supply->id)
        ->whereNotNull('arrived_at')
        ->latest('arrived_at')
        ->first();
@endphp
<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-4xl mx-auto px-6 py-10">

        <a href="{{ route('supplies.index') }}" class="text-sm" style="color:var(--accent-ink);">&larr; Back to supplies</a>

        <div class="flex justify-between items-end mt-2 mb-6">
            <div>
                <h1 class="ledger-font text-3xl" style="color:var(--ink);">{{ $supply->name }}</h1>
                <p class="text-sm mt-1" style="color:var(--ink-soft);">
                    {{ $supply->category ?: 'Uncategorized' }} @if($supply->brand) &middot; {{ $supply->brand }} @endif
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('supplies.edit', $supply) }}" class="text-sm font-semibold px-4 py-2.5 rounded-md" style="background:var(--accent); color:#FFF;">
                    Edit
                </a>
            </div>
        </div>

        @if ($supply->isLow())
            <div class="rounded-lg p-3 mb-6 text-sm" style="background:var(--warn-soft); color:var(--warn);">
                Running low â€” {{ number_format($supply->quantity_remaining, 2) }} {{ $supply->unit }} remaining.
            </div>
        @endif
<div class="rounded-lg p-6 grid grid-cols-2 sm:grid-cols-3 gap-y-6 gap-x-8" style="background:var(--surface); border:1px solid var(--line);">
    {{-- Row 1: stock --}}
    <div>
        <div class="text-xs" style="color:var(--ink-soft);">Remaining</div>
        <div class="text-lg font-semibold num">{{ number_format($supply->quantity_remaining, 2) }} {{ $supply->unit }}</div>
    </div>
    <div>
        <div class="text-xs" style="color:var(--ink-soft);">Total Purchased</div>
        <div class="text-lg font-semibold num">{{ number_format($supply->quantity_purchased, 2) }} {{ $supply->unit }}</div>
    </div>
    <div>
        <div class="text-xs" style="color:var(--ink-soft);">Reorder Level</div>
        <div class="text-lg font-semibold num">
            {{ $supply->reorder_level ? number_format($supply->reorder_level, 2).' '.$supply->unit : 'â€”' }}
        </div>
    </div>

    {{-- Row 2: cost --}}
    <div>
        <div class="text-xs" style="color:var(--ink-soft);">Price per {{ $supply->unit }}</div>
        <div class="text-lg font-semibold num">{{ number_format($supply->purchase_price, 2) }}</div>
    </div>
    <div>
        <div class="text-xs" style="color:var(--ink-soft);">Shipping (total)</div>
        <div class="text-lg font-semibold num">{{ number_format($supply->shipping_cost, 2) }}</div>
    </div>
    <div>
        <div class="text-xs" style="color:var(--ink-soft);">Landed cost / {{ $supply->unit }}</div>
        <div class="text-lg font-semibold num">{{ number_format($supply->cost_per_unit, 4) }}</div>
    </div>

    {{-- Row 3: totals & source --}}
    <div>
        <div class="text-xs" style="color:var(--ink-soft);">Total Cost Paid</div>
        <div class="text-lg font-semibold num">{{ number_format($supply->totalCost(), 2) }}</div>
    </div>
    <div>
        <div class="text-xs" style="color:var(--ink-soft);">Supplier</div>
        <div class="text-lg font-semibold">{{ $supply->supplier->name ?? 'â€”' }}</div>
    </div>
    <div>
        <div class="text-xs" style="color:var(--ink-soft);">Purchase Date</div>
        <div class="text-lg font-semibold num">{{ $supply->purchase_date?->format('Y-m-d') ?? 'â€”' }}</div>
    </div>

    {{-- Row 4: arrival --}}
    <div>
        <div class="text-xs" style="color:var(--ink-soft);">Arrival Date</div>
        <div class="text-lg font-semibold num">{{ $arrivedPurchase?->arrived_at?->format('Y-m-d') ?? 'â€”' }}</div>
        @if ($arrivedPurchase?->arrived_location)
            <div class="text-xs mt-0.5" style="color:var(--ink-soft);">Arrived at {{ $arrivedPurchase->arrived_location }}</div>
        @endif
    </div>
</div>

        {{-- Damaged stock --}}
<div class="rounded-lg p-6 mt-6" style="background:var(--warn-soft); border:1px solid var(--line);">
    <div class="flex justify-between items-start mb-4">
        <div>
            <h2 class="ledger-font text-lg" style="color:var(--warn);">Damaged Stock</h2>
            <p class="text-sm mt-1" style="color:var(--ink-soft);">Units set aside as unusable, kept separate from remaining stock.</p>
        </div>
        <span class="ledger-font text-2xl num" style="color:var(--warn);">{{ number_format($supply->damaged_stock, 2) }} {{ $supply->unit }}</span>
    </div>

    @error('quantity')
        <p class="text-sm mb-3" style="color:var(--danger);">{{ $message }}</p>
    @enderror

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <form method="POST" action="{{ route('supplies.mark-damaged', $supply) }}" class="rounded-md p-4" style="background:var(--surface); border:1px solid var(--line);">
            @csrf
            <p class="text-sm font-semibold mb-2">Mark as damaged</p>
            <input type="number" step="0.01" min="0.01" max="{{ $supply->quantity_remaining }}" name="quantity" placeholder="Qty" required class="field w-full mb-2">
            <input type="text" name="notes" placeholder="Reason (optional)" class="field w-full mb-2">
            <button type="submit" class="text-sm font-semibold px-3 py-2 rounded-md w-full" style="background:var(--warn); color:#FFF;">
                Mark damaged
            </button>
        </form>

        <form method="POST" action="{{ route('supplies.restore-damaged', $supply) }}" class="rounded-md p-4" style="background:var(--surface); border:1px solid var(--line);">
            @csrf
            <p class="text-sm font-semibold mb-2">Restore to stock</p>
            <input type="number" step="0.01" min="0.01" max="{{ $supply->damaged_stock }}" name="quantity" placeholder="Qty" required class="field w-full mb-2" @disabled(! $supply->hasDamagedStock())>
            <input type="text" name="notes" placeholder="Reason (optional)" class="field w-full mb-2" @disabled(! $supply->hasDamagedStock())>
            <button type="submit" class="text-sm font-semibold px-3 py-2 rounded-md w-full" style="background:var(--success); color:#FFF;" @disabled(! $supply->hasDamagedStock())>
                Restore stock
            </button>
        </form>
    </div>
</div>

{{-- Damage / restore history --}}
<div class="rounded-lg p-6 mt-6" style="background:var(--surface); border:1px solid var(--line);">
    <h2 class="ledger-font text-lg mb-4">Damage &amp; Restore History</h2>

    <div class="rounded-md overflow-hidden" style="border:1px solid var(--line);">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:var(--paper); border-bottom:1px solid var(--line);">
                    <th class="px-4 py-2 text-left font-medium" style="color:var(--ink-soft);">Date</th>
                    <th class="px-4 py-2 text-left font-medium" style="color:var(--ink-soft);">Action</th>
                    <th class="px-4 py-2 text-right font-medium" style="color:var(--ink-soft);">Quantity</th>
                    <th class="px-4 py-2 text-left font-medium" style="color:var(--ink-soft);">Reason</th>
                    <th class="px-4 py-2 text-left font-medium" style="color:var(--ink-soft);">By</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($damageTransactions as $transaction)
                    <tr style="border-bottom:1px solid var(--line);">
                        <td class="px-4 py-2" style="color:var(--ink-soft);">{{ $transaction->created_at->timezone('Asia/Kabul')->format('M j, Y g:ia') }}</td>
                        <td class="px-4 py-2">
                            @if ($transaction->type === 'damage')
                                <span class="badge" style="background:var(--warn-soft); color:var(--warn);">Marked damaged</span>
                            @else
                                <span class="badge" style="background:var(--success-soft); color:var(--success);">Restored</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right num">{{ number_format(abs($transaction->quantity), 2) }} {{ $supply->unit }}</td>
                        <td class="px-4 py-2" style="color:var(--ink-soft);">{{ $transaction->notes ?: 'â€”' }}</td>
                        <td class="px-4 py-2" style="color:var(--ink-soft);">{{ $transaction->createdBy->name ?? 'System' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-3 text-center text-sm" style="color:var(--ink-soft);">No damage or restore activity yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

        @if ($supply->notes)
            <div class="mt-6 rounded-lg p-5" style="background:var(--surface); border:1px solid var(--line);">
                <div class="text-xs mb-1" style="color:var(--ink-soft);">Notes</div>
                <div class="text-sm">{{ $supply->notes }}</div>
            </div>
        @endif
    </div>
</div>
</x-layouts.pixel>