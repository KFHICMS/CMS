<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Relations\MorphMany;
=======
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73

class ChildInformation extends Model
{
    use HasFactory;

    protected $table = 'child_information';

    protected $fillable = [
        'child_id',
        'information_type',
        'title',
        'description',
        'information_date',
        'added_by',
    ];

    protected function casts(): array
    {
        return [
            'information_date' => 'date',
        ];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }
<<<<<<< HEAD

    public function followUps(): MorphMany
    {
        return $this->morphMany(FollowUp::class, 'target');
    }
=======
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
}