<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Relations\MorphMany;
=======
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
        'event_id',
        'marked_by',
        'attendance_time',
        'method',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'attendance_time' => 'datetime',
        ];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
<<<<<<< HEAD

    public function followUps(): MorphMany
    {
        return $this->morphMany(FollowUp::class, 'target');
    }
=======
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
}