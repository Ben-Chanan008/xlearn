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
use App\Notifications\CourseRemoved;
use Illuminate\Support\Facades\Notification;

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

    // Soft-deleting a course soft-deletes its sections too
    protected static function booted(): void
    {
        static::deleting(function (Course $course) {
            $course->courseSections->each->delete();

            // Purging an already removed course isn't news, and its owner may be gone by then
            if ($course->isForceDeleting())
                return;

            // Covers both a single course delete and an instructor deleting their profile (question 5)
            Notification::send(
                $course->students->concat([$course->owner]),
                new CourseRemoved($course->name, $course->owner->fullName(), $course->owner->id),
            );
        });
    }

    public function courseSections(): HasMany
    {
        return $this->hasMany(CourseSection::class)->orderBy('position');
    }

}