<?php

namespace App\Mail;

use App\Models\Expense;
use App\Models\Purchase;
use App\Models\Sale;
use App\Services\FinancialService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $sections = [];

    public function __construct(
        public array $types,
        public Carbon $periodFrom,
        public Carbon $periodTo,
    ) {
        $financial = app(FinancialService::class);

        foreach ($types as $type) {
            $this->sections[$type] = match ($type) {
                'sale' => [
                    'title' => 'Sales report',
                    'sales' => Sale::with(['customer', 'items'])
                        ->where('status', 'completed')
                        ->whereBetween('sold_at', [$periodFrom, $periodTo])
                        ->latest('sold_at')
                        ->get(),
                ],
                'profit' => [
                    'title' => 'Profit report',
                    'summary' => $financial->summary($periodFrom, $periodTo),
                ],
                'product_profit' => [
                    'title' => 'Product profit',
                    'rows' => $financial->productProfitReport($periodFrom, $periodTo),
                ],
                'expense' => [
                    'title' => 'Expenses report',
                    'expenses' => Expense::with('category')
                        ->whereBetween('expense_date', [$periodFrom, $periodTo])
                        ->latest('expense_date')
                        ->get(),
                ],
                'purchase' => (function () use ($periodFrom, $periodTo) {
                    // Same rules as the dashboard: skip cancelled, filter on order_date (falling back to created_at).
                    $purchases = Purchase::with('supplier')
                        ->where(fn ($q) => $q->whereNull('status')->orWhere('status', '!=', 'cancelled'))
                        ->whereBetween(DB::raw('date(COALESCE(order_date, created_at))'), [$periodFrom->toDateString(), $periodTo->toDateString()])
                        ->get()
                        ->sortByDesc(fn ($p) => $p->order_date ?? $p->created_at)
                        ->values();

                    $total = (float) $purchases->sum(fn ($p) => (float) $p->total_cost_base);
                    $due   = (float) $purchases->sum(fn ($p) => (float) $p->amount_due_base);

                    return [
                        'title'     => 'Purchases report',
                        'purchases' => $purchases,
                        'total'     => $total,
                        'due'       => $due,
                        'paid'      => $total - $due,
                    ];
                })(),
                'transaction' => [
                    'title' => 'Transaction report',
                    'note' => 'Transaction report source not wired up yet.',
                ],
                default => ['title' => ucfirst($type)],
            };
        }
    }

    public function build()
    {
        return $this->subject('Your report: ' . $this->periodFrom->format('M j') . ' – ' . $this->periodTo->format('M j, Y'))
            ->view('emails.report')
            ->with([
                'sections' => $this->sections,
                'from' => $this->periodFrom,
                'to' => $this->periodTo,
            ]);
    }
}