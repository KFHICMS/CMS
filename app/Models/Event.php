<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Relations\MorphMany;
=======
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_code',
<<<<<<< HEAD
=======
        'program_id',
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
        'title',
        'description',
        'event_date',
        'start_time',
        'end_time',
        'location',
        'status',
        'enable_attendance',
        'enable_questionnaires',
        'created_by',
    ];
<<<<<<< HEAD

=======
     
    public function enumerators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_user')->withTimestamps();
    }

    /**
     * One of: upcoming | open | completed | cancelled.
     * Past-dated planned events stay open until an admin marks them completed.
     */
    public function collectionState(): string
    {
        $programStatus = $this->relationLoaded('program')
            ? $this->program?->status
            : $this->program()->value('status');

        return match (true) {
            $this->status === 'cancelled' || $programStatus === 'cancelled' => 'cancelled',
            $this->status === 'completed' || $programStatus === 'completed' => 'completed',
            $this->event_date !== null
                && $this->event_date->copy()->startOfDay()->gt(now()->startOfDay()) => 'upcoming',
            default => 'open',
        };
    }
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'enable_attendance' => 'boolean',
            'enable_questionnaires' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

<<<<<<< HEAD
=======
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
<<<<<<< HEAD
=======

    public function fieldActivitySubmissions(): HasMany
    {
        return $this->hasMany(FieldActivitySubmission::class);
    }
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
    public function questionnaires(): HasMany
{
    return $this->hasMany(Questionnaire::class);
}
<<<<<<< HEAD

    public function followUps(): MorphMany
    {
        return $this->morphMany(FollowUp::class, 'target');
    }
=======
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
}