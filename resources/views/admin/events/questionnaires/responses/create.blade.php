<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        {{ $questionnaire->title }} - KFHI
    </title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f1f8f3;
            color: #24352a;
            min-height: 100vh;
        }

        .container {
            width: min(900px, 92%);
            margin: 40px auto;
        }

        .header-card,
        .question-card,
        .already-card {
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(35, 90, 55, 0.08);
        }

        .header-card {
            padding: 28px;
            margin-bottom: 22px;
            border-left: 5px solid #4caf50;
        }

        .brand {
            color: #2e7d32;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        h1 {
            color: #205c32;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .description {
            color: #68756c;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .child-info {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        .info-box {
            background: #f3faf5;
            border-radius: 12px;
            padding: 13px 15px;
        }

        .info-label {
            display: block;
            color: #78907d;
            font-size: 12px;
            margin-bottom: 4px;
        }

        .info-value {
            color: #245b35;
            font-weight: 700;
        }

        .question-card {
            padding: 25px;
            margin-bottom: 18px;
        }

        .question-number {
            display: inline-block;
            background: #e5f5e8;
            color: #2e7d32;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .question-text {
            font-size: 17px;
            font-weight: 700;
            color: #294a32;
            line-height: 1.5;
            margin-bottom: 18px;
        }

        .required {
            color: #d9534f;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        input[type="time"],
        textarea,
        select {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #d5e3d8;
            border-radius: 10px;
            background: #fbfefc;
            font-size: 14px;
            color: #263b2c;
            outline: none;
        }

        input:focus,
        textarea:focus,
        select:focus {
            border-color: #62ad70;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.10);
        }

        textarea {
            min-height: 110px;
            resize: vertical;
        }

        .option-list {
            display: flex;
            flex-direction: column;
            gap: 11px;
        }

        .option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 13px;
            background: #f7fbf8;
            border: 1px solid #e1ece3;
            border-radius: 10px;
            cursor: pointer;
        }

        .option:hover {
            background: #eef8f0;
        }

        .option input {
            accent-color: #4caf50;
        }

        .rating {
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
        }

        .rating label {
            position: relative;
        }

        .rating input {
            position: absolute;
            opacity: 0;
        }

        .rating span {
            display: inline-flex;
            width: 42px;
            height: 42px;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #edf6ef;
            color: #367744;
            font-weight: 700;
            cursor: pointer;
        }

        .rating input:checked + span {
            background: #4caf50;
            color: white;
        }

        .error {
            margin-top: 8px;
            color: #c0392b;
            font-size: 13px;
        }

        .submit-area {
            background: white;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 8px 25px rgba(35, 90, 55, 0.08);
            margin-top: 25px;
        }

        .notice {
            background: #f3faf5;
            border-left: 4px solid #4caf50;
            padding: 13px 15px;
            border-radius: 8px;
            color: #49604f;
            font-size: 13px;
            margin-bottom: 18px;
            line-height: 1.5;
        }

        .submit-btn {
            width: 100%;
            border: none;
            border-radius: 11px;
            padding: 14px 20px;
            background: #3f914c;
            color: white;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
        }

        .submit-btn:hover {
            background: #327b3e;
        }

        .already-card {
            padding: 35px;
            text-align: center;
            border-top: 5px solid #4caf50;
        }

        .already-icon {
            font-size: 45px;
            margin-bottom: 15px;
        }

        .already-card h2 {
            color: #286637;
            margin-bottom: 10px;
        }

        .already-card p {
            color: #68756c;
            line-height: 1.6;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #2e7d32;
            text-decoration: none;
            font-weight: 700;
        }

        .alert {
            padding: 13px 15px;
            border-radius: 10px;
            margin-bottom: 18px;
        }

        .alert-error {
            background: #fff0f0;
            color: #a33a3a;
            border: 1px solid #f0cccc;
        }

        @media (max-width: 700px) {
            .container {
                width: 94%;
                margin: 20px auto;
            }

            .child-info {
                grid-template-columns: 1fr;
            }

            h1 {
                font-size: 23px;
            }

            .header-card,
            .question-card,
            .already-card,
            .submit-area {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <div class="header-card">

        <div class="brand">
            KFHI Child Management System
        </div>

        <h1>
            {{ $questionnaire->title }}
        </h1>

        @if($questionnaire->description)
            <div class="description">
                {{ $questionnaire->description }}
            </div>
        @endif

        <div class="child-info">

            <div class="info-box">
                <span class="info-label">
                    Child Code
                </span>

                <span class="info-value">
                    {{ $child->child_code ?? $child->id }}
                </span>
            </div>

            <div class="info-box">
                <span class="info-label">
                    Child Name
                </span>

                <span class="info-value">
                    {{ $child->full_name ?? $child->name ?? 'N/A' }}
                </span>
            </div>

            <div class="info-box">
                <span class="info-label">
                    Event
                </span>

                <span class="info-value">
                    {{ $questionnaire->event->title ?? 'N/A' }}
                </span>
            </div>

        </div>

    </div>


    @if(session('error'))

        <div class="alert alert-error">
            {{ session('error') }}
        </div>

    @endif


    @if($alreadyAnswered)

        <div class="already-card">

            <div class="already-icon">
                ✅
            </div>

            <h2>
                Questionnaire Already Completed
            </h2>

            <p>
                This child has already submitted a response
                for this questionnaire.
            </p>

            <p>
                Each child can answer this questionnaire only once.
            </p>

            <a
                href="{{ url()->previous() }}"
                class="back-link"
            >
                ← Go Back
            </a>

        </div>

    @else

        <form
            method="POST"
            action="{{ route(
                'questionnaires.responses.store',
                [$questionnaire, $child]
            ) }}"
        >

            @csrf

            @foreach($questionnaire->questions as $index => $question)

                <div class="question-card">

                    <span class="question-number">
                        Question {{ $index + 1 }}
                    </span>

                    <div class="question-text">

                        {{ $question->question_text }}

                        @if($question->is_required)
                            <span class="required">*</span>
                        @endif

                    </div>


                    {{-- TEXT --}}
                    @if($question->question_type === 'text')

                        <input
                            type="text"
                            name="question_{{ $question->id }}"
                            value="{{ old('question_'.$question->id) }}"
                            @required($question->is_required)
                        >


                    {{-- TEXTAREA --}}
                    @elseif($question->question_type === 'textarea')

                        <textarea
                            name="question_{{ $question->id }}"
                            @required($question->is_required)
                        >{{ old('question_'.$question->id) }}</textarea>


                    {{-- NUMBER --}}
                    @elseif($question->question_type === 'number')

                        <input
                            type="number"
                            name="question_{{ $question->id }}"
                            value="{{ old('question_'.$question->id) }}"
