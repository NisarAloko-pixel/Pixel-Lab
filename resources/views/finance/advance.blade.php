<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);" x-data="advancePage()" >

<script type="application/json" id="advance-page-data">{!! json_encode(['employees' => $employees, 'shareholders' => $shareholders, 'accounts' => $accounts, 'totalProfit' => $totalProfit, 'baseCurrency' => $baseCurrency, 'supportedCurrencies' => $supportedCurrencies, 'employeeTxnUrlBase' => url('/finance/employees'), 'shareholderTxnUrlBase' => url('/finance/shareholders'), 'employeesUrlBase' => url('/employees'), 'advanceUrlBase' => url('/finance/advance'), 'shareholdersUrlBase' => url('/finance/shareholders')], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>

    <div class="max-w-6xl mx-auto px-6 py-10">

        <template x-if="flash">
            <div class="rounded-md px-4 py-3 mb-6 text-sm" style="background:var(--success-soft); color:var(--success); border:1px solid var(--line);" x-text="flash"></div>
        </template>
                <template x-if="flashError">
            <div class="rounded-md px-4 py-3 mb-6 text-sm" style="background:var(--danger-soft); color:var(--danger); border:1px solid var(--danger-line);" x-text="flashError"></div>
        </template>
        <div class="mb-6">
            <h1 class="ledger-font text-3xl" style="color:var(--ink);">Advances &amp; Dues</h1>
            <p class="text-sm mt-1" style="color:var(--ink-soft);">Who owes the business, and who the business owes.</p>
        </div>

        {{-- Tabs --}}
        <div class="flex gap-1 mb-6" style="border-bottom:1px solid var(--line);">
            <button type="button" class="px-4 py-2.5 text-sm font-medium" :style="tab === 'customers' ? 'border-bottom:2px solid var(--accent); color:var(--accent-ink);' : 'color:var(--ink-soft);'" @click="tab = 'customers'">Customers</button>
            <button type="button" class="px-4 py-2.5 text-sm font-medium" :style="tab === 'suppliers' ? 'border-bottom:2px solid var(--accent); color:var(--accent-ink);' : 'color:var(--ink-soft);'" @click="tab = 'suppliers'">Suppliers</button>
            <button type="button" class="px-4 py-2.5 text-sm font-medium" :style="tab === 'employees' ? 'border-bottom:2px solid var(--accent); color:var(--accent-ink);' : 'color:var(--ink-soft);'" @click="tab = 'employees'">Employees</button>
            <button type="button" class="px-4 py-2.5 text-sm font-medium" :style="tab === 'shareholders' ? 'border-bottom:2px solid var(--accent); color:var(--accent-ink);' : 'color:var(--ink-soft);'" @click="tab = 'shareholders'">Shareholders</button>
        </div>

        {{-- Customers tab (read-only, no AJAX needed) --}}
        <div x-show="tab === 'customers'" x-cloak>
            <div class="rounded-lg overflow-hidden" style="background:var(--surface); border:1px solid var(--line);">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left" style="background:var(--paper); border-bottom:1px solid var(--line);">
                            <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Customer</th>
                            <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Balance</th>
                            <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Status</th>
                            <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customers as $customer)
                            <tr style="border-bottom:1px solid var(--line);">
                                <td class="px-4 py-3">{{ $customer->name }}</td>
                                <td class="px-4 py-3 text-right num font-medium" style="color:{{ $customer->outstanding_balance > 0 ? 'var(--danger)' : 'var(--success)' }};">
                                    {{ number_format(abs($customer->outstanding_balance), 2) }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge" style="background:{{ $customer->outstanding_balance > 0 ? 'var(--danger-soft)' : 'var(--success-soft)' }}; color:{{ $customer->outstanding_balance > 0 ? 'var(--danger)' : 'var(--success)' }};">
                                        {{ $customer->outstanding_balance > 0 ? 'Owes you' : 'Overpaid (credit)' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('customers.show', $customer) }}" title="View" class="icon-btn inline-flex" style="color:var(--accent-ink);">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-10 text-center text-sm" style="color:var(--ink-soft);">No customers with an open balance.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Suppliers tab (read-only, synced with the Suppliers module) --}}
        <div x-show="tab === 'suppliers'" x-cloak>
            <div class="flex justify-end mb-4">
                <a href="{{ route('suppliers.create') }}" class="text-sm font-semibold px-4 py-2.5 rounded-md" style="background:var(--accent); color:#FFF;">
                    + New Supplier
                </a>
            </div>

            <div class="rounded-lg overflow-hidden" style="background:var(--surface); border:1px solid var(--line);">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left" style="background:var(--paper); border-bottom:1px solid var(--line);">
                            <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Supplier</th>
                            <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Phone</th>
                            <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Balance ({{ $baseCurrency }})</th>
                            <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Status</th>
                            <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($suppliers as $supplier)
                            @php
                                $bal = (float) $supplier->outstanding_balance;
                                if ($bal > 0) {
                                    $color = 'var(--danger)';   $bg = 'var(--danger-soft)';             $label = 'You owe them';
                                } elseif ($bal < 0) {
                                    $color = 'var(--success)';  $bg = 'var(--success-soft)'; $label = 'Prepaid (credit)';
                                } else {
                                    $color = 'var(--ink-soft)'; $bg = 'var(--success-soft)'; $label = 'Settled';
                                }
                            @endphp
                            <tr style="border-bottom:1px solid var(--line);">
                                <td class="px-4 py-3">
                                    <a href="{{ route('suppliers.show', $supplier) }}" class="font-medium hover:underline">{{ $supplier->name }}</a>
                                </td>
                                <td class="px-4 py-3" style="color:var(--ink-soft);">{{ $supplier->phone ?: '—' }}</td>
                                <td class="px-4 py-3 text-right num font-medium" style="color:{{ $color }};">
                                    {{ number_format(abs($bal), 2) }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge" style="background:{{ $bg }}; color:{{ $color }};">{{ $label }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('suppliers.show', $supplier) }}" title="View supplier details" class="icon-btn inline-flex" style="color:var(--accent-ink);">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-10 text-center text-sm" style="color:var(--ink-soft);">No suppliers yet — add your first one.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Employees tab — fully AJAX-driven --}}
        <div x-show="tab === 'employees'" x-cloak>
            <div class="flex justify-end gap-2 mb-4">
                <button type="button" @click="openEmployeeModal()" class="text-sm px-4 py-2.5 font-semibold rounded-md" style="background:var(--accent); color:#FFF;">
                    Add Employee
                </button>
                <button type="button" @click="openAdvanceModal()" class="text-sm font-semibold px-4 py-2.5 rounded-md" style="background:var(--accent); color:#FFF;">
                    Record Advance / Repayment
                </button>
            </div>

            <div class="rounded-lg overflow-hidden" style="background:var(--surface); border:1px solid var(--line);">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left" style="background:var(--paper); border-bottom:1px solid var(--line);">
                            <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Employee</th>
                            <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Role</th>
                            <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Balance owed to business</th>
                            <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="employees.length === 0">
                            <tr><td colspan="4" class="px-4 py-10 text-center text-sm" style="color:var(--ink-soft);">No employees added yet.</td></tr>
                        </template>
                        <template x-for="employee in employees" :key="employee.id">
                            <tr style="border-bottom:1px solid var(--line);">
                                <td class="px-4 py-3" x-text="employee.name"></td>
                                <td class="px-4 py-3" style="color:var(--ink-soft);" x-text="employee.role || '—'"></td>
                                <td class="px-4 py-3 text-right num font-medium" :style="employee.balance > 0 ? 'color:var(--danger)' : 'color:var(--ink-soft)'" x-text="fmt(employee.balance)"></td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a :href="`${employeeTxnUrlBase}/${employee.id}/transactions`" title="View Transactions" class="icon-btn" style="color:var(--accent-ink);">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m6 10V11m-3 6V4" />
                                            </svg>
                                        </a>
                                        <button type="button" @click="deleteEmployee(employee)" title="Remove" class="icon-btn" style="color:var(--danger);">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Shareholders tab — fully AJAX-driven --}}
        <div x-show="tab === 'shareholders'" x-cloak>
            <div class="flex justify-between items-center mb-4">
                <div class="text-sm" style="color:var(--ink-soft);">
                    Total profit to date:
                    <span class="num font-semibold" :style="totalProfit >= 0 ? 'color:var(--success)' : 'color:var(--danger)'" x-text="fmt(totalProfit) + ' ' + baseCurrency"></span>
                </div>
                <div class="flex gap-2">
                    <button type="button" @click="openShareholderModal()" class="text-sm px-4 py-2.5 font-semibold rounded-md" style="background:var(--accent); color:#FFF;">
                        Add Shareholder
                    </button>
                    <button type="button" @click="openShareholderTxnModal()" class="text-sm font-semibold px-4 py-2.5 rounded-md" style="background:var(--accent); color:#FFF;">
                        Record Investment / Dividend
                    </button>
                </div>
            </div>

            <div class="rounded-lg overflow-hidden mb-6" style="background:var(--surface); border:1px solid var(--line);">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left" style="background:var(--paper); border-bottom:1px solid var(--line);">
                            <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Shareholder</th>
                            <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Role</th>
                            <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Share %</th>
                            <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Total Invested</th>
                            <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Current Profit Share</th>
                            <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="shareholders.length === 0">
                            <tr><td colspan="6" class="px-4 py-10 text-center text-sm" style="color:var(--ink-soft);">No shareholders added yet.</td></tr>
                        </template>
                        <template x-for="shareholder in shareholders" :key="shareholder.id">
                            <tr style="border-bottom:1px solid var(--line);">
                                <td class="px-4 py-3" x-text="shareholder.name"></td>
                                <td class="px-4 py-3" style="color:var(--ink-soft);" x-text="shareholder.role || '—'"></td>
                                <td class="px-4 py-3 text-right num" x-text="Number(shareholder.share_percentage).toFixed(2) + '%'"></td>
                                <td class="px-4 py-3 text-right num" x-text="fmt(shareholder.total_invested)"></td>
                                <td class="px-4 py-3 text-right num font-medium" style="color:var(--success);" x-text="fmt(totalProfit * (shareholder.share_percentage / 100))"></td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a :href="`${shareholderTxnUrlBase}/${shareholder.id}/transactions`" title="View Transactions" class="icon-btn" style="color:var(--accent-ink);">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m6 10V11m-3 6V4" />
                                            </svg>
                                        </a>
                                        <button type="button" @click="deleteShareholder(shareholder)" title="Remove" class="icon-btn" style="color:var(--danger);">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="grid grid-cols-2 gap-4" x-show="shareholders.length > 0">
                <div class="rounded-lg p-4" style="background:var(--surface); border:1px solid var(--line);">
                    <p class="text-sm font-medium mb-3" style="color:var(--ink-soft);">Share distribution</p>
                    <div style="position:relative; height:260px; width:100%;">
                        <canvas id="sharesChart"></canvas>
                    </div>
                </div>
                <div class="rounded-lg p-4" style="background:var(--surface); border:1px solid var(--line);">
                    <p class="text-sm font-medium mb-3" style="color:var(--ink-soft);" x-text="`Profit share by shareholder (${baseCurrency})`"></p>
                    <div style="position:relative; height:260px; width:100%;">
                        <canvas id="profitChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>


