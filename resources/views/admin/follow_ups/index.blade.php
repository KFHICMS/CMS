<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Follow-ups | KFHI CMS</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 30px; background: #eef7f0; color: #294432; font-family: Arial, sans-serif; }
        main { max-width: 1100px; margin: auto; }
        .card { margin-top: 18px; padding: 18px; background: white; border: 1px solid #dce9df; border-radius: 10px; }
        a { color: #28733a; }
        .button { display: inline-block; padding: 10px 14px; color: white; background: #28733a; border-radius: 8px; text-decoration: none; }
        .filters a { margin-right: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 8px; text-align: left; border-bottom: 1px solid #e6eee7; vertical-align: top; }
        .muted { color: #65756a; }
        @media (max-width: 700px) { body { padding: 16px; } .table-wrap { overflow-x: auto; } }
    </style>
</head>
<body>
<main>
    <a href="{{ route('admin.dashboard') }}">← Back to dashboard</a>
    <h1>Follow-ups</h1>
    @if (session('success')) <p role="status">{{ session('success') }}</p> @endif
    <p><a class="button" href="{{ route('admin_follow_ups.create') }}">+ New follow-up</a></p>
    <p class="filters">
        <a href="{{ route('admin_follow_ups.index') }}">All</a>
        <a href="{{ route('admin_follow_ups.index', ['status' => 'pending']) }}">Pending</a>
        <a href="{{ route('admin_follow_ups.index', ['status' => 'completed']) }}">Completed</a>
        <a href="{{ route('admin_follow_ups.index', ['status' => 'cancelled']) }}">Cancelled</a>
    </p>
    <section class="card table-wrap">
        <table>
            <thead><tr><th>Child code</th><th>Category</th><th>Assigned officer</th><th>Activity</th><th>Due</th><th>Priority</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($followUps as $followUp)
                <tr>
                    <td>{{ $followUp->child?->child_code }}</td>
                    <td>{{ $followUp->category ?: '—' }}</td>
                    <td>{{ $followUp->assignedTo?->name ?? 'Unassigned' }}</td>
                    <td>{{ $followUp->event?->title ?? '—' }}</td>
                    <td>{{ $followUp->next_follow_up_date?->format('d M Y') ?? $followUp->follow_up_date?->format('d M Y') ?? '—' }}</td>
                    <td>{{ ucfirst($followUp->priority) }}</td>
                    <td>{{ ucfirst($followUp->status) }}</td>
                    <td><a href="{{ route('admin_follow_ups.edit', $followUp) }}">Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="8">No follow-ups found.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $followUps->links() }}
    </section>
</main>
</body>
</html>