{{-- Notifications / internal messages. Data comes from App\Services\MessageInbox via a view composer. --}}
<section class="k-card" aria-labelledby="dash-msg-h">
    <div class="k-card-head">
        <h2 id="dash-msg-h">Messages</h2>
        <span>
            @if ($messageUnread > 0)
                <span class="k-badge k-badge-warning">{{ $messageUnread }} unread</span>
            @else
                No unread messages
            @endif
        </span>
    </div>

    @forelse ($messagePreview as $conversation)
        @php($latest = $conversation->latest_message)
        @php($isUnread = (int) $conversation->unread_count > 0)
        <div class="k-activity-item">
            <a href="{{ route('messages.show', $conversation) }}" style="text-decoration: none">
                <strong @if ($isUnread) style="font-weight: 800" @endif>
                    @if ($isUnread)<span class="k-badge k-badge-warning" style="margin-right: 6px">Unread</span>@endif{{ $conversation->subject }}
                </strong>
            </a>
            <p>
                {{ $latest?->sender?->name ?? 'Removed user' }}:
                {{ \Illuminate\Support\Str::limit((string) ($latest?->body), 90) }}
            </p>
            <small>{{ $conversation->last_message_at ? \Illuminate\Support\Carbon::parse($conversation->last_message_at)->format('d M Y, H:i') : '' }}</small>
        </div>
    @empty
        <div class="k-empty">No messages yet.</div>
    @endforelse

    <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 14px">
        <a class="k-btn k-btn-primary" href="{{ route('messages.index') }}">
            <svg class="k-icon" aria-hidden="true">
        <use href="#i-message"></use>
    </svg>Open inbox</a>
        <a class="k-btn k-btn-secondary" href="{{ route('messages.create') }}">+ New message</a>
    </div>
</section>