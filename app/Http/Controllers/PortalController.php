<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\DisciplinaryRecord;
use App\Models\Invoice;
use App\Models\Result;
use App\Models\StudentAttendance;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalController extends Controller
{
    private function guardian()
    {
        return auth()->user()->guardian->load('students.classroom');
    }

    public function dashboard(): View
    {
        $guardian = $this->guardian();
        $school   = auth()->user()->school;

        $students = $guardian->students;

        $childData = $students->map(function ($student) use ($school) {
            $balance = Invoice::where('student_id', $student->id)
                ->where('school_id', $school->id)
                ->whereIn('status', ['unpaid', 'partial', 'overdue'])
                ->sum('balance');

            $lastAttendance = StudentAttendance::where('student_id', $student->id)
                ->where('school_id', $school->id)
                ->orderByDesc('date')
                ->first();

            return [
                'student'        => $student,
                'balance'        => $balance,
                'lastAttendance' => $lastAttendance,
            ];
        });

        $announcements = Announcement::where('school_id', $school->id)
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        return view('portal.dashboard', compact('guardian', 'school', 'childData', 'announcements'));
    }

    public function child(Request $request, int $studentId): View
    {
        $guardian = $this->guardian();
        $school   = auth()->user()->school;

        $student = $guardian->students->firstWhere('id', $studentId);
        abort_unless($student, 403);

        $student->load('classroom');

        // Academic years for filter
        $years       = AcademicYear::where('school_id', $school->id)->orderByDesc('start_date')->get();
        $currentYear = $years->firstWhere('is_current', true) ?? $years->first();
        $yearId      = $request->integer('year_id', $currentYear?->id ?? 0);

        // Results
        $results = Result::where('student_id', $student->id)
            ->where('school_id', $school->id)
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->where('status', 'published')
            ->with(['academicYear', 'term', 'schoolClass'])
            ->orderByDesc('created_at')
            ->get();

        // Invoices
        $invoices = Invoice::where('student_id', $student->id)
            ->where('school_id', $school->id)
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->with(['academicYear', 'term'])
            ->orderByDesc('issue_date')
            ->get();

        // Attendance last 60 days
        $attendance = StudentAttendance::where('student_id', $student->id)
            ->where('school_id', $school->id)
            ->where('date', '>=', now()->subDays(60))
            ->orderByDesc('date')
            ->get();

        $attendanceSummary = [
            'present'  => $attendance->where('status', 'present')->count(),
            'absent'   => $attendance->where('status', 'absent')->count(),
            'late'     => $attendance->where('status', 'late')->count(),
            'total'    => $attendance->count(),
        ];

        // Disciplinary (open/pending only — no deleted)
        $disciplinary = DisciplinaryRecord::where('student_id', $student->id)
            ->where('school_id', $school->id)
            ->whereIn('status', ['open', 'pending_review'])
            ->orderByDesc('incident_date')
            ->get();

        $currency = $school->currency_symbol ?? '₵';

        return view('portal.child', compact(
            'guardian', 'school', 'student',
            'years', 'yearId', 'currentYear',
            'results', 'invoices', 'attendance', 'attendanceSummary',
            'disciplinary', 'currency',
        ));
    }

    public function invoice(int $invoiceId): View
    {
        $guardian = $this->guardian();
        $school   = auth()->user()->school;

        $studentIds = $guardian->students->pluck('id');

        $invoice = Invoice::where('school_id', $school->id)
            ->whereIn('student_id', $studentIds)
            ->with(['student', 'academicYear', 'term', 'items.feeCategory', 'payments'])
            ->findOrFail($invoiceId);

        $currency = $school->currency_symbol ?? '₵';

        return view('portal.invoice', compact('guardian', 'school', 'invoice', 'currency'));
    }

    public function result(int $resultId): View
    {
        $guardian = $this->guardian();
        $school   = auth()->user()->school;

        $studentIds = $guardian->students->pluck('id');

        $result = Result::where('school_id', $school->id)
            ->whereIn('student_id', $studentIds)
            ->where('status', 'published')
            ->with(['student', 'academicYear', 'term', 'schoolClass', 'subjectScores.subject'])
            ->findOrFail($resultId);

        return view('portal.result', compact('guardian', 'school', 'result'));
    }
}
