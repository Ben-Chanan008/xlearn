<?php

namespace App\Http\Requests;

use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * The sender and the receiver must both belong to the course, as its owner or as an enrolled student.
     */
    public function authorize(): bool
    {
        $course = Course::find($this->input('course_id'));

        if (! $course)
            return false;

        return $this->belongsToCourse($this->user()->id, $course)
            && $this->belongsToCourse($this->input('receiver_id'), $course);
    }

    private function belongsToCourse($userId, Course $course): bool
    {
        return $course->instructor_id == $userId
            || $course->students()->where('student_id', $userId)->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sender_id' => 'required|exists:users,id',
            'course_id' => 'required|integer|exists:courses,id',
            'receiver_id' => 'required|integer|exists:users,id',
            'content' => 'required|string|max:255',
        ];
    }
}
