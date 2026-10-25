@php($appCurrency = \App\Models\Setting::get('currency', 'AF'))
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Report — {{ $from->format('M j, Y') }} to {{ $to->format('M j, Y') }}</title>
<style>
  /* restored :root */
  :root{
    --paper:#FFFFFF; --ink:#21262F; --ink-soft:#6B7080; --line:#E3DFD3;
    --profit:#3D6B4F; --expense:#A13D2E;
  }

  * { box-sizing: border-box; }

  body {
    margin: 0;
    background: #EDEBE6;
    color: var(--ink);
    font-family: 'IBM Plex Sans', -apple-system, sans-serif;
    font-size: 14px;
    line-height: 1.55;
  }

  .sheet {
    max-width: 760px;
    margin: 48px auto;
    background: var(--paper);
    padding: 56px 60px 64px;
    border: 1px solid var(--line);
  }

  .report-header {
    padding-bottom: 24px;
    border-bottom: 2px solid var(--ink);
    margin-bottom: 8px;
  }

  .report-header h1 {
    font-family: 'Source Serif 4', Georgia, serif;
    font-weight: 600;
    font-size: 30px;
    margin: 0 0 6px;
    letter-spacing: -0.01em;
  }

  .report-header .period {
    color: var(--ink-soft);
    font-size: 14px;
  }

  .period .divider {
    margin: 0 8px;
    color: var(--line);
  }

  section.block {
    padding: 28px 0;
    border-bottom: 1px solid var(--line);
  }

  section.block:last-of-type {
    border-bottom: none;
  }

  section.block h2 {
    font-family: 'Source Serif 4', Georgia, serif;
    font-weight: 600;
    font-size: 18px;
    margin: 0 0 4px;
  }

  .block-note {
    color: var(--ink-soft);
    font-size: 13px;
    margin: 0 0 16px;
  }

  table.ledger {
    width: 100%;
    border-collapse: collapse;
    font-variant-numeric: tabular-nums;
  }

  table.ledger th {
    text-align: left;
    font-weight: 500;
    font-size: 12px;
    color: var(--ink-soft);
    padding: 0 0 8px;
    border-bottom: 1px solid var(--line);
  }

  table.ledger th.num,
  table.ledger td.num {
    text-align: right;
  }

  table.ledger td {
    padding: 9px 0;
    border-bottom: 1px solid #EAE7E0;
    font-size: 13.5px;
  }

  table.ledger tr:last-child td {
    border-bottom: none;
  }

  table.ledger td.id {
    color: var(--ink-soft);
    width: 70px;
  }

  table.ledger td.date {
    color: var(--ink-soft);
    text-align: right;
    width: 110px;
  }

  /* Profit summary — table-based (not flex) for PDF renderer compatibility */
  table.summary {
    width: 100%;
    border-collapse: collapse;
  }

  table.summary td {
    padding: 10px 0;
    border-bottom: 1px solid #EAE7E0;
  }

  table.summary tr:last-child td {
    border-bottom: none;
  }

  table.summary td.label {
    color: var(--ink-soft);
    font-size: 13.5px;
  }

  table.summary td.value {
    text-align: right;
    font-variant-numeric: tabular-nums;
    font-size: 15px;
  }

  table.summary tr.net td {
    border-bottom: none;
    border-top: 1px solid var(--ink);
    padding-top: 14px;
  }

  table.summary tr.net td.label {
    color: var(--ink);
    font-weight: 500;
  }

  table.summary tr.net td.value {
    font-size: 19px;
    font-weight: 600;
    color: var(--profit);
  }

  .expense-amount {
    color: var(--expense);
  }

  .empty-note {
    color: var(--ink-soft);
    font-style: italic;
    font-size: 13.5px;
  }

  @media print {
    body { background: var(--paper); }
    .sheet { margin: 0; border: none; padding: 0 8mm; max-width: none; }
    section.block { break-inside: avoid; }
  }
