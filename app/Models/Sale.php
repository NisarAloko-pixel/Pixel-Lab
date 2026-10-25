<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        "invoice_number", "customer_id", "sold_at", "subtotal", "discount", "tax", "total",
        "cost_total", "amount_paid", "balance", "payment_status", "payment_method",
        "status", "notes", "created_by", 'cancellation_reason',
    ];

    protected $casts = [
        "sold_at" => "datetime",
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, "payable");
    }

    public function creator()
    {
        return $this->belongsTo(User::class, "created_by");
    }

    /** Gross profit for this invoice: revenue recognised minus the cost of what was sold. */
    public function getGrossProfitAttribute(): string
    {
        return bcsub((string) $this->total, (string) $this->cost_total, 2);
    }

    /** True when the invoice has items and every one of them was sold free. */
    public function getIsFreeAttribute(): bool
    {
        return $this->items->isNotEmpty() && $this->items->every(fn ($item) => $item->is_free);
    }
}