{{-- Add Employee modal --}}
<div x-show="employeeModal.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.4);">
    <div class="rounded-lg p-6 w-full max-w-md" style="background:var(--surface); border:1px solid var(--line);">
        <h2 class="ledger-font text-xl mb-4" style="color:var(--ink);">Add Employee</h2>
        <template x-if="employeeModal.error"><p class="text-sm mb-3" style="color:var(--danger);" x-text="employeeModal.error"></p></template>
        <form @submit.prevent="submitEmployee()" class="space-y-3">
            <input type="text" x-model="employeeModal.form.name" placeholder="Full name" required class="field w-full">
            <input type="text" x-model="employeeModal.form.role" placeholder="Role (e.g. Printer Operator)" class="field w-full">
            <input type="text" x-model="employeeModal.form.phone" placeholder="Phone (optional)" class="field w-full">
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="employeeModal.open = false" class="text-sm px-4 py-2 rounded-md" style="border:1px solid var(--line); color:var(--ink-soft);">Cancel</button>
                <button type="submit" :disabled="employeeModal.saving" class="text-sm font-semibold px-4 py-2 rounded-md" style="background:var(--accent); color:#FFF;">
                    <span x-text="employeeModal.saving ? 'Saving...' : 'Add'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Record Advance modal --}}
