<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $questionnaire->title }} - Questionnaire</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #eef7f1;
            color: #1f3d2b;
            min-height: 100vh;
        }

        .container {
            width: min(1100px, 92%);
            margin: 0 auto;
            padding: 35px 0 50px;
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
</head>

<body>

<div class="container">

    <div class="header">

        <div class="top-row">

            <div>
                <h1>{{ $questionnaire->title }}</h1>

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

        <div class="actions">

            <a
                href="{{ route('events.questionnaires.index', $event) }}"
                class="btn btn-secondary"
            >
                ← Back
            </a>

            <a
                href="{{ route('events.questionnaires.edit', [$event, $questionnaire]) }}"
                class="btn btn-secondary"
            >
                Edit Questionnaire
            </a>

        </div>

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

</body>
</html>