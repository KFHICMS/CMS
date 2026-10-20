<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldActivityObservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'category',
        'note',
        'observed_at',
    ];

    protected function casts(): array
    {
        return [
            'observed_at' => 'datetime',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(
            FieldActivitySubmission::class,
            'submission_id'
        );
    }
}
