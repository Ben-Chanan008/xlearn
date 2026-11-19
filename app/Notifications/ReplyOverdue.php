<?php

namespace App\Notifications;

use App\Models\Message;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReplyOverdue extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Message $message)
    {
        $this->afterCommit();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("{$this->message->sender->shortName()} is waiting for a reply")
            ->greeting("Hi {$notifiable->first_name},")
            ->line($this->body())
            ->action('Reply now', $this->url());
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(User $notifiable): array
    {
        return [
            'title' => 'Reply overdue',
            'body' => $this->body(),
            'url' => $this->url(),
            'icon' => 'fa-hourglass-half',
            'course_id' => $this->message->course_id,
            'sender_id' => $this->message->sender_id,
        ];
    }

    private function body(): string
    {
        return "{$this->message->sender->shortName()} has been waiting over a day for a reply in {$this->message->course->name}. Slow replies lower your rating.";
    }

    // Opens that student's conversation on the manage students page
    private function url(): string
    {
        return route('instructor.courses.students', [$this->message->course, 'conversation' => $this->message->sender_id]);
    }
}