<<<<<<< HEAD
=======
                            min="{{ $question->settings['min'] ?? $question->settings['min_value'] ?? '' }}"
                            max="{{ $question->settings['max'] ?? $question->settings['max_value'] ?? '' }}"
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
                            @required($question->is_required)
                        >


                    {{-- DATE --}}
                    @elseif($question->question_type === 'date')

                        <input
                            type="date"
                            name="question_{{ $question->id }}"
                            value="{{ old('question_'.$question->id) }}"
<<<<<<< HEAD
=======
                            min="{{ $question->settings['min_date'] ?? '' }}"
                            max="{{ $question->settings['max_date'] ?? '' }}"
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
                            @required($question->is_required)
                        >


                    {{-- TIME --}}
                    @elseif($question->question_type === 'time')

                        <input
                            type="time"
                            name="question_{{ $question->id }}"
                            value="{{ old('question_'.$question->id) }}"
                            @required($question->is_required)
                        >


                    {{-- SELECT --}}
                    @elseif($question->question_type === 'select')

                        <select
                            name="question_{{ $question->id }}"
                            @required($question->is_required)
                        >

                            <option value="">
                                Select an option
                            </option>

                            @foreach($question->options as $option)

                                <option
                                    value="{{ $option->option_value ?? $option->option_label }}"
                                    @selected(
                                        old('question_'.$question->id)
                                        === ($option->option_value ?? $option->option_label)
                                    )
                                >
                                    {{ $option->option_label }}
                                </option>

                            @endforeach

                        </select>


                    {{-- RADIO / POLL --}}
                    @elseif(
                        $question->question_type === 'radio'
                        || $question->question_type === 'poll'
                    )

                        <div class="option-list">

                            @foreach($question->options as $option)

                                <label class="option">

                                    <input
                                        type="radio"
                                        name="question_{{ $question->id }}"
                                        value="{{ $option->option_value ?? $option->option_label }}"
                                        @checked(
                                            old('question_'.$question->id)
                                            === ($option->option_value ?? $option->option_label)
                                        )
                                        @required(
                                            $question->is_required
                                            && $loop->first
                                        )
                                    >

                                    <span>
                                        {{ $option->option_label }}
                                    </span>

                                </label>

                            @endforeach

                        </div>


                    {{-- CHECKBOX --}}
                    @elseif($question->question_type === 'checkbox')

                        <div class="option-list">

                            @foreach($question->options as $option)

                                <label class="option">

                                    <input
                                        type="checkbox"
                                        name="question_{{ $question->id }}[]"
                                        value="{{ $option->option_value ?? $option->option_label }}"
                                        @checked(
                                            in_array(
                                                $option->option_value ?? $option->option_label,
                                                old('question_'.$question->id, [])
                                            )
                                        )
                                    >

                                    <span>
                                        {{ $option->option_label }}
                                    </span>

                                </label>

                            @endforeach

                        </div>


                    {{-- YES / NO --}}
                    @elseif($question->question_type === 'yes_no')

                        <div class="option-list">

                            <label class="option">

                                <input
                                    type="radio"
                                    name="question_{{ $question->id }}"
                                    value="Yes"
                                    @checked(
                                        old('question_'.$question->id) === 'Yes'
                                    )
                                    @required($question->is_required)
                                >

                                <span>Yes</span>

                            </label>

                            <label class="option">

                                <input
                                    type="radio"
                                    name="question_{{ $question->id }}"
                                    value="No"
                                    @checked(
                                        old('question_'.$question->id) === 'No'
                                    )
                                >

                                <span>No</span>

                            </label>

                        </div>


                    {{-- RATING --}}
                    @elseif($question->question_type === 'rating')

                        <div class="rating">

                            @for($rating = 1; $rating <= 5; $rating++)

                                <label>

                                    <input
                                        type="radio"
                                        name="question_{{ $question->id }}"
                                        value="{{ $rating }}"
                                        @checked(
                                            old('question_'.$question->id)
                                            == $rating
                                        )
                                        @required(
                                            $question->is_required
                                            && $rating === 1
                                        )
                                    >

                                    <span>
                                        {{ $rating }}
                                    </span>

                                </label>

                            @endfor

                        </div>

                    @endif


                    @error('question_'.$question->id)

                        <div class="error">
                            {{ $message }}
                        </div>

                    @enderror
<<<<<<< HEAD
=======
                    @foreach($errors->get('question_'.$question->id.'.*') as $messages)
                        @foreach($messages as $message)
                            <div class="error">{{ $message }}</div>
                        @endforeach
                    @endforeach
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73

                </div>

            @endforeach


            <div class="submit-area">

                <div class="notice">
                    Please review your answers before submitting.
                    Once submitted, this child cannot submit the same
                    questionnaire again.
                </div>

                <button
                    type="submit"
                    class="submit-btn"
                >
                    Submit Questionnaire
                </button>

            </div>

        </form>

    @endif

</div>

</body>
</html>