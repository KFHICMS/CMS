<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Field Submissions | KFHI CMS</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 30px; background: #eef7f0; color: #294432; font-family: Arial, sans-serif; }
        main { max-width: 1100px; margin: auto; }
        .card { margin-top: 18px; padding: 18px; background: white; border: 1px solid #dce9df; border-radius: 10px; }
        a { color: #28733a; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 8px; text-align: left; border-bottom: 1px solid #e6eee7; vertical-align: top; }
        .muted { color: #65756a; }
        @media (max-width: 700px) { body { padding: 16px; } .table-wrap { overflow-x: auto; } }
    </style>
</head>
<body>
<main>
    <a href="{{ route('admin.dashboard') }}">← Back to dashboard</a>
    <h1>Field Submissions</h1>
    <p class="muted">Submitted field records. Open one to review it or record a correction.</p>
    <section class="card table-wrap">
        <table>
            <thead><tr><th>Activity</th><th>Program</th><th>Child code</th><th>Officer</th><th>Submitted</th><th>Corrections</th><th></th></tr></thead>
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
</main>
</body>
</html>