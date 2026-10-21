<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Relations\MorphMany;
=======
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
use Illuminate\Database\Eloquent\SoftDeletes;

class Child extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Attributes that can be mass assigned.
     *
     * @var list<string>
     */
    protected $fillable = [
        'child_code',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'school_name',
        'grade',
        'address',
        'district',
        'gn_division',
        'profile_photo',
        'status',
        'notes',
        'created_by',
    ];

    /**
     * Attribute casting.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    /**
     * User who created this child record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Guardians of this child.
     */
    public function guardians(): HasMany
    {
        return $this->hasMany(Guardian::class);
    }

    /**
     * QR code assigned to this child.
     */
    public function qrCode(): HasOne
    {
        return $this->hasOne(QRCode::class);
    }

    /**
     * Digital ID assigned to this child.
     */
    public function digitalId(): HasOne
    {
        return $this->hasOne(DigitalId::class);
    }

    /**
     * Attendance records for this child.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

<<<<<<< HEAD
    /**
     * Follow-up records for this child.
     */
    public function followUps(): MorphMany
    {
        return $this->morphMany(FollowUp::class, 'target');
=======
    public function fieldActivitySubmissions(): HasMany
    {
        return $this->hasMany(FieldActivitySubmission::class);
    }

    /**
     * Follow-up records for this child.
     */
    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
    }

    /**
     * Additional information records for this child.
     */
    public function additionalInformation(): HasMany
    {
        return $this->hasMany(ChildInformation::class);
    }

<<<<<<< HEAD
    public function customFieldValues(): HasMany
    {
        return $this->hasMany(ChildCustomFieldValue::class);
    }

=======
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
    /**
     * Questionnaire responses for this child.
     */
    public function questionnaireResponses(): HasMany
    {
        return $this->hasMany(QuestionnaireResponse::class);
    }
}