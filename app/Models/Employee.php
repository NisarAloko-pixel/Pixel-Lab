<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\Auditable;

class Employee extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = ["name", "role", "phone", "user_id", "is_active"];

    protected $casts = [
        "is_active" => "boolean",
    ];

    protected $appends = ["balance"];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function advances()
    {
        return $this->morphMany(Advance::class, "party");
    }

    /** Positive = employee owes the business (took more than they repaid). */
    public function getBalanceAttribute(): string
    {
        $given = $this->advances()->where("type", "given")->sum("amount");
        $repaid = $this->advances()->where("type", "repayment")->sum("amount");

        return bcsub($given, $repaid, 2);
    }
}