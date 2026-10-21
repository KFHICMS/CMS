<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Question;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuestionnaireResponseController extends Controller
{
    /**
     * Show questionnaire for a child.
     */
    public function create(
        Questionnaire $questionnaire,
        Child $child
    ): View {
        $this->authorizeResponseAccess($questionnaire, $child);

        $alreadyAnswered = QuestionnaireResponse::where(
            'questionnaire_id',
            $questionnaire->id
        )
            ->where('child_id', $child->id)
            ->exists();

        $questionnaire->load([
            'event',
            'questions.options',
        ]);

        return view(
            'admin.events.questionnaires.responses.create',
            compact(
                'questionnaire',
                'child',
                'alreadyAnswered'
            )
        );
    }

    /**
     * Store questionnaire answers.
     */
    public function store(
        Request $request,
        Questionnaire $questionnaire,
        Child $child
    ): RedirectResponse {
        $this->authorizeResponseAccess($questionnaire, $child);

        // Check duplicate response.
        $alreadyAnswered = QuestionnaireResponse::where(
            'questionnaire_id',
            $questionnaire->id
        )
            ->where('child_id', $child->id)
            ->exists();

        if ($alreadyAnswered) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'This child has already answered this questionnaire.'
                );
        }

        $questionnaire->load('questions.options');
        $validated = $request->validate($this->validationRules($questionnaire->questions));

        try {
            DB::transaction(function () use (
                $questionnaire,
                $child,
                $validated
            ) {
                $response = QuestionnaireResponse::create([
                    'questionnaire_id' => $questionnaire->id,
                    'child_id' => $child->id,
                    'submitted_by' => auth()->id(),
                    'submitted_at' => now(),
                    'response_uuid' => (string) Str::uuid(),
                ]);

                foreach ($questionnaire->questions as $question) {
                    $fieldName = 'question_'.$question->id;
                    $answer = $validated[$fieldName] ?? null;

                    $response->answers()->create([
                        'question_id' => $question->id,
                        'answer_text' => is_array($answer) ? null : $answer,
                        'answer_json' => is_array($answer) ? $answer : null,
                    ]);
                }
            });
        } catch (UniqueConstraintViolationException $exception) {
            if (! QuestionnaireResponse::query()
                ->where('questionnaire_id', $questionnaire->id)
                ->where('child_id', $child->id)
                ->exists()) {
                throw $exception;
            }

            return redirect()
                ->back()
                ->with('error', 'This child has already answered this questionnaire.');
        }

        return redirect()
            ->route(
                'questionnaires.responses.success',
                [
                    $questionnaire,
                    $child,
                ]
            )
            ->with(
                'success',
                'Questionnaire submitted successfully.'
            );
    }

    /**
     * Show submission success page.
     */
    public function success(
        Questionnaire $questionnaire,
        Child $child
    ): View {
        $this->authorizeResponseAccess(
            $questionnaire,
            $child,
            requirePublished: false
        );

        return view(
            'admin.events.questionnaires.responses.success',
            compact(
                'questionnaire',
                'child'
            )
        );
    }

    private function authorizeResponseAccess(
        Questionnaire $questionnaire,
        Child $child,
        bool $requirePublished = true
    ): void {
        abort_unless(
            (! $requirePublished || $questionnaire->status === 'published') &&
                $questionnaire->event?->enable_questionnaires &&
                $child->status === 'active',
            404
        );

        $user = auth()->user();

        if ($user->role === 'enumerator') {
            abort_unless(
                $questionnaire->event()
                    ->whereHas('enumerators', fn ($query) => $query->whereKey($user->id))
                    ->exists()
                    && $questionnaire->event?->collectionState() === 'open',
                404
            );
        } elseif ($user->role === 'field_officer') {
            abort_unless(
                $questionnaire->event()
                    ->whereHas('program.officers', fn ($query) => $query->whereKey($user->id))
                    ->exists(),
                404
            );
        }
    }

    /**
     * @param  Collection<int, Question>  $questions
     * @return array<string, array<int, mixed>>
     */
    private function validationRules(Collection $questions): array
    {
        $rules = [];

        foreach ($questions as $question) {
            $field = 'question_'.$question->id;
            $presence = $question->is_required ? ['required'] : ['nullable'];
            $settings = $question->settings ?? [];
            $allowedValues = $question->options
                ->map(fn ($option) => $option->option_value ?? $option->option_label)
                ->all();
            $min = $settings['min'] ?? $settings['min_value'] ?? null;
            $max = $settings['max'] ?? $settings['max_value'] ?? null;

            $rules[$field] = match ($question->question_type) {
                'text', 'textarea' => [...$presence, 'string', 'max:10000'],
                'number' => [
                    ...$presence,
                    'numeric',
                    ...($min !== null ? ['min:'.$min] : []),
                    ...($max !== null ? ['max:'.$max] : []),
                ],
                'date' => [
                    ...$presence,
                    'date_format:Y-m-d',
                    ...(isset($settings['min_date']) ? ['after_or_equal:'.$settings['min_date']] : []),
                    ...(isset($settings['max_date']) ? ['before_or_equal:'.$settings['max_date']] : []),
                ],
                'time' => [...$presence, 'date_format:H:i'],
                'radio', 'select', 'poll' => [
                    ...$presence,
                    'string',
                    Rule::in($allowedValues),
                ],
                'checkbox' => [
                    ...$presence,
                    'array',
                    ...($question->is_required ? ['min:1'] : []),
                ],
                'yes_no' => [...$presence, 'string', Rule::in(['Yes', 'No'])],
                'rating' => [...$presence, 'integer', 'min:1', 'max:5'],
                default => ['prohibited'],
            };

            if ($question->question_type === 'checkbox') {
                $rules[$field.'.*'] = [
                    'string',
                    'distinct:strict',
                    Rule::in($allowedValues),
                ];
            }
        }

        return $rules;
    }
}
