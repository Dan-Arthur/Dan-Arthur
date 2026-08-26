<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\SmsAlert;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsAlertController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('send messages'), 403);

        $schoolId = auth()->user()->school_id;

        $alerts = SmsAlert::where('school_id', $schoolId)
            ->with(['sender', 'schoolClass'])
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('sms.index', compact('alerts'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('send messages'), 403);

        $schoolId = auth()->user()->school_id;
        $classes  = SchoolClass::where('school_id', $schoolId)->where('is_active', true)
            ->orderBy('level')->get();
        $groups   = SmsAlert::GROUPS;

        return view('sms.create', compact('classes', 'groups'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('send messages'), 403);

        $validated = $request->validate([
            'recipient_group' => 'required|in:' . implode(',', array_keys(SmsAlert::GROUPS)),
            'class_id'        => 'required_if:recipient_group,class_parents,class_students|nullable|exists:school_classes,id',
            'body'            => 'required|string|max:640',
        ]);

        $schoolId = auth()->user()->school_id;
        $phones   = $this->resolvePhones($schoolId, $validated['recipient_group'], $validated['class_id'] ?? null);

        SmsAlert::create([
            'school_id'       => $schoolId,
            'sender_id'       => auth()->id(),
            'body'            => $validated['body'],
            'recipient_group' => $validated['recipient_group'],
            'class_id'        => $validated['class_id'] ?? null,
            'recipients_count'=> count($phones),
            'phone_numbers'   => $phones,
            'status'          => 'sent',
            'sent_at'         => now(),
        ]);

        $count = count($phones);
        return redirect()->route('sms.index')
            ->with('success', "Bulk SMS dispatched to {$count} recipient(s).");
    }

    public function show(SmsAlert $smsAlert): View
    {
        abort_unless(auth()->user()->can('send messages'), 403);
        abort_unless($smsAlert->school_id == auth()->user()->school_id, 403);

        $smsAlert->load(['sender', 'schoolClass']);

        return view('sms.show', compact('smsAlert'));
    }

    // ---------------------------------------------------------------
    // Resolve phone numbers for a group
    // ---------------------------------------------------------------

    private function resolvePhones(int $schoolId, string $group, ?int $classId): array
    {
        $phones = match ($group) {
            'all_parents' => $this->guardianPhones($schoolId),
            'all_staff'   => $this->staffPhones($schoolId),
            'all_students'=> $this->studentPhones($schoolId),
            'class_parents'  => $this->classGuardianPhones($schoolId, $classId),
            'class_students' => $this->classStudentPhones($schoolId, $classId),
            default => [],
        };

        return array_values(array_filter(array_unique($phones)));
    }

    private function guardianPhones(int $schoolId): array
    {
        return Guardian::where('school_id', $schoolId)
            ->where('status', 'active')
            ->where('is_primary_contact', true)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->pluck('phone')
            ->toArray();
    }

    private function staffPhones(int $schoolId): array
    {
        return Employee::where('school_id', $schoolId)
            ->where('status', 'active')
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->pluck('phone')
            ->toArray();
    }

    private function studentPhones(int $schoolId): array
    {
        return Student::where('school_id', $schoolId)
            ->where('status', 'active')
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->pluck('phone')
            ->toArray();
    }

    private function classGuardianPhones(int $schoolId, ?int $classId): array
    {
        if (!$classId) return [];

        $studentIds = Student::where('school_id', $schoolId)
            ->where('current_class_id', $classId)
            ->pluck('id');

        return Guardian::where('school_id', $schoolId)
            ->where('status', 'active')
            ->where('is_primary_contact', true)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->whereHas('students', fn($q) => $q->whereIn('students.id', $studentIds))
            ->pluck('phone')
            ->toArray();
    }

    private function classStudentPhones(int $schoolId, ?int $classId): array
    {
        if (!$classId) return [];

        return Student::where('school_id', $schoolId)
            ->where('current_class_id', $classId)
            ->where('status', 'active')
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->pluck('phone')
            ->toArray();
    }
}
