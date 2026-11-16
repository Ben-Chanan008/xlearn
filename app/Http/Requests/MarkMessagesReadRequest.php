<?php

namespace App\Http\Requests;

use App\Models\Message;
use Illuminate\Foundation\Http\FormRequest;

class MarkMessagesReadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Only the receiver can mark a message read, and only messages from this course.
     */
    public function authorize(): bool
    {
        $ids = collect($this->input('ids'))->unique();

        return Message::whereIn('id', $ids)
            ->where('course_id', $this->route('course')->id)
            ->where('receiver_id', $this->user()->id)
            ->count() === $ids->count();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ids' => 'required|array|max:500',
            'ids.*' => 'integer',
        ];
    }
}
