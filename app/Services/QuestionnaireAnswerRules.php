<?php

namespace App\Services;

use App\Models\Questionnaire;
use Illuminate\Validation\Rule;

/**
 * Answer validation for the public invitation form.
 *
 * Mirrors the rules used by the logged-in questionnaire flow. It is kept as a
 * separate copy on purpose so that flow stays untouched.
 */
class QuestionnaireAnswerRules
{
    public function rules(Questionnaire $questionnaire): array
    {
        $questionnaire->loadMissing('questions.options');

        $rules = [];

        foreach ($questionnaire->questions as $question) {
            $field = 'question_' . $question->id;
            $presence = $question->is_required ? 'required' : 'nullable';
            $allowed = $question->options
                ->map(fn ($option) => (string) ($option->option_value ?? $option->option_label))
                ->all();

            switch ($question->question_type) {
                case 'checkbox':
                    $rules[$field] = [$presence, 'array'];
                    $rules[$field . '.*'] = ['string', 'distinct', Rule::in($allowed)];
                    break;

                case 'radio':
                case 'select':
                case 'poll':
                    $rules[$field] = [$presence, 'string', Rule::in($allowed)];
                    break;

                case 'yes_no':
                    $rules[$field] = [$presence, 'string', Rule::in(['Yes', 'No'])];
                    break;

                case 'rating':
                    $rules[$field] = [$presence, 'integer', 'between:1,5'];
                    break;

                case 'number':
                    $rules[$field] = [$presence, 'numeric', 'between:-1000000000,1000000000'];
                    break;

                case 'date':
                    $rules[$field] = [$presence, 'date_format:Y-m-d'];
                    break;

                case 'time':
                    $rules[$field] = [$presence, 'date_format:H:i'];
                    break;

                case 'textarea':
                    $rules[$field] = [$presence, 'string', 'max:5000'];
                    break;

                default: // text
                    $rules[$field] = [$presence, 'string', 'max:255'];
            }
        }

        return $rules;
    }

    /** Use the question text in error messages instead of "question 12". */
    public function attributes(Questionnaire $questionnaire): array
    {
        $questionnaire->loadMissing('questions');

        $attributes = [];

        foreach ($questionnaire->questions as $question) {
            $attributes['question_' . $question->id] = $question->question_text;
            $attributes['question_' . $question->id . '.*'] = $question->question_text;
        }

        return $attributes;
    }
}