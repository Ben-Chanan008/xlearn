<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\SanitizesLessonHtml;
use Illuminate\Foundation\Http\FormRequest;

class StoreCourseSectionRequest extends FormRequest
{
    use SanitizesLessonHtml;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Ownership of the course is checked by the route's can('manage', 'course')
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->sanitizeLessonHtml();
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
            'contents' => 'required|array|min:1',
            'contents.*.content_name' => 'required|string|max:255',
            'contents.*.content_mode' => 'required|in:text,files,both',
            'contents.*.content_information' => 'nullable|string|required_if:contents.*.content_mode,text,both',
            'contents.*.files' => 'array|required_if:contents.*.content_mode,files,both',
            // 512MB each; zip covers Office files, which are zip archives underneath
            'contents.*.files.*' => 'file|max:524288|mimes:pdf,doc,docx,ppt,pptx,odp,key,xls,xlsx,csv,txt,zip,png,jpg,jpeg,gif,webp,mp3,wav,m4a,aac,ogg,mp4,webm,mov,qt,m4v',
            'contents.*.is_assignment' => 'boolean',
            'contents.*.due_at' => 'nullable|date|after:now',
        ];
    }

    public function messages(): array
    {
        return [
            'contents.required' => 'Add at least one content item to the section.',
            'contents.*.content_information.required_if' => 'Write the lesson content, or switch this item to files only.',
            'contents.*.files.required_if' => 'Upload at least one file, or switch this item to written content only.',
        ];
    }

    public function attributes(): array
    {
        return [
            'contents.*.content_name' => 'content title',
            'contents.*.content_mode' => 'content type',
            'contents.*.files.*' => 'file',
            'contents.*.due_at' => 'due date',
        ];
    }
}
