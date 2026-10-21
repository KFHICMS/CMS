<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChildCustomFieldValue extends Model
{
    protected $fillable = [
        'child_id',
        'custom_field_id',
        'value',
    ];

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function customField(): BelongsTo
    {
        return $this->belongsTo(ChildCustomField::class, 'custom_field_id');
    }
}
