<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductFilament extends Model


{
     use SoftDeletes;
    protected $fillable = ['product_id', 'filament_inventory_id', 'grams_used', 'waste_grams'];

    protected $casts = [
        'grams_used' => 'decimal:3',
        'waste_grams' => 'decimal:3',
    ];

    public function filament()
    {
        return $this->belongsTo(FilamentInventory::class, 'filament_inventory_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}