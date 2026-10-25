@php
    $unit = $purchase->item_type === 'filament' ? 'g' : ($purchase->supply->unit ?? $purchase->unit ?? '');
    $statusStyle = match ($purchase->status) {
        'arrived'   => 'background:var(--success-soft); color:var(--success);',
        'cancelled' => 'background:var(--danger-soft); color:var(--danger);',
        default     => 'background:var(--warn-soft); color:var(--warn);',
    };
    $statusText = match ($purchase->status) {
        'arrived'   => 'Arrived' . ($purchase->arrived_location ? ' @ ' . $purchase->arrived_location : ''),
        'cancelled' => 'Cancelled',
        default     => 'In Transit',
    };
@endphp
<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-2xl mx-auto px-6 py-10">

        <a href="{{ route('purchases.index') }}" class="text-sm" style="color:var(--accent-ink);">&larr; Back to purchases</a>

        <div class="flex justify-between items-end mt-2 mb-6">
            <div>
                <h1 class="ledger-font text-3xl" style="color:var(--ink);">{{ $purchase->displayName() }}</h1>
                <p class="text-sm mt-1" style="color:var(--ink-soft);">
                    Purchase #{{ $purchase->id }} &middot; {{ ucfirst($purchase->item_type) }}
                </p>
            </div>
            <span class="badge" style="{{ $statusStyle }}">{{ $statusText }}</span>
        </div>

        @if ($purchase->isCancelled() && $purchase->cancellation_reason)
            <div class="rounded-lg p-3 mb-6 text-sm" style="background:var(--danger-soft); color:var(--danger);">
                Cancelled{{ $purchase->cancelled_at ? ' on ' . $purchase->cancelled_at->format('Y-m-d') : '' }} â€” {{ $purchase->cancellation_reason }}
            </div>
        @endif

        <div class="rounded-lg p-6 grid grid-cols-2 gap-y-4 gap-x-6" style="background:var(--surface); border:1px solid var(--line);">
            <div>
                <div class="text-xs" style="color:var(--ink-soft);">Quantity</div>
                <div class="text-lg font-semibold num">{{ number_format($purchase->quantity, 2) }} {{ $unit }}</div>
            </div>
            <div>
                <div class="text-xs" style="color:var(--ink-soft);">Price per {{ $unit ?: 'unit' }}</div>
                <div class="text-lg font-semibold num">{{ $purchase->currency }} {{ number_format($purchase->price_per_unit, 4) }}</div>
            </div>
            <div>
                <div class="text-xs" style="color:var(--ink-soft);">Total price</div>
                <div class="text-lg font-semibold num">{{ $purchase->currency }} {{ number_format($purchase->total_price, 2) }}</div>
            </div>
            <div>
                <div class="text-xs" style="color:var(--ink-soft);">Shipping</div>
                <div class="text-lg font-semibold num">{{ $purchase->currency }} {{ number_format($purchase->shipping_cost, 2) }}</div>
            </div>
            @if ($purchase->currency !== ($baseCurrency ?? $purchase->currency))
                <div>
                    <div class="text-xs" style="color:var(--ink-soft);">Exchange rate</div>
                    <div class="text-lg font-semibold num">{{ $purchase->exchange_rate }}</div>
                </div>
            @endif
            <div>
                <div class="text-xs" style="color:var(--ink-soft);">Total cost{{ isset($baseCurrency) ? ' (' . $baseCurrency . ')' : '' }}</div>
                <div class="text-lg font-semibold num">{{ number_format((float) $purchase->total_cost_base, 2) }}</div>
            </div>
            <div>
                <div class="text-xs" style="color:var(--ink-soft);">Supplier</div>
                <div class="text-lg font-semibold">{{ $purchase->supplier->name ?? 'â€”' }}</div>
            </div>
            <div>
                <div class="text-xs" style="color:var(--ink-soft);">Order Date</div>
                <div class="text-lg font-semibold num">{{ $purchase->order_date?->format('Y-m-d') ?? 'â€”' }}</div>
            </div>
            <div>
                <div class="text-xs" style="color:var(--ink-soft);">Arrival Date</div>
                <div class="text-lg font-semibold num">{{ $purchase->arrived_at?->format('Y-m-d') ?? 'â€”' }}</div>
                @if ($purchase->arrived_location)
                    <div class="text-xs mt-0.5" style="color:var(--ink-soft);">Arrived at {{ $purchase->arrived_location }}</div>
                @endif
            </div>
            @if ($purchase->item_type === 'filament')
                <div>
                    <div class="text-xs" style="color:var(--ink-soft);">Filament</div>
                    <div class="text-lg font-semibold">{{ $purchase->filament_type }} {{ $purchase->filament_color }}</div>
                </div>
            @endif
        </div>

        @if ($purchase->notes)
            <div class="mt-6 rounded-lg p-5" style="background:var(--surface); border:1px solid var(--line);">
                <div class="text-xs mb-1" style="color:var(--ink-soft);">Notes</div>
                <div class="text-sm">{{ $purchase->notes }}</div>
            </div>
        @endif
    </div>
</div>
</x-layouts.pixel>