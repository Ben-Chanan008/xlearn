<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CheckInactiveMessages 
{
    // use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        // 
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Fetch messages that are active
        try{
            // update() returns the number of rows it changed
            $updatedCount = Message::where(['status' => 'active'])
                ->where('created_at', '<', now()->subWeeks(3))
                ->update(['status' => 'inactive']);

            Log::info("Inactive messages updated successfully on: " . now() . ". Total messages updated: " . $updatedCount);
        } catch (\Exception $e) {
            Log::error('Failed to update inactive messages on ' . now() . ': ' . $e->getMessage());
        }
    }
}