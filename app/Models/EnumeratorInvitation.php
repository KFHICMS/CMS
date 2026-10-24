<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;


class EnumeratorInvitation extends Model
{
    protected $fillable = [
        'event_id',
        'questionnaire_id',
        'token_hash',
        'token_hint',
        'max_submissions',
        'expires_at',
        'revoked_at',
        'created_by',
        'revoked_by',
        'token_encrypted',
    ];

    protected $hidden = ['token_hash', 'token_encrypted'];

    protected function casts(): array
    {
        return [
            'max_submissions' => 'integer',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /** 32 random bytes, URL-safe (43 characters). Never stored; only its hash is. */
    public static function generateToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    /** One of: active | expired | revoked. */
    public function status(): string
    {
        if ($this->revoked_at !== null) {
            return 'revoked';
        }

        return $this->expires_at->isPast() ? 'expired' : 'active';
    }

    public function isUsable(): bool
    {
        return $this->status() === 'active';
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(QuestionnaireResponse::class, 'invitation_id');
    }

        /** Full shareable link, or null for links created before copying existed. */
    public function shareUrl(): ?string
    {
        if (! $this->token_encrypted) {
            return null;
        }

        try {
            return url('/join/'.Crypt::decryptString($this->token_encrypted));
        } catch (DecryptException) {
            return null;
        }
    }
}