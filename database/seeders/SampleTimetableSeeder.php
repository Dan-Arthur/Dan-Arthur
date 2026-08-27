<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Timetable;
use App\Models\TimetablePeriod;
use App\Models\User;
use Illuminate\Database\Seeder;

class SampleTimetableSeeder extends Seeder
{
    public function run(): void
    {
        $school = \App\Models\School::first();
        if (!$school) {
            $this->command->warn('No school found. Skipping.');
            return;
        }

        $schoolId = $school->id;

        $year = AcademicYear::where('school_id', $schoolId)->where('is_current', true)->first()
             ?? AcademicYear::where('school_id', $schoolId)->first();

        if (!$year) {
            $this->command->warn('No academic year found. Skipping.');
            return;
        }

        // ============================================================
        // PERIODS  — typical Ghana primary school day
        // ============================================================
        $periodDefs = [
            ['Assembly / Opening',  '07:00', '07:30', true,  1],
            ['Period 1',            '07:30', '08:10', false, 2],
            ['Period 2',            '08:10', '08:50', false, 3],
            ['Period 3',            '08:50', '09:30', false, 4],
            ['Morning Break',       '09:30', '09:50', true,  5],
            ['Period 4',            '09:50', '10:30', false, 6],
            ['Period 5',            '10:30', '11:10', false, 7],
            ['Period 6',            '11:10', '11:50', false, 8],
            ['Lunch Break',         '11:50', '12:30', true,  9],
            ['Period 7',            '12:30', '13:10', false, 10],
            ['Period 8',            '13:10', '13:50', false, 11],
        ];

        $periodMap = []; // sort_order => TimetablePeriod

        foreach ($periodDefs as [$name, $start, $end, $isBreak, $order]) {
            $period = TimetablePeriod::updateOrCreate(
                ['school_id' => $schoolId, 'name' => $name],
                [
                    'start_time' => $start,
                    'end_time'   => $end,
                    'is_break'   => $isBreak,
                    'sort_order' => $order,
                ]
            );
            $periodMap[$order] = $period;
        }

        $this->command->info('Periods seeded: ' . count($periodDefs));

        // ============================================================
        // SAMPLE TIMETABLE — Primary 4
        // ============================================================
        $class = SchoolClass::where('school_id', $schoolId)
            ->where('name', 'like', 'Primary 4%')
            ->first();

        if (!$class) {
            $this->command->warn('Primary 4 class not found. Periods were seeded but no timetable created.');
            return;
        }

        // Subject ID lookup by code
        $sub = fn(string $code) => Subject::where('school_id', $schoolId)->where('code', $code)->value('id');

        $eng   = $sub('ENG');
        $math  = $sub('MATH');
        $sci   = $sub('SCI');
        $ss    = $sub('SOC-STUD');
        $owop  = $sub('OWOP');
        $ghl   = $sub('GHLANG');
        $arts  = $sub('CRTS');
        $rme   = $sub('RME');
        $pe    = $sub('PE');
        $comp  = $sub('COMP');

        // Teacher lookup — use first available teachers
        $teachers = User::where('school_id', $schoolId)
            ->whereHas('roles', fn($q) => $q->whereIn('name', ['teacher','principal','vice-principal','school-admin']))
            ->orderBy('id')
            ->get();

        $t1 = $teachers->skip(1)->first()?->id; // Abena Mensah
        $t2 = $teachers->skip(2)->first()?->id; // Kwame Asante

        // period slot helper — maps sort_order to period id
        $p = fn(int $order) => $periodMap[$order]->id;

        // Grid: [period_sort, day, subject_id, teacher_id]
        // Lesson periods: 2,3,4 (P1-P3), 6,7,8 (P4-P6), 10,11 (P7-P8)
        $slots = [
            // ── MONDAY ──────────────────────────────────────────────
            [$p(2),  1, $eng,  $t1],
            [$p(3),  1, $math, $t1],
            [$p(4),  1, $sci,  $t2],
            [$p(6),  1, $owop, $t1],
            [$p(7),  1, $ghl,  $t1],
            [$p(8),  1, $arts, $t2],
            [$p(10), 1, $comp, $t2],
            [$p(11), 1, $pe,   $t2],

            // ── TUESDAY ─────────────────────────────────────────────
            [$p(2),  2, $math, $t1],
            [$p(3),  2, $eng,  $t1],
            [$p(4),  2, $ss,   $t1],
            [$p(6),  2, $sci,  $t2],
            [$p(7),  2, $rme,  $t1],
            [$p(8),  2, $math, $t1],
            [$p(10), 2, $eng,  $t1],
            [$p(11), 2, $ghl,  $t1],

            // ── WEDNESDAY ───────────────────────────────────────────
            [$p(2),  3, $eng,  $t1],
            [$p(3),  3, $sci,  $t2],
            [$p(4),  3, $math, $t1],
            [$p(6),  3, $arts, $t2],
            [$p(7),  3, $ss,   $t1],
            [$p(8),  3, $eng,  $t1],
            [$p(10), 3, $rme,  $t1],
            [$p(11), 3, $pe,   $t2],

            // ── THURSDAY ────────────────────────────────────────────
            [$p(2),  4, $math, $t1],
            [$p(3),  4, $ghl,  $t1],
            [$p(4),  4, $eng,  $t1],
            [$p(6),  4, $sci,  $t2],
            [$p(7),  4, $math, $t1],
            [$p(8),  4, $ss,   $t1],
            [$p(10), 4, $comp, $t2],
            [$p(11), 4, $arts, $t2],

            // ── FRIDAY ──────────────────────────────────────────────
            [$p(2),  5, $eng,  $t1],
            [$p(3),  5, $math, $t1],
            [$p(4),  5, $rme,  $t1],
            [$p(6),  5, $sci,  $t2],
            [$p(7),  5, $owop, $t1],
            [$p(8),  5, $ghl,  $t1],
            [$p(10), 5, $pe,   $t2],
            [$p(11), 5, $eng,  $t1],
        ];

        $created = 0;
        $skipped = 0;

        foreach ($slots as [$periodId, $day, $subjectId, $teacherId]) {
            if (!$subjectId) { $skipped++; continue; }

            Timetable::updateOrCreate(
                [
                    'class_id'         => $class->id,
                    'academic_year_id' => $year->id,
                    'period_id'        => $periodId,
                    'day_of_week'      => $day,
                ],
                [
                    'subject_id' => $subjectId,
                    'teacher_id' => $teacherId,
                    'is_active'  => true,
                ]
            );
            $created++;
        }

        $this->command->info("Timetable seeded for '{$class->name}' ({$year->name}): {$created} slots created, {$skipped} skipped.");
    }
}
