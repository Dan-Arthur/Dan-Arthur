<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Campus;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\SchoolClass;
use App\Models\Term;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeeController extends Controller
{
    private function gate(): void
    {
        abort_unless(auth()->user()->can('view fees'), 403);
    }

    private function manageGate(): void
    {
        abort_unless(auth()->user()->can('manage fee structures'), 403);
    }

    // ============================================================
    // FEE STRUCTURES
    // ============================================================

    public function index(Request $request): View
    {
        $this->gate();

        $schoolId = auth()->user()->school_id;
        $tab = $request->get('tab', 'structures');

        $years    = AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get();
        $currentYear = $years->firstWhere('is_current', true) ?? $years->first();
        $selectedYearId = $request->integer('year_id', $currentYear?->id ?? 0);

        $structures = FeeStructure::where('school_id', $schoolId)
            ->when($selectedYearId, fn($q) => $q->where('academic_year_id', $selectedYearId))
            ->with(['academicYear', 'term', 'schoolClass', 'items'])
            ->orderBy('name')
            ->get();

        $categories = FeeCategory::where('school_id', $schoolId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('fees.index', compact('structures', 'categories', 'years', 'selectedYearId', 'tab'));
    }

    public function create(): View
    {
        $this->manageGate();

        $schoolId = auth()->user()->school_id;
        $years    = AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get();
        $currentYear = $years->firstWhere('is_current', true) ?? $years->first();
        $terms    = $currentYear ? Term::where('academic_year_id', $currentYear->id)->orderBy('sequence')->get() : collect();
        $classes  = SchoolClass::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();
        $campuses = Campus::where('school_id', $schoolId)->orderBy('name')->get();
        $categories = FeeCategory::where('school_id', $schoolId)->where('is_active', true)->orderBy('sort_order')->get();

        return view('fees.create', compact('years', 'terms', 'classes', 'campuses', 'categories', 'currentYear'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->manageGate();

        $validated = $request->validate([
            'name'                  => 'required|string|max:200',
            'academic_year_id'      => 'required|exists:academic_years,id',
            'term_id'               => 'nullable|exists:terms,id',
            'class_id'              => 'nullable|exists:school_classes,id',
            'campus_id'             => 'nullable|exists:campuses,id',
            'applies_to_all_classes'=> 'boolean',
            'student_category'      => 'nullable|string|max:50',
            'items'                 => 'required|array|min:1',
            'items.*.name'          => 'required|string|max:200',
            'items.*.amount'        => 'required|numeric|min:0',
            'items.*.fee_category_id' => 'nullable|exists:fee_categories,id',
            'items.*.is_mandatory'  => 'boolean',
        ]);

        $schoolId = auth()->user()->school_id;

        $structure = FeeStructure::create([
            'school_id'              => $schoolId,
            'academic_year_id'       => $validated['academic_year_id'],
            'term_id'                => $validated['term_id'] ?? null,
            'class_id'               => $validated['class_id'] ?? null,
            'campus_id'              => $validated['campus_id'] ?? null,
            'name'                   => $validated['name'],
            'applies_to_all_classes' => $request->boolean('applies_to_all_classes'),
            'student_category'       => $validated['student_category'] ?? null,
            'is_active'              => true,
        ]);

        foreach ($validated['items'] as $i => $item) {
            $structure->items()->create([
                'fee_category_id' => $item['fee_category_id'] ?? null,
                'name'            => $item['name'],
                'amount'          => $item['amount'],
                'is_mandatory'    => !empty($item['is_mandatory']),
                'sort_order'      => $i + 1,
            ]);
        }

        return redirect()->route('fees.show', $structure)->with('success', 'Fee structure created.');
    }

    public function show(FeeStructure $fee): View
    {
        $this->gate();
        abort_unless($fee->school_id == auth()->user()->school_id, 403);

        $fee->load(['academicYear', 'term', 'schoolClass', 'campus', 'items.feeCategory']);

        return view('fees.show', compact('fee'));
    }

    public function edit(FeeStructure $fee): View
    {
        $this->manageGate();
        abort_unless($fee->school_id == auth()->user()->school_id, 403);

        $schoolId   = auth()->user()->school_id;
        $years      = AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get();
        $terms      = Term::where('academic_year_id', $fee->academic_year_id)->orderBy('sequence')->get();
        $classes    = SchoolClass::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();
        $campuses   = Campus::where('school_id', $schoolId)->orderBy('name')->get();
        $categories = FeeCategory::where('school_id', $schoolId)->where('is_active', true)->orderBy('sort_order')->get();

        $fee->load('items.feeCategory');

        return view('fees.edit', compact('fee', 'years', 'terms', 'classes', 'campuses', 'categories'));
    }

    public function update(Request $request, FeeStructure $fee): RedirectResponse
    {
        $this->manageGate();
        abort_unless($fee->school_id == auth()->user()->school_id, 403);

        $validated = $request->validate([
            'name'             => 'required|string|max:200',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id'          => 'nullable|exists:terms,id',
            'class_id'         => 'nullable|exists:school_classes,id',
            'campus_id'        => 'nullable|exists:campuses,id',
            'student_category' => 'nullable|string|max:50',
            'items'            => 'required|array|min:1',
            'items.*.name'     => 'required|string|max:200',
            'items.*.amount'   => 'required|numeric|min:0',
            'items.*.fee_category_id' => 'nullable|exists:fee_categories,id',
            'items.*.is_mandatory'    => 'boolean',
        ]);

        $fee->update([
            'name'             => $validated['name'],
            'academic_year_id' => $validated['academic_year_id'],
            'term_id'          => $validated['term_id'] ?? null,
            'class_id'         => $validated['class_id'] ?? null,
            'campus_id'        => $validated['campus_id'] ?? null,
            'student_category' => $validated['student_category'] ?? null,
            'applies_to_all_classes' => $request->boolean('applies_to_all_classes'),
        ]);

        $fee->items()->delete();
        foreach ($validated['items'] as $i => $item) {
            $fee->items()->create([
                'fee_category_id' => $item['fee_category_id'] ?? null,
                'name'            => $item['name'],
                'amount'          => $item['amount'],
                'is_mandatory'    => !empty($item['is_mandatory']),
                'sort_order'      => $i + 1,
            ]);
        }

        return redirect()->route('fees.show', $fee)->with('success', 'Fee structure updated.');
    }

    public function destroy(FeeStructure $fee): RedirectResponse
    {
        $this->manageGate();
        abort_unless($fee->school_id == auth()->user()->school_id, 403);

        if ($fee->invoices()->exists()) {
            return back()->with('error', 'Cannot delete fee structure that has invoices linked to it.');
        }

        $fee->items()->delete();
        $fee->delete();

        return redirect()->route('fees.index')->with('success', 'Fee structure deleted.');
    }

    // ============================================================
    // FEE CATEGORIES
    // ============================================================

    public function storeCategory(Request $request): RedirectResponse
    {
        $this->manageGate();

        $validated = $request->validate([
            'name'        => 'required|string|max:150',
            'code'        => 'nullable|string|max:30',
            'description' => 'nullable|string|max:500',
        ]);

        $schoolId = auth()->user()->school_id;
        $maxOrder = FeeCategory::where('school_id', $schoolId)->max('sort_order') ?? 0;

        FeeCategory::create([
            'school_id'  => $schoolId,
            'name'       => $validated['name'],
            'code'       => $validated['code'] ?? null,
            'description'=> $validated['description'] ?? null,
            'is_active'  => true,
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()->route('fees.index', ['tab' => 'categories'])->with('success', 'Category added.');
    }

    public function updateCategory(Request $request, FeeCategory $category): RedirectResponse
    {
        $this->manageGate();
        abort_unless($category->school_id == auth()->user()->school_id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:30',
        ]);

        $category->update($validated);

        return redirect()->route('fees.index', ['tab' => 'categories'])->with('success', 'Category updated.');
    }

    public function destroyCategory(FeeCategory $category): RedirectResponse
    {
        $this->manageGate();
        abort_unless($category->school_id == auth()->user()->school_id, 403);

        if ($category->structureItems()->exists()) {
            return back()->with('error', 'Category is in use by fee structure items.');
        }

        $category->delete();

        return redirect()->route('fees.index', ['tab' => 'categories'])->with('success', 'Category deleted.');
    }
}
