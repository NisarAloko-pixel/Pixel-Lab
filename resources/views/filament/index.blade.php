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
        }
    }">

        <div class="flex justify-between items-end mb-6">
            <div>
                <h1 class="ledger-font text-3xl" style="color:var(--ink);">Filament Inventory</h1>
                <p class="text-sm mt-1" style="color:var(--ink-soft);">Every spool on hand, its landed cost, and what's left.</p>
            </div>
            
        </div>
        @if ($pendingPurchases->isNotEmpty())
    <div class="rounded-lg p-5 mb-8" style="background:var(--warn-soft); border:1px solid var(--warn-line, var(--warn));">
        <h2 class="text-sm font-semibold mb-3" style="color:var(--warn);">
            Arrived — not yet added ({{ $pendingPurchases->count() }})
        </h2>
        <p class="text-xs mb-4" style="color:var(--ink-soft);">
            These purchases physically showed up but aren't in your Filament Inventory yet. Review and add them.
        </p>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach ($pendingPurchases as $purchase)
                <div class="rounded-md p-3 flex flex-col gap-2" style="background:var(--surface); border:1px solid var(--line);">
                    <div class="text-sm font-medium truncate">
                        {{ trim(($purchase->filament_brand ?? '').' '.$purchase->filament_type.' '.$purchase->filament_color) }}
                    </div>
                    <div class="text-xs" style="color:var(--ink-soft);">
                        {{ number_format($purchase->quantity, 1) }}g<br>
                        {{ $purchase->currency }} {{ number_format($purchase->total_price, 2) }}
                        @if ($purchase->supplier)
                            <br>{{ $purchase->supplier->name }}
                        @endif
                        <br>arrived {{ $purchase->arrived_at?->format('Y-m-d') }}
                    </div>
                    <div class="mt-auto flex items-center gap-2">
    <a href="{{ route('filament.create', ['purchase_id' => $purchase->id]) }}"
        class="flex-1 text-xs font-semibold text-center px-2 py-1.5 rounded-md"
        style="background:var(--accent); color:#FFF;">
        Add
    </a>

    <form method="POST" action="{{ route('purchases.destroy', $purchase) }}"
        x-ref="deleteForm{{ $purchase->id }}"
        class="flex-1">
        @csrf @method('DELETE')
        <input type="hidden" name="cancellation_reason">
        <button type="button"
            @click="openDeleteModal($refs.deleteForm{{ $purchase->id }}, @js(trim(($purchase->filament_brand ?? '').' '.$purchase->filament_type.' '.$purchase->filament_color)))"
            class="w-full text-xs font-semibold px-2 py-1.5 rounded-md"
            style="border:1px solid var(--line); color:var(--danger);">
            Cancel
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
        <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Type</label>
        <select name="type" class="field w-full">
            <option value="">All types</option>
            @foreach ($types as $type)
                <option value="{{ $type }}" @selected(request('type') === $type)>{{ $type }}</option>
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
@php
    $mediumOrLowStock = $stockSummary->filter(fn ($group) => $group->total_remaining <= 300)->values();
@endphp

@if ($mediumOrLowStock->isNotEmpty())
    <div class="rounded-lg p-4 mb-4 flex items-center gap-3" style="background:var(--warn-soft); border:1px solid var(--warn-line, var(--warn));">
        <svg viewBox="0 0 24 24" fill="none" stroke="var(--warn)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px; height:20px; flex-shrink:0;">
            <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/>
            <line x1="12" y1="9" x2="12" y2="13"/>
            <line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
        <p class="text-sm font-medium" style="color:var(--warn);">
            Low stock — {{ $mediumOrLowStock->count() }} {{ Str::plural('spool', $mediumOrLowStock->count()) }} at 300gr or less. Please restock soon.
        </p>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6 mb-10">
        @foreach ($mediumOrLowStock as $group)
            @php
                $percentRemaining = $group->total_purchased > 0
                    ? ($group->total_remaining / $group->total_purchased) * 100
                    : 0;
                $percentRemaining = max(0, min($percentRemaining, 100));
                $lowGroup = $percentRemaining <= 15;
                $swatch = strtolower(str_replace(' ', '', $group->color));
                $ringColor = $lowGroup ? 'var(--danger)' : ($percentRemaining <= 40 ? 'var(--warn)' : 'var(--success)');

                // Outer progress ring math
                $radius = 54;
                $circumference = 2 * pi() * $radius;
                $dashOffset = $circumference * (1 - $percentRemaining / 100);
            @endphp
            <div class="flex flex-col items-center text-center">

                <div class="relative w-[128px] h-[128px]">
                    {{-- Progress ring — how much filament is left --}}
                    <svg viewBox="0 0 128 128" class="absolute inset-0 w-full h-full -rotate-90">
                        <circle cx="64" cy="64" r="{{ $radius }}" fill="none" stroke="var(--line)" stroke-width="3"/>
                        <circle cx="64" cy="64" r="{{ $radius }}" fill="none" stroke="{{ $ringColor }}" stroke-width="3"
                            stroke-linecap="round"
                            stroke-dasharray="{{ $circumference }}"
                            stroke-dashoffset="{{ $dashOffset }}"/>
                    </svg>

                    {{-- The spool itself: flange, wound filament, hub, text --}}
                    <svg viewBox="0 0 128 128" class="absolute inset-0 w-full h-full">
                        {{-- outer flange shadow --}}
                        <circle cx="64" cy="64" r="46" fill="{{ $swatch }}" opacity="0.10"/>
                        {{-- wound filament ring --}}
                        <circle cx="64" cy="64" r="46" fill="none" stroke="{{ $swatch }}" stroke-width="16"/>
                        {{-- subtle wind lines for texture --}}
                        <circle cx="64" cy="64" r="46" fill="none" stroke="#000" stroke-opacity="0.08" stroke-width="1" stroke-dasharray="2 3"/>
                        {{-- center hub --}}
                        <circle cx="64" cy="64" r="30" fill="var(--surface)" stroke="var(--line)" stroke-width="1.5"/>
                        @if ($lowGroup)
                            <circle cx="100" cy="28" r="9" fill="var(--danger)"/>
                            <text x="100" y="32" text-anchor="middle" font-size="11" font-weight="700" fill="#fff">!</text>
                        @endif
                    </svg>

                    {{-- Text content sitting inside the hub --}}
                    <div class="absolute inset-0 flex flex-col items-center justify-center px-6">
                        <div class="text-[11px] font-bold leading-tight truncate w-full" style="color:var(--ink);">{{ $group->type }}</div>
                        
                        <div class="text-sm font-bold num leading-none" style="color:var(--ink);">{{ number_format($group->total_remaining / 1000, 2) }}<span class="text-[9px] font-normal" style="color:var(--ink-soft);">kg</span></div>
                        <div class="text-[9px] font-semibold mt-0.5" style="color:{{ $ringColor }};">{{ number_format($percentRemaining, 0) }}%</div>
                    </div>
                </div>

              @if ($group->color)
    <div class="text-sm font-medium mt-2 truncate max-w-full" style="color:var(--ink);">{{ $group->color }}</div>