</style>
</head>
<body>
<div class="sheet">

  <div class="report-header">
    <h1>Business Report</h1>
    <div class="period">
      {{ $from->format('M j, Y') }}<span class="divider">–</span>{{ $to->format('M j, Y') }}
    </div>
  </div>

  @foreach ($sections as $type => $section)
    <section class="block">
      <h2>{{ $section['title'] }}</h2>

      @if ($type === 'sale')
        <p class="block-note">{{ $section['sales']->count() }} completed sale{{ $section['sales']->count() === 1 ? '' : 's' }}</p>
        @if ($section['sales']->count())
          <table class="ledger">
            <thead>
              <tr>
                <th style="width:70px">Sale</th>
                <th>Customer</th>
                <th class="num" style="width:110px">Date</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($section['sales'] as $sale)
                <tr>
                  <td class="id">#{{ $sale->id }}</td>
                  <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                  <td class="date">{{ $sale->sold_at->format('M j, Y') }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        @else
          <p class="empty-note">No sales recorded in this period.</p>
        @endif

      @elseif ($type === 'profit')
        <table class="summary">
          <tr>
            <td class="label">Revenue</td>
            <td class="value">${{ number_format($section['summary']['revenue'], 2) }}</td>
          </tr>
          <tr>
            <td class="label">Gross profit</td>
            <td class="value">${{ number_format($section['summary']['gross_profit'], 2) }}</td>
          </tr>
          <tr class="net">
            <td class="label">Net profit</td>
            <td class="value">${{ number_format($section['summary']['net_profit'], 2) }}</td>
          </tr>
        </table>

      @elseif ($type === 'product_profit')
        @if (count($section['rows']))
          <table class="ledger">
            <thead>
              <tr>
                <th>Product</th>
                <th class="num" style="width:120px">Profit</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($section['rows'] as $row)
                <tr>
                  <td>{{ data_get($row, 'name', '') }}</td>
                  <td class="num @if(data_get($row, 'profit', 0) < 0) expense-amount @endif">${{ number_format(data_get($row, 'profit', 0), 2) }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        @else
          <p class="empty-note">No product profit data for this period.</p>
        @endif

      @elseif ($type === 'expense')
        <p class="block-note">{{ $section['expenses']->count() }} expense{{ $section['expenses']->count() === 1 ? '' : 's' }}</p>
        @if ($section['expenses']->count())
          <table class="ledger">
            <thead>
              <tr>
                <th>Category</th>
                <th class="num" style="width:100px">Amount</th>
                <th class="num" style="width:110px">Date</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($section['expenses'] as $expense)
                <tr>
                  <td>{{ $expense->category->name ?? '' }}</td>
                  <td class="num expense-amount">${{ number_format($expense->amount, 2) }}</td>
                  <td class="date">{{ $expense->expense_date->format('M j, Y') }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        @else
          <p class="empty-note">No expenses recorded in this period.</p>
        @endif

      @elseif ($type === 'purchase')
        <p class="block-note">{{ $section['purchases']->count() }} purchase{{ $section['purchases']->count() === 1 ? '' : 's' }}</p>
        @if ($section['purchases']->count())
          <table class="ledger">
            <thead>
              <tr>
                <th style="width:60px">#</th>
                <th>Item</th>
                <th>Supplier</th>
                <th class="num" style="width:110px">Total</th>
                <th class="num" style="width:100px">Date</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($section['purchases'] as $purchase)
                <tr>
                  <td class="id">#{{ $purchase->id }}</td>
                  <td>{{ $purchase->displayName() }}</td>
                  <td>{{ $purchase->supplier->name ?? 'No supplier' }}</td>
                  <td class="num">{{ number_format($purchase->total_cost_base, 2) }} {{ $appCurrency }}</td>
                  <td class="date">{{ ($purchase->order_date ?? $purchase->created_at)?->format('M j, Y') }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>

          <table class="summary" style="margin-top:16px;">
            <tr>
              <td class="label">Total purchased</td>
              <td class="value">{{ number_format($section['total'], 2) }} {{ $appCurrency }}</td>
            </tr>
            <tr>
              <td class="label">Paid</td>
              <td class="value">{{ number_format($section['paid'], 2) }} {{ $appCurrency }}</td>
            </tr>
            <tr class="net">
              <td class="label">Owed to suppliers</td>
              <td class="value expense-amount">{{ number_format($section['due'], 2) }} {{ $appCurrency }}</td>
            </tr>
          </table>
        @else
          <p class="empty-note">No purchases recorded in this period.</p>
        @endif

      @else
        <p class="block-note">{{ $section['note'] ?? '' }}</p>
      @endif
    </section>
  @endforeach

</div>
</body>
</html>