<div x-show="advanceModal.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.4);">
    <div class="rounded-lg p-6 w-full max-w-md" style="background:var(--surface); border:1px solid var(--line);">
        <h2 class="ledger-font text-xl mb-4" style="color:var(--ink);">Record Advance / Repayment</h2>
        <template x-if="advanceModal.error"><p class="text-sm mb-3" style="color:var(--danger);" x-text="advanceModal.error"></p></template>
        <form @submit.prevent="submitAdvance()" class="space-y-3">
            <select x-model="advanceModal.form.employee_id" required class="field w-full">
                <option value="">Select employee</option>
                <template x-for="employee in employees" :key="employee.id">
                    <option :value="employee.id" x-text="employee.name"></option>
                </template>
            </select>
            <select x-model="advanceModal.form.type" required class="field w-full">
                <option value="given">Gave advance (money out)</option>
                <option value="repayment">Received repayment (money in)</option>
            </select>
            <select x-model="advanceModal.form.account_id" required class="field w-full">
                <option value="">Account</option>
                <template x-for="account in accounts" :key="account.id">
                    <option :value="account.id" x-text="account.name"></option>
                </template>
            </select>
            <input type="number" step="0.01" x-model="advanceModal.form.amount" placeholder="Amount" required class="field w-full">
            <input type="date" x-model="advanceModal.form.advance_date" required class="field w-full">
            <input type="text" x-model="advanceModal.form.notes" placeholder="Note (optional)" class="field w-full">
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="advanceModal.open = false" class="text-sm px-4 py-2 rounded-md" style="border:1px solid var(--line); color:var(--ink-soft);">Cancel</button>
                <button type="submit" :disabled="advanceModal.saving" class="text-sm font-semibold px-4 py-2 rounded-md" style="background:var(--accent); color:#FFF;">
                    <span x-text="advanceModal.saving ? 'Saving...' : 'Save'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Add Shareholder modal --}}
