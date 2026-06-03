<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseSection extends Model
{
    use HasFactory, SoftDeletes;

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function sectionContents(): HasMany
    {
        return $this->hasMany(CourseSectionsContent::class);
    }
}