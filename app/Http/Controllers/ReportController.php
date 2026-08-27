<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Enrolment;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    private function gate(string $permission = 'view analytics'): void
    {
        abort_unless(auth()->user()->can($permission), 403);
    }

    // ────────────────────────────────────────────────────────────
    // HUB
    // ────────────────────────────────────────────────────────────

    public function index(): View
    {
        $this->gate();

        $schoolId    = $this->schoolId();
        $currentYear = AcademicYear::where('school_id', $schoolId)->where('is_current', true)->first();

        // Quick KPIs for the hub
        $totalStudents = Student::where('school_id', $schoolId)->where('status', 'active')->count();

        $feesCollected = $currentYear
            ? Payment::where('school_id', $schoolId)
                ->where('status', 'confirmed')
                ->whereHas('invoice', fn($q) => $q->where('academic_year_id', $currentYear->id))
                ->sum('amount')
            : 0;

        $feesOutstanding = $currentYear
            ? Invoice::where('school_id', $schoolId)
                ->where('academic_year_id', $currentYear->id)
                ->whereIn('status', ['unpaid', 'partial', 'overdue'])
                ->sum('balance')
            : 0;

        $defaulterCount = $currentYear
            ? Invoice::where('school_id', $schoolId)
                ->where('academic_year_id', $currentYear->id)
                ->whereIn('status', ['unpaid', 'partial', 'overdue'])
                ->distinct('student_id')
                ->count('student_id')
            : 0;

        $school    = auth()->user()->school;
        $currency  = $school->currency_symbol ?? '₵';

        return view('reports.index', compact(
            'currentYear', 'totalStudents', 'feesCollected',
            'feesOutstanding', 'defaulterCount', 'currency'
        ));
    }

    // ────────────────────────────────────────────────────────────
    // FINANCIAL REPORT
    // ────────────────────────────────────────────────────────────

    public function financial(Request $request): View
    {
        $this->gate();

        $schoolId    = $this->schoolId();
        $school      = auth()->user()->school;
        $currency    = $school->currency_symbol ?? '₵';
        $years       = AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get();
        $currentYear = AcademicYear::where('school_id', $schoolId)->where('is_current', true)->first();
        $yearId      = $request->get('year_id', $currentYear?->id);
        $selectedYear = $yearId ? $years->firstWhere('id', $yearId) : null;

        // ── Invoices summary ──────────────────────────────────
        $invoiceQuery = Invoice::where('school_id', $schoolId)
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId));

        $totalBilled      = (clone $invoiceQuery)->sum('total_amount');
        $totalCollected   = (clone $invoiceQuery)->sum('amount_paid');
        $totalOutstanding = (clone $invoiceQuery)->whereIn('status', ['unpaid', 'partial', 'overdue'])->sum('balance');
        $totalDiscount    = (clone $invoiceQuery)->sum('discount_amount');
        $totalScholarship = (clone $invoiceQuery)->sum('scholarship_amount');
        $collectionRate   = $totalBilled > 0 ? round(($totalCollected / $totalBilled) * 100, 1) : 0;

        // ── Status breakdown ──────────────────────────────────
        $statusBreakdown = (clone $invoiceQuery)
            ->selectRaw('status, COUNT(*) as count, SUM(total_amount) as total, SUM(amount_paid) as collected, SUM(balance) as outstanding')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        // ── Payment method breakdown ──────────────────────────
        $methodBreakdown = Payment::where('school_id', $schoolId)
            ->where('status', 'confirmed')
            ->when($yearId, fn($q) => $q->whereHas('invoice', fn($q2) => $q2->where('academic_year_id', $yearId)))
            ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        // ── Monthly collections (last 12 months) ─────────────
        $monthlyCollections = Payment::where('school_id', $schoolId)
            ->where('status', 'confirmed')
            ->when($yearId, fn($q) => $q->whereHas('invoice', fn($q2) => $q2->where('academic_year_id', $yearId)))
            ->selectRaw("FORMAT(payment_date, 'yyyy-MM') as month, SUM(amount) as total, COUNT(*) as count")
            ->groupByRaw("FORMAT(payment_date, 'yyyy-MM')")
            ->orderBy('month')
            ->get();

        // ── Expenses summary ──────────────────────────────────
        $expenseQuery = Expense::where('school_id', $schoolId)
            ->when($yearId && $selectedYear, function ($q) use ($selectedYear) {
                $q->whereBetween('date', [$selectedYear->start_date, $selectedYear->end_date]);
            });

        $totalExpenses     = (clone $expenseQuery)->sum('amount');
        $expenseByCategory = (clone $expenseQuery)
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $netIncome = $totalCollected - $totalExpenses;

        return view('reports.financial', compact(
            'currency', 'years', 'yearId', 'selectedYear',
            'totalBilled', 'totalCollected', 'totalOutstanding',
            'totalDiscount', 'totalScholarship', 'collectionRate',
            'statusBreakdown', 'methodBreakdown', 'monthlyCollections',
            'totalExpenses', 'expenseByCategory', 'netIncome'
        ));
    }

    // ────────────────────────────────────────────────────────────
    // FEE DEFAULTERS
    // ────────────────────────────────────────────────────────────

    public function defaulters(Request $request): View
    {
        $this->gate();

        $schoolId    = $this->schoolId();
        $school      = auth()->user()->school;
        $currency    = $school->currency_symbol ?? '₵';
        $years       = AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get();
        $currentYear = AcademicYear::where('school_id', $schoolId)->where('is_current', true)->first();
        $yearId      = $request->get('year_id', $currentYear?->id);
        $classes     = SchoolClass::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();

        $defaulters = Invoice::where('school_id', $schoolId)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->when($request->class_id, fn($q) => $q->whereHas('student', fn($q2) => $q2->where('current_class_id', $request->class_id)))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->with(['student.schoolClass', 'academicYear', 'term'])
            ->orderByDesc('balance')
            ->paginate(30)
            ->withQueryString();

        $totalOutstanding = Invoice::where('school_id', $schoolId)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->sum('balance');

        return view('reports.defaulters', compact(
            'defaulters', 'totalOutstanding', 'currency',
            'years', 'yearId', 'classes'
        ));
    }

    // ────────────────────────────────────────────────────────────
    // ATTENDANCE REPORT
    // ────────────────────────────────────────────────────────────

    public function attendance(Request $request): View
    {
        $this->gate();

        $schoolId    = $this->schoolId();
        $years       = AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get();
        $currentYear = AcademicYear::where('school_id', $schoolId)->where('is_current', true)->first();
        $yearId      = $request->get('year_id', $currentYear?->id);
        $termId      = $request->get('term_id');
        $terms       = $yearId ? Term::where('academic_year_id', $yearId)->orderBy('sequence')->get() : collect();
        $classes     = SchoolClass::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();

        // Class-by-class summary
        $classSummary = StudentAttendance::where('school_id', $schoolId)
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->when($termId, fn($q) => $q->where('term_id', $termId))
            ->when($request->class_id, fn($q) => $q->where('class_id', $request->class_id))
            ->selectRaw('class_id,
                COUNT(*) as total_records,
                SUM(CASE WHEN status = \'present\' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN status = \'absent\' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN status = \'late\' THEN 1 ELSE 0 END) as late_count')
            ->groupBy('class_id')
            ->get()
            ->map(function ($row) use ($classes) {
                $row->class_name = $classes->firstWhere('id', $row->class_id)?->name ?? 'Unknown';
                $row->rate = $row->total_records > 0
                    ? round((($row->present_count + $row->late_count) / $row->total_records) * 100, 1)
                    : 0;
                return $row;
            })
            ->sortBy('class_name');

        // Overall totals
        $overallTotal   = $classSummary->sum('total_records');
        $overallPresent = $classSummary->sum('present_count') + $classSummary->sum('late_count');
        $overallRate    = $overallTotal > 0 ? round(($overallPresent / $overallTotal) * 100, 1) : 0;

        // Top 10 most absent students
        $mostAbsent = StudentAttendance::where('school_id', $schoolId)
            ->where('status', 'absent')
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->when($termId, fn($q) => $q->where('term_id', $termId))
            ->when($request->class_id, fn($q) => $q->where('class_id', $request->class_id))
            ->selectRaw('student_id, COUNT(*) as absent_count')
            ->groupBy('student_id')
            ->orderByDesc('absent_count')
            ->limit(10)
            ->with('student.schoolClass')
            ->get();

        return view('reports.attendance', compact(
            'years', 'yearId', 'terms', 'termId', 'classes',
            'classSummary', 'overallTotal', 'overallPresent', 'overallRate',
            'mostAbsent'
        ));
    }

    // ────────────────────────────────────────────────────────────
    // ENROLLMENT REPORT
    // ────────────────────────────────────────────────────────────

    public function enrollment(Request $request): View
    {
        $this->gate();

        $schoolId    = $this->schoolId();
        $years       = AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get();
        $currentYear = AcademicYear::where('school_id', $schoolId)->where('is_current', true)->first();
        $yearId      = $request->get('year_id', $currentYear?->id);

        // Enrollment by class for selected year
        $byClass = Enrolment::where('school_id', $schoolId)
            ->where('status', 'active')
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->selectRaw('class_id,
                COUNT(*) as total,
                SUM(CASE WHEN students.gender = \'male\' THEN 1 ELSE 0 END) as male_count,
                SUM(CASE WHEN students.gender = \'female\' THEN 1 ELSE 0 END) as female_count')
            ->join('students', 'enrolments.student_id', '=', 'students.id')
            ->groupBy('class_id')
            ->get()
            ->map(function ($row) {
                $row->class = SchoolClass::find($row->class_id);
                return $row;
            })
            ->filter(fn($r) => $r->class)
            ->sortBy('class.name');

        $totalEnrolled = $byClass->sum('total');
        $totalMale     = $byClass->sum('male_count');
        $totalFemale   = $byClass->sum('female_count');

        // Enrollment trend across all years
        $trend = Enrolment::where('school_id', $schoolId)
            ->where('status', 'active')
            ->join('academic_years', 'enrolments.academic_year_id', '=', 'academic_years.id')
            ->selectRaw('enrolments.academic_year_id, academic_years.name as year_name, COUNT(*) as total')
            ->groupBy('enrolments.academic_year_id', 'academic_years.name')
            ->orderBy('academic_years.start_date')
            ->get();

        // Active students not enrolled in any class this year
        $unenrolled = 0;
        if ($yearId) {
            $enrolledIds = Enrolment::where('school_id', $schoolId)
                ->where('academic_year_id', $yearId)
                ->where('status', 'active')
                ->pluck('student_id');

            $unenrolled = Student::where('school_id', $schoolId)
                ->where('status', 'active')
                ->whereNotIn('id', $enrolledIds)
                ->count();
        }

        return view('reports.enrollment', compact(
            'years', 'yearId', 'byClass',
            'totalEnrolled', 'totalMale', 'totalFemale',
            'trend', 'unenrolled'
        ));
    }
}
