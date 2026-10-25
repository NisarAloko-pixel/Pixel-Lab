@php
    // Formats a quantity without trailing decimal noise: 1.000 -> "1", 1.500 -> "1.5".
    if (!function_exists('formatQty')) {
        function formatQty($value, int $decimals = 3): string
        {
            $formatted = number_format((float) $value, $decimals, '.', '');
            if (str_contains($formatted, '.')) {
                $formatted = rtrim(rtrim($formatted, '0'), '.');
            }
            return $formatted;
        }
    }
@endphp
<x-layouts.pixel>
<style>
    :root { --btn-dark: #21262F; }
    [data-theme="dark"] { --btn-dark: #3A4050; }
</style>

<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-7xl mx-auto px-6 py-10" x-data="{
        restockOpen: false,
        restockUrl: '',
        restockName: '',
        restockUnit: '',
        openRestock(url, name, unit) {
            this.restockUrl = url;
            this.restockName = name;
            this.restockUnit = unit;
            this.restockOpen = true;
            $nextTick(() => $refs.restockQty?.focus());
        }
    }">

        <div class="flex justify-between items-end mb-6">
            <div>
                <h1 class="ledger-font text-3xl" style="color:var(--ink);">Products</h1>
                <p class="text-sm mt-1" style="color:var(--ink-soft);">Everything you make, its stock, and its price.</p>
            </div>
            <a href="{{ route('products.create') }}"
                class="text-sm font-semibold px-4 py-2.5 rounded-md"
                style="background:var(--accent); color:#FFF;">
                + New Product
            </a>
        </div>

        {{-- Filter bar --}}
        <form method="GET" data-live-search class="rounded-lg p-4 mb-6 flex flex-wrap items-end gap-3" style="background:var(--surface); border:1px solid var(--line);">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-[11px] font-medium mb-1" style="color:var(--ink-soft);">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or SKU..." class="field w-full">
            </div>
            <div>
                <label class="block text-[11px] font-medium mb-1" style="color:var(--ink-soft);">Category</label>
                <select name="category_id" class="field">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <label class="flex items-center gap-2 text-sm pb-2.5" style="color:var(--ink-soft);">
                <input type="checkbox" name="low_stock" value="1" @checked(request('low_stock'))> Low stock only
            </label>
            <button class="text-sm font-medium px-4 py-2 rounded-md" style="background:var(--btn-dark); color:#FFF;">
                Filter
            </button>
            @if (request('search') || request('category_id') || request('low_stock'))
                <a href="{{ route('products.index') }}" class="text-sm font-medium px-2 py-2" style="color:var(--ink-soft);">
                    Clear
                </a>
            @endif
        </form>

        {{-- Table --}}
        <div data-live-results>
<div class="rounded-lg overflow-hidden" style="background:var(--surface); border:1px solid var(--line); overflow-x:auto;">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left" style="background:var(--paper); border-bottom:1px solid var(--line);">
                        <th class="px-4 py-2.5 font-medium" style="color:var(--ink-soft); white-space:nowrap;">SKU</th>
                        <th class="px-4 py-2.5 font-medium" style="color:var(--ink-soft); white-space:nowrap;">Name</th>
                        <th class="px-4 py-2.5 font-medium" style="color:var(--ink-soft); white-space:nowrap;">Category</th>
                        <th class="px-4 py-2.5 font-medium text-center" style="color:var(--ink-soft); white-space:nowrap;">Stock</th>
                        <th class="px-4 py-2.5 font-medium text-center" style="color:var(--ink-soft); white-space:nowrap;">Damaged</th>
                        <th class="px-4 py-2.5 font-medium text-center" style="color:var(--ink-soft); white-space:nowrap;">Selling Price</th>
                        <th class="px-4 py-2.5 font-medium text-center" style="color:var(--ink-soft); white-space:nowrap;">Cost Price</th>
                        <th class="px-4 py-2.5 font-medium" style="color:var(--ink-soft); white-space:nowrap;">Status</th>
                        <th class="px-4 py-2.5 font-medium" style="color:var(--ink-soft); white-space:nowrap;">Created</th>
                        <th class="px-4 py-2.5 font-medium text-center" style="color:var(--ink-soft); white-space:nowrap;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr style="border-bottom:1px solid var(--line); {{ $product->isLowStock() ? 'background:var(--warn-soft);' : '' }}">
                            <td class="px-4 py-1.5 num" style="color:var(--ink-soft); white-space:nowrap;">{{ $product->sku }}</td>
                            <td class="px-4 py-1.5 font-medium" style="white-space:nowrap;">{{ $product->name }}</td>
                            <td class="px-4 py-1.5" style="color:var(--ink-soft); white-space:nowrap;">{{ $product->category->name ?? '—' }}</td>
                            <td class="px-4 py-1.5 text-center" style="white-space:nowrap;">
                                @if ((float) $product->current_stock <= 0)
                                    <span class="badge" style="background:var(--danger-soft); color:var(--danger); font-size:10px;">Out of stock</span>
                                @else
                                    <span class="num text-xs" style="color:var(--ink-soft);">{{ formatQty($product->current_stock) }} {{ $product->unit }}</span>
                                    @if ($product->isLowStock())
                                        <span class="badge ml-1" style="background:var(--warn-soft); color:var(--warn); font-size:10px;">Low</span>
                                    @endif
                                @endif
                            </td>
                            <td class="px-4 py-1.5 text-center" style="white-space:nowrap;">
                                @if ($product->damaged_stock > 0)
                                    <span class="badge" style="background:var(--danger-soft); color:var(--danger); font-size:10px;" title="Set aside, not sellable">
                                        {{ formatQty($product->damaged_stock) }} {{ $product->unit }}
                                    </span>
                                @else
                                    <span class="text-xs" style="color:var(--ink-soft);">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-1.5 text-center num font-medium" style="white-space:nowrap;">{{ number_format($product->selling_price, 2) }}</td>
                            <td class="px-4 py-1.5 text-center num" style="color:var(--ink-soft); white-space:nowrap;">{{ number_format($product->cost_price, 2) }}</td>
                            <td class="px-4 py-1.5" style="white-space:nowrap;">
                                <span class="badge" style="{{ $product->is_active ? 'background:var(--success-soft); color:var(--success);' : 'background:var(--danger-soft); color:var(--danger);' }}">
                                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                           <td class="px-4 py-1.5 num" style="color:var(--ink-soft); white-space:nowrap;">
    {{ $product->created_at?->timezone($displayTimezone)->format('Y-m-d') ?? '—' }}
</td>
                            <td class="px-4 py-1.5" style="white-space:nowrap;">
                                <div class="flex justify-end items-center gap-1">
                                    @can('products.edit')
                                        <button type="button" class="icon-btn" style="color:var(--success);" title="Restock"
                                            @click="openRestock(@js(route('products.restock', $product)), @js($product->name), @js($product->unit))">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12 5v14"/>
                                                <path d="M5 12h14"/>
                                            </svg>
                                        </button>
                                    @endcan
                                    <a href="{{ route('products.show', $product) }}" class="icon-btn" style="color:var(--ink-soft);" title="View">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('products.edit', $product) }}" class="icon-btn" style="color:var(--accent-ink);" title="Edit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('products.destroy', $product) }}"
                                        data-confirm="Delete {{ $product->name }}?"
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
                            <td colspan="10" class="px-4 py-10 text-center text-sm" style="color:var(--ink-soft);">
                                No products yet — add your first one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $products->links() }}</div>
