<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentTransport extends Model
{
    protected $table = 'student_transport';

    protected $fillable = ['student_id', 'route_id', 'stop_id', 'academic_year_id', 'direction', 'status'];

    public function student(): BelongsTo      { return $this->belongsTo(Student::class); }
    public function route(): BelongsTo        { return $this->belongsTo(TransportRoute::class, 'route_id'); }
    public function stop(): BelongsTo         { return $this->belongsTo(TransportStop::class, 'stop_id'); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
}
