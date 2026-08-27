<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\Enrolment;
use App\Models\Result;
use App\Models\ResultSubjectScore;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Term;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ResultController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('view results'), 403);

        $schoolId = auth()->user()->school_id;

        $years   = AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get();
        $classes = SchoolClass::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();

        $currentYear = $years->firstWhere('is_current', true) ?? $years->first();
        $selectedYearId  = $request->integer('year_id', $currentYear?->id ?? 0);
        $selectedClassId = $request->integer('class_id');

        $terms = $selectedYearId
            ? Term::where('academic_year_id', $selectedYearId)->orderBy('sequence')->get()
            : collect();

        $selectedTermId = $request->integer('term_id', $terms->firstWhere('is_current', true)?->id ?? $terms->first()?->id ?? 0);

        $results = collect();

        if ($selectedClassId && $selectedTermId) {
            $results = Result::where('class_id', $selectedClassId)
                ->where('term_id', $selectedTermId)
                ->where('academic_year_id', $selectedYearId)
                ->with(['student', 'term'])
                ->when($request->get('status'), fn($q, $s) => $q->where('status', $s))
                ->orderBy('position')
                ->get();
        }

        return view('results.index', compact(
            'years', 'classes', 'terms', 'results',
            'selectedYearId', 'selectedClassId', 'selectedTermId',
        ));
    }

    public function show(Result $result): View
    {
        abort_unless(auth()->user()->can('view results'), 403);
        abort_unless($result->school_id == auth()->user()->school_id, 403);

        $result->load([
            'student', 'schoolClass', 'academicYear', 'term',
            'subjectScores.subject', 'approvedBy',
        ]);

        return view('results.show', compact('result'));
    }

    public function generate(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('enter results'), 403);

        $request->validate([
            'class_id'         => 'required|exists:school_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id'          => 'required|exists:terms,id',
        ]);

        $schoolId  = auth()->user()->school_id;
        $classId   = $request->integer('class_id');
        $yearId    = $request->integer('academic_year_id');
        $termId    = $request->integer('term_id');

        // Enrolled students
        $enrolments = Enrolment::where('class_id', $classId)
            ->where('academic_year_id', $yearId)
            ->where('status', 'active')
            ->with('student')
            ->get();

        if ($enrolments->isEmpty()) {
            return back()->with('error', 'No active students enrolled in this class for the selected year.');
        }

        // All completed assessments for this class/term
        $assessments = Assessment::where('class_id', $classId)
            ->where('term_id', $termId)
            ->where('marks_entered', true)
            ->with('marks')
            ->get();

        if ($assessments->isEmpty()) {
            return back()->with('error', 'No completed assessments found for this class and term.');
        }

        $classSize = $enrolments->count();

        DB::transaction(function () use ($enrolments, $assessments, $classId, $yearId, $termId, $schoolId, $classSize) {
            // Build per-student, per-subject score map
            $subjectScoreMap = [];
            foreach ($assessments as $assessment) {
                foreach ($assessment->marks as $mark) {
                    if (!$mark->is_absent && !$mark->is_exempt && !is_null($mark->score)) {
                        $subjectScoreMap[$mark->student_id][$assessment->subject_id][] = $mark->score;
                    }
                }
            }

            $studentAverages = [];

            foreach ($enrolments as $enrolment) {
                $student = $enrolment->student;
                if (!$student) continue;

                $result = Result::updateOrCreate(
                    ['student_id' => $student->id, 'term_id' => $termId],
                    [
                        'school_id'        => $schoolId,
                        'class_id'         => $classId,
                        'academic_year_id' => $yearId,
                        'class_size'       => $classSize,
                        'status'           => 'draft',
                    ]
                );

                $studentSubjects = $subjectScoreMap[$student->id] ?? [];
                $subjectTotals = [];

                foreach ($studentSubjects as $subjectId => $scores) {
                    $avg = count($scores) ? array_sum($scores) / count($scores) : 0;
                    $subjectTotals[$subjectId] = round($avg, 2);

                    ResultSubjectScore::updateOrCreate(
                        ['result_id' => $result->id, 'subject_id' => $subjectId],
                        [
                            'student_id'  => $student->id,
                            'total_score' => round($avg, 2),
                        ]
                    );
                }

                $overallAvg = count($subjectTotals) ? array_sum($subjectTotals) / count($subjectTotals) : null;

                $result->update([
                    'total_score'       => $overallAvg ? round(array_sum($subjectTotals), 2) : null,
                    'average_score'     => $overallAvg ? round($overallAvg, 2) : null,
                    'subjects_offered'  => count($subjectTotals),
                ]);

                $studentAverages[$student->id] = $overallAvg;
            }

            // Compute positions
            arsort($studentAverages);
            $pos = 1;
            foreach ($studentAverages as $studentId => $avg) {
                Result::where('student_id', $studentId)->where('term_id', $termId)->update(['position' => $pos]);
                $pos++;
            }
        });

        return redirect()->route('results.index', [
            'class_id'         => $classId,
            'academic_year_id' => $yearId,
            'term_id'          => $termId,
            'year_id'          => $yearId,
        ])->with('success', 'Results generated for ' . $enrolments->count() . ' student(s).');
    }

    public function updateComment(Request $request, Result $result): RedirectResponse
    {
        abort_unless(auth()->user()->can('edit results') || auth()->user()->can('approve results'), 403);
        abort_unless($result->school_id == auth()->user()->school_id, 403);

        $validated = $request->validate([
            'class_teacher_comment' => 'nullable|string|max:1000',
            'principal_comment'     => 'nullable|string|max:1000',
        ]);

        $result->update($validated);

        return back()->with('success', 'Comments updated.');
    }

    public function approve(Result $result): RedirectResponse
    {
        abort_unless(auth()->user()->can('approve results'), 403);
        abort_unless($result->school_id == auth()->user()->school_id, 403);

        $result->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Result approved.');
    }

    public function publish(Result $result): RedirectResponse
    {
        abort_unless(auth()->user()->can('publish results'), 403);
        abort_unless($result->school_id == auth()->user()->school_id, 403);

        $result->update([
            'status'       => 'published',
            'published_at' => now(),
        ]);

        return back()->with('success', 'Result published.');
    }

    public function reportCard(Result $result): View
    {
        abort_unless(auth()->user()->can('view results'), 403);
        abort_unless($result->school_id == auth()->user()->school_id, 403);

        $result->load([
            'student', 'schoolClass', 'academicYear', 'term',
            'subjectScores.subject', 'approvedBy',
        ]);

        $school = auth()->user()->school;

        return view('results.report-card', compact('result', 'school'));
    }

    public function bulkApprove(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('approve results'), 403);

        $request->validate([
            'class_id'         => 'required|exists:school_classes,id',
            'term_id'          => 'required|exists:terms,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $schoolId = auth()->user()->school_id;
        $count = Result::where('class_id', $request->class_id)
            ->where('term_id', $request->term_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('school_id', $schoolId)
            ->where('status', 'draft')
            ->update([
                'status'      => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

        return back()->with('success', "$count result(s) approved.");
    }

    public function bulkPublish(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('publish results'), 403);

        $request->validate([
            'class_id'         => 'required|exists:school_classes,id',
            'term_id'          => 'required|exists:terms,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $schoolId = auth()->user()->school_id;
        $count = Result::where('class_id', $request->class_id)
            ->where('term_id', $request->term_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('school_id', $schoolId)
            ->whereIn('status', ['draft', 'approved'])
            ->update([
                'status'       => 'published',
                'published_at' => now(),
            ]);

        return back()->with('success', "$count result(s) published.");
    }
}
