<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Question;
use App\Models\Questionnaire;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function create(
        Event $event,
        Questionnaire $questionnaire
    ): View {
        $this->verifyQuestionnaire($event, $questionnaire);

        return view(
            'admin.events.questionnaires.questions.create',
            compact('event', 'questionnaire')
        );
    }

    public function store(
        Request $request,
        Event $event,
        Questionnaire $questionnaire
    ): RedirectResponse {
        $this->verifyQuestionnaire($event, $questionnaire);

        $validated = $request->validate([
            'question_text' => [
                'required',
                'string',
                'max:1000',
            ],
            'question_type' => [
                'required',
                'in:text,textarea,radio,select,checkbox,poll,number,date,time,yes_no,rating',
            ],
            'is_required' => [
                'nullable',
                'boolean',
            ],
            'options' => [
                'nullable',
                'array',
            ],
            'options.*' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $nextOrder = $questionnaire->questions()->max('sort_order');

        $question = Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question_text' => $validated['question_text'],
            'question_type' => $validated['question_type'],
            'is_required' => $request->boolean('is_required'),
            'sort_order' => ($nextOrder ?? -1) + 1,
        ]);

        $this->saveOptions(
            $question,
            $validated['question_type'],
            $validated['options'] ?? []
        );

        return redirect()
            ->route(
                'events.questionnaires.show',
                [$event, $questionnaire]
            )
            ->with('success', 'Question added successfully.');
    }

    public function edit(
        Event $event,
        Questionnaire $questionnaire,
        Question $question
    ): View {
        $this->verifyQuestionnaire($event, $questionnaire);
        $this->verifyQuestion($questionnaire, $question);

        $question->load('options');

        return view(
            'admin.events.questionnaires.questions.edit',
            compact('event', 'questionnaire', 'question')
        );
    }

    public function update(
        Request $request,
        Event $event,
        Questionnaire $questionnaire,
        Question $question
    ): RedirectResponse {
        $this->verifyQuestionnaire($event, $questionnaire);
        $this->verifyQuestion($questionnaire, $question);

        $validated = $request->validate([
            'question_text' => [
                'required',
                'string',
                'max:1000',
            ],
            'question_type' => [
                'required',
                'in:text,textarea,radio,select,checkbox,poll,number,date,time,yes_no,rating',
            ],
            'is_required' => [
                'nullable',
                'boolean',
            ],
            'options' => [
                'nullable',
                'array',
            ],
            'options.*' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $question->update([
            'question_text' => $validated['question_text'],
            'question_type' => $validated['question_type'],
            'is_required' => $request->boolean('is_required'),
        ]);

        $question->options()->delete();

        $this->saveOptions(
            $question,
            $validated['question_type'],
            $validated['options'] ?? []
        );

        return redirect()
            ->route(
                'events.questionnaires.show',
                [$event, $questionnaire]
            )
            ->with('success', 'Question updated successfully.');
    }

    public function destroy(
        Event $event,
        Questionnaire $questionnaire,
        Question $question
    ): RedirectResponse {
        $this->verifyQuestionnaire($event, $questionnaire);
        $this->verifyQuestion($questionnaire, $question);

        $question->delete();

        return redirect()
            ->route(
                'events.questionnaires.show',
                [$event, $questionnaire]
            )
            ->with('success', 'Question deleted successfully.');
    }

    private function saveOptions(
        Question $question,
        string $questionType,
        array $options
    ): void {
        $optionTypes = [
            'radio',
            'select',
            'checkbox',
            'poll',
        ];

        if (!in_array($questionType, $optionTypes, true)) {
            return;
        }

        foreach ($options as $index => $optionLabel) {
            $optionLabel = trim($optionLabel);

            if ($optionLabel === '') {
                continue;
            }

            $question->options()->create([
                'option_label' => $optionLabel,
                'option_value' => null,
                'sort_order' => $index,
            ]);
        }
    }

    private function verifyQuestionnaire(
        Event $event,
        Questionnaire $questionnaire
    ): void {
        abort_unless(
            $questionnaire->event_id === $event->id,
            404
        );
    }

    private function verifyQuestion(
        Questionnaire $questionnaire,
        Question $question
    ): void {
        abort_unless(
            $question->questionnaire_id === $questionnaire->id,
            404
        );
    }
}