<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionnaireAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'response_id',
        'question_id',
        'answer_text',
        'answer_json',
    ];

    protected function casts(): array
    {
        return [
            'answer_json' => 'array',
        ];
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(
            QuestionnaireResponse::class,
            'response_id'
        );
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}