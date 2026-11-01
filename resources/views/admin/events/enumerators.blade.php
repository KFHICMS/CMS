@extends('layouts.app', [
    'active' => 'events',
    'crumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Events', 'url' => route('events.index')],
        ['label' => $event->title, 'url' => route('events.show', $event)],
        ['label' => 'Assign Enumerators'],
    ],
])

@section('title', 'Assign Enumerators')
@section('heading', 'Assign Enumerators')
@section('subheading', $event->title.' · '.$event->event_date->format('d M Y'))

@section('content')
    @if ($errors->any())
        <div class="k-alert k-alert-error" role="alert">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('events.enumerators.update', $event) }}" class="k-card" style="max-width: 720px">
        @csrf
        @method('PUT')

        @forelse ($enumerators as $enumerator)
            <label style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--k-border)">
                <input type="checkbox" name="enumerators[]" value="{{ $enumerator->id }}" style="width:auto"
                    @checked(in_array($enumerator->id, old('enumerators', $assignedIds)))>
                <span>{{ $enumerator->name }} ({{ $enumerator->username }})
                    @if ($enumerator->status !== 'active') <em class="muted">· inactive</em> @endif
                </span>
            </label>
        @empty
            <p class="muted">No active enumerator accounts exist yet. Create one in Users first.</p>
        @endforelse

        <p style="margin-top: 16px">
            <button type="submit" class="k-btn k-btn-primary">Save assignments</button>
            <a class="k-btn k-btn-secondary" href="{{ route('events.show', $event) }}">Cancel</a>
        </p>
    </form>
@endsection