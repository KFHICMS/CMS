<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Questionnaire extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'title',
        'description',
        'status',
        'created_by',
        'invitation_id',
        'respondent_name',
        'idempotency_key',
        'review_status',
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
    public function invitations(): HasMany
    {
        return $this->hasMany(EnumeratorInvitation::class);
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(EnumeratorInvitation::class, 'invitation_id');
    }
}