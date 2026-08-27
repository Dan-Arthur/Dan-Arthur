<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\School;
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
        $school = School::first();
        if (!$school) {
            $this->command->warn('No school found. Skipping.');
            return;
        }

        $schoolId = $school->id;
        $year     = AcademicYear::where('school_id', $schoolId)->where('is_current', true)->first()
                 ?? AcademicYear::where('school_id', $schoolId)->first();

        if (!$year) {
            $this->command->warn('No academic year found. Skipping.');
            return;
        }

        // ============================================================
        // CLEAR OLD DATA
        // Must delete timetable entries before periods (FK constraint)
        // ============================================================
        $classIds = SchoolClass::where('school_id', $schoolId)->pluck('id');
        Timetable::whereIn('class_id', $classIds)->delete();
        TimetablePeriod::where('school_id', $schoolId)->delete();
        $this->command->info('Old periods and timetable entries cleared.');

        // ============================================================
        // PERIODS
        // 9 lesson slots per day (each 40 min) + 3 break rows
        // Session 1 : 07:30 – 10:10 (4 periods)
        // Break      : 10:10 – 10:40 (30 min, breakfast)
        // Session 2  : 10:40 – 12:00 (2 periods)
        // Lunch      : 12:00 – 12:30 (30 min)
        // Session 3  : 12:30 – 14:30 (3 periods)
        // Assembly   : 14:30 – 15:00 (closing)
        // ============================================================
        $periodDefs = [
            // name               start    end      is_break  order
            ['Period 1',         '07:30', '08:10', false,    1],
            ['Period 2',         '08:10', '08:50', false,    2],
            ['Period 3',         '08:50', '09:30', false,    3],
            ['Period 4',         '09:30', '10:10', false,    4],
            ['Morning Break',    '10:10', '10:40', true,     5],
            ['Period 5',         '10:40', '11:20', false,    6],
            ['Period 6',         '11:20', '12:00', false,    7],
            ['Lunch Break',      '12:00', '12:30', true,     8],
            ['Period 7',         '12:30', '13:10', false,    9],
            ['Period 8',         '13:10', '13:50', false,   10],
            ['Period 9',         '13:50', '14:30', false,   11],
            ['Closing Assembly', '14:30', '15:00', true,    12],
        ];

        $periods = []; // sort_order => TimetablePeriod

        foreach ($periodDefs as [$name, $start, $end, $isBreak, $order]) {
            $p = TimetablePeriod::create([
                'school_id'  => $schoolId,
                'name'       => $name,
                'start_time' => $start,
                'end_time'   => $end,
                'is_break'   => $isBreak,
                'sort_order' => $order,
            ]);
            $periods[$order] = $p;
        }

        $this->command->info('Periods created: ' . count($periodDefs));

        // ============================================================
        // SAMPLE TIMETABLE — Primary 4
        // Lesson periods: 1,2,3,4 (session 1), 5,6 (session 2), 7,8,9 (session 3)
        // Double periods: consecutive pairs share one subject (P1+P2, P3+P4, P5+P6, P7+P8)
        // Single periods: P9 used for PE, Computing or short subjects
        //
        // Week distribution (9 slots/day × 5 days = 45):
        //  English       : 10 (5 double pairs)
        //  Mathematics   :  8 (4 double pairs)
        //  Science       :  4 (2 double pairs)
        //  Social Studies:  4 (2 double pairs)
        //  Creative Arts :  4 (2 double pairs)
        //  RME           :  4 (2 double pairs)
        //  Ghanaian Lang :  4 (2 double pairs)
        //  OWOP          :  2 (1 double pair)
        //  PE            :  3 (single slots)
        //  Computing     :  2 (single slots)
        //                  ──
        //  Total         : 45 ✓
        // ============================================================

        $class = SchoolClass::where('school_id', $schoolId)
            ->where('name', 'like', 'Primary 4%')->first();

        if (!$class) {
            $this->command->warn('Primary 4 not found — periods seeded but no timetable created.');
            return;
        }

        $sub = fn(string $code) => Subject::where('school_id', $schoolId)->where('code', $code)->value('id');

        $eng  = $sub('ENG');    // English Language
        $math = $sub('MATH');   // Mathematics
        $sci  = $sub('SCI');    // Science (Primary)
        $ss   = $sub('SOC-STUD'); // Social Studies
        $owop = $sub('OWOP');   // Our World and Our People
        $ghl  = $sub('GHLANG'); // Ghanaian Language
        $arts = $sub('CRTS');   // Creative Arts
        $rme  = $sub('RME');    // Religious & Moral Education
        $pe   = $sub('PE');     // Physical Education
        $comp = $sub('COMP');   // Computing / ICT

        $teachers = User::where('school_id', $schoolId)
            ->whereHas('roles', fn($q) => $q->whereIn('name', ['teacher','principal','vice-principal','school-admin']))
            ->orderBy('id')->get();

        $t1 = $teachers->get(1)?->id; // Abena Mensah  — class teacher (English, Math, SS, RME, GHL, OWOP)
        $t2 = $teachers->get(2)?->id; // Kwame Asante  — specialist (Science, Creative Arts, PE, Computing)

        // Helper: get period id by sort order
        $p = fn(int $order) => $periods[$order]->id;

        // [period_sort_order, day_of_week, subject_id, teacher_id]
        // day: 1=Mon, 2=Tue, 3=Wed, 4=Thu, 5=Fri
        $slots = [

            // ────────────── MONDAY ──────────────────────────────────
            // Session 1 — double Maths (P1+P2), double English (P3+P4)
            [$p(1), 1, $math, $t1],
            [$p(2), 1, $math, $t1],
            [$p(3), 1, $eng,  $t1],
            [$p(4), 1, $eng,  $t1],
            // Session 2 — double Science (P5+P6)
            [$p(6), 1, $sci,  $t2],
            [$p(7), 1, $sci,  $t2],
            // Session 3 — double Social Studies (P7+P8), single Computing (P9)
            [$p(9),  1, $ss,   $t1],
            [$p(10), 1, $ss,   $t1],
            [$p(11), 1, $comp, $t2],

            // ────────────── TUESDAY ─────────────────────────────────
            // Session 1 — double English (P1+P2), double Maths (P3+P4)
            [$p(1), 2, $eng,  $t1],
            [$p(2), 2, $eng,  $t1],
            [$p(3), 2, $math, $t1],
            [$p(4), 2, $math, $t1],
            // Session 2 — double Creative Arts (P5+P6)
            [$p(6), 2, $arts, $t2],
            [$p(7), 2, $arts, $t2],
            // Session 3 — double Ghanaian Language (P7+P8), single PE (P9)
            [$p(9),  2, $ghl, $t1],
            [$p(10), 2, $ghl, $t1],
            [$p(11), 2, $pe,  $t2],

            // ────────────── WEDNESDAY ───────────────────────────────
            // Session 1 — double Maths (P1+P2), double English (P3+P4)
            [$p(1), 3, $math, $t1],
            [$p(2), 3, $math, $t1],
            [$p(3), 3, $eng,  $t1],
            [$p(4), 3, $eng,  $t1],
            // Session 2 — double RME (P5+P6)
            [$p(6), 3, $rme,  $t1],
            [$p(7), 3, $rme,  $t1],
            // Session 3 — double Science (P7+P8), single PE (P9)
            [$p(9),  3, $sci, $t2],
            [$p(10), 3, $sci, $t2],
            [$p(11), 3, $pe,  $t2],

            // ────────────── THURSDAY ────────────────────────────────
            // Session 1 — double English (P1+P2), double Maths (P3+P4)
            [$p(1), 4, $eng,  $t1],
            [$p(2), 4, $eng,  $t1],
            [$p(3), 4, $math, $t1],
            [$p(4), 4, $math, $t1],
            // Session 2 — double Social Studies (P5+P6)
            [$p(6), 4, $ss,   $t1],
            [$p(7), 4, $ss,   $t1],
            // Session 3 — double Creative Arts (P7+P8), single Computing (P9)
            [$p(9),  4, $arts, $t2],
            [$p(10), 4, $arts, $t2],
            [$p(11), 4, $comp, $t2],

            // ────────────── FRIDAY ──────────────────────────────────
            // Session 1 — double Ghanaian Language (P1+P2), double English (P3+P4)
            [$p(1), 5, $ghl, $t1],
            [$p(2), 5, $ghl, $t1],
            [$p(3), 5, $eng, $t1],
            [$p(4), 5, $eng, $t1],
            // Session 2 — double OWOP (P5+P6)
            [$p(6), 5, $owop, $t1],
            [$p(7), 5, $owop, $t1],
            // Session 3 — double RME (P7+P8), single PE (P9)
            [$p(9),  5, $rme, $t1],
            [$p(10), 5, $rme, $t1],
            [$p(11), 5, $pe,  $t2],
        ];

        $created = 0;
        $skipped = 0;

        foreach ($slots as [$periodId, $day, $subjectId, $teacherId]) {
            if (!$subjectId) { $skipped++; continue; }

            Timetable::create([
                'class_id'         => $class->id,
                'academic_year_id' => $year->id,
                'period_id'        => $periodId,
                'day_of_week'      => $day,
                'subject_id'       => $subjectId,
                'teacher_id'       => $teacherId,
                'is_active'        => true,
            ]);
            $created++;
        }

        $this->command->info("Timetable for '{$class->name}' ({$year->name}): {$created} slots, {$skipped} skipped.");
    }
}
