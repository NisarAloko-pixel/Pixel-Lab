<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-2xl mx-auto px-6 py-10">
        <a href="{{ route('roles.index') }}" class="inline-flex items-center gap-1 text-sm font-medium mb-4" style="color:var(--accent-ink);">
            &larr; Back to roles
        </a>
        <h1 class="ledger-font text-3xl mb-6">New Role</h1>

        @if ($errors->any())
            <div class="rounded-md p-4 mb-6 text-sm" style="background:var(--danger-soft); color:var(--danger);">
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('roles.store') }}" class="rounded-lg p-6 space-y-5" style="background:var(--surface); border:1px solid var(--line);">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-1" style="color:var(--ink-soft);">Role name</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="w-full rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2" style="border:1px solid var(--line); background:var(--paper); --tw-ring-color:var(--accent);">
            </div>

            @include('roles._matrix', ['matrix' => $matrix, 'current' => old('permissions', [])])

            <div class="flex gap-3 pt-2">
                <button type="submit" class="text-sm font-semibold px-4 py-2.5 rounded-md" style="background:var(--accent); color:#FFF;">Create role</button>
                <a href="{{ route('roles.index') }}" class="text-sm font-medium px-4 py-2.5" style="color:var(--ink-soft);">Cancel</a>
            </div>
        </form>
    </div>
</div>
</x-layouts.pixel>
