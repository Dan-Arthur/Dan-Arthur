<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\BookCopy;
use App\Models\BookLoan;
use App\Models\Employee;
use App\Models\Publisher;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LibraryController extends Controller
{
    // ============================================================
    // BOOKS
    // ============================================================

    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('view library'), 403);

        $schoolId = auth()->user()->school_id;

        $query = Book::where('school_id', $schoolId)
            ->with(['category', 'authors'])
            ->withCount(['copies', 'loans as active_loans_count' => fn($q) => $q->where('status', 'active')]);

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(fn($q) => $q
                ->where('title', 'like', $s)
                ->orWhere('isbn', 'like', $s)
                ->orWhereHas('authors', fn($a) => $a->where('name', 'like', $s)));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('available')) {
            $query->where('available_copies', '>', 0);
        }

        $books      = $query->orderBy('title')->paginate(30)->withQueryString();
        $categories = BookCategory::where('school_id', $schoolId)->orderBy('name')->get();

        return view('library.index', compact('books', 'categories'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('manage books'), 403);

        $schoolId   = auth()->user()->school_id;
        $categories = BookCategory::where('school_id', $schoolId)->orderBy('name')->get();
        $publishers = Publisher::where('school_id', $schoolId)->orderBy('name')->get();
        $authors    = Author::where('school_id', $schoolId)->orderBy('name')->get();

        return view('library.create', compact('categories', 'publishers', 'authors'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage books'), 403);

        $validated = $request->validate([
            'title'        => 'required|string|max:300',
            'isbn'         => 'nullable|string|max:30',
            'edition'      => 'nullable|string|max:50',
            'publish_year' => 'nullable|integer|min:1800|max:' . (date('Y') + 1),
            'language'     => 'nullable|string|max:50',
            'category_id'  => 'nullable|exists:book_categories,id',
            'publisher_id' => 'nullable|exists:publishers,id',
            'location'     => 'nullable|string|max:100',
            'copies'       => 'required|integer|min:1',
            'author_ids'   => 'nullable|array',
            'author_ids.*' => 'exists:authors,id',
        ]);

        $schoolId = auth()->user()->school_id;

        DB::transaction(function () use ($validated, $schoolId) {
            $book = Book::create([
                'school_id'        => $schoolId,
                'title'            => $validated['title'],
                'isbn'             => $validated['isbn'] ?? null,
                'edition'          => $validated['edition'] ?? null,
                'publish_year'     => $validated['publish_year'] ?? null,
                'language'         => $validated['language'] ?? 'English',
                'category_id'      => $validated['category_id'] ?? null,
                'publisher_id'     => $validated['publisher_id'] ?? null,
                'location'         => $validated['location'] ?? null,
                'total_copies'     => $validated['copies'],
                'available_copies' => $validated['copies'],
                'is_active'        => true,
            ]);

            if (!empty($validated['author_ids'])) {
                $book->authors()->sync($validated['author_ids']);
            }

            for ($i = 1; $i <= $validated['copies']; $i++) {
                $book->copies()->create([
                    'accession_number' => $this->nextAccession($schoolId, $book->id, $i),
                    'condition'        => 'good',
                    'status'           => 'available',
                ]);
            }
        });

        return redirect()->route('library.index')->with('success', 'Book added to library.');
    }

    public function show(Book $book): View
    {
        abort_unless(auth()->user()->can('view library'), 403);
        abort_unless($book->school_id == auth()->user()->school_id, 403);

        $book->load(['category', 'authors', 'publisher',
            'copies' => fn($q) => $q->with('activeLoan')]);

        return view('library.show', compact('book'));
    }

    // ============================================================
    // LOANS
    // ============================================================

    public function loans(Request $request): View
    {
        abort_unless(auth()->user()->can('view library'), 403);

        $schoolId = auth()->user()->school_id;

        $query = BookLoan::where('school_id', $schoolId)
            ->with(['bookCopy.book', 'issuedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sync overdue status
        BookLoan::where('school_id', $schoolId)
            ->where('status', 'active')
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);

        $loans   = $query->orderByDesc('loan_date')->paginate(30)->withQueryString();
        $statuses = BookLoan::STATUSES;

        return view('library.loans', compact('loans', 'statuses'));
    }

    public function issue(Request $request): View
    {
        abort_unless(auth()->user()->can('issue books'), 403);

        $schoolId = auth()->user()->school_id;
        $books    = Book::where('school_id', $schoolId)
            ->where('available_copies', '>', 0)
            ->where('is_active', true)
            ->with('authors')
            ->orderBy('title')->get();

        return view('library.issue', compact('books'));
    }

    public function issueStore(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('issue books'), 403);

        $validated = $request->validate([
            'book_id'     => 'required|exists:books,id',
            'member_type' => 'required|in:student,employee',
            'member_id'   => 'required|integer',
            'loan_date'   => 'required|date',
            'due_date'    => 'required|date|after:loan_date',
        ]);

        $schoolId = auth()->user()->school_id;

        $book = Book::where('school_id', $schoolId)->findOrFail($validated['book_id']);

        if ($book->available_copies < 1) {
            return back()->with('error', 'No available copies of this book.');
        }

        $copy = $book->copies()->where('status', 'available')->first();
        if (!$copy) {
            return back()->with('error', 'No available copy found.');
        }

        DB::transaction(function () use ($validated, $schoolId, $book, $copy) {
            BookLoan::create([
                'school_id'    => $schoolId,
                'book_copy_id' => $copy->id,
                'member_type'  => $validated['member_type'],
                'member_id'    => $validated['member_id'],
                'loan_date'    => $validated['loan_date'],
                'due_date'     => $validated['due_date'],
                'status'       => 'active',
                'issued_by'    => auth()->id(),
            ]);

            $copy->update(['status' => 'loaned']);
            $book->decrement('available_copies');
        });

        return redirect()->route('library.loans')->with('success', 'Book issued successfully.');
    }

    public function returnBook(Request $request, BookLoan $loan): RedirectResponse
    {
        abort_unless(auth()->user()->can('return books'), 403);
        abort_unless($loan->school_id == auth()->user()->school_id, 403);

        if (!in_array($loan->status, ['active', 'overdue'])) {
            return back()->with('error', 'This loan is not active.');
        }

        $validated = $request->validate([
            'return_condition' => 'required|in:good,fair,poor,damaged,lost',
            'fine_paid'        => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($loan, $validated) {
            $newStatus = $validated['return_condition'] === 'lost' ? 'lost' : 'returned';

            $loan->update([
                'return_date'      => now()->toDateString(),
                'status'           => $newStatus,
                'returned_to'      => auth()->id(),
                'return_condition' => $validated['return_condition'],
                'fine_paid'        => $validated['fine_paid'] ?? 0,
            ]);

            $copyStatus = match ($validated['return_condition']) {
                'lost', 'damaged' => $validated['return_condition'],
                default           => 'available',
            };
            $loan->bookCopy->update(['status' => $copyStatus, 'condition' => $validated['return_condition']]);

            if ($copyStatus === 'available') {
                $loan->bookCopy->book->increment('available_copies');
            }
        });

        return back()->with('success', 'Book returned.');
    }

    // ============================================================
    // CATALOGUE MANAGEMENT (categories, authors, publishers)
    // ============================================================

    public function catalogue(): View
    {
        abort_unless(auth()->user()->can('manage books'), 403);

        $schoolId   = auth()->user()->school_id;
        $categories = BookCategory::where('school_id', $schoolId)->withCount('books')->orderBy('name')->get();
        $authors    = Author::where('school_id', $schoolId)->withCount('books')->orderBy('name')->get();
        $publishers = Publisher::where('school_id', $schoolId)->withCount('books')->orderBy('name')->get();

        return view('library.catalogue', compact('categories', 'authors', 'publishers'));
    }

    public function storeCatalogue(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage books'), 403);

        $schoolId = auth()->user()->school_id;
        $type = $request->input('type');

        match ($type) {
            'category' => BookCategory::create([
                'school_id' => $schoolId,
                'name'      => $request->validate(['name' => 'required|string|max:150'])['name'],
                'code'      => $request->input('code'),
            ]),
            'author' => Author::create([
                'school_id' => $schoolId,
                'name'      => $request->validate(['name' => 'required|string|max:200'])['name'],
            ]),
            'publisher' => Publisher::create([
                'school_id' => $schoolId,
                'name'      => $request->validate(['name' => 'required|string|max:200'])['name'],
                'contact'   => $request->input('contact'),
            ]),
            default => abort(400),
        };

        return redirect()->route('library.catalogue')->with('success', ucfirst($type) . ' added.');
    }

    private function nextAccession(int $schoolId, int $bookId, int $seq): string
    {
        return 'ACC-' . str_pad($bookId, 5, '0', STR_PAD_LEFT) . '-' . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }
}
