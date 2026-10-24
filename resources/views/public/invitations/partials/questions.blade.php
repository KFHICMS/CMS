@foreach ($questions as $question)
    @php($field = 'question_'.$question->id)
    <div class="q @error($field) has-error @enderror">
        <label class="q-label" @if (in_array($question->question_type, ['text', 'textarea', 'number', 'date', 'time', 'select'], true)) for="{{ $field }}" @endif>
            {{ $loop->iteration }}. {{ $question->question_text }}
            @if ($question->is_required)<span class="req" aria-hidden="true">*</span><span class="sr">(required)</span>@endif
        </label>

        @switch($question->question_type)
            @case('textarea')
                <textarea id="{{ $field }}" name="{{ $field }}" rows="4" maxlength="5000" @required($question->is_required)>{{ old($field) }}</textarea>
                @break

            @case('number')
                <input type="number" step="any" inputmode="decimal" id="{{ $field }}" name="{{ $field }}" value="{{ old($field) }}" @required($question->is_required)>
                @break

            @case('date')
                <input type="date" id="{{ $field }}" name="{{ $field }}" value="{{ old($field) }}" @required($question->is_required)>
                @break

            @case('time')
                <input type="time" id="{{ $field }}" name="{{ $field }}" value="{{ old($field) }}" @required($question->is_required)>
                @break

            @case('select')
                <select id="{{ $field }}" name="{{ $field }}" @required($question->is_required)>
                    <option value="">Select…</option>
                    @foreach ($question->options as $option)
                        @php($value = (string) ($option->option_value ?? $option->option_label))
                        <option value="{{ $value }}" @selected((string) old($field) === $value)>{{ $option->option_label }}</option>
                    @endforeach
                </select>
                @break

            @case('radio')
            @case('poll')
                @foreach ($question->options as $option)
                    @php($value = (string) ($option->option_value ?? $option->option_label))
                    <label class="choice">
                        <input type="radio" name="{{ $field }}" value="{{ $value }}" @checked((string) old($field) === $value) @required($question->is_required)>
                        <span>{{ $option->option_label }}</span>
                    </label>
                @endforeach
                @break

            @case('checkbox')
                @foreach ($question->options as $option)
                    @php($value = (string) ($option->option_value ?? $option->option_label))
                    <label class="choice">
                        <input type="checkbox" name="{{ $field }}[]" value="{{ $value }}" @checked(in_array($value, (array) old($field, []), true))>
                        <span>{{ $option->option_label }}</span>
                    </label>
                @endforeach
                @break

            @case('yes_no')
                @foreach (['Yes', 'No'] as $value)
                    <label class="choice">
                        <input type="radio" name="{{ $field }}" value="{{ $value }}" @checked((string) old($field) === $value) @required($question->is_required)>
                        <span>{{ $value }}</span>
                    </label>
                @endforeach
                @break

            @case('rating')
                <div class="rating">
                    @foreach (range(1, 5) as $value)
                        <label class="choice">
                            <input type="radio" name="{{ $field }}" value="{{ $value }}" @checked((string) old($field) === (string) $value) @required($question->is_required)>
                            <span>{{ $value }}</span>
                        </label>
                    @endforeach
                </div>
                @break

            @default
                <input type="text" id="{{ $field }}" name="{{ $field }}" maxlength="255" value="{{ old($field) }}" @required($question->is_required)>
        @endswitch

        @if ($errors->has($field) || $errors->has($field.'.*'))
            <div class="err" role="alert">{{ $errors->first($field) ?: $errors->first($field.'.*') }}</div>
        @endif
    </div>
@endforeach