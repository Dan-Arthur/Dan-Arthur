<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentScholarship extends Model
{
    protected $fillable = [
        'school_id', 'student_id', 'scholarship_id', 'academic_year_id',
        'notes', 'assigned_by',
    ];

    public function student(): BelongsTo      { return $this->belongsTo(Student::class); }
    public function scholarship(): BelongsTo  { return $this->belongsTo(Scholarship::class); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function assignedBy(): BelongsTo   { return $this->belongsTo(User::class, 'assigned_by'); }
}
