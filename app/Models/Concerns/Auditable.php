<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Add `use Auditable;` to a model and every create / update / delete / restore
 * is written to audit_logs with who did it and what changed.
 *
 * Optional per-model setting to keep noisy or sensitive columns out of the log:
 *     protected array $auditExclude = ['some_column'];
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->writeAudit('created', null, $model->auditValues(array_keys($model->getAttributes()), false));
        });

        static::updated(function ($model) {
            $keys = array_keys($model->getChanges());
            $old = $model->auditValues($keys, true);
            $new = $model->auditValues($keys, false);

            // Nothing meaningful changed (e.g. only updated_at)
            if (empty($new)) {
                return;
            }

            $model->writeAudit('updated', $old, $new);
        });

        static::deleted(function ($model) {
            $model->writeAudit('deleted', $model->auditValues(array_keys($model->getAttributes()), false), null);
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class), true)) {
            static::restored(function ($model) {
                $model->writeAudit('restored', null, null);
            });
        }
    }

    protected function auditSkipped(): array
    {
        $own = property_exists($this, 'auditExclude') ? $this->auditExclude : [];

        return array_merge(
            ['password', 'remember_token', 'created_at', 'updated_at', 'deleted_at'],
            $own
        );
    }

    /** Values for the given columns, minus anything excluded. $original = read the pre-change value. */
    protected function auditValues(array $keys, bool $original): array
    {
        $values = [];

        foreach ($keys as $key) {
            if (in_array($key, $this->auditSkipped(), true)) {
                continue;
            }
            $values[$key] = $original ? $this->getOriginal($key) : $this->getAttribute($key);
        }

        return $values;
    }

    protected function writeAudit(string $action, ?array $old, ?array $new): void
    {
        // Logging must never break the real action (a sale, an expense, ...).
        try {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'auditable_type' => static::class,
                'auditable_id' => $this->getKey(),
                'old_values' => $old ?: null,
                'new_values' => $new ?: null,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}