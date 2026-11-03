<?php

namespace App\Http\Requests;

use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * The full edit page; same fields as creating a section, plus the ids of existing items and the files they keep
 */
class UpdateCourseSectionRequest extends StoreCourseSectionRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            // Only items of this section, so changing an id can't edit another section's content
            'contents.*.id' => ['nullable', 'integer', Rule::in($this->route('courseSection')->sectionContents()->pluck('id'))],
            'contents.*.keep_files' => 'array',
            'contents.*.keep_files.*' => 'string',
            // Kept files count too, so after() checks each file item still ends up with one
            'contents.*.files' => 'array',
            // after() checks this, since an unchanged past date is allowed
            'contents.*.due_at' => 'nullable|date',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $existing = $this->route('courseSection')->sectionContents->keyBy('id');

                foreach ($this->input('contents', []) as $index => $content) {
                    $current = $existing->get($content['id'] ?? null);

                    if (in_array($content['content_mode'] ?? null, ['files', 'both'], true)) {
                        $kept = collect($current?->files ?? [])->pluck('path')->intersect($content['keep_files'] ?? [])->count();
                        $new = count($this->file("contents.$index.files") ?? []);

                        if ($kept + $new === 0)
                            $validator->errors()->add("contents.$index.files", $this->messages()['contents.*.files.required_if']);
                    }

                    $dueAt = $content['due_at'] ?? null;
                    if (($content['is_assignment'] ?? false) && $dueAt && strtotime($dueAt) !== false) {
                        $due = Carbon::parse($dueAt)->utc();
                        // Minutes only: the date picker has no seconds
                        $unchanged = $current?->due_at?->format('Y-m-d H:i') === $due->format('Y-m-d H:i');

                        if (! $unchanged && $due->isPast())
                            $validator->errors()->add("contents.$index.due_at", 'The due date field must be a date after now.');
                    }
                }
            },
        ];
    }
}
