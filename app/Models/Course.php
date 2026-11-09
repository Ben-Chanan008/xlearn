<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    // A course is published automatically once it has this many sections (VerifyCourseCanBePublished)
    public const SECTIONS_TO_PUBLISH = 5;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'thumbnail',
        'price',
        'discount_code',
        'instructor_id',
        'course_code',
        'max_students',
        'status',
        'tags',
    ];  

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'student_course', 'course_id', 'student_id')
            ->withPivot('course_progress', 'completed_at', 'accessed_at')
            ->withTimestamps();
    }

    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('status', 'published');
    }

    /**
     * Not published yet, so students can't see it
     */
    public function isDraft(): bool
    {
        return $this->status !== 'published';
    }

    public function courseSections(): HasMany
    {
        return $this->hasMany(CourseSection::class)->orderBy('position');
    }

}