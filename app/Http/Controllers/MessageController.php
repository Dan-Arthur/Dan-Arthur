<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\MessageRecipient;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('view messages'), 403);

        $schoolId = auth()->user()->school_id;
        $tab      = $request->get('tab', 'inbox');

        if ($tab === 'sent') {
            $messages = Message::where('school_id', $schoolId)
                ->where('sender_id', auth()->id())
                ->with(['recipients.user'])
                ->orderByDesc('created_at')
                ->paginate(25)
                ->withQueryString();

            $unreadCount = 0;
        } else {
            // inbox: messages where I am a recipient and haven't deleted
            $messages = MessageRecipient::where('recipient_id', auth()->id())
                ->where('is_deleted', false)
                ->with(['message.sender'])
                ->orderByDesc('created_at')
                ->paginate(25)
                ->withQueryString();

            $unreadCount = MessageRecipient::where('recipient_id', auth()->id())
                ->where('is_read', false)
                ->where('is_deleted', false)
                ->count();
        }

        return view('messages.index', compact('messages', 'tab', 'unreadCount'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('send messages'), 403);
        return view('messages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('send messages'), 403);

        $validated = $request->validate([
            'subject'        => 'nullable|string|max:300',
            'body'           => 'required|string',
            'type'           => 'required|in:internal,email,sms',
            'recipient_ids'  => 'required|array|min:1',
            'recipient_ids.*'=> 'exists:users,id',
        ]);

        $schoolId = auth()->user()->school_id;

        // Ensure all recipients belong to the same school
        $recipientIds = collect($validated['recipient_ids'])->unique()->values();
        $validCount   = User::where('school_id', $schoolId)->whereIn('id', $recipientIds)->count();

        if ($validCount !== $recipientIds->count()) {
            return back()->withInput()->with('error', 'One or more recipients are invalid.');
        }

        DB::transaction(function () use ($validated, $recipientIds, $schoolId) {
            $message = Message::create([
                'school_id' => $schoolId,
                'sender_id' => auth()->id(),
                'subject'   => $validated['subject'] ?? null,
                'body'      => $validated['body'],
                'type'      => $validated['type'],
                'status'    => 'sent',
            ]);

            foreach ($recipientIds as $recipientId) {
                MessageRecipient::create([
                    'message_id'   => $message->id,
                    'recipient_id' => $recipientId,
                ]);
            }
        });

        return redirect()->route('messages.index', ['tab' => 'sent'])->with('success', 'Message sent.');
    }

    public function show(Request $request, $messageOrRecipient): View
    {
        $schoolId = auth()->user()->school_id;

        // Accept either a message_id (sent view) or recipient pivot record
        if ($request->get('tab') === 'sent') {
            $message = Message::where('school_id', $schoolId)
                ->where('sender_id', auth()->id())
                ->with(['sender', 'recipients.user'])
                ->findOrFail($messageOrRecipient);

            $pivot = null;
        } else {
            $pivot = MessageRecipient::where('recipient_id', auth()->id())
                ->where('message_id', $messageOrRecipient)
                ->firstOrFail();

            $message = $pivot->message()->with('sender', 'recipients.user')->first();
            abort_unless($message->school_id === $schoolId, 403);

            // Mark as read
            if (!$pivot->is_read) {
                $pivot->update(['is_read' => true, 'read_at' => now()]);
            }
        }

        return view('messages.show', compact('message', 'pivot'));
    }

    public function reply(Request $request, Message $message): RedirectResponse
    {
        $schoolId = auth()->user()->school_id;
        abort_unless($message->school_id === $schoolId, 403);

        $validated = $request->validate(['body' => 'required|string']);

        DB::transaction(function () use ($validated, $message, $schoolId) {
            $reply = Message::create([
                'school_id' => $schoolId,
                'sender_id' => auth()->id(),
                'subject'   => 'Re: ' . ($message->subject ?? '(no subject)'),
                'body'      => $validated['body'],
                'type'      => 'internal',
                'status'    => 'sent',
            ]);

            MessageRecipient::create([
                'message_id'   => $reply->id,
                'recipient_id' => $message->sender_id,
            ]);
        });

        return back()->with('success', 'Reply sent.');
    }

    public function star(Request $request, $messageId): RedirectResponse
    {
        $pivot = MessageRecipient::where('recipient_id', auth()->id())
            ->where('message_id', $messageId)
            ->firstOrFail();

        $pivot->update(['is_starred' => !$pivot->is_starred]);

        return back()->with('success', $pivot->is_starred ? 'Starred.' : 'Unstarred.');
    }

    public function trash($messageId): RedirectResponse
    {
        $pivot = MessageRecipient::where('recipient_id', auth()->id())
            ->where('message_id', $messageId)
            ->firstOrFail();

        $pivot->update(['is_deleted' => true]);

        return redirect()->route('messages.index')->with('success', 'Message removed from inbox.');
    }

    public function searchUsers(Request $request): JsonResponse
    {
        $schoolId = auth()->user()->school_id;
        $q = '%' . $request->get('q', '') . '%';

        $users = User::where('school_id', $schoolId)
            ->where('id', '!=', auth()->id())
            ->where(fn($query) => $query->where('name', 'like', $q)->orWhere('email', 'like', $q))
            ->limit(15)
            ->get()
            ->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email]);

        return response()->json($users);
    }
}
