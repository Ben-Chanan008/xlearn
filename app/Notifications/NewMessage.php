<?php

namespace App\Notifications;

use App\Models\Message;
use App\Models\User;
use Illuminate\Broadcasting\Broadcasters\PusherBroadcaster;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Broadcast;
use Throwable;

class NewMessage extends Notification implements ShouldQueue
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
        return ['database'];
    }

    /**
     * Skipped if the message was already read, the receiver is viewing the conversation, or it already has an unread notification
     */
    public function shouldSend(User $notifiable, string $channel): bool
    {
        return ! $this->message->fresh()->is_read
            && ! $this->isViewing($notifiable)
            && ! self::forConversation($notifiable, $this->message->course_id, $this->message->sender_id)
                ->unread()
                ->exists();
    }

    /**
     * Whether the receiver has this conversation open right now, i.e. is in its presence channel
     */
    protected function isViewing(User $notifiable): bool
    {
        $broadcaster = Broadcast::driver();
        if (! $broadcaster instanceof PusherBroadcaster)
            return false;

        $instructorId = $this->message->course->instructor_id;
        $studentId = $this->message->sender_id == $instructorId ? $this->message->receiver_id : $this->message->sender_id;

        try {
            $users = $broadcaster->getPusher()
                ->getPresenceUsers("presence-conversation.{$this->message->course_id}.{$studentId}.{$instructorId}")
                ->users;
        } catch (Throwable) {
            // Reverb unreachable: notify rather than miss the message
            return false;
        }
        
        /* makes sure the receiver is viewing the channel too. */
        return collect($users)->contains(fn ($user) => $user->id == $notifiable->id);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(User $notifiable): array
    {
        $course = $this->message->course;
        $toInstructor = $course->instructor_id == $notifiable->id;

        return [
            'title' => 'New message',
            'body' => "{$this->message->sender->fullName()} sent you a message in {$course->name}",
            'url' => $toInstructor
                ? route('instructor.courses.students', [$course, 'conversation' => $this->message->sender_id])
                : route('course.messages', $course),
            'icon' => 'fa-envelope',
            'course_id' => $course->id,
            'sender_id' => $this->message->sender_id,
        ];
    }

    /**
     * The user's NewMessage notifications from one sender in one course, read or unread
     */
    public static function forConversation(User $user, int $courseId, int $senderId)
    {
        return $user->notifications()
            ->where('type', self::class)
            ->where('data->course_id', $courseId)
            ->where('data->sender_id', $senderId);
    }

    /**
     * Marks that conversation's notifications read, once the user has seen its messages
     */
    public static function markConversationRead(User $user, int $courseId, int $senderId): void
    {
        self::forConversation($user, $courseId, $senderId)->unread()->update(['read_at' => now()]);
    }
}