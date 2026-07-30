<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseSectionsContent extends Model
{
    use HasFactory;

    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'students_id');
    }

    public function courseSection(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }
}