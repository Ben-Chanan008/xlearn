<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseSectionsContent extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            // List of ['path' => ..., 'name' => ..., 'mime' => ...] on the public disk
            'files' => 'array',
            'due_at' => 'datetime',
        ];
    }

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