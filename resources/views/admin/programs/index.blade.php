@extends('layouts.app', [
    'active' => 'programs',
    'crumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Programs'],
    ],
])

@section('title', 'Programs')
@section('heading', 'Programs')
@section('subheading', 'Manage programs and Field Officer assignments.')

@section('actions')
<a class="k-btn k-btn-primary" href="{{ route('programs.create') }}">+ Create program</a>
@endsection

@section('content')
<div class="k-stack">
<section class="k-card k-table-wrap">
<table class="k-table">
<thead>
<tr><th>Program</th><th>Status</th><th>Activities</th><th>Assigned officers</th><th></th></tr>
</thead>
<tbody>
            @forelse ($programs as $program)
                @php($badge = ['planned' => 'k-badge-info', 'cancelled' => 'k-badge-muted'][$program->status] ?? '')
<tr>
<td><strong>{{ $program->name }}</strong><br><span class="muted">{{ $program->location ?: 'Location not set' }}</span></td>
<td><span class="k-badge {{ $badge }}">{{ ucfirst($program->status) }}</span></td>
<td>{{ $program->events_count }}</td>
<td>{{ $program->officers->pluck('name')->join(', ') ?: 'None' }}</td>
<td>
<div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px">
<a class="k-btn k-btn-secondary" href="{{ route('programs.edit', $program) }}">Edit</a>
<a class="k-btn k-btn-secondary" href="{{ route('events.create', ['program_id' => $program->id]) }}">Add activity</a>
<form method="POST" action="{{ route('programs.destroy', $program) }}" onsubmit="return confirm('Delete this program? Its activities will become unassigned.')">
                                @csrf @method('DELETE')
<button type="submit" class="k-btn k-btn-danger">Delete</button>
</form>
</div>
</td>
</tr>
            @empty
<tr><td colspan="5">No programs have been created.</td></tr>
            @endforelse
</tbody>
</table>
        {{ $programs->links() }}
</section>
</div>
@endsection