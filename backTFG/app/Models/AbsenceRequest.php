<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AbsenceRequest extends Model
{
    protected $fillable = [
        'user_id',
        'absence_type_id',
        'start_date',
        'end_date',
        'status',
        'comments',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function absenceType(): BelongsTo
    {
        return $this->belongsTo(AbsenceType::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class);
    }

    public function approval(): HasOne
    {
        return $this->hasOne(Approval::class);
    }
}
