@extends('layouts.app', [
    'active' => 'field_submissions',
    'crumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Field Submissions', 'url' => route('field_submissions.index')],
        ['label' => 'Submission'],
    ],
])

@section('title', $submission->event->title)
@section('heading', $submission->event->title)
@section('subheading', 'Review the record or record a correction.')

@section('content')
    @if ($errors->any())
        <div class="k-alert k-alert-error" role="alert">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="k-stack">
        <section class="k-card">
            <p><strong>Program:</strong> {{ $submission->event->program?->name ?? '—' }}</p>
            <p><strong>Child:</strong> {{ $submission->child->child_code }}
                ({{ trim(implode(' ', array_filter([$submission->child->first_name, $submission->child->middle_name, $submission->child->last_name]))) }})
            </p>
            <p><strong>Officer:</strong> {{ $submission->user->name }}</p>
            <p><strong>Submitted:</strong> {{ $submission->submitted_at?->format('d M Y H:i') }}</p>
        </section>

        <section class="k-card">
            <h2>Record a correction</h2>
            <p class="muted">Changes are saved with the old values and your reason. Nothing is deleted.</p>
            <form method="POST" action="{{ route('field_submissions.correct', $submission) }}">
                @csrf
                @method('PATCH')

                @if ($submission->attendance_status)
                    <div class="k-field">
                        <label for="attendance_status">Attendance</label>
                        <select id="attendance_status" name="attendance_status" class="k-select">
                            @foreach (['present', 'absent'] as $status)
                                <option value="{{ $status }}" @selected(old('attendance_status', $submission->attendance_status) === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @foreach ($submission->benefits as $benefit)
                    <fieldset class="k-card" style="margin-bottom: 12px">
                        <legend><strong>Benefit #{{ $benefit->id }}</strong></legend>
                        <div class="k-form-row">
                            <div class="k-field"><label>Item</label><input class="k-input" name="benefits[{{ $benefit->id }}][item_name]" value="{{ old("benefits.{$benefit->id}.item_name", $benefit->item_name) }}" required></div>
                            <div class="k-field"><label>Category</label><input class="k-input" name="benefits[{{ $benefit->id }}][category]" value="{{ old("benefits.{$benefit->id}.category", $benefit->category) }}"></div>
                            <div class="k-field"><label>Quantity</label><input class="k-input" type="number" min="1" name="benefits[{{ $benefit->id }}][quantity]" value="{{ old("benefits.{$benefit->id}.quantity", $benefit->quantity) }}" required></div>
                        </div>
                        <div class="k-field"><label>Notes</label><textarea class="k-textarea" name="benefits[{{ $benefit->id }}][notes]" rows="2">{{ old("benefits.{$benefit->id}.notes", $benefit->notes) }}</textarea></div>
                    </fieldset>
                @endforeach

                @foreach ($submission->observations as $observation)
                    <fieldset class="k-card" style="margin-bottom: 12px">
                        <legend><strong>Field note #{{ $observation->id }}</strong></legend>
                        <div class="k-field"><label>Category</label><input class="k-input" name="observations[{{ $observation->id }}][category]" value="{{ old("observations.{$observation->id}.category", $observation->category) }}"></div>
                        <div class="k-field"><label>Note</label><textarea class="k-textarea" name="observations[{{ $observation->id }}][note]" rows="4" required>{{ old("observations.{$observation->id}.note", $observation->note) }}</textarea></div>
                    </fieldset>
                @endforeach

                <div class="k-field">
                    <label for="reason">Reason for correction (required)</label>
                    <textarea id="reason" name="reason" class="k-textarea" rows="3" required>{{ old('reason') }}</textarea>
                </div>

                <button type="submit" class="k-btn k-btn-primary">Save correction</button>
            </form>
        </section>

        <section class="k-card">
            <h2>Correction history</h2>
            @forelse ($submission->corrections as $correction)
                <div class="k-activity-item">
                    <strong>{{ $correction->created_at->format('d M Y H:i') }}</strong>
                    by {{ $correction->corrector?->name ?? 'Unknown user' }}
                    <p>Reason: {{ $correction->reason }}</p>
                    <ul>
                        @foreach ($correction->changes as $change)
                            <li>
                                {{ ucfirst($change['target']) }}{{ $change['id'] ? ' #'.$change['id'] : '' }}
                                {{ $change['field'] }}:
                                {{ \Illuminate\Support\Str::limit((string) $change['old'], 200) }}
                                →
                                {{ \Illuminate\Support\Str::limit((string) $change['new'], 200) }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <p class="muted">This record has not been corrected.</p>
            @endforelse
        </section>
    </div>
@endsection