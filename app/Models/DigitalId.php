<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalId extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
        'qr_code_id',
        'card_number',
        'issued_date',
        'expiry_date',
        'card_image_path',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'issued_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    /**
     * Child associated with this digital ID.
     */
    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    /**
     * QR code associated with this digital ID.
     */
    public function qrCode(): BelongsTo
    {
        return $this->belongsTo(QRCode::class);
    }
}