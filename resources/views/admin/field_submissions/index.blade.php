@extends('layouts.app', [
    'active' => 'field_submissions',
    'crumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Field Submissions'],
    ],
])

@section('title', 'Field Submissions')
@section('heading', 'Field Submissions')
@section('subheading', 'Submitted field records. Open one to review it or record a correction.')

@section('content')
    <section class="k-card k-table-wrap">
        <table class="k-table">
            <thead>
            <tr><th>Activity</th><th>Program</th><th>Child code</th><th>Officer</th><th>Submitted</th><th>Corrections</th><th></th></tr>
            </thead>
            <tbody>
            @forelse ($submissions as $submission)
                <tr>
                    <td>{{ $submission->event->title }}</td>
                    <td>{{ $submission->event->program?->name ?? '—' }}</td>
                    <td>{{ $submission->child->child_code }}</td>
                    <td>{{ $submission->user->name }}</td>
                    <td>{{ $submission->submitted_at?->format('d M Y H:i') }}</td>
                    <td>{{ $submission->corrections_count }}</td>
                    <td><a href="{{ route('field_submissions.show', $submission) }}">Open</a></td>
                </tr>
            @empty
                <tr><td colspan="7">No submitted field records yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $submissions->links() }}
    </section>
@endsection