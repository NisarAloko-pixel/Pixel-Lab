<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\Auditable;

class Expense extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
    "expense_date", "expense_category_id", "account_id", "description", "amount",
    "currency", "exchange_rate", "original_amount",
    "payment_method", "supplier_id", "notes", "created_by",
];

protected $casts = [
    "expense_date" => "date",
    "exchange_rate" => "decimal:6",
    "original_amount" => "decimal:2",
];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, "expense_category_id");
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, "created_by");
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, "attachable");
    }

    public function transaction()
    {
        return $this->morphOne(Transaction::class, "reference");
    }
}