@endif
            </div>
        @endforeach
    </div>
@endif

        <div class="rounded-lg overflow-hidden" style="background:var(--surface); border:1px solid var(--line);">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left" style="background:var(--paper); border-bottom:1px solid var(--line);">
    <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Type</th>
    <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Color</th>
    <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Brand</th>
    <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Remaining / Purchased</th>
    <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Purchase Price</th>
    <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Shipping</th>
    <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Price/gram ({{ $baseCurrency }})</th>
    <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Cost/kg (landed)</th>
    <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Supplier</th>
    <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Purchased</th>
    <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Actions</th>
</tr>
                </thead>
                <tbody>
                    @forelse ($filaments as $filament)
                        @php
                            $lowFilament = $filament->weight_purchased_g > 0
                                && ($filament->weight_remaining_g / $filament->weight_purchased_g) <= 0.15;
                        @endphp
                       <tr style="border-bottom:1px solid var(--line); {{ $lowFilament ? 'background:var(--warn-soft);' : '' }}">
    <td class="px-4 py-3 font-medium">{{ $filament->type }}</td>
    <td class="px-4 py-3">{{ $filament->color }}</td>
    <td class="px-4 py-3" style="color:var(--ink-soft);">{{ $filament->brand ?: '—' }}</td>
    <td class="px-4 py-3 text-right num">
        {{ number_format($filament->weight_remaining_g, 1) }}g / {{ number_format($filament->weight_purchased_g, 1) }}g
        @if ($lowFilament)
            <span class="badge ml-1" style="background:var(--warn-soft); color:var(--warn);">Low</span>
        @endif
    </td>
    <td class="px-4 py-3 text-right num">{{ number_format($filament->purchase_price, 2) }}</td>
    <td class="px-4 py-3 text-right num" style="color:var(--ink-soft);">{{ number_format($filament->shipping_cost, 2) }}</td>
    <td class="px-4 py-3 text-right num">
        {{ number_format($filament->weight_purchased_g > 0 ? ($filament->purchase_price + $filament->shipping_cost) / $filament->weight_purchased_g : 0, 4) }}
    </td>
    <td class="px-4 py-3 text-right num font-medium">{{ number_format($filament->cost_per_kg, 2) }}</td>
    <td class="px-4 py-3" style="color:var(--ink-soft);">{{ $filament->supplier->name ?? '—' }}</td>
    <td class="px-4 py-3 num" style="color:var(--ink-soft);">{{ $filament->purchase_date?->format('Y-m-d') ?? '—' }}</td>
    <td class="px-4 py-3">
        <div class="flex justify-end items-center gap-1">
            <a href="{{ route('filament.edit', $filament) }}" class="icon-btn" style="color:var(--accent-ink);" title="Edit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 20h9"/>
                    <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                </svg>
            </a>
            <form method="POST" action="{{ route('filament.destroy', $filament) }}"
                onsubmit="return confirm('Delete this filament spool? This cannot be undone.');">
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
<td colspan="11" class="px-4 py-10 text-center text-sm" style="color:var(--ink-soft);">
                                        No filament spools recorded yet — add your first one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

                <div class="mt-5">{{ $filaments->links() }}</div>
</div>

        {{-- Cancel-purchase reason modal --}}
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
    </div>
</div>
</x-layouts.pixel>