<div x-show="shareholderModal.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.4);">
    <div class="rounded-lg p-6 w-full max-w-md" style="background:var(--surface); border:1px solid var(--line);">
        <h2 class="ledger-font text-xl mb-4" style="color:var(--ink);">Add Shareholder</h2>
        <template x-if="shareholderModal.error"><p class="text-sm mb-3" style="color:var(--danger);" x-text="shareholderModal.error"></p></template>
        <form @submit.prevent="submitShareholder()" class="space-y-3">
            <input type="text" x-model="shareholderModal.form.name" placeholder="Full name" required class="field w-full">
            <input type="text" x-model="shareholderModal.form.role" placeholder="Role in the business (e.g. Co-founder, Investor)" class="field w-full">
            <input type="text" x-model="shareholderModal.form.phone" placeholder="Phone (optional)" class="field w-full">
            <input type="number" step="0.01" min="0.01" max="100" x-model="shareholderModal.form.share_percentage" placeholder="Share % (e.g. 25.00)" required class="field w-full">

            <div style="border-top:1px solid var(--line); padding-top:12px;">
                <p class="text-xs mb-2" style="color:var(--ink-soft);">First-time investment</p>
                <div class="grid grid-cols-2 gap-2">
                    <input type="number" step="0.01" x-model="shareholderModal.form.initial_investment" placeholder="Amount invested" required class="field w-full">
                    <select x-model="shareholderModal.form.currency" required class="field w-full">
                        <template x-for="code in supportedCurrencies" :key="code">
                            <option :value="code" x-text="code"></option>
                        </template>
                    </select>
                </div>
                <div class="mt-2" x-show="shareholderModal.form.currency !== baseCurrency">
                    <input type="number" step="0.000001" x-model="shareholderModal.form.exchange_rate" :placeholder="`Exchange rate to ${baseCurrency}`" class="field w-full">
                </div>
            </div>

            <select x-model="shareholderModal.form.account_id" required class="field w-full">
                <option value="">Account to receive funds</option>
                <template x-for="account in accounts" :key="account.id">
                    <option :value="account.id" x-text="account.name"></option>
                </template>
            </select>
            <input type="date" x-model="shareholderModal.form.invested_at" required class="field w-full">

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="shareholderModal.open = false" class="text-sm px-4 py-2 rounded-md" style="border:1px solid var(--line); color:var(--ink-soft);">Cancel</button>
                <button type="submit" :disabled="shareholderModal.saving" class="text-sm font-semibold px-4 py-2 rounded-md" style="background:var(--accent); color:#FFF;">
                    <span x-text="shareholderModal.saving ? 'Saving...' : 'Add'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Record Shareholder Transaction modal --}}
