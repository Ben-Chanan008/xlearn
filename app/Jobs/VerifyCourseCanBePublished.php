<?php

namespace App\Jobs;

use App\Models\Course;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class VerifyCourseCanBePublished implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Course $course
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        $sectionsCount = $this->course->courseSections()->count();
        
        switch(true){
            case $sectionsCount === 5 && $this->course->status !== 'published':
                $this->course->update(['status' => 'published']);
                Log::info("Course {$this->course->id} has been published.");
                break;
            case $sectionsCount > 5 && $this->course->status !== 'published':
                $this->course->update(['status' => 'published']);
                Log::info("Course {$this->course->id} has been published.");
                break;
            case $sectionsCount < 5 && $this->course->status === 'published':
                $this->course->update(['status' => 'pending']);
                Log::info("Course {$this->course->id} has been reverted to draft.");
                break;
            default:
                Log::info("Course {$this->course->id} has {$sectionsCount} sections and is in status '{$this->course->status}'. No action taken.");
        }
    }
}