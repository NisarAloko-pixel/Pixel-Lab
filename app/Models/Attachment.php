<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = ["attachable_type", "attachable_id", "path", "original_name", "type", "uploaded_by"];

    public function attachable()
    {
        return $this->morphTo();
    }
}
