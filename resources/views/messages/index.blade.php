@extends('layouts.app', [
    'active' => 'messages',
    'crumbs' => [
        ['label' => 'Dashboard', 'url' => route(auth()->user()->role === 'admin' ? 'admin.dashboard' : auth()->user()->role.'.dashboard')],
        ['label' => 'Messages'],
    ],
])

@section('title', 'Messages')
@section('heading', 'Messages')
@section('subheading', $folder === 'deleted' ? 'Conversations you deleted. Restore one to move it back to your inbox.' : 'Messages between system users.')

@section('actions')
<a class="k-btn k-btn-primary" href="{{ route('messages.create') }}">+ New message</a>
@endsection

@section('content')
<div class="k-stack">
    <p class="tabs">
        <a @class(['current' => $folder === 'inbox']) href="{{ route('messages.index') }}">Inbox @if ($unread > 0)({{ $unread }} unread)@endif</a>
        <a @class(['current' => $folder === 'deleted']) href="{{ route('messages.index', ['folder' => 'deleted']) }}">Deleted</a>
    </p>

    <section class="k-card k-table-wrap">
        <table class="k-table">
            <thead>
                <tr><th>With</th><th>Subject</th><th>Latest message</th><th>Date</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            @forelse ($conversations as $conversation)
                @php($latest = $conversation->latest_message)
                @php($isUnread = (int) $conversation->unread_count > 0)
                <tr>
                    <td @if ($isUnread) style="font-weight: 700" @endif>{{ $conversation->with_names }}</td>
                    <td @if ($isUnread) style="font-weight: 700" @endif>
                        <a href="{{ route('messages.show', $conversation) }}">{{ $conversation->subject }}</a>
                    </td>
                    <td>
                        @if ($latest)
                            <span class="muted">{{ $latest->sender?->name ?? 'Removed user' }}:</span>
                            {{ \Illuminate\Support\Str::limit($latest->body, 80) }}
                        @endif
                    </td>
                    <td style="white-space: nowrap">{{ $conversation->last_message_at ? \Illuminate\Support\Carbon::parse($conversation->last_message_at)->format('d M Y, H:i') : '' }}</td>
                    <td>
                        @if ($isUnread)
                            <span class="k-badge k-badge-warning">Unread ({{ (int) $conversation->unread_count }})</span>
                        @else
                            <span class="k-badge k-badge-muted">Read</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap">
                            <a class="k-btn k-btn-secondary" href="{{ route('messages.show', $conversation) }}">Open</a>
                            @if ($folder === 'deleted')
                                <form method="POST" action="{{ route('messages.restore', $conversation) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="k-btn k-btn-secondary">Restore</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('messages.destroy', $conversation) }}" onsubmit="return confirm('Delete this conversation? It is only removed from your inbox and can be restored.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="k-btn k-btn-danger">Delete</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">{{ $folder === 'deleted' ? 'No deleted conversations.' : 'Your inbox is empty.' }}</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $conversations->links() }}
    </section>
</div>
@endsection