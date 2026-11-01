@extends('layouts.app', [
    'active' => 'messages',
    'crumbs' => [
        ['label' => 'Messages', 'url' => route('messages.index')],
        ['label' => 'New message'],
    ],
])

@section('title', 'New message')
@section('heading', 'New message')
@section('subheading', auth()->user()->role === 'admin' ? 'Send a message to one or more users.' : 'Send a message to an administrator.')

@section('content')
    @if ($errors->any())
        <div class="k-alert k-alert-error" role="alert">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('messages.store') }}" class="k-card" style="max-width: 760px">
        @csrf

        <div class="k-field">
            <label for="recipients">To</label>
            <select id="recipients" name="recipients[]" class="k-select" multiple required style="min-height: 130px">
                @foreach ($recipients as $recipient)
                    <option value="{{ $recipient->id }}" @selected(in_array($recipient->id, (array) old('recipients', [])))>
                        {{ $recipient->name }} ({{ str_replace('_', ' ', $recipient->role) }})
                    </option>
                @endforeach
            </select>
            <p class="k-help">Hold Ctrl (or Cmd on Mac) to select more than one person.</p>
        </div>

        <div class="k-field">
            <label for="subject">Subject</label>
            <input id="subject" name="subject" class="k-input" maxlength="150" required value="{{ old('subject') }}">
        </div>

        <div class="k-field">
            <label for="body">Message</label>
            <textarea id="body" name="body" class="k-textarea" rows="7" maxlength="5000" required>{{ old('body') }}</textarea>
            <p class="k-help">Do not include children’s names, addresses, health details or passwords in a message. Use the child code instead.</p>
        </div>

        <button type="submit" class="k-btn k-btn-primary">Send message</button>
        <a class="k-btn k-btn-secondary" href="{{ route('messages.index') }}">Cancel</a>
    </form>
@endsection