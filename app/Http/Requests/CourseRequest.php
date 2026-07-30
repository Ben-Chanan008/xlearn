<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $courseId = $this->route('course')?->id;

        return [
            //
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'tags' => 'required',
            'max_students' => 'required|integer|min:1',
            'course_code' => 'required|string|max:255|unique:courses,course_code,' . ($courseId ?? ''),
            'price' => 'required|min:0',
            'discount_code' => 'integer|min:0|max:100|nullable',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,avif|max:2048'
        ];
    }
}
