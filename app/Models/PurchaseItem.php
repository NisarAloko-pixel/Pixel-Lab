<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [
        "purchase_id", "purchasable_type", "purchasable_id", "description",
        "quantity", "unit_cost", "line_total",
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function purchasable()
    {
        return $this->morphTo();
    }
}
