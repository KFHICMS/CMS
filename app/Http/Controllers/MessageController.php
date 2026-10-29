<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\ConversationParticipant;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\MessageInbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Internal messages between system users.
 * Who may message whom: administrators can message any active user; everyone else
 * can message active administrators only. "Delete" hides the conversation for the
 * person who deleted it (recoverable); it never removes anyone else's copy.
 */
class MessageController extends Controller
{
    public function __construct(
        private readonly MessageInbox $inbox,
        private readonly AuditLogService $audit,
    ) {
    }

    public function index(Request $request): View
    {
        $folder = $request->query('folder') === 'deleted' ? 'deleted' : 'inbox';
        $userId = $request->user()->id;

        $page = $this->inbox->query($userId, $folder)->paginate(15)->withQueryString();
        $this->inbox->decorate($page->getCollection(), $userId);

        return view('messages.index', [
            'folder' => $folder,
            'conversations' => $page,
            'unread' => $this->inbox->unreadCount($userId),
        ]);
    }

    /**
     * JSON for the top-bar bell. Deliberately contains only sender, subject and time:
     * message text is never put in a notification (it can show on a locked screen).
     */
    public function notifications(Request $request): JsonResponse
    {
        $user = $request->user();
        $unread = $this->inbox->unreadCount($user->id);
        $items = [];

        if ($unread > 0) {
            $conversations = $this->inbox->query($user->id)->limit(30)->get()
                ->filter(fn ($c) => (int) $c->unread_count > 0)->take(5)->values();
            $this->inbox->decorate($conversations, $user->id);

            $items = $conversations->map(fn ($c) => [
                'id' => $c->id,
                'subject' => Str::limit($c->subject, 80),
                'from' => $c->latest_message?->sender?->name ?? 'Removed user',
                'message_id' => $c->latest_message?->id,
                'time' => Carbon::parse($c->last_message_at)->format('d M, H:i'),
                'url' => route('messages.show', $c),
            ])->all();
        }

        return response()
            ->json([
                'unread' => $unread,
                'items' => $items,
                'inbox_url' => route('messages.index'),
                'login_at' => $user->last_login_at?->timestamp,
            ])
            ->header('Cache-Control', 'no-store');
    }

    public function create(Request $request): View
    {
        return view('messages.create', [
            'recipients' => $this->recipientsFor($request->user())->get(['users.id', 'users.name', 'users.role']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $allowedIds = $this->recipientsFor($user)->pluck('users.id')->all();

        $data = $request->validate([
            'recipients' => ['required', 'array', 'min:1', 'max:50'],
            'recipients.*' => ['integer', 'in:'.implode(',', $allowedIds ?: [0])],
            'subject' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:5000'],
        ], [
            'recipients.*.in' => 'You cannot send a message to one of the selected people.',
        ]);

        $recipientIds = collect($data['recipients'])->unique()->values();

        $conversation = DB::transaction(function () use ($user, $data, $recipientIds) {
            $conversation = Conversation::create(['subject' => $data['subject'], 'created_by' => $user->id]);

            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'last_read_at' => now(),
            ]);
            foreach ($recipientIds as $id) {
                ConversationParticipant::create(['conversation_id' => $conversation->id, 'user_id' => $id]);
            }

            ConversationMessage::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $user->id,
                'body' => $data['body'],
            ]);

            return $conversation;
        });

        // Metadata only: message text is never written to the audit log.
        $this->audit->record($request, 'message_sent', 'messages', $conversation->id,
            'Message sent to '.$recipientIds->count().' recipient(s).');

        return redirect()->route('messages.show', $conversation)->with('success', 'Message sent.');
    }

    public function show(Request $request, Conversation $conversation): View
    {
        $participant = $this->participantOrFail($conversation, $request->user());

        // Opening a conversation marks it as read for this person only.
        $participant->update(['last_read_at' => now()]);

        return view('messages.show', [
            'conversation' => $conversation,
            'messages' => $conversation->messages()->with('sender:id,name,role')->orderBy('created_at')->orderBy('id')->get(),
            'people' => $conversation->participants()->with('user:id,name,role')->get(),
            'hidden' => $participant->deleted_at !== null,
        ]);
    }

    public function reply(Request $request, Conversation $conversation): RedirectResponse
    {
        $participant = $this->participantOrFail($conversation, $request->user());

        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        DB::transaction(function () use ($conversation, $participant, $request, $data) {
            ConversationMessage::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $request->user()->id,
                'body' => $data['body'],
            ]);
            $conversation->touch();

            // A new reply brings the conversation back for anyone who had deleted it.
            $conversation->participants()->update(['deleted_at' => null]);
            $participant->update(['last_read_at' => now()]);
        });

        $this->audit->record($request, 'message_replied', 'messages', $conversation->id, 'Reply sent.');

        return redirect()->route('messages.show', $conversation)->with('success', 'Reply sent.');
    }

    public function destroy(Request $request, Conversation $conversation): RedirectResponse
    {
        $participant = $this->participantOrFail($conversation, $request->user());
        $participant->update(['deleted_at' => now()]);

        $this->audit->record($request, 'message_deleted', 'messages', $conversation->id,
            'Conversation moved to Deleted (recoverable).');

        return redirect()->route('messages.index')
            ->with('success', 'Conversation deleted. You can restore it from the Deleted tab.');
    }

    public function restore(Request $request, Conversation $conversation): RedirectResponse
    {
        $participant = $this->participantOrFail($conversation, $request->user());
        $participant->update(['deleted_at' => null]);

        $this->audit->record($request, 'message_restored', 'messages', $conversation->id, 'Conversation restored.');

        return redirect()->route('messages.show', $conversation)->with('success', 'Conversation restored.');
    }

    /** 404 (not 403) for non-participants so the existence of a conversation is not revealed. */
    private function participantOrFail(Conversation $conversation, User $user): ConversationParticipant
    {
        return $conversation->participants()->where('user_id', $user->id)->firstOrFail();
    }

    private function recipientsFor(User $user)
    {
        return User::query()
            ->where('status', 'active')
            ->where('users.id', '!=', $user->id)
            ->when($user->role !== 'admin', fn ($q) => $q->where('role', 'admin'))
            ->orderBy('name');
    }
}