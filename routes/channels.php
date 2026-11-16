<?php

use App\Models\Course;
use App\Models\Message;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('order.{}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('message.{courseId}', function ($user, $courseId) {
    return Message::where('course_id', $courseId)
        ->where(fn ($q) => $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id))
        ->exists();
});