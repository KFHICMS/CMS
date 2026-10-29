@extends('layouts.app', [
    'active' => 'messages',
    'crumbs' => [
        ['label' => 'Messages', 'url' => route('messages.index')],
        ['label' => 'Conversation'],
    ],
])

@section('title', $conversation->subject)
@section('heading', $conversation->subject)
@section('subheading', 'With: '.$people->where('user_id', '!=', auth()->id())->map(fn ($p) => $p->user?->name ?? 'Removed user')->join(', '))

@section('actions')
@if ($hidden)
    <form method="POST" action="{{ route('messages.restore', $conversation) }}">
        @csrf @method('PATCH')
        <button type="submit" class="k-btn k-btn-primary">Restore to inbox</button>
    </form>
@else
    <form method="POST" action="{{ route('messages.destroy', $conversation) }}" onsubmit="return confirm('Delete this conversation? It is only removed from your inbox and can be restored.')">
        @csrf @method('DELETE')
        <button type="submit" class="k-btn k-btn-danger">Delete</button>
    </form>
@endif
@endsection

@section('content')
    @if ($errors->any())
        <div class="k-alert k-alert-error" role="alert">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="k-stack" style="max-width: 820px">
        @if ($hidden)
            <div class="k-alert k-alert-warning" role="status">You deleted this conversation. It is hidden from your inbox. Replying or restoring brings it back.</div>
        @endif

        @foreach ($messages as $message)
            @php($mine = $message->sender_id === auth()->id())
            <article class="k-card" @if ($mine) style="border-left: 4px solid var(--k-primary)" @endif>
                <div class="k-card-head">
                    <h3 style="margin: 0">{{ $mine ? 'You' : ($message->sender?->name ?? 'Removed user') }}
                        @if (! $mine && $message->sender)<span class="muted">({{ str_replace('_', ' ', $message->sender->role) }})</span>@endif
                    </h3>
                    <span><time datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->format('d M Y, H:i') }}</time></span>
                </div>
                <div style="white-space: pre-wrap">{{ $message->body }}</div>
            </article>
        @endforeach

        <form method="POST" action="{{ route('messages.reply', $conversation) }}" class="k-card">
            @csrf
            <div class="k-field">
                <label for="body">Reply</label>
                <textarea id="body" name="body" class="k-textarea" rows="4" maxlength="5000" required>{{ old('body') }}</textarea>
                <p class="k-help">Do not include children’s personal details. Use the child code.</p>
            </div>
            <button type="submit" class="k-btn k-btn-primary">Send reply</button>
            <a class="k-btn k-btn-secondary" href="{{ route('messages.index') }}">Back to inbox</a>
        </form>
    </div>
@endsection