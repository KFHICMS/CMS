<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowUp extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
        'assigned_to',
        'event_id',
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

    /**
     * User assigned to this follow-up.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Activity (event) this follow-up relates to, if any.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}