<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guardian extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id', 'user_id', 'title', 'first_name', 'last_name', 'other_names',
        'gender', 'relationship', 'phone', 'alt_phone', 'email', 'occupation',
        'employer', 'address', 'city', 'state', 'nationality', 'national_id',
        'photo', 'is_primary_contact', 'is_emergency_contact', 'portal_access', 'status',
    ];

    protected function casts(): array
    {
        return [
            'is_primary_contact'   => 'boolean',
            'is_emergency_contact' => 'boolean',
            'portal_access'        => 'boolean',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_guardians')
            ->withPivot('relationship', 'is_primary', 'is_emergency', 'can_pickup', 'receives_reports', 'receives_invoices')
            ->withTimestamps();
    }

    public function getFullNameAttribute(): string
    {
        $name = trim("{$this->first_name} {$this->last_name}");
        return $this->title ? "{$this->title} {$name}" : $name;
    }

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }
        $name = urlencode(trim("{$this->first_name} {$this->last_name}"));
        $bg   = $this->gender === 'female' ? 'ec4899' : '2563eb';
        return "https://ui-avatars.com/api/?name={$name}&background={$bg}&color=fff";
    }

    public function scopeForSchool($query, int $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }
}
