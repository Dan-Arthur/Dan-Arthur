<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\SchoolClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('view announcements'), 403);

        $schoolId = auth()->user()->school_id;

        $query = Announcement::where('school_id', $schoolId)
            ->with('author')
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('audience')) {
            $query->where('audience', $request->audience);
        }

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(fn($q) => $q->where('title', 'like', $s)->orWhere('body', 'like', $s));
        }

        $announcements = $query->paginate(20)->withQueryString();
        $types         = Announcement::TYPES;
        $audiences     = Announcement::AUDIENCES;
        $statuses      = Announcement::STATUSES;

        return view('announcements.index', compact('announcements', 'types', 'audiences', 'statuses'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('create announcements'), 403);

        $types     = Announcement::TYPES;
        $audiences = Announcement::AUDIENCES;
        $classes   = SchoolClass::where('school_id', auth()->user()->school_id)
            ->where('is_active', true)->orderBy('name')->get();

        return view('announcements.create', compact('types', 'audiences', 'classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('create announcements'), 403);

        $validated = $request->validate([
            'title'      => 'required|string|max:300',
            'body'       => 'required|string',
            'type'       => 'required|in:' . implode(',', array_keys(Announcement::TYPES)),
            'audience'   => 'required|in:' . implode(',', array_keys(Announcement::AUDIENCES)),
            'is_pinned'  => 'boolean',
            'publish_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:publish_at',
            'status'     => 'required|in:draft,published,archived',
        ]);

        $validated['school_id']  = auth()->user()->school_id;
        $validated['created_by'] = auth()->id();
        $validated['is_pinned']  = $request->boolean('is_pinned');

        if ($request->filled('class_ids')) {
            $validated['audience_filter'] = ['class_ids' => $request->input('class_ids')];
        }

        $announcement = Announcement::create($validated);

        return redirect()->route('announcements.show', $announcement)->with('success', 'Announcement created.');
    }

    public function show(Announcement $announcement): View
    {
        abort_unless(auth()->user()->can('view announcements'), 403);
        abort_unless($announcement->school_id == auth()->user()->school_id, 403);

        $announcement->load('author');

        return view('announcements.show', compact('announcement'));
    }

    public function edit(Announcement $announcement): View
    {
        abort_unless(auth()->user()->can('manage announcements'), 403);
        abort_unless($announcement->school_id == auth()->user()->school_id, 403);

        $types     = Announcement::TYPES;
        $audiences = Announcement::AUDIENCES;
        $classes   = SchoolClass::where('school_id', auth()->user()->school_id)
            ->where('is_active', true)->orderBy('name')->get();

        return view('announcements.edit', compact('announcement', 'types', 'audiences', 'classes'));
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage announcements'), 403);
        abort_unless($announcement->school_id == auth()->user()->school_id, 403);

        $validated = $request->validate([
            'title'      => 'required|string|max:300',
            'body'       => 'required|string',
            'type'       => 'required|in:' . implode(',', array_keys(Announcement::TYPES)),
            'audience'   => 'required|in:' . implode(',', array_keys(Announcement::AUDIENCES)),
            'is_pinned'  => 'boolean',
            'publish_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'status'     => 'required|in:draft,published,archived',
        ]);

        $validated['is_pinned'] = $request->boolean('is_pinned');

        if ($request->filled('class_ids')) {
            $validated['audience_filter'] = ['class_ids' => $request->input('class_ids')];
        } else {
            $validated['audience_filter'] = null;
        }

        $announcement->update($validated);

        return redirect()->route('announcements.show', $announcement)->with('success', 'Announcement updated.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage announcements'), 403);
        abort_unless($announcement->school_id == auth()->user()->school_id, 403);

        $announcement->delete();

        return redirect()->route('announcements.index')->with('success', 'Announcement deleted.');
    }

    public function publish(Announcement $announcement): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage announcements'), 403);
        abort_unless($announcement->school_id == auth()->user()->school_id, 403);

        $announcement->update([
            'status'     => 'published',
            'publish_at' => $announcement->publish_at ?? now(),
        ]);

        return back()->with('success', 'Announcement published.');
    }

    public function archive(Announcement $announcement): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage announcements'), 403);
        abort_unless($announcement->school_id == auth()->user()->school_id, 403);

        $announcement->update(['status' => 'archived']);

        return back()->with('success', 'Announcement archived.');
    }
}
