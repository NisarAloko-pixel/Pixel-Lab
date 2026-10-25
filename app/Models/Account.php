<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\Auditable;

class Account extends Model
{
    use HasFactory, SoftDeletes, Auditable;

protected $fillable = ["name", "type", "opening_balance", "currency", "is_active"];
    protected $casts = [
        "opening_balance" => "decimal:2",
        "is_active" => "boolean",
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /** Opening balance + all income - all expense, for this account only. */
    public function getCurrentBalanceAttribute(): string
    {
        $in = $this->transactions()->whereIn("type", ["income", "transfer_in"])->sum("amount");
        $out = $this->transactions()->whereIn("type", ["expense", "transfer_out"])->sum("amount");

        return bcadd(bcsub($in, $out, 2), $this->opening_balance, 2);
    }
}