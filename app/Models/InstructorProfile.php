<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * The extra, optional info an instructor adds after registering
 */
class InstructorProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'headline',
        'bio',
        'expertise',
        'years_experience',
        'website',
        'linkedin',
    ];

    protected function casts(): array
    {
        return [
            'expertise' => 'array',
            'years_experience' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}