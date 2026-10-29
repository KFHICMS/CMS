<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChildFormField extends Model
{
    protected $fillable = [
        'key', 'label', 'type', 'options', 'is_required', 'is_system',
        'is_protected', 'is_active', 'sort_order', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_required' => 'boolean',
            'is_system' => 'boolean',
            'is_protected' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function values(): HasMany
    {
        return $this->hasMany(ChildFieldValue::class);
    }

    /** Custom (admin-defined) fields currently shown on the form. */
    public function scopeActiveCustom(Builder $query): Builder
    {
        return $query->where('is_system', false)->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }
}