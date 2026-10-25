<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Every write to a financial or inventory-affecting record should go through here.
 * Rows are append-only: we never update or delete an audit_logs row.
 */
class AuditService
{
    public function log(string $action, ?Model $subject = null, ?array $oldValues = null, ?array $newValues = null): void
    {
        AuditLog::create([
            "user_id" => Auth::id(),
            "action" => $action,
            "auditable_type" => $subject ? get_class($subject) : null,
            "auditable_id" => $subject?->getKey(),
            "old_values" => $oldValues,
            "new_values" => $newValues,
        ]);
    }
}
