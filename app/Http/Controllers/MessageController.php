<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Http\Requests\MarkMessagesReadRequest;
use App\Http\Requests\StoreMessageRequest;
use App\Models\InstructorReview;
use App\Models\Message;
use App\Models\Course;
use App\Models\User;
use App\Notifications\NewMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class MessageController extends Controller
{
    private object $message;

    public function showMessages(Course $course)
    {
        // Active messages both ways (the last 3 weeks)
        $messages = Message::where('course_id', $course->id)
            ->between(Auth::id(), $course->instructor_id)
            ->oldest()
            ->get();

        // Opening the page counts as reading the instructor's messages
        Message::where('course_id', $course->id)
            ->where('sender_id', $course->instructor_id)
            ->where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        NewMessage::markConversationRead(Auth::user(), $course->id, $course->instructor_id);

        // Pre-fills the review form if the student already rated this instructor
        $review = InstructorReview::where('course_id', $course->id)->where('student_id', Auth::id())->first();

        return view('student.messages', compact('course', 'messages', 'review'));
    }

    /**
     * The instructor's conversation with one student in a course, oldest message first.
     */
    public function conversation(Course $course, User $student)
    {
        Gate::forUser($student)->authorize('enrolled', $course);

        $instructorId = Auth::id();

        // Active messages only, same as the student's page
        $messages = Message::where('course_id', $course->id)
            ->between($instructorId, $student->id)
            ->oldest()
            ->get();

        // Opening the conversation counts as reading the student's replies
        Message::where('course_id', $course->id)
            ->where('sender_id', $student->id)
            ->where('receiver_id', $instructorId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        NewMessage::markConversationRead(Auth::user(), $course->id, $student->id);

        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $student->fullName(),
                'initials' => $student->initials(),
            ],
            'messages' => $messages->map(fn ($message) => [
                'id' => $message->id,
                'content' => $message->content,
                'from_instructor' => $message->sender_id == $instructorId,
                // ISO, so the page can show "x minutes ago" and keep it current
                'created_at' => $message->created_at,
            ]),
        ]);
    }

    /**
     * Marks the messages that arrived live while the conversation dialog was open as read.
     */
    public function markRead(MarkMessagesReadRequest $request, Course $course)
    {
        $messages = Message::whereIn('id', $request->validated('ids'));
        $senderIds = $messages->clone()->distinct()->pluck('sender_id');
        $messages->update(['is_read' => true]);

        $senderIds->each(fn ($senderId) => NewMessage::markConversationRead($request->user(), $course->id, $senderId));

        return response()->json(['message' => 'Messages marked as read.']);
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreMessageRequest $request)
    {
        //
        $validated = $request->validated();
        try{
            DB::transaction(function () use ($request, $validated) {
                $course = Course::find($validated['course_id']);
                // Always the logged-in user, never the request body, so nobody can send as someone else
                $senderId = $request->user()->id;
                $fromInstructor = $course->instructor_id == $senderId;

                // The open wait in this conversation, if the student is already waiting on a reply
                $openWait = fn () => Message::where('course_id', $course->id)
                    ->where('sender_id', $fromInstructor ? $validated['receiver_id'] : $senderId)
                    ->where('awaits_reply', true)
                    ->whereNull('replied_at');

                $this->message = Message::create([
                    'sender_id' => $senderId,
                    'receiver_id' => $validated['receiver_id'],
                    'course_id' => $course->id,
                    'content' => $validated['content'],
                    // Only the first unanswered message starts the clock (question 3)
                    'awaits_reply' => ! $fromInstructor && ! $openWait()->exists(),
                ]);
                
                broadcast(new MessageSent($this->message))->toOthers();

                // Any instructor message to the student stops the clock
                // Inactive waits close too, so none are left open forever
                if ($fromInstructor)
                    $openWait()->withoutGlobalScope('active')->update(['replied_at' => now()]);
            });

            $this->message->receiver->notify(new NewMessage($this->message));

            return response()->json(['message' => 'Message sent successfully.', 'data' => $this->message ], 200);
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            Log::error('Failed to send message: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to send message.'], 500);
        }
    }
}