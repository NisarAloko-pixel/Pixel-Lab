<x-layouts.pixel>
@php
    $badge = [
        'created'      => 'background:var(--success-soft); color:var(--success);',
        'updated'      => 'background:var(--blue-soft); color:var(--blue);',
        'deleted'      => 'background:var(--danger-soft); color:var(--danger);',
        'restored'     => 'background:var(--purple-soft); color:var(--purple);',
        'login'        => 'background:var(--accent-soft); color:var(--accent-ink);',
        'logout'       => 'background:var(--paper); color:var(--ink-soft); border:1px solid var(--line);',
        'login_failed' => 'background:var(--warn-soft); color:var(--warn);',
    ];
    $fmt = function ($v) {
        if (is_null($v)) return '—';
        if (is_bool($v)) return $v ? 'true' : 'false';
        if (is_array($v)) return json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return (string) $v;
    };
    $label = fn ($action) => ucfirst(str_replace('_', ' ', $action));
@endphp

<style>
    .al-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
    .al-table th { text-align: left; font-size: .72rem; font-weight: 600; color: var(--ink-soft); padding: .6rem .8rem; border-bottom: 1px solid var(--line); white-space: nowrap; background: var(--paper); }
    .al-table td { padding: .7rem .8rem; border-bottom: 1px solid var(--line); vertical-align: top; }
    .al-table tbody tr:hover { background: var(--paper); }
    .al-filters { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: .75rem; align-items: end; }
    .al-filters label { display: block; font-size: .72rem; color: var(--ink-soft); margin-bottom: .25rem; }
    .al-details summary { cursor: pointer; font-size: .8rem; font-weight: 600; color: var(--accent-ink); list-style: none; }
    .al-details summary::-webkit-details-marker { display: none; }
    .al-diff { margin-top: .5rem; width: 100%; font-size: .78rem; border-collapse: collapse; }
    .al-diff th, .al-diff td { text-align: left; padding: .3rem .5rem; border-bottom: 1px dashed var(--line); overflow-wrap: anywhere; }
    .al-diff th { color: var(--ink-soft); font-weight: 600; }
    .al-old { color: var(--danger); }
    .al-new { color: var(--success); }
    @media (max-width: 900px) { .al-filters { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
</style>

<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-6xl mx-auto px-6 py-10">

        <div class="mb-6">
            <h1 class="ledger-font text-3xl">Audit Log</h1>
            <p class="text-sm mt-1" style="color:var(--ink-soft);">Who did what, and when. Logs can't be edited or deleted from the app.</p>
        </div>

        <form method="GET" class="card-3d" style="padding:1.1rem; margin-bottom:1.25rem;">
            <div class="al-filters">
                <div>
                    <label>User</label>
                    <select name="user_id" class="field w-full">
                        <option value="">All users</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Action</label>
                    <select name="action" class="field w-full">
                        <option value="">All actions</option>
                        @foreach ($actions as $a)
                            <option value="{{ $a }}" @selected(request('action') === $a)>{{ $label($a) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Record type</label>
                    <select name="model" class="field w-full">
                        <option value="">All records</option>
                        @foreach ($models as $m)
                            <option value="{{ $m }}" @selected(request('model') === $m)>{{ class_basename($m) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>From</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="field w-full">
                </div>
                <div>
                    <label>To</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="field w-full">
                </div>
                <div style="display:flex; gap:.5rem;">
                    <button type="submit" class="text-sm font-semibold px-4 py-2 rounded-md" style="background:var(--accent); color:#FFF;">Filter</button>
                    <a href="{{ route('audit-logs.index') }}" class="text-sm px-4 py-2 rounded-md" style="border:1px solid var(--line); color:var(--ink-soft);">Reset</a>
                </div>
            </div>
        </form>

        <div class="card-3d" style="overflow:hidden;">
            <div style="overflow-x:auto;">
                <table class="al-table" style="min-width:820px;">
                    <thead>
                        <tr><th>When</th><th>User</th><th>Action</th><th>Record</th><th>Details</th></tr>
                    </thead>
                    <tbody>
                    @forelse ($logs as $log)
                        @php
                            $old = $log->old_values ?? [];
                            $new = $log->new_values ?? [];
                            $fields = array_values(array_unique(array_merge(array_keys($old), array_keys($new))));
                        @endphp
                        <tr>
                            <td style="white-space:nowrap; color:var(--ink-soft);">{{ $log->created_at->format('M j, Y') }}<br><span class="text-xs">{{ $log->created_at->format('H:i:s') }}</span></td>
                            <td>
                                @if ($log->user)
                                    <div style="display:flex; align-items:center; gap:.5rem;">
                                        <img src="{{ $log->user->avatar_url }}" alt="" style="width:24px; height:24px; border-radius:9999px; border:1px solid var(--line); background:var(--accent-soft);">
                                        <span class="font-medium">{{ $log->user->name }}</span>
                                    </div>
                                @else
                                    <span style="color:var(--ink-soft);">System</span>
                                @endif
                            </td>
                            <td><span class="badge" style="{{ $badge[$log->action] ?? $badge['logout'] }}">{{ $label($log->action) }}</span></td>
                            <td style="white-space:nowrap;">
                                @if ($log->auditable_type)
                                    {{ class_basename($log->auditable_type) }} <span style="color:var(--ink-soft);">#{{ $log->auditable_id }}</span>
                                @else
                                    <span style="color:var(--ink-soft);">—</span>
                                @endif
                            </td>
                            <td style="min-width:260px;">
                                @if (count($fields))
                                    <details class="al-details">
                                        <summary>{{ count($fields) }} {{ count($fields) === 1 ? 'field' : 'fields' }}</summary>
                                        <table class="al-diff">
                                            <thead><tr><th>Field</th>@if ($old)<th>Before</th>@endif @if ($new)<th>After</th>@endif</tr></thead>
                                            <tbody>
                                            @foreach ($fields as $f)
                                                <tr>
                                                    <td>{{ $f }}</td>
                                                    @if ($old)<td class="al-old">{{ $fmt($old[$f] ?? null) }}</td>@endif
                                                    @if ($new)<td class="al-new">{{ $fmt($new[$f] ?? null) }}</td>@endif
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </details>
                                @else
                                    <span style="color:var(--ink-soft);">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align:center; padding:2.5rem; color:var(--ink-soft);">No activity matches these filters.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">{{ $logs->links() }}</div>
    </div>
</div>
</x-layouts.pixel>