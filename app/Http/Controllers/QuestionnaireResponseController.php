<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use App\Services\AuditLogService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuestionnaireResponseController extends Controller
{
    /** Session key holding the child an enumerator identified for a questionnaire. */
    public const IDENTIFIED_SESSION = 'enumerator_questionnaire_child.';

    public function __construct(private readonly AuditLogService $audit)
    {
    }

    /**
     * Show questionnaire for a child.
     */
    public function create(
        Questionnaire $questionnaire,
        Child $child
    ): View|RedirectResponse {
        $this->authorizeResponseAccess($questionnaire, $child);

        if ($redirect = $this->enumeratorGate($questionnaire, $child)) {
            return $redirect;
        }

        $alreadyAnswered = $this->alreadyAnswered($questionnaire, $child);

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

        if ($redirect = $this->enumeratorGate($questionnaire, $child)) {
            return $redirect;
        }

        if ($this->alreadyAnswered($questionnaire, $child)) {
            return $this->duplicate($questionnaire);
        }

        $questionnaire->load('questions.options');

        $validated = $request->validate(
            $this->rulesFor($questionnaire),
            [],
            $this->attributesFor($questionnaire)
        );

        try {
            $response = DB::transaction(function () use (
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
                    $answer = $validated['question_' . $question->id] ?? null;

                    if (is_array($answer)) {
                        $response->answers()->create([
                            'question_id' => $question->id,
                            'answer_text' => null,
                            'answer_json' => array_values($answer),
                        ]);
                    } else {
                        $response->answers()->create([
                            'question_id' => $question->id,
                            'answer_text' => $answer,
                            'answer_json' => null,
                        ]);
                    }
                }

                return $response;
            });
        } catch (UniqueConstraintViolationException) {
            // Two requests raced; the database unique index decided.
            return $this->duplicate($questionnaire);
        }

        session()->forget(self::IDENTIFIED_SESSION . $questionnaire->id);

        $this->audit->record(
            $request,
            'questionnaire_submitted',
            'questionnaire_response',
            $response->id,
            'Questionnaire response submitted.'
        );

        if (auth()->user()->role === 'enumerator') {
            return redirect()
                ->route('enumerator.events.questionnaires.show', [$questionnaire->event_id, $questionnaire])
                ->with('success', 'Questionnaire submitted for child '.$child->child_code.'. You can scan the next child now.');
        }

        return redirect()
            ->route('questionnaires.responses.success', [$questionnaire, $child])
            ->with('success', 'Questionnaire submitted successfully.');
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

        if (auth()->user()->role === 'enumerator') {
            abort_unless(
                QuestionnaireResponse::query()
                    ->where('questionnaire_id', $questionnaire->id)
                    ->where('child_id', $child->id)
                    ->where('submitted_by', auth()->id())
                    ->exists(),
                404
            );
        }

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
            (!$requirePublished || $questionnaire->status === 'published') &&
                $questionnaire->event?->enable_questionnaires &&
                $child->status === 'active',
            404
        );

        $user = auth()->user();

        if ($user->role === 'field_officer') {
            abort_unless(
                $questionnaire->event()
                    ->whereHas(
                        'program.officers',
                        fn ($query) => $query->where('users.id', $user->id)
                    )
                    ->exists(),
                404
            );
        }

        if ($user->role === 'enumerator') {
            abort_unless(
                $questionnaire->event
                    ->enumerators()
                    ->whereKey($user->id)
                    ->exists(),
                404
            );
        }
    }

    /**
     * Extra rules for enumerators: the event must be open, and the child must
     * have been identified (QR or Child Code) for this questionnaire first.
     */
    private function enumeratorGate(
        Questionnaire $questionnaire,
        Child $child
    ): ?RedirectResponse {
        if (auth()->user()->role !== 'enumerator') {
            return null;
        }

        $event = $questionnaire->event;

        if ($event->collectionState() !== 'open') {
            return redirect()
                ->route('enumerator.events.show', $event)
                ->with('error', 'This event is not open for data collection.');
        }

        if (session(self::IDENTIFIED_SESSION . $questionnaire->id) !== $child->id) {
            // A repeated submit after success should explain the duplicate,
            // not ask the enumerator to identify the child again.
            if ($this->alreadyAnswered($questionnaire, $child)) {
                return $this->duplicate($questionnaire);
            }

            return redirect()
                ->route('enumerator.events.questionnaires.show', [$event, $questionnaire])
                ->with('error', 'Identify the child by scanning their QR code or entering their Child Code first.');
        }

        return null;
    }

    private function alreadyAnswered(Questionnaire $questionnaire, Child $child): bool
    {
        return QuestionnaireResponse::query()
            ->where('questionnaire_id', $questionnaire->id)
            ->where('child_id', $child->id)
            ->exists();
    }

    private function duplicate(Questionnaire $questionnaire): RedirectResponse
    {
        session()->forget(self::IDENTIFIED_SESSION . $questionnaire->id);

        $this->audit->record(
            request(),
            'questionnaire_duplicate',
            'questionnaire_response',
            $questionnaire->id,
            'Duplicate questionnaire submission rejected.'
        );

        $message = 'This child has already answered this questionnaire.';

        if (auth()->user()->role === 'enumerator') {
            return redirect()
                ->route('enumerator.events.questionnaires.show', [$questionnaire->event_id, $questionnaire])
                ->with('error', $message);
        }

        return redirect()->back()->with('error', $message);
    }

    /**
     * Validation rules built from each question's configured type.
     */
    private function rulesFor(Questionnaire $questionnaire): array
    {
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

    /**
     * Use the question text in error messages instead of "question 12".
     */
    private function attributesFor(Questionnaire $questionnaire): array
    {
        $attributes = [];

        foreach ($questionnaire->questions as $question) {
            $attributes['question_' . $question->id] = $question->question_text;
            $attributes['question_' . $question->id . '.*'] = $question->question_text;
        }

        return $attributes;
    }
}