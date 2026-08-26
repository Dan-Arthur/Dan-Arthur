<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('manage events'), 403);

        $schoolId = auth()->user()->school_id;

        $query = Event::where('school_id', $schoolId)->with('author');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('month')) {
            [$year, $month] = explode('-', $request->month);
            $query->whereYear('start_datetime', $year)->whereMonth('start_datetime', $month);
        }

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(fn($q) => $q->where('title', 'like', $s)->orWhere('location', 'like', $s));
        }

        // Auto-update statuses: past scheduled → completed, ongoing → in_progress
        Event::where('school_id', $schoolId)
            ->where('status', 'scheduled')
            ->where('start_datetime', '<=', now())
            ->whereNull('end_datetime')
            ->update(['status' => 'completed']);

        Event::where('school_id', $schoolId)
            ->where('status', 'scheduled')
            ->where('start_datetime', '<=', now())
            ->whereNotNull('end_datetime')
            ->where('end_datetime', '>', now())
            ->update(['status' => 'in_progress']);

        Event::where('school_id', $schoolId)
            ->where('status', 'in_progress')
            ->where('end_datetime', '<=', now())
            ->update(['status' => 'completed']);

        $events  = $query->orderBy('start_datetime')->paginate(25)->withQueryString();
        $types   = Event::TYPES;
        $statuses = Event::STATUSES;
        $upcoming = Event::where('school_id', $schoolId)
            ->where('status', 'scheduled')
            ->where('start_datetime', '>', now())
            ->orderBy('start_datetime')
            ->limit(5)
            ->get();

        return view('events.index', compact('events', 'types', 'statuses', 'upcoming'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('manage events'), 403);

        $types     = Event::TYPES;
        $audiences = Event::AUDIENCES;

        return view('events.create', compact('types', 'audiences'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage events'), 403);

        $validated = $request->validate([
            'title'          => 'required|string|max:300',
            'description'    => 'nullable|string',
            'type'           => 'required|in:' . implode(',', array_keys(Event::TYPES)),
            'audience'       => 'required|in:' . implode(',', array_keys(Event::AUDIENCES)),
            'start_datetime' => 'required|date',
            'end_datetime'   => 'nullable|date|after_or_equal:start_datetime',
            'all_day'        => 'boolean',
            'location'       => 'nullable|string|max:300',
            'color'          => 'nullable|string|max:20',
            'status'         => 'required|in:scheduled,in_progress,completed,cancelled',
        ]);

        $validated['school_id']  = auth()->user()->school_id;
        $validated['created_by'] = auth()->id();
        $validated['all_day']    = $request->boolean('all_day');
        $validated['color']      = $validated['color'] ?? '#3B82F6';

        $event = Event::create($validated);

        return redirect()->route('events.show', $event)->with('success', 'Event created.');
    }

    public function show(Event $event): View
    {
        abort_unless(auth()->user()->can('manage events'), 403);
        abort_unless($event->school_id == auth()->user()->school_id, 403);

        $event->load('author');

        return view('events.show', compact('event'));
    }

    public function edit(Event $event): View
    {
        abort_unless(auth()->user()->can('manage events'), 403);
        abort_unless($event->school_id == auth()->user()->school_id, 403);

        $types     = Event::TYPES;
        $audiences = Event::AUDIENCES;

        return view('events.edit', compact('event', 'types', 'audiences'));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage events'), 403);
        abort_unless($event->school_id == auth()->user()->school_id, 403);

        $validated = $request->validate([
            'title'          => 'required|string|max:300',
            'description'    => 'nullable|string',
            'type'           => 'required|in:' . implode(',', array_keys(Event::TYPES)),
            'audience'       => 'required|in:' . implode(',', array_keys(Event::AUDIENCES)),
            'start_datetime' => 'required|date',
            'end_datetime'   => 'nullable|date|after_or_equal:start_datetime',
            'all_day'        => 'boolean',
            'location'       => 'nullable|string|max:300',
            'color'          => 'nullable|string|max:20',
            'status'         => 'required|in:scheduled,in_progress,completed,cancelled',
        ]);

        $validated['all_day'] = $request->boolean('all_day');

        $event->update($validated);

        return redirect()->route('events.show', $event)->with('success', 'Event updated.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage events'), 403);
        abort_unless($event->school_id == auth()->user()->school_id, 403);

        $event->delete();

        return redirect()->route('events.index')->with('success', 'Event deleted.');
    }

    public function cancel(Event $event): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage events'), 403);
        abort_unless($event->school_id == auth()->user()->school_id, 403);

        if ($event->status === 'completed') {
            return back()->with('error', 'Cannot cancel a completed event.');
        }

        $event->update(['status' => 'cancelled']);

        return back()->with('success', 'Event cancelled.');
    }
}
