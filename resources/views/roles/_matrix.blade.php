@php
    // request field => [permission, label]. Keep in sync with RoleController::EXTRAS.
    $extras = [
        'audit_view'    => ['audit.view',    'Can view audit logs'],
    ];
    $current = $current ?? [];
    // Row labels that differ from ucfirst(resource).
    $labels = ['settings' => 'App settings'];
@endphp

<div>
    <label class="block text-sm font-medium mb-2" style="color:var(--ink-soft);">Permissions</label>
    <div class="rounded-md overflow-x-auto" style="border:1px solid var(--line);">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:var(--paper);">
                    <th class="text-left px-4 py-2 font-medium" style="color:var(--ink-soft);">Resource</th>
                    @foreach (['view', 'create', 'edit', 'delete'] as $action)
                        <th class="px-3 py-2 font-medium text-center" style="color:var(--ink-soft);">{{ ucfirst($action) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($matrix as $resource => $actions)
                    <tr style="border-top:1px solid var(--line);">
                        <td class="px-4 py-2 font-medium">{{ $labels[$resource] ?? ucfirst($resource) }}</td>
                        @foreach (['view', 'create', 'edit', 'delete'] as $action)
                            <td class="px-3 py-2 text-center">
                                @if (in_array($action, $actions))
                                    <input type="checkbox" name="permissions[]" value="{{ $resource }}.{{ $action }}"
                                        @checked(in_array("{$resource}.{$action}", $current))>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-3 space-y-2">
        @foreach ($extras as $field => [$permission, $label])
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="{{ $field }}" value="1"
                    @checked(old($field, in_array($permission, $current)))>
                {{ $label }}
            </label>
        @endforeach
    </div>
</div>