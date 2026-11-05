<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Models\Message;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function showMessages(Course $course)
    {

        $messages = Message::with('sender')
            ->where('receiver_id', Auth::id())
            ->where('course_id', $course->id)
            ->where('status', 'active')
            ->latest()
            ->limit(5)
            ->get();

        return view('student.messages', compact('course', 'messages'));
    }

    /**
     * The instructor's conversation with one student in a course, oldest message first.
     */
    public function conversation(Course $course, User $student)
    {
        abort_unless($course->students()->where('student_id', $student->id)->exists(), 404);

        $instructorId = Auth::id();

        $messages = Message::where('course_id', $course->id)
            ->where('status', 'active')
            ->where(function ($query) use ($instructorId, $student) {
                $query->where(fn ($q) => $q->where('sender_id', $instructorId)->where('receiver_id', $student->id))
                    ->orWhere(fn ($q) => $q->where('sender_id', $student->id)->where('receiver_id', $instructorId));
            })
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
            Message::create([
                'sender_id' => $validated['sender_id'],
                'receiver_id' => $validated['receiver_id'],
                'course_id' => $validated['course_id'],
                'content' => $validated['content'],
            ]);
                
            return response()->json(['message' => 'Message sent successfully.'], 200);
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            Log::error('Failed to send message: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to send message.'], 500);
        }
    }
}