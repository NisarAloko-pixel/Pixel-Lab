<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\Auditable;

class Advance extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        "party_type", "party_id", "type", "amount", "advance_date",
        "account_id", "notes", "created_by",
    ];

    protected $casts = [
        "advance_date" => "date",
        "amount" => "decimal:2",
    ];

    public function party()
    {
        return $this->morphTo();
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, "created_by");
    }

    public function transaction()
    {
        return $this->morphOne(Transaction::class, "reference");
    }
}