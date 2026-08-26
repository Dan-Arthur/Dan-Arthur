<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Arr;

/**
 * Add this trait to any Eloquent model to automatically write audit log entries
 * on created, updated, deleted, and restored events.
 *
 * Fields in $auditExclude are stripped from old/new values before writing.
 */
trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            AuditLog::record('created', $model, [], $model->getAuditNewValues());
        });

        static::updated(function ($model) {
            $changed = array_keys($model->getChanges());
            $exclude = $model->auditExclude ?? ['updated_at', 'created_at', 'deleted_at', 'remember_token', 'password'];
            $changed = array_diff($changed, $exclude);

            if (empty($changed)) return;

            $old = Arr::only($model->getOriginal(), $changed);
            $new = Arr::only($model->getChanges(), $changed);

            AuditLog::record('updated', $model, $old, $new);
        });

        static::deleted(function ($model) {
            AuditLog::record('deleted', $model, $model->getAuditNewValues(), []);
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                AuditLog::record('restored', $model, [], $model->getAuditNewValues());
            });
        }
    }

    protected function getAuditNewValues(): array
    {
        $exclude = $this->auditExclude ?? ['updated_at', 'created_at', 'deleted_at', 'remember_token', 'password'];
        return Arr::except($this->getAttributes(), $exclude);
    }
}
