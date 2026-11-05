<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RenameCourseSectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Ownership of the course is checked by the route's can('manage', 'course'),
        // and scopeBindings() 404s a section that isn't in that course
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'section_name' => 'required|string|max:255',
        ];
    }
}
