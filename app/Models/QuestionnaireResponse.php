<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionnaireResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'questionnaire_id',
        'child_id',
        'submitted_by',
        'submitted_at',
        'response_uuid',
        'invitation_id',
        'respondent_name',
        'idempotency_key',
        'review_status',
        'photo_path',
        'photo_disk',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuestionnaireAnswer::class, 'response_id');
    }

    public function edits(): HasMany
    {
        return $this->hasMany(QuestionnaireResponseEdit::class, 'response_id')->latest();
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}