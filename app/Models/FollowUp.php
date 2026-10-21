<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Relations\MorphTo;
=======
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73

class FollowUp extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
<<<<<<< HEAD
        'target_type',
        'target_id',
        'assigned_to',
=======
        'assigned_to',
        'event_id',
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
        'follow_up_date',
        'next_follow_up_date',
        'category',
        'description',
        'action_taken',
        'status',
        'priority',
    ];

    protected function casts(): array
    {
        return [
            'follow_up_date' => 'date',
            'next_follow_up_date' => 'date',
        ];
    }

    /**
     * Child related to this follow-up.
     */
    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

<<<<<<< HEAD
    public function target(): MorphTo
    {
        return $this->morphTo()->morphWith([
            Guardian::class => ['child'],
            Attendance::class => ['child', 'event'],
            Questionnaire::class => ['event'],
            QuestionnaireResponse::class => ['child', 'questionnaire'],
            ChildInformation::class => ['child'],
        ]);
    }

    public function targetModuleLabel(): string
    {
        return match ($this->target_type) {
            Child::class => 'Child Management',
            Guardian::class => 'Guardians',
            Event::class => 'Events',
            Attendance::class => 'Attendance',
            Questionnaire::class => 'Questionnaires',
            QuestionnaireResponse::class => 'Questionnaire Responses',
            ChildInformation::class => 'Child Information',
            default => 'Unknown module',
        };
    }

    public function targetDisplayName(): string
    {
        $target = $this->target;

        if ($target instanceof Child) {
            return trim(implode(' ', array_filter([
                $target->first_name,
                $target->middle_name,
                $target->last_name,
            ]))) . ' (' . $target->child_code . ')';
        }

        if ($target instanceof Guardian) {
            return $target->full_name . ($target->child ? ' — ' . $target->child->child_code : '');
        }

        if ($target instanceof Event) {
            return $target->title . ' (' . $target->event_code . ')';
        }

        if ($target instanceof Attendance) {
            return ($target->child?->child_code ?? 'Child unavailable')
                . ' — ' . ($target->event?->title ?? 'Event unavailable');
        }

        if ($target instanceof Questionnaire) {
            return $target->title
                . ($target->event ? ' — ' . $target->event->title : '');
        }

        if ($target instanceof QuestionnaireResponse) {
            return ($target->child?->child_code ?? 'Child unavailable')
                . ' — ' . ($target->questionnaire?->title ?? 'Questionnaire response');
        }

        if ($target instanceof ChildInformation) {
            return $target->title
                . ($target->child ? ' — ' . $target->child->child_code : '');
        }

        return 'Related record unavailable';
    }

=======
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
    /**
     * User assigned to this follow-up.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
<<<<<<< HEAD
=======

    /**
     * Activity (event) this follow-up relates to, if any.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
}