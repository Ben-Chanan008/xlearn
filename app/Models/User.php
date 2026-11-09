<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'date_of_birth',
        'gender',
        'address',
        'province',
        'phone',
        'rating',
        'response_score',
        'on_time_rate',
        'rating_updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'rating' => 'float',
            'response_score' => 'float',
            'on_time_rate' => 'integer',
            'rating_updated_at' => 'datetime',
        ];
    }

    public function fullName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function initials(): string
    {
        return strtoupper($this->first_name[0] . $this->last_name[0]);
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    public function studentCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'student_course', 'student_id', 'course_id')
            ->withPivot('course_progress', 'completed_at', 'accessed_at')
            ->withTimestamps();
    }

    /**
     * Star reviews students left for this instructor
     */
    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(InstructorReview::class, 'instructor_id');
    }

    /**
     * How fast this instructor usually replies, from response_score; null until there's enough data
     */
    public function replyTimeLabel(): ?string
    {
        return match (true) {
            $this->response_score === null => null,
            $this->response_score >= 4 => 'Usually replies within a few hours',
            $this->response_score >= 3 => 'Usually replies within a day',
            default => 'Replies can be slow',
        };
    }

    public function isInstructor(): bool
    {
        return $this->role === 'instructor';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }
}