<div x-show="shareholderTxnModal.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.4);">
    <div class="rounded-lg p-6 w-full max-w-md" style="background:var(--surface); border:1px solid var(--line);">
        <h2 class="ledger-font text-xl mb-4" style="color:var(--ink);">Record Investment / Dividend</h2>
        <template x-if="shareholderTxnModal.error"><p class="text-sm mb-3" style="color:var(--danger);" x-text="shareholderTxnModal.error"></p></template>
        <form @submit.prevent="submitShareholderTxn()" class="space-y-3">
            <select x-model="shareholderTxnModal.form.shareholder_id" required class="field w-full">
                <option value="">Select shareholder</option>
                <template x-for="sh in shareholders" :key="sh.id">
                    <option :value="sh.id" x-text="sh.name"></option>
                </template>
            </select>
            <select x-model="shareholderTxnModal.form.type" required class="field w-full">
                <option value="investment">Investment (money in)</option>
                <option value="dividend">Dividend paid (money out)</option>
            </select>

            <div class="grid grid-cols-2 gap-2">
                <input type="number" step="0.01" x-model="shareholderTxnModal.form.amount" placeholder="Amount" required class="field w-full">
                <select x-model="shareholderTxnModal.form.currency" required class="field w-full">
                    <template x-for="code in supportedCurrencies" :key="code">
                        <option :value="code" x-text="code"></option>
                    </template>
                </select>
            </div>
            <div x-show="shareholderTxnModal.form.currency !== baseCurrency">
                <input type="number" step="0.000001" x-model="shareholderTxnModal.form.exchange_rate" :placeholder="`Exchange rate to ${baseCurrency}`" class="field w-full">
            </div>

            <select x-model="shareholderTxnModal.form.account_id" required class="field w-full">
                <option value="">Account</option>
                <template x-for="account in accounts" :key="account.id">
                    <option :value="account.id" x-text="account.name"></option>
                </template>
            </select>
            <input type="date" x-model="shareholderTxnModal.form.transaction_date" required class="field w-full">
            <input type="text" x-model="shareholderTxnModal.form.notes" placeholder="Note (optional)" class="field w-full">
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="shareholderTxnModal.open = false" class="text-sm px-4 py-2 rounded-md" style="border:1px solid var(--line); color:var(--ink-soft);">Cancel</button>
                <button type="submit" :disabled="shareholderTxnModal.saving" class="text-sm font-semibold px-4 py-2 rounded-md" style="background:var(--accent); color:#FFF;">
                    <span x-text="shareholderTxnModal.saving ? 'Saving...' : 'Save'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
