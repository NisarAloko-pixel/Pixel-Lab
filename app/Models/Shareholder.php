<?php

namespace App\Models;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;

class Shareholder extends Model
{
    protected $fillable = ['name', 'role', 'phone', 'share_percentage', 'is_active'];

    public function transactions()
    {
        return $this->hasMany(ShareholderTransaction::class);
    }

    // Sum of all money the shareholder has put in (base currency)
    public function getTotalInvestedAttribute(): float
    {
        return (float) $this->transactions()->where('type', 'investment')->sum('amount');
    }

    // Sum of all dividends already paid out to them
    public function getTotalDividendsPaidAttribute(): float
    {
        return (float) $this->transactions()->where('type', 'dividend')->sum('amount');
    }

    // Their cut of total business profit, based on share %
    public function profitShare(float $totalProfit): float
    {
        return $totalProfit * ($this->share_percentage / 100);
    }

    // Profit share still available to withdraw (their cut minus what's already been paid out)
    public function availableProfitShare(float $totalProfit): float
    {
        return max(0, $this->profitShare($totalProfit) - $this->total_dividends_paid);
    }
}