<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\Auditable;

class PrintingJob extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    const STATUSES = ["new", "quoted", "approved", "printing", "completed", "delivered", "cancelled"];

    protected $fillable = [
        "job_number", "customer_id", "name", "customer_request", "reference_image_path",
        "model_file_path", "material", "filament_type", "filament_inventory_id",
        "filament_weight_used_g", "print_time_hours", "machine", "material_cost",
        "electricity_cost", "labor_cost", "other_cost", "total_cost", "selling_price",
        "profit", "status", "notes", "created_by",
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function filament()
    {
        return $this->belongsTo(FilamentInventory::class, "filament_inventory_id");
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    /** Recompute cost/profit fields from their components. Call before save whenever a cost changes. */
    public function recalculate(): void
    {
        $this->total_cost = bcadd(
            bcadd((string) $this->material_cost, (string) $this->electricity_cost, 2),
            bcadd((string) $this->labor_cost, (string) $this->other_cost, 2),
            2
        );
        $this->profit = bcsub((string) $this->selling_price, $this->total_cost, 2);
    }
}
