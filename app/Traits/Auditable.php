<?php

namespace App\Traits;

use App\Models\AuditLog;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            static::logAuditEvent('created', $model, null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $oldValues = array_intersect_key($model->getOriginal(), $model->getChanges());
            $newValues = $model->getChanges();
            
            // Remove sensitive password hashes from audit logs
            unset($oldValues['password'], $newValues['password']);

            if (! empty($newValues)) {
                static::logAuditEvent('updated', $model, $oldValues, $newValues);
            }
        });

        static::deleted(function ($model) {
            static::logAuditEvent('deleted', $model, $model->getAttributes(), null);
        });
    }

    protected static function logAuditEvent(string $event, $model, ?array $oldValues, ?array $newValues): void
    {
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            // Optional: allow console commands or skip unless running in test/web
        }

        AuditLog::create([
            'company_id' => auth()->check() ? auth()->user()->company_id : ($model->company_id ?? null),
            'user_id' => auth()->id(),
            'event' => $event,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent() ?? 'System',
        ]);
    }
}
