<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}