<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'school_id', 'key', 'value', 'type', 'group', 'description',
    ];

    public static function get(string $key, int $schoolId, mixed $default = null): mixed
    {
        $setting = static::where('school_id', $schoolId)->where('key', $key)->first();

        if (!$setting) return $default;

        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $setting->value,
            'json', 'array' => json_decode($setting->value, true),
            default   => $setting->value,
        };
    }

    public static function set(string $key, mixed $value, int $schoolId, string $type = 'string', string $group = 'general'): void
    {
        static::updateOrCreate(
            ['school_id' => $schoolId, 'key' => $key],
            ['value' => $value, 'type' => $type, 'group' => $group]
        );
    }
}