</div>

        {{-- Restock modal --}}
        <div x-show="restockOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center px-4"
            style="background:rgba(0,0,0,0.4);"
            @keydown.escape.window="restockOpen = false">
            <form method="POST" :action="restockUrl"
                class="rounded-lg w-full max-w-sm p-6"
                style="background:var(--surface); border:1px solid var(--line);"
                @click.outside="restockOpen = false">
                @csrf

                <h2 class="ledger-font text-lg mb-1" style="color:var(--ink);">Restock</h2>
                <p class="text-sm mb-4" style="color:var(--ink-soft);">
                    Add newly printed units of
                    <span x-text="restockName" style="color:var(--ink); font-weight:500;"></span>.
                </p>

                <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">
                    Quantity (<span x-text="restockUnit"></span>)
                </label>
                <input type="number" name="quantity" step="0.001" min="0.001" required
                    x-ref="restockQty" class="field w-full mb-3">

                <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Notes (optional)</label>
                <textarea name="notes" rows="2" class="field w-full mb-3"></textarea>

                <label class="flex items-center gap-2 text-sm mb-5" style="color:var(--ink-soft);">
                    <input type="checkbox" name="deduct_filament" value="1" checked>
                    Deduct filament used from spools
                </label>

                <div class="flex justify-end gap-2">
                    <button type="button" @click="restockOpen = false"
                        class="text-sm font-semibold px-4 py-2 rounded-md"
                        style="background:var(--paper); color:var(--ink-soft); border:1px solid var(--line);">
                        Cancel
                    </button>
                    <button type="submit"
                        class="text-sm font-semibold px-4 py-2 rounded-md"
                        style="background:var(--accent); color:#FFF;">
                        Restock
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
</x-layouts.pixel>