</div>
<script>
function advancePage() {
    const initial = JSON.parse(document.getElementById('advance-page-data').textContent);
    const todayStr = () => new Date().toISOString().slice(0, 10);

    return {
        tab: 'customers',
        flash: null,
        flashError: null,
        employees: initial.employees,
        shareholders: initial.shareholders,
        accounts: initial.accounts,
        totalProfit: initial.totalProfit,
        baseCurrency: initial.baseCurrency,
        supportedCurrencies: initial.supportedCurrencies,
        employeeTxnUrlBase: initial.employeeTxnUrlBase,
        shareholderTxnUrlBase: initial.shareholderTxnUrlBase,
        employeesUrlBase: initial.employeesUrlBase,
        advanceUrlBase: initial.advanceUrlBase,
        shareholdersUrlBase: initial.shareholdersUrlBase,
        sharesChart: null,
        profitChart: null,
        _renderingCharts: false,

        employeeModal: { open: false, saving: false, error: null, form: { name: '', role: '', phone: '' } },
        advanceModal: { open: false, saving: false, error: null, form: { employee_id: '', type: 'given', account_id: '', amount: '', advance_date: todayStr(), notes: '' } },
        shareholderModal: { open: false, saving: false, error: null, form: { name: '', role: '', phone: '', share_percentage: '', initial_investment: '', currency: initial.baseCurrency, exchange_rate: 1, account_id: '', invested_at: todayStr() } },
        shareholderTxnModal: { open: false, saving: false, error: null, form: { shareholder_id: '', type: 'investment', amount: '', currency: initial.baseCurrency, exchange_rate: 1, account_id: '', transaction_date: todayStr(), notes: '' } },

        init() {
            this.$watch('shareholders', () => this.renderCharts());
            this.$watch('totalProfit', () => this.renderCharts());
            this.$watch('tab', (value) => {
                if (value === 'shareholders') {
                    this.$nextTick(() => this.renderCharts());
                }
            });
            if (this.tab === 'shareholders') {
                this.$nextTick(() => this.renderCharts());
            }
        },

        todayStr,

        fmt(n) {
            return Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        csrf() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        },

        async postJson(url, method, body) {
            const res = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrf(),
                },
                body: JSON.stringify(body),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                const message = data.message || (data.errors ? Object.values(data.errors)[0][0] : 'Something went wrong.');
                throw new Error(message);
            }
            return data;
        },

        showFlash(msg) {
            this.flash = msg;
            setTimeout(() => { this.flash = null; }, 3000);
        },
                showError(msg) {
            this.flashError = msg;
            setTimeout(() => { this.flashError = null; }, 5000);
        },

        // Employees
        openEmployeeModal() {
            this.employeeModal.form = { name: '', role: '', phone: '' };
            this.employeeModal.error = null;
            this.employeeModal.open = true;
        },
        async submitEmployee() {
            this.employeeModal.saving = true;
            this.employeeModal.error = null;
            try {
                const data = await this.postJson(this.employeesUrlBase, 'POST', this.employeeModal.form);
                this.employees = data.employees;
                this.employeeModal.open = false;
                this.showFlash('Employee added.');
            } catch (e) {
                this.employeeModal.error = e.message;
            } finally {
                this.employeeModal.saving = false;
            }
        },
        async deleteEmployee(employee) {
            if (!(await pxConfirm('Remove this employee?'))) return;
            try {
                const data = await this.postJson(`${this.employeesUrlBase}/${employee.id}`, 'DELETE', {});
                this.employees = data.employees;
                this.showFlash('Employee removed.');
            } catch (e) {
                                this.showError(e.message);
            }
        },

        // Advances
        openAdvanceModal() {
            this.advanceModal.form = { employee_id: '', type: 'given', account_id: '', amount: '', advance_date: todayStr(), notes: '' };
            this.advanceModal.error = null;
            this.advanceModal.open = true;
        },
        async submitAdvance() {
            this.advanceModal.saving = true;
            this.advanceModal.error = null;
            try {
                const data = await this.postJson(this.advanceUrlBase, 'POST', this.advanceModal.form);
                this.employees = data.employees;
                this.advanceModal.open = false;
                this.showFlash('Recorded.');
            } catch (e) {
                this.advanceModal.error = e.message;
            } finally {
                this.advanceModal.saving = false;
            }
        },

        // Shareholders
        openShareholderModal() {
            this.shareholderModal.form = { name: '', role: '', phone: '', share_percentage: '', initial_investment: '', currency: this.baseCurrency, exchange_rate: 1, account_id: '', invested_at: todayStr() };
            this.shareholderModal.error = null;
            this.shareholderModal.open = true;
        },
        async submitShareholder() {
            this.shareholderModal.saving = true;
            this.shareholderModal.error = null;
            try {
                const form = { ...this.shareholderModal.form };
                if (form.currency === this.baseCurrency) form.exchange_rate = 1;
                const data = await this.postJson(this.shareholdersUrlBase, 'POST', form);
                this.shareholders = data.shareholders;
                this.totalProfit = data.totalProfit;
                this.shareholderModal.open = false;
                this.showFlash('Shareholder added.');
            } catch (e) {
                this.shareholderModal.error = e.message;
            } finally {
                this.shareholderModal.saving = false;
            }
        },
        async deleteShareholder(shareholder) {
            if (!(await pxConfirm('Remove this shareholder?'))) return;
            try {
                const data = await this.postJson(`${this.shareholdersUrlBase}/${shareholder.id}`, 'DELETE', {});
                this.shareholders = data.shareholders;
                this.totalProfit = data.totalProfit;
                this.showFlash('Shareholder removed.');
            } catch (e) {
                                this.showError(e.message);
            }
        },

        // Shareholder transactions
        openShareholderTxnModal() {
            this.shareholderTxnModal.form = { shareholder_id: '', type: 'investment', amount: '', currency: this.baseCurrency, exchange_rate: 1, account_id: '', transaction_date: todayStr(), notes: '' };
            this.shareholderTxnModal.error = null;
            this.shareholderTxnModal.open = true;
        },
        async submitShareholderTxn() {
            this.shareholderTxnModal.saving = true;
            this.shareholderTxnModal.error = null;
            try {
                const form = { ...this.shareholderTxnModal.form };
                if (form.currency === this.baseCurrency) form.exchange_rate = 1;
                const data = await this.postJson(`${this.shareholdersUrlBase}/transactions`, 'POST', form);
                this.shareholders = data.shareholders;
                this.totalProfit = data.totalProfit;
                this.shareholderTxnModal.open = false;
                this.showFlash('Recorded.');
            } catch (e) {
                this.shareholderTxnModal.error = e.message;
            } finally {
                this.shareholderTxnModal.saving = false;
            }
        },

        renderCharts() {
            if (typeof Chart === 'undefined') return;
            if (this.tab !== 'shareholders') return;
            if (this._renderingCharts) return;
            this._renderingCharts = true;

            requestAnimationFrame(() => {
                this._renderingCharts = false;

                const sharesEl = document.getElementById('sharesChart');
                const profitEl = document.getElementById('profitChart');
                if (!sharesEl || !profitEl) return;

                const labels = this.shareholders.map(s => s.name);
                const shares = this.shareholders.map(s => Number(s.share_percentage));
                const profits = this.shareholders.map(s => Math.round(this.totalProfit * (s.share_percentage / 100) * 100) / 100);
                const palette = ['#4C6EF5', '#2FA36B', '#E8590C', '#AE3EC9', '#F08C00', '#1098AD', '#E64980', '#495057'];

                try {
                    if (this.sharesChart) {
                        this.sharesChart.data.labels = labels;
                        this.sharesChart.data.datasets[0].data = shares;
                        this.sharesChart.update();
                        this.sharesChart.resize();
                    } else {
                        this.sharesChart = new Chart(sharesEl, {
                            type: 'pie',
                            data: {
                                labels: labels,
                                datasets: [{ data: shares, backgroundColor: palette }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            generateLabels: (chart) => {
                                                const data = chart.data;
                                                return data.labels.map((label, i) => ({
                                                    text: `${label} — ${Number(data.datasets[0].data[i]).toFixed(2)}%`,
                                                    fillStyle: data.datasets[0].backgroundColor[i],
                                                    strokeStyle: data.datasets[0].backgroundColor[i],
                                                    index: i
                                                }));
                                            }
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: (context) => `${context.label}: ${Number(context.parsed).toFixed(2)}%`
                                        }
                                    },
                                    datalabels: {
                                        color: '#FFF',
                                        font: { weight: 'bold', size: 12 },
                                        formatter: (value, context) => {
                                            const label = context.chart.data.labels[context.dataIndex];
                                            return `${label}\n${Number(value).toFixed(2)}%`;
                                        }
                                    }
                                }
                            }
                        });
                    }

                    if (this.profitChart) {
                        this.profitChart.data.labels = labels;
                        this.profitChart.data.datasets[0].data = profits;
                        this.profitChart.update();
                        this.profitChart.resize();
                    } else {
                        this.profitChart = new Chart(profitEl, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [{ label: 'Profit Share', data: profits, backgroundColor: palette }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    datalabels: { display: false }
                                },
                                scales: { y: { beginAtZero: true } }
                            }
                        });
                    }
                } catch (err) {
                    console.error('Chart render failed:', err);
                }
            });
        },
    };
}
</script>
</x-layouts.pixel>