<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldActivityCorrection extends Model
{
    protected $fillable = [
        'submission_id',
        'corrected_by',
        'reason',
        'changes',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(FieldActivitySubmission::class, 'submission_id');
    }

    public function corrector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'corrected_by');
    }
}