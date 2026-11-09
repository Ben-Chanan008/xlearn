<?php

namespace App\Http\Requests;

use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Messages only go between the course's instructor and one of its enrolled students, never student to student.
     */
    public function authorize(): bool
    {
        $course = Course::find($this->input('course_id'));

        if (! $course)
            return false;

        $senderId = $this->user()->id;
        $receiverId = (int) $this->input('receiver_id');

        if ($course->instructor_id == $senderId)
            return $this->isEnrolled($receiverId, $course);

        return $course->instructor_id == $receiverId && $this->isEnrolled($senderId, $course);
    }

    private function isEnrolled(int $userId, Course $course): bool
    {
        return $course->students()->where('student_id', $userId)->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'course_id' => 'required|integer|exists:courses,id',
            'receiver_id' => 'required|integer|exists:users,id',
            'content' => 'required|string|max:2000',
        ];
    }
}
