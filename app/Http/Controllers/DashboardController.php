<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\StudentAttendance;
use App\Models\StaffAttendance;
use App\Models\Enrolment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = auth()->user();
        $school = $user->school;
        $today = now()->toDateString();

        // If super admin with no school, show platform overview
        if ($user->isSuperAdmin() && !$school) {
            return view('dashboard.super-admin');
        }

        $currentYear = $school?->currentAcademicYear();
        $currentTerm = $school?->currentTerm();
        $schoolId = $school?->id;

        // === KPI Stats ===
        $totalStudents = Student::where('school_id', $schoolId)
            ->where('status', 'active')->count();

        $totalStaff = User::where('school_id', $schoolId)
            ->whereHas('roles', fn($q) => $q->whereIn('name', ['teacher', 'accountant', 'hr-officer', 'librarian', 'receptionist']))
            ->where('status', 'active')->count();

        // Today's attendance
        $presentToday = StudentAttendance::where('school_id', $schoolId)
            ->where('date', $today)
            ->where('status', 'present')
            ->count();

        $absentToday = StudentAttendance::where('school_id', $schoolId)
            ->where('date', $today)
            ->where('status', 'absent')
            ->count();

        $staffPresentToday = StaffAttendance::where('school_id', $schoolId)
            ->where('date', $today)
            ->where('status', 'present')
            ->count();

        // Finance stats (current term)
        $totalFeesBilled = 0;
        $totalFeesCollected = 0;
        $outstandingFees = 0;

        if ($currentTerm) {
            $totalFeesBilled = Invoice::where('school_id', $schoolId)
                ->where('term_id', $currentTerm->id)
                ->whereNotIn('status', ['cancelled'])
                ->sum('total_amount');

            $totalFeesCollected = Payment::where('school_id', $schoolId)
                ->whereHas('invoice', fn($q) => $q->where('term_id', $currentTerm->id))
                ->where('status', 'confirmed')
                ->sum('amount');

            $outstandingFees = $totalFeesBilled - $totalFeesCollected;
        }

        // New students this month
        $newStudentsThisMonth = Student::where('school_id', $schoolId)
            ->whereMonth('admission_date', now()->month)
            ->whereYear('admission_date', now()->year)
            ->count();

        // Today's payments
        $todaysCollections = Payment::where('school_id', $schoolId)
            ->where('payment_date', $today)
            ->where('status', 'confirmed')
            ->sum('amount');

        // Recent announcements
        $recentAnnouncements = Announcement::where('school_id', $schoolId)
            ->where('status', 'published')
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->latest('publish_at')
            ->limit(5)
            ->get();

        // Attendance trend (last 7 days)
        $attendanceTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $present = StudentAttendance::where('school_id', $schoolId)
                ->where('date', $date)->where('status', 'present')->count();
            $absent = StudentAttendance::where('school_id', $schoolId)
                ->where('date', $date)->where('status', 'absent')->count();
            $attendanceTrend[] = [
                'date' => now()->subDays($i)->format('D'),
                'present' => $present,
                'absent' => $absent,
            ];
        }

        // Class distribution
        $classDistribution = Enrolment::where('enrolments.status', 'active')
            ->join('school_classes', 'enrolments.class_id', '=', 'school_classes.id')
            ->where('school_classes.school_id', $schoolId)
            ->when($currentYear, fn($q) => $q->where('enrolments.academic_year_id', $currentYear->id))
            ->selectRaw('school_classes.name as class_name, COUNT(*) as count')
            ->groupBy('school_classes.name')
            ->orderByDesc('count')
            ->limit(8)
            ->get();

        // Recent students
        $recentStudents = Student::where('school_id', $schoolId)
            ->with('currentClass')
            ->latest()
            ->limit(5)
            ->get();

        // Upcoming events
        $upcomingEvents = \App\Models\Event::where('school_id', $schoolId)
            ->where('start_datetime', '>=', now())
            ->orderBy('start_datetime')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'school', 'currentYear', 'currentTerm',
            'totalStudents', 'totalStaff',
            'presentToday', 'absentToday', 'staffPresentToday',
            'totalFeesBilled', 'totalFeesCollected', 'outstandingFees',
            'newStudentsThisMonth', 'todaysCollections',
            'recentAnnouncements', 'attendanceTrend', 'classDistribution',
            'recentStudents', 'upcomingEvents'
        ));
    }
}
