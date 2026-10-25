@php
    $embed = request()->boolean('embed');
    $payStatus = strtolower($sale->payment_status);
    $isCancelled = strtolower($sale->status) === 'cancelled';
    $hasLogo = !empty($business['logoUrl']);
    $dark = request('theme') === 'dark';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $sale->invoice_number }}</title>
    <style>
        /* restored :root */
        :root{
            --ink:#2A2521; --text:#4A443E; --soft:#7A7268; --line:#E6E0D6;
            --wash:#F6F3EC; --paper:#FFFFFF; --page:#EDEBE6;
            --brand:#A8571E; --brand-dk:#7A3E13; --brand-lt:#F3E4D2; --brand-tx:#A8571E;
            --ok:#2F6B49; --ok-lt:#E7F1EA; --ok-solid:#2E7D55;
            --warn:#8A6A12; --warn-lt:#FBF1DC;
            --bad:#A13D2E; --bad-lt:#FBEDEA;
        }

        * { box-sizing: border-box; }
        html { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        body {
            margin: 0;
            padding: 28px 16px;
            background: var(--page);
            color: var(--text);
            font-family: "Segoe UI", system-ui, -apple-system, "Helvetica Neue", Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
        }
        body.embed { background: var(--paper); padding: 0; }

        /* ---------- toolbar (standalone view only) ---------- */
        .toolbar { max-width: 820px; margin: 0 auto 14px; display: flex; gap: 8px; justify-content: flex-end; }
        .toolbar a, .toolbar button {
            font: inherit; font-size: 13px; font-weight: 600;
            padding: 8px 16px; border-radius: 8px; cursor: pointer; text-decoration: none;
            border: 1px solid var(--line); background: var(--paper); color: var(--ink);
        }
        .toolbar .primary { background: var(--brand); border-color: var(--brand); color: #fff; }
        .toolbar a:focus-visible, .toolbar button:focus-visible { outline: 3px solid var(--brand-lt); outline-offset: 1px; }

        /* ---------- sheet ---------- */
        .sheet {
            position: relative;
            max-width: 820px;
            margin: 0 auto;
            background: var(--paper);
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(42,37,33,.08), 0 8px 30px rgba(42,37,33,.10);
        }
        body.embed .sheet { box-shadow: none; border-radius: 0; max-width: none; }

        .band { height: 8px; background: linear-gradient(90deg, var(--brand-dk), var(--brand)); }
        .inner { padding: 40px 48px 32px; position: relative; }

        /* ---------- header ---------- */
        .head { display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; }
        .brand { max-width: 62%; display: flex; align-items: center; gap: 16px; }
        .logo { display: block; max-height: 96px; max-width: 190px; object-fit: contain; flex-shrink: 0; }
        .brand-name { font-family: Georgia, "Palatino Linotype", "Book Antiqua", Palatino, serif; margin: 0 0 4px; font-size: 22px; font-weight: 700; color: var(--ink); letter-spacing: -.01em; }
        .brand-name.small { font-size: 26px; font-weight: 700; letter-spacing: 0; margin: 0; padding-left: 16px; border-left: 2px solid var(--line); line-height: 1.3; }
        .brand-line { color: var(--soft); font-size: 13px; }

        .doc { text-align: right; }
        .doc-title { margin: 0; font-size: 40px; line-height: 1; font-weight: 300; color: var(--brand-tx); letter-spacing: -.02em; }
        .doc-number { margin-top: 8px; font-size: 15px; font-weight: 600; color: var(--ink); font-variant-numeric: tabular-nums; }

        /* ---------- info blocks ---------- */
        .info {
            display: grid; grid-template-columns: 1.3fr 1fr; gap: 20px;
            margin-top: 34px; padding: 22px 24px;
            background: var(--wash); border-radius: 10px;
        }
        .label { font-size: 12px; font-weight: 600; color: var(--soft); margin-bottom: 4px; }
        .party-name { font-size: 16px; font-weight: 700; color: var(--ink); }
        .party-line { color: var(--text); font-size: 13px; }

        .facts { display: grid; grid-template-columns: auto 1fr; gap: 6px 18px; font-size: 13px; align-content: start; }
        .facts dt { color: var(--soft); }
        .facts dd { margin: 0; text-align: right; color: var(--ink); font-weight: 600; font-variant-numeric: tabular-nums; }

        .badge { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .badge.paid    { background: var(--ok-lt);   color: var(--ok); }
        .badge.partial { background: var(--warn-lt); color: var(--warn); }
        .badge.unpaid  { background: var(--bad-lt);  color: var(--bad); }

        /* ---------- items ---------- */
        table.items { width: 100%; border-collapse: collapse; margin-top: 30px; }
        .items thead th {
            background: var(--brand); color: #fff;
            font-size: 12px; font-weight: 600; text-align: left;
            padding: 11px 12px;
        }
        .items thead th:first-child { border-radius: 8px 0 0 8px; }
        .items thead th:last-child  { border-radius: 0 8px 8px 0; }
        .items td { padding: 14px 12px; border-bottom: 1px solid var(--line); vertical-align: top; }
        .items tbody tr:last-child td { border-bottom: 2px solid var(--line); }
        .items tr { break-inside: avoid; page-break-inside: avoid; }
        .items .idx { color: var(--soft); width: 32px; }
        .item-name { font-weight: 600; color: var(--ink); }
        .item-extra { color: var(--soft); font-size: 12px; margin-top: 2px; }
        .num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
        .items th.num { text-align: right; }

        /* "Sold free" label */
        .free-tag {
            display: inline-block; margin-left: 8px; padding: 1px 9px;
            border-radius: 999px; font-size: 11px; font-weight: 700;
            letter-spacing: .04em; text-transform: uppercase; vertical-align: middle;
            background: var(--ok-lt); color: var(--ok); border: 1px solid var(--ok);
        }
        .items tr.is-free .num { color: var(--soft); }

        /* ---------- summary ---------- */
        .summary { display: flex; justify-content: space-between; align-items: flex-start; gap: 32px; margin-top: 26px; }
        .payments { flex: 1; max-width: 340px; }
        .payments h3 { margin: 0 0 8px; font-size: 13px; font-weight: 700; color: var(--ink); }
        .pay-row { display: flex; justify-content: space-between; gap: 12px; padding: 7px 0; border-bottom: 1px dashed var(--line); font-size: 13px; }
        .pay-row .when { color: var(--soft); }
        .pay-row .amt { font-weight: 600; color: var(--ink); font-variant-numeric: tabular-nums; }

        .totals { width: 300px; }
        .t-row { display: flex; justify-content: space-between; padding: 5px 0; font-size: 13.5px; }
        .t-row span:first-child { color: var(--soft); }
        .t-row span:last-child { color: var(--ink); font-variant-numeric: tabular-nums; }
        .t-row.total { margin-top: 6px; padding-top: 12px; border-top: 1px solid var(--line); font-size: 16px; font-weight: 700; }
        .t-row.total span:first-child { color: var(--ink); }

        .balance {
            margin-top: 12px; padding: 14px 16px; border-radius: 10px;
            display: flex; justify-content: space-between; align-items: center;
            background: var(--brand); color: #fff;
        }
        .balance.settled { background: var(--ok-solid); }
        .balance .b-label { font-size: 13px; font-weight: 600; opacity: .9; }
        .balance .b-value { font-size: 22px; font-weight: 700; font-variant-numeric: tabular-nums; }

        /* ---------- notes / footer ---------- */
        .notes { margin-top: 30px; padding: 14px 16px; border-left: 3px solid var(--brand); background: var(--brand-lt); border-radius: 0 8px 8px 0; font-size: 13px; }
        .notes strong { display: block; color: var(--brand-tx); margin-bottom: 2px; }

        .foot { margin-top: 44px; padding-top: 18px; border-top: 1px solid var(--line); display: flex; justify-content: space-between; gap: 20px; font-size: 12.5px; color: var(--soft); }
        .foot .thanks { color: var(--ink); font-weight: 600; font-size: 14px; }
        .foot .contact { text-align: right; }

        /* ---------- cancelled watermark ---------- */
        .watermark {
            position: absolute; top: 46%; left: 50%;
            transform: translate(-50%, -50%) rotate(-24deg);
            font-size: 96px; font-weight: 800; letter-spacing: .06em;
            color: rgba(180, 35, 24, .12); border: 8px solid rgba(180, 35, 24, .12);
            padding: 0 28px; border-radius: 14px; pointer-events: none; user-select: none;
        }

        /* ---------- dark theme (screen only; printing is always light) ---------- */
        @media screen {
            body.dark {
                color-scheme: dark;
                --ink: #F3EFE8; --text: #D2CCC2; --soft: #9A9388; --line: #343947;
                --wash: #272C39; --paper: #1E222D; --page: #14171F;
                --brand-tx: #E58F52; --brand-lt: #33261D;
                --ok: #7BD6A3; --ok-lt: #1C3A2A;
                --warn: #EBC65E; --warn-lt: #3B3212;
                --bad: #FF9D93; --bad-lt: #472320;
            }
            body.dark .logo { background: #fff; padding: 8px 12px; border-radius: 10px; }
            body.dark .watermark { color: rgba(255,130,120,.16); border-color: rgba(255,130,120,.16); }
        }

        /* ---------- small screens ---------- */
        @media (max-width: 640px) {
            .inner { padding: 28px 20px 24px; }
            .head, .summary, .foot { flex-direction: column; }
            .brand { max-width: 100%; flex-wrap: wrap; }
            .doc, .foot .contact { text-align: left; }
            .info { grid-template-columns: 1fr; }
            .totals, .payments { width: 100%; max-width: none; }
            .watermark { font-size: 54px; }
        }

        /* ---------- print ---------- */
        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .sheet { box-shadow: none; border-radius: 0; max-width: none; }
            .inner { padding: 8px 4px 0; }
            @page { size: A4; margin: 12mm; }
        }
    </style>
</head>
<body class="{{ $embed ? 'embed' : '' }} {{ $dark ? 'dark' : '' }}">

@unless ($embed)
<div class="toolbar">
    <a href="{{ route('sales.show', $sale) }}">&larr; Back</a>
    <button type="button" class="primary" onclick="window.print()">Print invoice</button>
</div>
@endunless

<div class="sheet">
    <div class="band"></div>

    <div class="inner">
        @if ($isCancelled)
            <div class="watermark">CANCELLED</div>
        @endif

        {{-- Header --}}
        <div class="head">
            <div class="brand">
                @if ($hasLogo)
                    <img src="{{ $business['logoUrl'] }}" alt="Logo" class="logo">
                @endif

                @if (!empty($business['name']))
                    <h1 class="brand-name {{ $hasLogo ? 'small' : '' }}">{{ $business['name'] }}</h1>
                @elseif (!$hasLogo)
                    <h1 class="brand-name">{{ config('app.name') }}</h1>
                @endif

            </div>

            <div class="doc">
                <h2 class="doc-title">Invoice</h2>
                <div class="doc-number">{{ $sale->invoice_number }}</div>
            </div>
        </div>

        {{-- Billed to + facts --}}
        <div class="info">
            <div>
                <div class="label">Billed to</div>
                <div class="party-name">{{ $sale->customer->name ?? 'Walk-in customer' }}</div>
                @if (!empty($sale->customer?->phone))<div class="party-line">{{ $sale->customer->phone }}</div>@endif
                @if (!empty($sale->customer?->email))<div class="party-line">{{ $sale->customer->email }}</div>@endif
                @if (!empty($sale->customer?->address))<div class="party-line">{{ $sale->customer->address }}</div>@endif
            </div>

            <dl class="facts">
                <dt>Issued</dt>
                <dd>{{ $sale->sold_at->format('d M Y') }}</dd>
                <dt>Time</dt>
                <dd>{{ $sale->sold_at->format('H:i') }}</dd>
                <dt>Payment</dt>
                <dd>
                   <span class="badge {{ $sale->is_free ? 'paid' : (in_array($payStatus, ['paid','partial']) ? $payStatus : 'unpaid') }}">
    {{ $sale->is_free ? 'Free' : ucfirst($sale->payment_status) }}
</span>
                </dd>
            </dl>
        </div>

        {{-- Items --}}
        <table class="items">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th class="num">Qty</th>
                    <th class="num">Unit price</th>
                    <th class="num">Discount</th>
                    <th class="num">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->items as $i => $item)
                    <tr class="{{ $item->is_free ? 'is-free' : '' }}">
                        <td class="idx">{{ $i + 1 }}</td>
                        <td>
                            <div class="item-name">
                                {{ $item->description }}
                                @if ($item->is_free)
                                    <span class="free-tag">Sold free</span>
                                @endif
                            </div>
                            @if ($item->extra_items)
                                <div class="item-extra">+ {{ collect($item->extra_items)->pluck('name')->filter()->implode(', ') }}</div>
                            @endif
                        </td>
                        <td class="num">{{ rtrim(rtrim(number_format($item->quantity, 3), '0'), '.') }}</td>
                        <td class="num">{{ $item->is_free ? 'Free' : number_format($item->unit_price, 2) }}</td>
                        <td class="num">{{ $item->is_free ? '—' : number_format($item->discount, 2) }}</td>
                        <td class="num"><strong style="color:var(--ink);">{{ $item->is_free ? '0.00' : number_format($item->line_total, 2) }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Payments + totals --}}
        <div class="summary">
            <div class="payments">
                @if ($sale->payments->isNotEmpty())
                    <h3>Payments received</h3>
                    @foreach ($sale->payments as $payment)
                        <div class="pay-row">
                            <span class="when">{{ $payment->paid_at->format('d M Y') }} &middot; {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
                            <span class="amt">{{ number_format($payment->amount, 2) }}</span>
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="totals">
                <div class="t-row"><span>Subtotal</span><span>{{ number_format($sale->subtotal, 2) }}</span></div>
                <div class="t-row"><span>Discount</span><span>-{{ number_format($sale->discount, 2) }}</span></div>
                <div class="t-row"><span>Tax</span><span>+{{ number_format($sale->tax, 2) }}</span></div>
                <div class="t-row total"><span>Total</span><span>{{ number_format($sale->total, 2) }}</span></div>
                <div class="t-row"><span>Paid</span><span>{{ number_format($sale->amount_paid, 2) }}</span></div>

                <div class="balance {{ $sale->balance > 0 ? '' : 'settled' }}">
                    <span class="b-label">{{ $sale->balance > 0 ? 'Balance due' : 'Paid in full' }}</span>
                    <span class="b-value">{{ number_format($sale->balance, 2) }}</span>
                </div>
            </div>
        </div>

        @if ($sale->notes)
            <div class="notes">
                <strong>Notes</strong>
                {{ $sale->notes }}
            </div>
        @endif

        {{-- Footer --}}
        <div class="foot">
            <div>
                <div class="thanks">Thank you for your business.</div>
                <div>Please keep this invoice for your records.</div>
            </div>
            <div class="contact">
                @if (!empty($business['address']))<div>{{ $business['address'] }}</div>@endif
                @if (!empty($business['email']))<div>{{ $business['email'] }}</div>@endif
                @if (!empty($business['phone']))<div>{{ $business['phone'] }}</div>@endif
            </div>
        </div>
    </div>
</div>

</body>
</html>