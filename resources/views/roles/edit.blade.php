@php
    $isAdminRole = $role->name === 'Administrator';
    $isSuper = (bool) auth()->user()->is_super_admin;
    $locked = $isAdminRole && ! $isSuper;   // the client can view but not edit Administrator
@endphp
<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-2xl mx-auto px-6 py-10">
        <h1 class="ledger-font text-3xl mb-6">Edit Role</h1>

        @if (session('error'))
            <div class="rounded-md p-4 mb-6 text-sm" style="background:var(--danger-soft); color:var(--danger);">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-md p-4 mb-6 text-sm" style="background:var(--danger-soft); color:var(--danger);">
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('roles.update', $role) }}" class="rounded-lg p-6 space-y-5" style="background:var(--surface); border:1px solid var(--line);">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium mb-1" style="color:var(--ink-soft);">Role name</label>
                <input type="text" name="name" value="{{ old('name', $role->name) }}" required
                    @disabled($isAdminRole)
                    class="w-full rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2" style="border:1px solid var(--line); background:var(--paper); --tw-ring-color:var(--accent);">
            </div>

            @if ($locked)
                <p class="text-sm rounded-md p-3" style="background:var(--warn-soft); color:var(--warn);">
                    The Administrator role can only be edited by the developer.
                </p>
            @else
                @if ($isAdminRole)
                    <p class="text-sm rounded-md p-3" style="background:var(--warn-soft); color:var(--warn);">
                        You're editing the Administrator role (developer only). The client sees these changes immediately.
                    </p>
                @endif
                @include('roles._matrix', ['matrix' => $matrix, 'current' => old('permissions', $current)])
            @endif

            <div class="flex gap-3 pt-2">
                @unless ($locked)
                    <button type="submit" class="text-sm font-semibold px-4 py-2.5 rounded-md" style="background:var(--accent); color:#FFF;">Save changes</button>
                @endunless
                <a href="{{ route('roles.index') }}" class="text-sm font-medium px-4 py-2.5" style="color:var(--accent-ink);">Back</a>
            </div>
        </form>
    </div>
</div>
</x-layouts.pixel>