<x-layouts.pixel>
<style>
    /* Only component styles here, no :root overrides, so the theme in pixel-theme.css controls light/dark */
    .field {
        border: 1px solid var(--line);
        background: var(--paper);
        color: var(--ink);
        border-radius: .5rem;
        padding: .55rem .75rem;
        font-size: .875rem;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .field::placeholder { color: var(--ink-soft); opacity: .6; }
    .field:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 2px var(--accent);
    }
    html[data-theme="dark"] .field { color-scheme: dark; }
</style>

<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-2xl mx-auto px-6 py-10">

        <a href="{{ route('customers.index') }}" class="inline-flex items-center gap-1 text-sm font-medium mb-4" style="color:var(--accent-ink);">
            &larr; Back to customers
        </a>

        <h1 class="ledger-font text-3xl mb-6" style="color:var(--ink);">New Customer</h1>

        @if ($errors->any())
            <div class="rounded-lg p-4 mb-6 text-sm" style="background:var(--danger-soft); color:var(--danger); border:1px solid var(--line);">
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-lg p-6" style="background:var(--surface); border:1px solid var(--line);">
            <form method="POST" action="{{ route('customers.store') }}" class="grid grid-cols-2 gap-4">
                @csrf
                <div class="col-span-2">
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Name</label>
                    <input name="name" value="{{ old('name') }}" required class="field w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Phone</label>
                    <input name="phone" value="{{ old('phone') }}" class="field w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Email</label>
                    <input name="email" value="{{ old('email') }}" class="field w-full">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Company</label>
                    <input name="company" value="{{ old('company') }}" class="field w-full">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Address</label>
                    <textarea name="address" rows="3" class="field w-full">{{ old('address') }}</textarea>
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Notes</label>
                    <textarea name="notes" rows="2" class="field w-full">{{ old('notes') }}</textarea>
                </div>

                <div class="col-span-2 flex items-center gap-3">
                    <button type="submit" class="text-sm font-semibold px-5 py-2.5 rounded-md"
                        style="background:var(--accent); color:#FFF;">
                        Save Customer
                    </button>
                    <a href="{{ route('customers.index') }}" class="text-sm font-medium px-4 py-2.5 rounded-md"
                        style="color:var(--ink-soft); border:1px solid var(--line);">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
</x-layouts.pixel>