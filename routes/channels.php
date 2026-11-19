<?php

use App\Models\Course;
use App\Models\Message;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('order.{}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('message.{courseId}.{senderId}.{receiverId}', function ($user, $courseId, $senderId, $receiverId) {
    $message = Message::where('course_id', $courseId)->between($senderId, $receiverId)
        ->exists();

    if (!$message)
        return Course::find($courseId)->students()->where('student_id', $senderId)->orWhere('student_id', $receiverId)->exists();

    return $message;
});

// Joined only while a conversation is open on screen, so NewMessage can skip notifying someone already reading it
Broadcast::channel('conversation.{courseId}.{studentId}.{instructorId}', function ($user, $courseId, $studentId, $instructorId) {
    if (! in_array($user->id, [(int) $studentId, (int) $instructorId], true))
        return false;

    $course = Course::find($courseId);
    $isConversation = $course
        && $course->instructor_id == $instructorId
        && $course->students()->where('student_id', $studentId)->exists();

    // Presence channels need the user's info back, not just true
    return $isConversation ? ['id' => $user->id] : false;
});