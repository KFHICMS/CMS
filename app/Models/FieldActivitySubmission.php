<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FieldActivitySubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'child_id',
        'user_id',
        'status',
        'attendance_status',
        'identification_method',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function benefits(): HasMany
    {
        return $this->hasMany(FieldActivityBenefit::class, 'submission_id');
    }
    public function corrections(): HasMany
    {
        return $this->hasMany(FieldActivityCorrection::class, 'submission_id')->latest();
    }

    public function observations(): HasMany
    {
        return $this->hasMany(FieldActivityObservation::class, 'submission_id');
    }
}
