<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Models\InstructorReview;
use App\Models\Message;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class MessageController extends Controller
{
    public function showMessages(Course $course)
    {
        // Every message both ways, whatever its status, so the student sees their whole history
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

        // Every message whatever its status, same as the student's page
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
                'sent_at' => $message->created_at->diffForHumans(),
            ]),
        ]);
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

                Message::create([
                    'sender_id' => $senderId,
                    'receiver_id' => $validated['receiver_id'],
                    'course_id' => $course->id,
                    'content' => $validated['content'],
                    // Only the first unanswered message starts the clock (question 3)
                    'awaits_reply' => ! $fromInstructor && ! $openWait()->exists(),
                ]);

                // Any instructor message to the student stops the clock
                if ($fromInstructor)
                    $openWait()->update(['replied_at' => now()]);
            });

            return response()->json(['message' => 'Message sent successfully.'], 200);
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            Log::error('Failed to send message: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to send message.'], 500);
        }
    }
}