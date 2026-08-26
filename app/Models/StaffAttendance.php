<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffAttendance extends Model
{
    protected $table = 'staff_attendance';

    protected $fillable = [
        'school_id', 'user_id', 'date', 'status',
        'check_in', 'check_out', 'reason', 'method',
    ];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }
}
