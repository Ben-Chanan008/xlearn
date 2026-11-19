<?php

namespace App\Jobs;

use App\Models\Message;
use App\Notifications\ReplyOverdue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendReplyReminders implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // One reminder per wait, not one per hour (question 7)
        // eachById, not each: setting reminded_at drops rows from this query, which would make offset paging skip some
        Message::overdue()->whereNull('reminded_at')->with(['receiver', 'sender', 'course'])->eachById(function (Message $message) {
            $message->receiver->notify(new ReplyOverdue($message));
            $message->update(['reminded_at' => now()]);
        });
    }
}
