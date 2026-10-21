<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QRCode extends Model
{
    use HasFactory;

    protected $table = 'qr_codes';

    protected $fillable = [
        'child_id',
        'token_hash',
        'qr_image_path',
        'status',
        'issued_at',
        'expires_at',
        'last_scanned_at',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'expires_at' => 'datetime',
            'last_scanned_at' => 'datetime',
        ];
    }

    /**
     * Child associated with this QR code.
     */
    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }
}