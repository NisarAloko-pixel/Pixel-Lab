<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShareholderTransaction extends Model
{
    protected $fillable = [
        'shareholder_id', 'type', 'amount', 'currency', 'original_amount',
        'exchange_rate', 'transaction_date', 'account_id', 'transaction_id',
        'notes', 'created_by',
    ];

    public function shareholder()
    {
        return $this->belongsTo(Shareholder::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}