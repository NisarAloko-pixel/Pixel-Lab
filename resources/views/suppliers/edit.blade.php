<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-2xl mx-auto px-6 py-10">

        <a href="{{ route('suppliers.show', $supplier) }}" class="text-sm" style="color:var(--accent-ink);">&larr; Back to {{ $supplier->name }}</a>
        <h1 class="ledger-font text-3xl mt-2 mb-6" style="color:var(--ink);">Edit Supplier</h1>

        @if ($errors->any())
            <div class="rounded-lg p-4 mb-6 text-sm" style="background:var(--danger-soft); color:var(--danger); border:1px solid var(--danger-line);">
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-lg p-6" style="background:var(--surface); border:1px solid var(--line);">
            <form method="POST" action="{{ route('suppliers.update', $supplier) }}" class="grid grid-cols-2 gap-4">
                @csrf
                @method('PUT')
                <div class="col-span-2">
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Name</label>
                    <input name="name" value="{{ old('name', $supplier->name) }}" required class="field w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Contact Person</label>
                    <input name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}" class="field w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Phone</label>
                    <input name="phone" value="{{ old('phone', $supplier->phone) }}" class="field w-full">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Email</label>
                    <input name="email" value="{{ old('email', $supplier->email) }}" class="field w-full">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Address</label>
                    <textarea name="address" rows="3" class="field w-full">{{ old('address', $supplier->address) }}</textarea>
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Notes</label>
                    <textarea name="notes" rows="2" class="field w-full">{{ old('notes', $supplier->notes) }}</textarea>
                </div>

                <button class="text-sm font-semibold px-5 py-2.5 rounded-md col-span-2 w-fit" style="background:var(--accent); color:#FFF;">
                    Save Changes
                </button>
            </form>
        </div>
    </div>
</div>
</x-layouts.pixel>
