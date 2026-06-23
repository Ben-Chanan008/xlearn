<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmission extends Model
{
    //
    public function courseSectionContent(): BelongsTo
    {
        return $this->belongsTo(CourseSectionsContent::class);
    }
}