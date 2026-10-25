<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\Auditable;

class Customer extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = ["name", "phone", "email", "address", "company", "notes"];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function printingJobs()
    {
        return $this->hasMany(PrintingJob::class);
    }

    /** Sum of all non-cancelled sale totals. */
    public function getTotalPurchasesAttribute(): string
    {
        return (string) $this->sales()->where("status", "!=", "cancelled")->sum("total");
    }

    /** Sum of all payments actually received against this customer's sales. */
    public function getTotalPaidAttribute(): string
    {
        return (string) $this->sales()->where("status", "!=", "cancelled")->sum("amount_paid");
    }

    /** Outstanding balance = total sold - total paid, always derived, never stored on the customer. */
    public function getOutstandingBalanceAttribute(): string
    {
        return bcsub($this->total_purchases, $this->total_paid, 2);
    }
}
