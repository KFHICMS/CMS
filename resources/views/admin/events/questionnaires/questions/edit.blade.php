@extends('layouts.app', [
    'active' => 'events',
    'crumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Events', 'url' => route('events.index')],
        ['label' => $event->title, 'url' => route('events.show', $event)],
        ['label' => $questionnaire->title, 'url' => route('events.questionnaires.show', [$event, $questionnaire])],
        ['label' => 'Edit Question'],
    ],
])

@section('title', 'Edit Question')
@section('heading', 'Edit Question')
@section('subheading', $questionnaire->title.' · '.$event->title)

@section('actions')
    <a class="k-btn k-btn-secondary" href="{{ route('events.questionnaires.show', [$event, $questionnaire]) }}">Back to Questionnaire</a>
@endsection

@push('head')
    <style>
        * {
            box-sizing: border-box;
        }

        .page {
            max-width: none;
            margin: 0;
            padding: 0;
        }

        .back-link {
            display: inline-block;
            text-decoration: none;
            color: #287a45;
            font-weight: 600;
            margin-bottom: 18px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        h1 {
            font-size: 30px;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #66806f;
            font-size: 15px;
            margin-bottom: 25px;
        }

        .card {
            background: rgba(255, 255, 255, 0.92);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(40, 90, 55, 0.10);
            border: 1px solid #dcece1;
        }

        .form-group {
            margin-bottom: 22px;
        }

        label {
            display: block;
            font-weight: 700;
            margin-bottom: 8px;
            color: #234a30;
        }

        .required {
            color: #c0392b;
        }

        input[type="text"],
        select,
        textarea {
            width: 100%;
            border: 1px solid #cfe1d4;
            border-radius: 12px;
            padding: 13px 14px;
            font-size: 15px;
            background: #fbfefc;
            color: #183b28;
            outline: none;
        }

        input[type="text"]:focus,
        select:focus,
        textarea:focus {
            border-color: #5ca874;
            box-shadow: 0 0 0 3px rgba(92, 168, 116, 0.12);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .suggestion {
            display: none;
            margin-top: 12px;
            padding: 14px 16px;
            border-radius: 12px;
            background: #edf8f0;
            border: 1px solid #cce4d2;
        }

        .suggestion.show {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .suggestion-text {
            font-size: 14px;
            color: #41644c;
        }

        .suggestion-text strong {
            color: #287a45;
        }

        .use-suggestion {
            border: none;
            background: #398653;
            color: white;
            padding: 8px 13px;
            border-radius: 9px;
            cursor: pointer;
            font-weight: 700;
            white-space: nowrap;
        }

        .use-suggestion:hover {
            background: #2f7446;
        }

        .hint {
            margin-top: 7px;
            font-size: 13px;
            color: #71877a;
        }

        .checkbox-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 16px;
            background: #f4faf6;
            border-radius: 12px;
            border: 1px solid #dcece1;
        }

        .checkbox-row input {
            width: 17px;
            height: 17px;
            accent-color: #398653;
        }

        .checkbox-row label {
            margin: 0;
            font-weight: 600;
        }

        .options-box {
            display: none;
            margin-top: 18px;
            padding: 20px;
            background: #f5faf7;
            border: 1px solid #d9eade;
            border-radius: 15px;
        }

        .options-box.active {
            display: block;
        }

        .options-title {
            font-weight: 700;
            margin-bottom: 5px;
        }

        .options-description {
            color: #71877a;
            font-size: 13px;
            margin-bottom: 15px;
        }

        .option-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .option-row input {
            flex: 1;
        }

        .remove-option {
            border: none;
            background: #f4dede;
            color: #a33a3a;
            width: 42px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 18px;
        }

        .add-option {
            border: 1px solid #8abd98;
            background: #edf8f0;
            color: #287a45;
            padding: 9px 14px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
        }

        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 30px;
        }

        .btn {
            border: none;
            border-radius: 12px;
            padding: 12px 20px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-cancel {
            background: #edf2ee;
            color: #506457;
        }

        .btn-primary {
            background: #398653;
            color: white;
        }

        .error-box {
            background: #fff0f0;
            border: 1px solid #edcaca;
            color: #9f3030;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 22px;
        }

        .error-box ul {
            margin-left: 20px;
        }

        @media (max-width: 600px) {
            .page {
                padding: 25px 15px 40px;
            }

            .card {
                padding: 20px;
            }

            h1 {
                font-size: 25px;
            }

            .suggestion.show {
                align-items: stretch;
                flex-direction: column;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
@endpush

@section('content')
<div class="page">
    <div class="card">

        @if ($errors->any())
            <div class="error-box">
                <strong>Please correct the following:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route(
                'events.questionnaires.questions.update',
                [$event, $questionnaire, $question]
            ) }}"
        >

            @csrf
            @method('PUT')

            <div class="form-group">

                <label for="question_text">
                    Question <span class="required">*</span>
                </label>

                <textarea
                    id="question_text"
                    name="question_text"
                    required
                >{{ old('question_text', $question->question_text) }}</textarea>

                <div class="suggestion" id="suggestionBox">

                    <div class="suggestion-text">
                        💡 Suggested type:
                        <strong id="suggestedType"></strong>
                    </div>

                    <button
                        type="button"
                        class="use-suggestion"
                        onclick="useSuggestion()"
                    >
                        Use Suggestion
                    </button>

                </div>

            </div>

            <div class="form-group">

                <label for="question_type">
                    Question Type <span class="required">*</span>
                </label>

                <select
                    id="question_type"
                    name="question_type"
                    required
                >
                    <option value="text">Text Box</option>
                    <option value="textarea">Long Text</option>
                    <option value="radio">Radio Button</option>
                    <option value="select">Dropdown</option>
                    <option value="checkbox">Checkbox</option>
                    <option value="poll">Poll</option>
                    <option value="number">Number</option>
                    <option value="date">Date</option>
                    <option value="time">Time</option>
                    <option value="yes_no">Yes / No</option>
                    <option value="rating">Rating</option>
                </select>

            </div>

            <div
                id="optionsBox"
                class="options-box"
            >

                <div class="options-title">
                    Answer Options
                </div>

                <div class="options-description">
                    Add or edit the choices available to respondents.
                </div>

                <div id="optionsContainer">

                    @forelse ($question->options as $option)

                        <div class="option-row">

                            <input
                                type="text"
                                name="options[]"
                                value="{{ $option->option_label }}"
                                placeholder="Answer option"
                            >

                            <button
                                type="button"
                                class="remove-option"
                                onclick="removeOption(this)"
                            >
                                ×
                            </button>

                        </div>

                    @empty

                        <div class="option-row">

                            <input
                                type="text"
                                name="options[]"
                                placeholder="Option 1"
                            >

                            <button
                                type="button"
                                class="remove-option"
                                onclick="removeOption(this)"
                            >
                                ×
                            </button>

                        </div>

                    @endforelse

                </div>

                <button
                    type="button"
                    class="add-option"
                    onclick="addOption()"
                >
                    + Add Option
                </button>

            </div>

            <div
                class="form-group"
                style="margin-top: 22px;"
            >

                <div class="checkbox-row">

                    <input
                        type="checkbox"
                        id="is_required"
                        name="is_required"
                        value="1"
                        {{ old('is_required', $question->is_required) ? 'checked' : '' }}
                    >

                    <label for="is_required">
                        Required question
                    </label>

                </div>

            </div>

            <div class="actions">

                <a
                    href="{{ route('events.questionnaires.show', [$event, $questionnaire]) }}"
                    class="btn btn-cancel"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Save Changes
                </button>

            </div>

        </form>

    </div>

</div>

<script>
    const questionText =
        document.getElementById('question_text');

    const questionType =
        document.getElementById('question_type');

    const suggestionBox =
        document.getElementById('suggestionBox');

    const suggestedType =
        document.getElementById('suggestedType');

    const optionsBox =
        document.getElementById('optionsBox');

    const optionsContainer =
        document.getElementById('optionsContainer');

    let currentSuggestion = null;

    const typeNames = {
        text: 'Text Box',
        textarea: 'Long Text',
        radio: 'Radio Button',
        select: 'Dropdown',
        checkbox: 'Checkbox',
        poll: 'Poll',
        number: 'Number',
        date: 'Date',
        time: 'Time',
        yes_no: 'Yes / No',
        rating: 'Rating'
    };

    function detectQuestionType(text) {

        const value = text.toLowerCase().trim();

        if (!value) {
            return null;
        }

        if (
            value.includes('date of birth') ||
            value.includes('dob') ||
            value.includes('birth date') ||
            value.includes('birthday')
        ) {
            return 'date';
        }

        if (
            value.includes('what time') ||
            value.includes('time of') ||
            value.includes('start time') ||
            value.includes('end time')
        ) {
            return 'time';
        }

        if (
            value.includes('how old') ||
            value.includes('age') ||
            value.includes('how many')
        ) {
            return 'number';
        }

        if (
            value.includes('yes or no') ||
            value.includes('yes/no') ||
            value.includes('do you') ||
            value.includes('are you') ||
            value.includes('is the') ||
            value.includes('have you')
        ) {
            return 'yes_no';
        }

        if (
            value.includes('rate') ||
            value.includes('rating') ||
            value.includes('satisfied') ||
            value.includes('satisfaction') ||
            value.includes('how would you rate')
        ) {
            return 'rating';
        }

        if (
            value.includes('select all') ||
            value.includes('choose all') ||
            value.includes('which activities') ||
            value.includes('which of these')
        ) {
            return 'checkbox';
        }

        if (
            value.includes('gender') ||
            value.includes('sex') ||
            value.includes('relationship') ||
            value.includes('category')
        ) {
            return 'radio';
        }

        if (
            value.includes('select') ||
            value.includes('choose one') ||
            value.includes('which one') ||
            value.includes('district')
        ) {
            return 'select';
        }

        if (
            value.includes('describe') ||
            value.includes('explain') ||
            value.includes('comments') ||
            value.includes('address') ||
            value.includes('why')
        ) {
            return 'textarea';
        }

        if (
            value.includes('name') ||
            value.includes('email') ||
            value.includes('phone') ||
            value.includes('school')
        ) {
            return 'text';
        }

        return null;
    }

    function checkSuggestion() {

        const detected =
            detectQuestionType(questionText.value);

        currentSuggestion = detected;

        if (detected) {

            suggestedType.textContent =
                typeNames[detected];

            suggestionBox.classList.add('show');

        } else {

            suggestionBox.classList.remove('show');

        }
    }

    function useSuggestion() {

        if (!currentSuggestion) {
            return;
        }

        questionType.value = currentSuggestion;

        updateOptionsVisibility();

        suggestionBox.classList.remove('show');
    }

    questionText.addEventListener(
        'input',
        checkSuggestion
    );

    const optionTypes = [
        'radio',
        'select',
        'checkbox',
        'poll'
    ];

    function updateOptionsVisibility() {

        if (optionTypes.includes(questionType.value)) {
            optionsBox.classList.add('active');
        } else {
            optionsBox.classList.remove('active');
        }
    }

    questionType.addEventListener(
        'change',
        updateOptionsVisibility
    );

    function addOption() {

        const optionNumber =
            optionsContainer.children.length + 1;

        const row =
            document.createElement('div');

        row.className = 'option-row';

        row.innerHTML = `
            <input
                type="text"
                name="options[]"
                placeholder="Option ${optionNumber}"
            >

            <button
                type="button"
                class="remove-option"
                onclick="removeOption(this)"
            >
                ×
            </button>
        `;

        optionsContainer.appendChild(row);
    }

    function removeOption(button) {

        const rows =
            optionsContainer.querySelectorAll('.option-row');

        if (rows.length <= 1) {
            return;
        }

        button.parentElement.remove();
    }

    questionType.value =
        @json($question->question_type);

    updateOptionsVisibility();
</script>

@endsection