<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\Auditable;

class Category extends Model
{
    use HasFactory, Auditable;

    protected $fillable = ["name", "type", "is_active"];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
