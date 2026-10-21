<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Relations\MorphMany;
=======
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73

class Guardian extends Model
{
    use HasFactory;

    /**
     * Attributes that can be mass assigned.
     *
     * @var list<string>
     */
    protected $fillable = [
        'child_id',
        'full_name',
        'relationship',
        'phone',
        'alternate_phone',
        'address',
        'occupation',
        'guardian_type',
        'is_primary',
    ];

    /**
     * Attribute casting.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    /**
     * Child associated with this guardian.
     */
    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }
<<<<<<< HEAD

    public function followUps(): MorphMany
    {
        return $this->morphMany(FollowUp::class, 'target');
    }
=======
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
}