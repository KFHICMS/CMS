@extends('layouts.app', [
    'active' => 'follow_ups',
    'crumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Follow-ups'],
    ],
])

@section('title', 'Follow-ups')
@section('heading', 'Follow-ups')
@section('subheading', 'Child follow-ups assigned to field officers.')

@section('actions')
    <a class="k-btn k-btn-primary" href="{{ route('admin_follow_ups.create') }}">+ New follow-up</a>
@endsection

@section('content')
    <div class="k-stack">
        <p class="tabs">
            <a @class(['current' => ! $status]) href="{{ route('admin_follow_ups.index') }}">All</a>
            <a @class(['current' => $status === 'pending']) href="{{ route('admin_follow_ups.index', ['status' => 'pending']) }}">Pending</a>
            <a @class(['current' => $status === 'completed']) href="{{ route('admin_follow_ups.index', ['status' => 'completed']) }}">Completed</a>
            <a @class(['current' => $status === 'cancelled']) href="{{ route('admin_follow_ups.index', ['status' => 'cancelled']) }}">Cancelled</a>
        </p>

        <section class="k-card k-table-wrap">
            <table class="k-table">
                <thead>
                <tr><th>Child code</th><th>Category</th><th>Assigned officer</th><th>Activity</th><th>Due</th><th>Priority</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                @forelse ($followUps as $followUp)
                    @php($badge = ['pending' => 'k-badge-warning', 'cancelled' => 'k-badge-muted'][$followUp->status] ?? '')
                    <tr>
                        <td>{{ $followUp->child?->child_code }}</td>
                        <td>{{ $followUp->category ?: '—' }}</td>
                        <td>{{ $followUp->assignedTo?->name ?? 'Unassigned' }}</td>
                        <td>{{ $followUp->event?->title ?? '—' }}</td>
                        <td>{{ $followUp->next_follow_up_date?->format('d M Y') ?? $followUp->follow_up_date?->format('d M Y') ?? '—' }}</td>
                        <td>{{ ucfirst($followUp->priority) }}</td>
                        <td><span class="k-badge {{ $badge }}">{{ ucfirst($followUp->status) }}</span></td>
                        <td><a href="{{ route('admin_follow_ups.edit', $followUp) }}">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8">No follow-ups found.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $followUps->links() }}
        </section>
    </div>
@endsection