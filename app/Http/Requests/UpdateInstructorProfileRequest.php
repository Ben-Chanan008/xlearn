<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInstructorProfileRequest extends FormRequest
{
    /**
     * Only the instructor whose profile it is can edit it
     */
    public function authorize(): bool
    {
        return $this->route('user')->is($this->user()) && $this->user()->isInstructor();
    }

    public function rules(): array
    {
        return [
            'avatar' => ['nullable', 'image', 'max:2048'],
            'headline' => ['nullable', 'string', 'max:120'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'expertise' => ['nullable', 'array', 'max:8'],
            'expertise.*' => ['string', 'max:30', 'distinct'],
            'years_experience' => ['nullable', 'integer', 'between:0,60'],
            'website' => ['nullable', 'url', 'max:255'],
            'linkedin' => ['nullable', 'url', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'expertise.max' => 'Pick up to 8 areas of expertise.',
            'expertise.*.max' => 'Each area of expertise can be up to 30 characters.',
            'expertise.*.distinct' => 'That area of expertise is already listed.',
            'avatar.max' => 'The photo must be 2 MB or smaller.',
        ];
    }
}
