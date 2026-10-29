<?php

namespace App\Http\Controllers\Enumerator;

use App\Http\Controllers\Controller;
use App\Http\Controllers\QuestionnaireResponseController;
use App\Models\Event;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use App\Services\AuditLogService;
use App\Services\QrIdentificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionnaireController extends Controller
{
    public function __construct(
        private readonly AuditLogService $audit,
        private readonly QrIdentificationService $identifier
    ) {
    }

    public function show(Request $request, Event $event, Questionnaire $questionnaire): View
    {
        $this->authorizeQuestionnaire($request, $event, $questionnaire);

        $unavailable = $this->unavailable($event, $questionnaire);

        $mine = QuestionnaireResponse::query()
            ->where('questionnaire_id', $questionnaire->id)
            ->where('submitted_by', $request->user()->id)
            ->with('child:id,child_code')
            ->latest('submitted_at')
            ->limit(20)
            ->get();

        return view('enumerator.questionnaires.show', compact('event', 'questionnaire', 'unavailable', 'mine'));
    }

    public function identify(Request $request, Event $event, Questionnaire $questionnaire): JsonResponse
    {
        $this->authorizeQuestionnaire($request, $event, $questionnaire);

        if ($problem = $this->unavailable($event, $questionnaire)) {
            return $this->fail($problem[1], $problem[0]);
        }

        $validated = $request->validate([
            'token' => ['nullable', 'required_without:child_code', 'string', 'max:2048'],
            'child_code' => ['nullable', 'required_without:token', 'string', 'max:30'],
        ]);

        $result = $this->identifier->resolve($validated['token'] ?? null, $validated['child_code'] ?? null);

        if (! $result['child']) {
            // The scanned value and any typed child code are deliberately not logged.
            $this->audit->record(
                $request,
                'identify_failed',
                'enumerator_questionnaire',
                $questionnaire->id,
                'Child identification failed: '.$result['reason'].'.'
            );

            return $this->fail($result['message'], $result['status']);
        }

        $child = $result['child'];

        $answered = QuestionnaireResponse::query()
            ->where('questionnaire_id', $questionnaire->id)
            ->where('child_id', $child->id)
            ->exists();

        if ($answered) {
            return $this->fail('This child has already answered this questionnaire.', 409);
        }

        $request->session()->put(
            QuestionnaireResponseController::IDENTIFIED_SESSION.$questionnaire->id,
            $child->id
        );

        return response()->json([
            'success' => true,
            'child' => [
                'child_code' => $child->child_code,
                'name' => trim(implode(' ', array_filter([
                    $child->first_name,
                    $child->middle_name,
                    $child->last_name,
                ]))),
            ],
            'form_url' => route('questionnaires.responses.create', [$questionnaire, $child]),
        ]);
    }

    private function authorizeQuestionnaire(Request $request, Event $event, Questionnaire $questionnaire): void
    {
        abort_unless($event->enumerators()->whereKey($request->user()->id)->exists(), 404);
        abort_unless($questionnaire->event_id === $event->id, 404);
        abort_if($questionnaire->status === 'draft', 404);
    }

    /** @return array{0:int,1:string}|null */
    private function unavailable(Event $event, Questionnaire $questionnaire): ?array
    {
        if (! $event->enable_questionnaires) {
            return [403, 'Questionnaires have not been enabled for this event. Please contact your administrator.'];
        }

        if ($questionnaire->status !== 'published') {
            return [409, 'This questionnaire is closed and no longer accepts responses.'];
        }

        return match ($event->collectionState()) {
            'cancelled' => [409, 'This event has been cancelled. Questionnaires are not available.'],
            'completed' => [409, 'This event is completed. Questionnaire collection is closed.'],
            'upcoming' => [409, 'This event has not started yet. Questionnaires open on '.$event->event_date->format('M j, Y').'.'],
            default => null,
        };
    }

    private function fail(string $message, int $status): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message], $status);
    }
}