<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Programs | KFHI CMS</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 30px; background: #eef7f0; color: #294432; font-family: Arial, sans-serif; }
        main { max-width: 1100px; margin: auto; }
        header { display: flex; justify-content: space-between; align-items: center; gap: 16px; }
        .card { margin-top: 18px; padding: 18px; background: white; border: 1px solid #dce9df; border-radius: 10px; }
        a, button { color: #28733a; }
        .primary { display: inline-block; padding: 10px 14px; color: white; background: #28733a; border-radius: 8px; text-decoration: none; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 8px; text-align: left; border-bottom: 1px solid #e6eee7; vertical-align: top; }
        .actions { display: flex; align-items: center; gap: 12px; }
        .notice { color: #28733a; }
        @media (max-width: 700px) { body { padding: 16px; } .table-wrap { overflow-x: auto; } }
    </style>
</head>
<body>
<main>
    <header>
        <div><h1>Programs</h1><p>Manage programs and Field Officer assignments.</p></div>
        <a class="primary" href="{{ route('programs.create') }}">Create program</a>
    </header>
    @if (session('success')) <p class="notice" role="status">{{ session('success') }}</p> @endif
    @if (session('error')) <p class="notice" role="alert">{{ session('error') }}</p> @endif
    <section class="card table-wrap">
        <table>
            <thead><tr><th>Program</th><th>Status</th><th>Activities</th><th>Assigned officers</th><th></th></tr></thead>
            <tbody>
            @forelse ($programs as $program)
                <tr>
                    <td><strong>{{ $program->name }}</strong><br><small>{{ $program->location ?: 'Location not set' }}</small></td>
                    <td>{{ ucfirst($program->status) }}</td>
                    <td>{{ $program->events_count }}</td>
                    <td>{{ $program->officers->pluck('name')->join(', ') ?: 'None' }}</td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('programs.edit', $program) }}">Edit</a>
                            <a href="{{ route('events.create', ['program_id' => $program->id]) }}">Add activity</a>
                            <form method="POST" action="{{ route('programs.destroy', $program) }}" onsubmit="return confirm('Delete this program? Its activities will become unassigned.')">
                                @csrf @method('DELETE')
                                <button type="submit">Delete</button>
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
</main>
</body>
</html>
