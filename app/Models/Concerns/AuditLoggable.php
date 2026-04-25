<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

trait AuditLoggable
{
    protected static function bootAuditLoggable(): void
    {
        static::created(function (Model $model): void {
            self::logActivity($model, 'created');
        });

        static::updated(function (Model $model): void {
            self::logActivity($model, 'updated', [
                'old' => array_intersect_key($model->getOriginal(), $model->getDirty()),
                'new' => $model->getDirty(),
            ]);
        });

        static::deleted(function (Model $model): void {
            self::logActivity($model, 'deleted');
        });
    }

    protected static function logActivity(Model $model, string $event, array $extra = []): void
    {
        $user = auth()->user();
        if ($user && $user->tenant && !$user->tenant->canAccess('audit_trail')) {
            return;
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'tenant_id' => $user?->tenant_id,
            'location_id' => \App\Support\ActiveLocation::id(),
            'action' => strtolower(class_basename($model)).'_'.$event,
            'metadata' => array_merge([
                'id' => $model->id,
                'name' => $model->name ?? $model->reference_no ?? null,
            ], $extra),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'occurred_at' => now(),
        ]);
    }
}
