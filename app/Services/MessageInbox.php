<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Read side of internal messaging: one person's inbox, unread counts and previews.
 * "Unread" = the conversation has a message from someone else newer than this
 * person's last_read_at.
 */
class MessageInbox
{
    /** Conversations visible to $userId: folder 'inbox' (not deleted) or 'deleted' (hidden by them). */
    public function query(int $userId, string $folder = 'inbox'): Builder
    {
        return Conversation::query()
            ->join('conversation_participants as p', function ($join) use ($userId) {
                $join->on('p.conversation_id', '=', 'conversations.id')
                    ->where('p.user_id', '=', $userId);
            })
            ->when(
                $folder === 'deleted',
                fn ($q) => $q->whereNotNull('p.deleted_at'),
                fn ($q) => $q->whereNull('p.deleted_at')
            )
            ->select('conversations.*', 'p.last_read_at', 'p.deleted_at as hidden_at')
            ->selectSub(
                fn ($q) => $q->from('conversation_messages as m')
                    ->selectRaw('MAX(m.created_at)')
                    ->whereColumn('m.conversation_id', 'conversations.id'),
                'last_message_at'
            )
            ->selectSub(
                fn ($q) => $q->from('conversation_messages as m')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('m.conversation_id', 'conversations.id')
                    ->where('m.sender_id', '!=', $userId)
                    ->where(fn ($w) => $w->whereNull('p.last_read_at')
                        ->orWhereColumn('m.created_at', '>', 'p.last_read_at')),
                'unread_count'
            )
            ->orderByDesc('last_message_at')
            ->orderByDesc('conversations.id');
    }

    /** Number of inbox conversations that contain something unread. */
    public function unreadCount(int $userId): int
    {
        return ConversationMessage::query()
            ->join('conversation_participants as p', function ($join) use ($userId) {
                $join->on('p.conversation_id', '=', 'conversation_messages.conversation_id')
                    ->where('p.user_id', '=', $userId)
                    ->whereNull('p.deleted_at');
            })
            ->where('conversation_messages.sender_id', '!=', $userId)
            ->where(fn ($q) => $q->whereNull('p.last_read_at')
                ->orWhereColumn('conversation_messages.created_at', '>', 'p.last_read_at'))
            ->distinct()
            ->count('conversation_messages.conversation_id');
    }

    /** Adds ->latest_message and ->with_names (the other people) to each conversation. */
    public function decorate(iterable $conversations, int $userId): Collection
    {
        $conversations = collect($conversations);
        $ids = $conversations->pluck('id');

        if ($ids->isEmpty()) {
            return $conversations;
        }

        $latestIds = ConversationMessage::query()
            ->selectRaw('MAX(id) as id')
            ->whereIn('conversation_id', $ids)
            ->groupBy('conversation_id')
            ->pluck('id');

        $latest = ConversationMessage::with('sender:id,name')
            ->whereIn('id', $latestIds)->get()->keyBy('conversation_id');

        $participants = \App\Models\ConversationParticipant::with('user:id,name')
            ->whereIn('conversation_id', $ids)->where('user_id', '!=', $userId)
            ->get()->groupBy('conversation_id');

        return $conversations->each(function ($conversation) use ($latest, $participants) {
            $conversation->latest_message = $latest->get($conversation->id);
            $conversation->with_names = $participants->get($conversation->id, collect())
                ->map(fn ($p) => $p->user?->name ?? 'Removed user')->join(', ') ?: 'Only you';
        });
    }
}
