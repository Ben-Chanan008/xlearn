<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Models\Message;
use App\Models\Course;
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