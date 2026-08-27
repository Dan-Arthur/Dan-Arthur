<?php

namespace App\Http\Controllers;

use App\Models\GradeBand;
use App\Models\GradingScale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GradingScaleController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(): View
    {
        abort_unless(auth()->user()->can('manage grading scales'), 403);

        $scales = GradingScale::where('school_id', $this->schoolId())
            ->with('bands')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return view('grading-scales.index', compact('scales'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('manage grading scales'), 403);

        return view('grading-scales.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage grading scales'), 403);

        $validated = $request->validate([
            'name'                  => 'required|string|max:100',
            'is_default'            => 'boolean',
            'bands'                 => 'required|array|min:1',
            'bands.*.grade'         => 'required|string|max:10',
            'bands.*.min_score'     => 'required|numeric|min:0|max:100',
            'bands.*.max_score'     => 'required|numeric|min:0|max:100',
            'bands.*.remark'        => 'nullable|string|max:100',
            'bands.*.gpa_point'     => 'nullable|integer|min:0|max:10',
        ]);

        $schoolId = $this->schoolId();

        DB::transaction(function () use ($validated, $schoolId) {
            if (!empty($validated['is_default'])) {
                GradingScale::where('school_id', $schoolId)->update(['is_default' => false]);
            }

            $scale = GradingScale::create([
                'school_id'  => $schoolId,
                'name'       => $validated['name'],
                'is_default' => !empty($validated['is_default']),
            ]);

            foreach ($validated['bands'] as $band) {
                $scale->bands()->create([
                    'grade'     => trim($band['grade']),
                    'min_score' => $band['min_score'],
                    'max_score' => $band['max_score'],
                    'remark'    => $band['remark']    ?? null,
                    'gpa_point' => $band['gpa_point'] ?? 0,
                ]);
            }
        });

        return redirect()->route('grading-scales.index')
            ->with('success', 'Grading scale created.');
    }

    public function edit(GradingScale $gradingScale): View
    {
        abort_unless(auth()->user()->can('manage grading scales'), 403);
        abort_unless($gradingScale->school_id == $this->schoolId(), 403);

        $gradingScale->load('bands');

        return view('grading-scales.edit', compact('gradingScale'));
    }

    public function update(Request $request, GradingScale $gradingScale): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage grading scales'), 403);
        abort_unless($gradingScale->school_id == $this->schoolId(), 403);

        $validated = $request->validate([
            'name'                  => 'required|string|max:100',
            'is_default'            => 'boolean',
            'bands'                 => 'required|array|min:1',
            'bands.*.grade'         => 'required|string|max:10',
            'bands.*.min_score'     => 'required|numeric|min:0|max:100',
            'bands.*.max_score'     => 'required|numeric|min:0|max:100',
            'bands.*.remark'        => 'nullable|string|max:100',
            'bands.*.gpa_point'     => 'nullable|integer|min:0|max:10',
        ]);

        $schoolId = $this->schoolId();

        DB::transaction(function () use ($validated, $schoolId, $gradingScale) {
            if (!empty($validated['is_default'])) {
                GradingScale::where('school_id', $schoolId)
                    ->where('id', '!=', $gradingScale->id)
                    ->update(['is_default' => false]);
            }

            $gradingScale->update([
                'name'       => $validated['name'],
                'is_default' => !empty($validated['is_default']),
            ]);

            // Replace all bands
            $gradingScale->bands()->delete();
            foreach ($validated['bands'] as $band) {
                $gradingScale->bands()->create([
                    'grade'     => trim($band['grade']),
                    'min_score' => $band['min_score'],
                    'max_score' => $band['max_score'],
                    'remark'    => $band['remark']    ?? null,
                    'gpa_point' => $band['gpa_point'] ?? 0,
                ]);
            }
        });

        return redirect()->route('grading-scales.index')
            ->with('success', 'Grading scale updated.');
    }

    public function setDefault(GradingScale $gradingScale): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage grading scales'), 403);
        abort_unless($gradingScale->school_id == $this->schoolId(), 403);

        DB::transaction(function () use ($gradingScale) {
            GradingScale::where('school_id', $gradingScale->school_id)->update(['is_default' => false]);
            $gradingScale->update(['is_default' => true]);
        });

        return back()->with('success', '"' . $gradingScale->name . '" is now the default grading scale.');
    }

    public function destroy(GradingScale $gradingScale): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage grading scales'), 403);
        abort_unless($gradingScale->school_id == $this->schoolId(), 403);

        if ($gradingScale->is_default) {
            return back()->with('error', 'Cannot delete the default grading scale. Set another scale as default first.');
        }

        $gradingScale->bands()->delete();
        $gradingScale->delete();

        return redirect()->route('grading-scales.index')
            ->with('success', 'Grading scale deleted.');
    }
}
