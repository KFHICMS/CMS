@extends('layouts.app', [
    'active' => 'events',
    'crumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Events', 'url' => route('events.index')],
        ['label' => $event->title, 'url' => route('events.show', $event)],
        ['label' => 'Questionnaires', 'url' => route('events.questionnaires.index', $event)],
        ['label' => $questionnaire->title],
    ],
])

@section('title', 'Questionnaire')
@section('heading', $questionnaire->title)
@section('subheading', 'Questionnaire for '.$event->title)

@section('actions')
    <a class="k-btn k-btn-secondary" href="{{ route('events.questionnaires.index', $event) }}">Back to Questionnaires</a>
    <a class="k-btn k-btn-primary" href="{{ route('events.questionnaires.edit', [$event, $questionnaire]) }}">Edit Questionnaire</a>
@endsection

@push('head')
    <style>
        * {
            box-sizing: border-box;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            padding: 0;
        }

        .header {
            background: #ffffff;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(46, 125, 50, 0.08);
        }

        .top-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            flex-wrap: wrap;
        }

        .header h1 {
            color: #2e7d32;
            margin-bottom: 8px;
        }

        .header p {
            color: #68776e;
            line-height: 1.6;
            font-size: 14px;
        }

        .status {
            display: inline-block;
            padding: 7px 13px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            text-transform: capitalize;
        }

        .status.draft {
            background: #fff3e0;
            color: #e65100;
        }

        .status.published {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status.closed {
            background: #eeeeee;
            color: #616161;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .btn {
            display: inline-block;
            border: none;
            border-radius: 10px;
            padding: 11px 18px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #2e7d32;
            color: white;
        }

        .btn-secondary {
            background: #e8f3eb;
            color: #2e7d32;
        }

        .section {
            background: #ffffff;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 22px;
            box-shadow: 0 8px 25px rgba(46, 125, 50, 0.08);
        }

        .section-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .section-title h2 {
            color: #245c2a;
            font-size: 21px;
        }

        .question {
            border: 1px solid #e2ebe4;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            background: #fbfdfb;
        }

        .question:last-child {
            margin-bottom: 0;
        }

        .question-number {
            color: #2e7d32;
            font-weight: 700;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .question-text {
            font-size: 16px;
            font-weight: 700;
            color: #294b32;
            margin-bottom: 12px;
        }

        .question-meta {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .badge {
            background: #e8f3eb;
            color: #2e7d32;
            border-radius: 20px;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 600;
        }

        .required {
            background: #fdeaea;
            color: #c62828;
        }

        .options {
            margin-top: 14px;
            padding-left: 20px;
            color: #59695f;
        }

        .options li {
            margin-bottom: 6px;
        }

        .empty {
            text-align: center;
            padding: 35px 15px;
            color: #718078;
        }

        .empty-icon {
            font-size: 42px;
            margin-bottom: 10px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .info-item {
            background: #f3f8f4;
            border-radius: 12px;
            padding: 15px;
        }

        .info-label {
            font-size: 12px;
            color: #728077;
            margin-bottom: 5px;
        }

        .info-value {
            font-weight: 700;
            color: #31583a;
        }
        .question-actions {
    display: flex;
    gap: 10px;
    margin-top: 18px;
    flex-wrap: wrap;
}

.edit-btn,
.delete-btn {
    border: none;
    border-radius: 9px;
    padding: 8px 13px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
}

.edit-btn {
    background: #eaf6ee;
    color: #287a45;
}

.delete-btn {
    background: #fbecec;
    color: #a33a3a;
}

.edit-btn:hover {
    background: #dcefe2;
}

.delete-btn:hover {
    background: #f4dada;
}
    </style>
@endpush

@section('content')
<div class="container">
    <div class="header">

        <div class="top-row">

            <div>
                <p>
                    Event:
                    <strong>{{ $event->title }}</strong>
                </p>

                @if($questionnaire->description)
                    <p style="margin-top: 8px;">
                        {{ $questionnaire->description }}
                    </p>
                @endif
            </div>

            <span class="status {{ $questionnaire->status }}">
                {{ $questionnaire->status }}
            </span>

        </div>

        <div class="info-grid">

            <div class="info-item">
                <div class="info-label">Questions</div>
                <div class="info-value">
                    {{ $questionnaire->questions->count() }}
                </div>
            </div>

            <div class="info-item">
                <div class="info-label">Created</div>
                <div class="info-value">
                    {{ $questionnaire->created_at->format('d M Y') }}
                </div>
            </div>

            <div class="info-item">
                <div class="info-label">Status</div>
                <div class="info-value">
                    {{ ucfirst($questionnaire->status) }}
                </div>
            </div>

        </div>

    </div>
    
        <div class="section" id="invitations">
        <style>
            .inv-box { margin: 12px 0; padding: 14px; border: 1px solid #c9dfce; border-radius: 10px; background: #f6fbf7; }
            .inv-box input[type=text] { width: 100%; padding: 10px; border: 1px solid #bdcbbf; border-radius: 8px; font: inherit; }
            .inv-row { display: flex; flex-wrap: wrap; gap: 12px; align-items: end; margin: 12px 0; }
            .inv-row label { display: block; font-size: 13px; margin-bottom: 4px; }
            .inv-row select, .inv-row input[type=number] { padding: 9px; border: 1px solid #bdcbbf; border-radius: 8px; font: inherit; }
            .inv-table { width: 100%; border-collapse: collapse; margin-top: 12px; }
            .inv-table th, .inv-table td { padding: 10px 8px; text-align: left; border-bottom: 1px solid #e3ede6; vertical-align: top; }
            .inv-status { display: inline-block; padding: 3px 9px; border-radius: 20px; font-size: 12px; }
            .inv-active { background: #dff3e3; color: #1d6b34; }
            .inv-expired { background: #fff3d6; color: #8a6200; }
            .inv-revoked { background: #f8dcdc; color: #9a2a2a; }
            .inv-warn { padding: 10px 12px; border-radius: 8px; background: #fff7e0; border: 1px solid #f0dfa6; margin: 10px 0; }
            .inv-inline { display: inline; }
            .inv-scroll { overflow-x: auto; }
        </style>

        <div class="section-title">
            <h2>Enumerator Invitations</h2>
        </div>

        <p>Generate a link for enumerators to open this questionnaire without logging in. Only this event and questionnaire can be reached through the link.</p>

        @if (session('error'))
            <p class="inv-warn" role="alert">{{ session('error') }}</p>
        @endif
        @if (session('success'))
            <p role="status">{{ session('success') }}</p>
        @endif

        @if (session('new_invitation_url'))
            <div class="inv-box" role="status">
                <strong>New link (shown only once)</strong>
                <p>Copy it and share it, for example on WhatsApp. You can also copy it again later from the table below.</p>
                <input type="text" id="invite-url" value="{{ session('new_invitation_url') }}" readonly>
                <p><button type="button" class="btn" id="copy-invite">Copy link</button> <span id="copy-status" role="status"></span></p>
            </div>
        @endif

        @if ($questionnaire->status !== 'published')
            <p class="inv-warn">Publish this questionnaire to generate an enumerator link.</p>
        @else
            @if (! $event->enable_questionnaires)
                <p class="inv-warn">Questionnaires are not enabled for this event, so links will not open until you enable them.</p>
            @elseif ($event->collectionState() !== 'open')
                <p class="inv-warn">This event is currently {{ $event->collectionState() }}, so links will not accept responses until it is open.</p>
            @endif

            <form method="POST" action="{{ route('events.questionnaires.invitations.store', [$event, $questionnaire]) }}">
                @csrf
                <div class="inv-row">
                    <div>
                        <label for="expires_in_days">Link valid for</label>
                        <select id="expires_in_days" name="expires_in_days">
                            @foreach ([1 => '1 day', 3 => '3 days', 7 => '7 days', 14 => '14 days', 30 => '30 days'] as $days => $label)
                                <option value="{{ $days }}" @selected($days === 7)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="max_submissions">Maximum submissions</label>
                        <input type="number" id="max_submissions" name="max_submissions" min="1" max="1000" value="100">
                    </div>
                    <div>
                        <button type="submit" class="btn">Generate Enumerator Link</button>
                    </div>
                </div>
                @error('expires_in_days') <p class="inv-warn">{{ $message }}</p> @enderror
                @error('max_submissions') <p class="inv-warn">{{ $message }}</p> @enderror
            </form>
        @endif

        @php($invitations = $questionnaire->invitations()->with('creator:id,name')->latest()->limit(20)->get())

        @if ($invitations->isEmpty())
            <p>No invitation links have been generated for this questionnaire yet.</p>
        @else
            <div class="inv-scroll">
                <table class="inv-table">
                    <thead>
                    <tr><th>Link</th><th>Status</th><th>Created</th><th>Expires</th><th>Max</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    @foreach ($invitations as $invitation)
                        @php($status = $invitation->status())
                        <tr>
                            <td>
                                …{{ $invitation->token_hint }}
                                @php($shareUrl = $status === 'active' ? $invitation->shareUrl() : null)
                                @if ($shareUrl)
                                    <div style="margin-top:6px">
                                        <button type="button" class="btn btn-secondary copy-link" data-url="{{ $shareUrl }}">Copy link</button>
                                        <a href="{{ $shareUrl }}" target="_blank" rel="noopener noreferrer">Open</a>
                                    </div>
                                @elseif ($status === 'active')
                                    <div><small>Created before copying was available. Use Replace to get a copyable link.</small></div>
                                @endif
                            </td>
                            <td><span class="inv-status inv-{{ $status }}">{{ ucfirst($status) }}</span></td>
                            <td>{{ $invitation->created_at->format('d M Y H:i') }}<br><small>{{ $invitation->creator?->name }}</small></td>
                            <td>{{ $invitation->expires_at->format('d M Y H:i') }}</td>
                            <td>{{ $invitation->max_submissions }}</td>
                            <td>
                                @if ($status !== 'revoked')
                                    <form class="inv-inline" method="POST"
                                          action="{{ route('events.questionnaires.invitations.revoke', [$event, $questionnaire, $invitation]) }}"
                                          onsubmit="return confirm('Revoke this link? Enumerators will no longer be able to use it.');">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary">Revoke</button>
                                    </form>
                                    @if ($questionnaire->status === 'published')
                                        <form class="inv-inline" method="POST"
                                              action="{{ route('events.questionnaires.invitations.replace', [$event, $questionnaire, $invitation]) }}"
                                              onsubmit="return confirm('Replace this link? The old link will stop working and a new one will be generated.');">
                                            @csrf
                                            <input type="hidden" name="expires_in_days" value="7">
                                            <input type="hidden" name="max_submissions" value="{{ $invitation->max_submissions }}">
                                            <button type="submit" class="btn btn-secondary">Replace</button>
                                        </form>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <script>
            document.getElementById('copy-invite')?.addEventListener('click', async () => {
                const input = document.getElementById('invite-url');
                const status = document.getElementById('copy-status');
                try {
                    await navigator.clipboard.writeText(input.value);
                    status.textContent = 'Copied.';
                } catch (e) {
                    input.select();
                    status.textContent = document.execCommand('copy') ? 'Copied.' : 'Press Ctrl+C to copy.';
                }
            });
            document.querySelectorAll('.copy-link').forEach((button) => {
                button.addEventListener('click', async () => {
                    const url = button.dataset.url;
                    const original = button.textContent;
                    try {
                        await navigator.clipboard.writeText(url);
                    } catch (e) {
                        const box = document.createElement('textarea');
                        box.value = url;
                        document.body.appendChild(box);
                        box.select();
                        try { document.execCommand('copy'); } catch (err) { window.prompt('Copy this link:', url); }
                        document.body.removeChild(box);
                    }
                    button.textContent = 'Copied';
                    setTimeout(() => { button.textContent = original; }, 1500);
                });
            });
        </script>
    </div>
    <div class="section">

        <div class="section-title">

            <h2>Questions</h2>

            <a
    href="{{ route(
        'events.questionnaires.questions.create',
        [$event, $questionnaire]
    ) }}"
    class="btn"
>
    + Add Question
</a>

        </div>

        @if($questionnaire->questions->count())

            @foreach($questionnaire->questions as $index => $question)

                <div class="question">

                    <div class="question-number">
                        QUESTION {{ $index + 1 }}
                    </div>

                    <div class="question-text">
                        {{ $question->question_text }}
                    </div>

                    <div class="question-meta">

                        <span class="badge">
                            {{ ucfirst(str_replace('_', ' ', $question->question_type)) }}
                        </span>

                        @if($question->is_required)
                            <span class="badge required">
                                Required
                            </span>
                        @endif

                    </div>

                    @if($question->options->count())

                        <ul class="options">

                            @foreach($question->options as $option)
                                <li>
                                    {{ $option->option_label }}
                                </li>
                            @endforeach

                        </ul>

                    @endif
                    <div class="question-actions">

<a
    href="{{ route(
        'events.questionnaires.questions.edit',
        [$event, $questionnaire, $question]
    ) }}"
    class="edit-btn"
>
    ✏️ Edit
</a>

<form
    method="POST"
    action="{{ route(
        'events.questionnaires.questions.destroy',
        [$event, $questionnaire, $question]
    ) }}"
    onsubmit="return confirm('Are you sure you want to delete this question?');"
    style="display:inline;"
>
    @csrf
    @method('DELETE')

    <button
        type="submit"
        class="delete-btn"
    >
        🗑 Delete
    </button>
</form>

</div>
                </div>

            @endforeach

        @else

            <div class="empty">

                <div class="empty-icon">
                    📝
                </div>

                <h3>No Questions Yet</h3>

                <p style="margin-top: 8px;">
                    Add questions to build this questionnaire.
                </p>

            </div>

        @endif

    </div>

</div>
@endsection