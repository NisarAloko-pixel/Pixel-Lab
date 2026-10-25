<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-3xl mx-auto px-6 py-10" x-data="{ tab: 'business' }">

        <h1 class="ledger-font text-3xl mb-1">App Settings</h1>
        <p class="text-sm mb-6" style="color:var(--ink-soft);">Business details, branding, and automated report delivery.</p>


        @if (session('error'))
            <div class="rounded-md p-4 mb-6 text-sm" style="background:var(--danger-soft); color:var(--danger); border:1px solid var(--danger-line);">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-md p-4 mb-6 text-sm" style="background:var(--danger-soft); color:var(--danger); border:1px solid var(--danger-line);">
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        {{-- Tabs --}}
        <div class="pill-group mb-6">
    <button type="button" class="pill" :class="{ active: tab === 'business' }" @click="tab = 'business'">Business</button>
    <button type="button" class="pill" :class="{ active: tab === 'branding' }" @click="tab = 'branding'">Branding</button>
    <button type="button" class="pill" :class="{ active: tab === 'reports' }" @click="tab = 'reports'">Reports</button>
    <button type="button" class="pill" :class="{ active: tab === 'backup' }" @click="tab = 'backup'">Backup &amp; Restore</button>

</div>
        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            {{-- Business details --}}
            <div class="card-3d p-6" x-show="tab === 'business'" x-cloak>
                <div class="flex items-center gap-3 mb-5">
                    <div class="section-icon" style="background:var(--accent-soft); color:var(--accent-ink);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-semibold text-sm">Business details</h2>
                        <p class="text-xs" style="color:var(--ink-soft);">Used on invoices, receipts, and reports.</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    @foreach ($fields as $key => $field)
                        @continue($field['group'] !== 'business')
                        <div class="{{ in_array($key, ['business_name', 'business_address']) ? 'col-span-2' : '' }}">
                            <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">{{ $field['label'] }}</label>

                            @if ($field['type'] === 'select')
                                <select name="{{ $key }}" class="field w-full">
                                    @if ($field['grouped'] ?? false)
                                        @foreach ($field['options'] as $groupLabel => $groupOptions)
                                            <optgroup label="{{ $groupLabel }}">
                                                @foreach ($groupOptions as $optValue => $optLabel)
                                                    <option value="{{ $optValue }}" @selected(old($key, $values[$key]) === $optValue)>{{ $optLabel }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    @else
                                        @foreach ($field['options'] as $optValue => $optLabel)
                                            <option value="{{ $optValue }}" @selected(old($key, $values[$key]) === $optValue)>{{ $optLabel }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            @else
                                <input type="{{ $field['type'] }}" name="{{ $key }}" value="{{ old($key, $values[$key]) }}" class="field w-full">
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Branding / logo --}}
            <div class="card-3d p-6" x-show="tab === 'branding'" x-cloak x-data="{ preview: null, removed: false }">
                <div class="flex items-center gap-3 mb-5">
                    <div class="section-icon" style="background:var(--purple-soft); color:var(--purple);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-semibold text-sm">Logo</h2>
                        <p class="text-xs" style="color:var(--ink-soft);">Shown on invoices and receipts. PNG or JPG, up to 2MB.</p>
                    </div>
                </div>

                <div class="flex items-center gap-5">
                    <div class="w-24 h-24 rounded-lg flex items-center justify-center overflow-hidden shrink-0" style="background:var(--paper); border:1px solid var(--line);">
                        <template x-if="!removed && (preview || @js($logoUrl))">
                            <img :src="preview || @js($logoUrl)" alt="Logo" class="w-full h-full object-contain">
                        </template>
                        <template x-if="removed || !(preview || @js($logoUrl))">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--ink-soft)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/>
                            </svg>
                        </template>
                    </div>

                    <div class="flex-1">
                        <input type="file" name="business_logo" accept="image/png,image/jpeg"
                            @change="removed = false; preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                            class="w-full text-sm">
                        <label class="flex items-center gap-2 text-sm mt-2" style="color:var(--ink-soft);" x-show="@js((bool) $logoUrl)">
                            <input type="checkbox" name="remove_logo" value="1" x-model="removed">
                            Remove current logo
                        </label>
                    </div>
                </div>
            </div>

            {{-- Reports: recipient emails, schedule, report type --}}
            <div
                class="card-3d p-6"
                x-show="tab === 'reports'"
                x-cloak
                x-data="{
                    emails: {{ Illuminate\Support\Js::from(old('report_emails', $reportEmails ?? [''])) }},
                    period: 'month',
                    startDate: '',
                    endDate: '',
                    addEmail() { this.emails.push('') },
                    removeEmail(i) {
                        if (this.emails.length > 1) this.emails.splice(i, 1);
                        else this.emails[0] = '';
                    }
                }"
            >
                <div class="flex items-center gap-3 mb-5">
                    <div class="section-icon" style="background:var(--blue-soft); color:var(--blue);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16v16H4z"/><path d="M22 6l-10 7L2 6"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-semibold text-sm">Automated reports</h2>
                        <p class="text-xs" style="color:var(--ink-soft);">Add recipients, choose report types, and set the delivery schedule.</p>
                    </div>
                </div>

                {{-- Recipient emails --}}
                <div class="mb-6">
                    <label class="block text-xs font-medium mb-2" style="color:var(--ink-soft);">Recipient email(s)</label>

                    <div class="space-y-2">
                        <template x-for="(email, index) in emails" :key="index">
                            <div class="flex items-center gap-2">
                                <input
                                    type="email"
                                    :name="`report_emails[${index}]`"
                                    x-model="emails[index]"
                                    placeholder="name@example.com"
                                    class="field w-full"
                                >
                                <button
                                    type="button"
                                    @click="removeEmail(index)"
                                    class="shrink-0 w-9 h-9 flex items-center justify-center rounded-md"
                                    style="border:1px solid var(--line); color:var(--danger);"
                                    title="Remove"
                                >
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18 6L6 18"/><path d="M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    <button
                        type="button"
                        @click="addEmail()"
                        class="mt-3 text-xs font-semibold px-3 py-2 rounded-md"
                        style="background:var(--accent-soft); color:var(--accent-ink);"
                    >
                        Add another email
                    </button>
                </div>

                {{-- Report type(s) --}}
                <div class="mb-6">
                    <label class="block text-xs font-medium mb-2" style="color:var(--ink-soft);">Which reports should be sent?</label>

                    @php
                        $selectedTypes = old('report_types', $reportTypes ?? []);
                        $reportTypeOptions = [
                            'sale'            => 'Sales report',
                            'profit'          => 'Profit report',
                            'product_profit'  => 'Product profit',
                            'expense'         => 'Expenses report',
                            'purchase'        => 'Purchases report',
                            'transaction'     => 'Transaction report',
                        ];
                    @endphp

                    <div class="grid grid-cols-2 gap-3">
                        @foreach ($reportTypeOptions as $value => $label)
                            <label
                                class="flex items-center gap-2 text-sm px-3 py-2.5 rounded-md cursor-pointer"
                                style="border:1px solid var(--line);"
                            >
                                <input
                                    type="checkbox"
                                    name="report_types[]"
                                    value="{{ $value }}"
                                    @checked(in_array($value, $selectedTypes))
                                >
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Automatic sending --}}
                <div class="mb-6">
                    <label class="flex items-center gap-2 text-sm" style="color:var(--ink-soft);">
                        <input
                            type="checkbox"
                            name="report_enabled"
                            value="1"
                            @checked(old('report_enabled', $reportEnabled ?? false))
                        >
                        Enable automatic sending
                    </label>
                </div>

                {{-- Date range (used by "Send report now") --}}
                <div class="mb-6">
                    <label class="block text-xs font-medium mb-2" style="color:var(--ink-soft);">Report period</label>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <select x-model="period" class="field w-full">
                                <option value="today">Today</option>
                                <option value="week">This week</option>
                                <option value="month">This month</option>
                                <option value="last_month">Last month</option>
                                <option value="year">This year</option>
                                <option value="custom">Custom range</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mt-3" x-show="period === 'custom'" x-cloak>
                        <div>
                            <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">From</label>
                            <input type="date" x-model="startDate" :max="endDate || null" class="field w-full">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">To</label>
                            <input type="date" x-model="endDate" :min="startDate || null" class="field w-full">
                        </div>
                    </div>
                    <p class="text-xs mt-2" style="color:var(--ink-soft);">Applies to "Send report now".</p>
                </div>

                {{-- Send now --}}
                <div class="pt-4" style="border-top:1px solid var(--line);">
                    <button
                        type="button"
                        @click="
                            const form = document.getElementById('send-report-now-form');
                            form.querySelectorAll('.dyn-field').forEach(el => el.remove());
                            emails.forEach((email, i) => {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = `report_emails[${i}]`;
                                input.value = email;
                                input.className = 'dyn-field';
                                form.appendChild(input);
                            });
                            document.querySelectorAll('input[name=\'report_types[]\']:checked').forEach(cb => {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = 'report_types[]';
                                input.value = cb.value;
                                input.className = 'dyn-field';
                                form.appendChild(input);
                            });
                            [['report_period', period], ['start_date', startDate], ['end_date', endDate]].forEach(([n, v]) => {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = n;
                                input.value = v;
                                input.className = 'dyn-field';
                                form.appendChild(input);
                            });
                            form.submit();
                        "
                        class="text-sm font-semibold px-4 py-2.5 rounded-md flex items-center gap-2"
                        style="background:var(--blue-soft); color:var(--blue); border:1px solid var(--line);"
                    >
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/>
                        </svg>
                        Send report now
                    </button>
                    <p class="text-xs mt-2" style="color:var(--ink-soft);">Sends the selected report type(s) to the recipient email(s) above immediately.</p>
                </div>

            </div> {{-- closes the Reports tab card-3d div --}}

            {{-- Backup & Restore --}}
            <div class="card-3d p-6" x-show="tab === 'backup'" x-cloak>
                <div class="flex items-center gap-3 mb-5">
                    <div class="section-icon" style="background:var(--warn-soft); color:var(--warn);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14a9 3 0 0 0 18 0V5"/><path d="M3 12a9 3 0 0 0 18 0"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-semibold text-sm">Backup &amp; Restore</h2>
                        <p class="text-xs" style="color:var(--ink-soft);">Save a copy of your database, or restore from a previous backup.</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-2">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Backup folder path</label>
                        <input type="text" name="backup_path" value="{{ old('backup_path', $backupPath) }}"
                            placeholder="e.g. D:\PixelBackups"
                            class="field w-full">
                        <p class="text-xs mt-1" style="color:var(--ink-soft);">Must not be on the C: drive. Used for both automatic and manual backups.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Automatic backup frequency</label>
                        <select name="backup_frequency" class="field w-full">
                            @php $currentBackupFreq = old('backup_frequency', $backupFrequency); @endphp
                            <option value="daily" @selected($currentBackupFreq === 'daily')>Daily</option>
                            <option value="weekly" @selected($currentBackupFreq === 'weekly')>Weekly</option>
                            <option value="monthly" @selected($currentBackupFreq === 'monthly')>Monthly</option>
                            <option value="yearly" @selected($currentBackupFreq === 'yearly')>Yearly</option>
                        </select>
                    </div>

                    <div>
                        <label class="flex items-center gap-2 text-sm mt-6" style="color:var(--ink-soft);">
                            <input type="checkbox" name="backup_enabled" value="1" @checked(old('backup_enabled', $backupEnabled))>
                            Enable automatic backups
                        </label>
                    </div>
                </div>

                <div class="pt-4 mt-2" style="border-top:1px solid var(--line);">
                    <h3 class="text-xs font-semibold mb-2" style="color:var(--ink-soft);">Manual backup</h3>
                    <button type="button"
                        @click="document.getElementById('backup-now-form').submit()"
                        class="text-sm font-semibold px-4 py-2.5 rounded-md flex items-center gap-2"
                        style="background:var(--blue-soft); color:var(--blue); border:1px solid var(--line);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Backup now (download)
                    </button>
                    <p class="text-xs mt-2" style="color:var(--ink-soft);">Downloads a copy immediately, and also saves one to the folder above if set.</p>
                </div>

                {{--
                    NOTE: this used to be a <form> nested inside the main settings <form> above.
                    HTML does not allow nested forms — the browser closes the OUTER form early when
                    it hits this block's closing tag, which silently broke every control below it,
                    including the "Save settings" button. Fixed by moving the actual <form id="restore-form">
                    down to the bottom of the page (next to the other hidden forms) and wiring these
                    controls to it via the HTML `form="restore-form"` attribute instead.
                --}}
                <div class="pt-4 mt-4" style="border-top:1px solid var(--line);" x-data="{ file: null, confirmed: false }">
                    <h3 class="text-xs font-semibold mb-2" style="color:var(--danger);">Restore from backup</h3>
                    <p class="text-xs mb-3" style="color:var(--ink-soft);">
                        This replaces your current live database with the uploaded file. A safety backup of the current database is taken automatically first, but this action cannot be undone through the app.
                    </p>

                    <input type="file" name="backup_file" form="restore-form" accept=".sqlite"
                        @change="file = $event.target.files[0]" class="w-full text-sm mb-3">

                    <label class="flex items-center gap-2 text-sm mb-3" style="color:var(--ink-soft);">
                        <input type="checkbox" name="confirm_restore" form="restore-form" value="1" x-model="confirmed">
                        I understand this will overwrite the current database.
                    </label>

                    <button type="submit" form="restore-form" :disabled="!file || !confirmed"
                        class="text-sm font-semibold px-4 py-2.5 rounded-md"
                        :style="(file && confirmed) ? 'background:var(--danger); color:#FFF;' : 'background:var(--line); color:var(--ink-soft); cursor:not-allowed;'">
                        Restore database
                    </button>
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="text-sm font-semibold px-4 py-2.5 rounded-md" style="background:var(--accent); color:#FFF;">
                    Save settings
                </button>
            </div>
        </form>

        {{-- Hidden form used only by "Send report now" --}}
        <form id="send-report-now-form" method="POST" action="{{ route('settings.reports.sendNow') }}" class="hidden">
            @csrf
        </form>

        {{-- Hidden form used only by "Backup now" --}}
        <form id="backup-now-form" method="POST" action="{{ route('settings.backup.run') }}" class="hidden">
            @csrf
        </form>

        {{--
            Restore form — lives here (outside the main settings form) rather than nested inside
            the Backup tab's markup above. Its actual input fields are rendered inline in the Backup
            tab and linked here via the HTML form="restore-form" attribute on each control.
        --}}
        <form id="restore-form" method="POST" action="{{ route('settings.backup.restore') }}" enctype="multipart/form-data"
            data-confirm="Replace your live database?"
data-confirm-text="This will replace your live database with the uploaded file. Continue?"
data-confirm-button="Restore">
            @csrf
        </form>

    </div>
</div>
</x-layouts.pixel>