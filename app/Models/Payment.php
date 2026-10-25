<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        "reference_number", "paid_at", "amount", "payment_type", "payment_method",
        "payable_type", "payable_id", "notes", "received_by",
    ];

    protected $casts = [
        "paid_at" => "date",
    ];
    public function transactions()
{
    return $this->morphMany(Transaction::class, 'reference');
}

    public function payable()
    {
        return $this->morphTo();
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, "received_by");
    }
}
