<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    const UPDATED_AT = null; // append-only ledger, no updates

    protected $fillable = [
        "trackable_type", "trackable_id", "type", "quantity", "unit_cost",
        "reference_type", "reference_id", "notes", "created_by",
    ];

    public function trackable()
    {
        return $this->morphTo();
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, "created_by");
    }
    public function createdBy()
{
    return $this->belongsTo(\App\Models\User::class, 'created_by');
}
}
