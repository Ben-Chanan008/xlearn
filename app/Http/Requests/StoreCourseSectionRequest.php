<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class StoreCourseSectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Ownership of the course is checked by the route's can('manage', 'course')
        return true;
    }

    /**
     * Instructor HTML is shown to students, so only the tags the editor's toolbar makes are kept
     */
    protected function prepareForValidation(): void
    {
        $sanitizer = new HtmlSanitizer(
            (new HtmlSanitizerConfig())
                ->allowElement('p')
                ->allowElement('br')
                ->allowElement('h2')
                ->allowElement('h3')
                ->allowElement('strong')
                ->allowElement('em')
                ->allowElement('u')
                ->allowElement('s')
                ->allowElement('ol')
                ->allowElement('ul')
                ->allowElement('li')
                ->allowElement('blockquote')
                ->allowElement('pre')
                ->allowElement('code')
                ->allowElement('a', ['href'])
                ->allowLinkSchemes(['http', 'https', 'mailto'])
                ->forceAttribute('a', 'target', '_blank')
                ->forceAttribute('a', 'rel', 'noopener noreferrer')
        );

        $contents = collect($this->input('contents', []))->map(function ($content) use ($sanitizer) {
            $html = $sanitizer->sanitize($content['content_information'] ?? '');
            // An empty editor still sends <p><br></p>, which counts as no text
            $content['content_information'] = trim(strip_tags($html)) === '' ? null : $html;

            return $content;
        });

        $this->merge(['contents' => $contents->all()]);
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
