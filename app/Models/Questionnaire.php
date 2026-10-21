<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Relations\MorphMany;
=======
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73

class Questionnaire extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'title',
        'description',
        'status',
        'created_by',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
    public function responses(): HasMany
{
    return $this->hasMany(QuestionnaireResponse::class);
}
<<<<<<< HEAD

    public function followUps(): MorphMany
    {
        return $this->morphMany(FollowUp::class, 'target');
    }
=======
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
}