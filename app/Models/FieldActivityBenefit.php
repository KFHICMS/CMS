<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldActivityBenefit extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'item_name',
        'category',
        'quantity',
        'received_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'received_at' => 'date',
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
