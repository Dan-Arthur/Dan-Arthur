<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultSubjectScore extends Model
{
    protected $table = 'result_subject_scores';

    protected $fillable = [
        'result_id', 'subject_id', 'student_id',
        'ca_score', 'exam_score', 'total_score', 'grade', 'remark',
        'position', 'class_average', 'highest_score', 'lowest_score', 'teacher_comment',
    ];

    protected function casts(): array
    {
        return [
            'ca_score'      => 'float',
            'exam_score'    => 'float',
            'total_score'   => 'float',
            'class_average' => 'float',
            'highest_score' => 'float',
            'lowest_score'  => 'float',
        ];
    }

    public function result(): BelongsTo  { return $this->belongsTo(Result::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
}
