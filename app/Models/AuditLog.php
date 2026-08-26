<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;

    const UPDATED_AT = null;

    const EVENTS = [
        'created'   => ['label' => 'Created',   'color' => 'badge-success'],
        'updated'   => ['label' => 'Updated',   'color' => 'badge-primary'],
        'deleted'   => ['label' => 'Deleted',   'color' => 'badge-danger'],
        'restored'  => ['label' => 'Restored',  'color' => 'badge-warning'],
        'login'     => ['label' => 'Login',     'color' => 'badge-gray'],
        'logout'    => ['label' => 'Logout',    'color' => 'badge-gray'],
        'approved'  => ['label' => 'Approved',  'color' => 'badge-success'],
        'rejected'  => ['label' => 'Rejected',  'color' => 'badge-danger'],
        'published' => ['label' => 'Published', 'color' => 'badge-success'],
        'cancelled' => ['label' => 'Cancelled', 'color' => 'badge-danger'],
        'exported'  => ['label' => 'Exported',  'color' => 'badge-warning'],
        'viewed'    => ['label' => 'Viewed',    'color' => 'badge-gray'],
    ];

    protected $fillable = [
        'school_id', 'user_id', 'user_type', 'event',
        'auditable_type', 'auditable_id',
        'old_values', 'new_values',
        'url', 'ip_address', 'user_agent', 'tags',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function getEventLabelAttribute(): string { return self::EVENTS[$this->event]['label'] ?? ucfirst($this->event); }
    public function getEventColorAttribute(): string { return self::EVENTS[$this->event]['color'] ?? 'badge-gray'; }

    public function getModelLabelAttribute(): string
    {
        return $this->auditable_type ? class_basename($this->auditable_type) : '—';
    }

    public function getChangedFieldsAttribute(): array
    {
        $old = $this->old_values ?? [];
        $new = $this->new_values ?? [];
        $keys = array_unique(array_merge(array_keys($old), array_keys($new)));
        $diff = [];

        foreach ($keys as $key) {
            $oldVal = $old[$key] ?? null;
            $newVal = $new[$key] ?? null;
            if ($oldVal !== $newVal) {
                $diff[$key] = ['old' => $oldVal, 'new' => $newVal];
            }
        }

        return $diff;
    }

    // ---------------------------------------------------------------------------
    // Static helper — call from controllers or the Auditable trait
    // ---------------------------------------------------------------------------

    public static function record(
        string $event,
        ?Model $model = null,
        array  $oldValues = [],
        array  $newValues = [],
        string $tags = ''
    ): void {
        // Skip during CLI (seeding, migrations) unless it's a deliberate call
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return;
        }

        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        static::create([
            'school_id'      => $user?->school_id,
            'user_id'        => $user?->id,
            'user_type'      => $user ? class_basename($user) : null,
            'event'          => $event,
            'auditable_type' => $model ? get_class($model) : null,
            'auditable_id'   => $model?->getKey(),
            'old_values'     => $oldValues ?: null,
            'new_values'     => $newValues ?: null,
            'url'            => request()->fullUrl(),
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
            'tags'           => $tags ?: null,
        ]);
    }
}
