@extends('layouts.app', [
    'active' => 'backups',
    'crumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Backup & Recovery', 'url' => route('admin.backups.index')],
        ['label' => 'Restore'],
    ],
])

@section('title', 'Restore backup')
@section('heading', 'Restore a backup')
@section('subheading', $backup->filename)

@section('content')
    @if ($errors->any())
        <div class="k-alert k-alert-error" role="alert">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="k-stack" style="max-width: 760px">
        <div class="k-alert k-alert-error" role="alert">
            <strong>Warning: this replaces current data.</strong>
            <ul style="margin: 8px 0 0 18px; padding: 0">
                <li>Everything saved since this backup was made ({{ ($backup->completed_at ?? $backup->created_at)->format('d M Y, H:i') }}) can be lost from the live database.</li>
                <li>The system goes into maintenance mode while the restore runs, and everyone is logged out.</li>
                <li>Before restoring, the system saves a safety copy of the current data, so this can be undone by restoring that copy.</li>
                <li>Tables created after this backup are left in place. Run <code>php artisan migrate</code> afterwards if the app reports missing columns.</li>
            </ul>
        </div>

        <form method="POST" action="{{ route('admin.backups.restore', $backup) }}" class="k-card">
            @csrf

            <div class="k-field">
                <label for="confirm_phrase">Type RESTORE to confirm</label>
                <input id="confirm_phrase" name="confirm_phrase" class="k-input" autocomplete="off" required>
            </div>

            <div class="k-field">
                <label for="password">Your password</label>
                <input id="password" type="password" name="password" class="k-input" autocomplete="current-password" required>
                <p class="k-help">We ask again to make sure it is really you.</p>
            </div>

            <div class="k-field">
                <label style="display: flex; gap: 10px; align-items: flex-start; font-weight: 500">
                    <input type="checkbox" name="understand" value="1" style="width: 20px; height: 20px; flex: none; margin-top: 2px" required>
                    I understand that this may replace the current database data.
                </label>
            </div>

            <button type="submit" class="k-btn k-btn-danger">Restore this backup</button>
            <a class="k-btn k-btn-secondary" href="{{ route('admin.backups.index') }}">Cancel</a>
        </form>
    </div>
@endsection