<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ReviewWebsiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'moderation_note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this->input('status') === 'rejected' && blank($this->input('moderation_note'))) {
                    $validator->errors()->add('moderation_note', 'Add a reason when rejecting a website.');
                }
            },
        ];
    }
}
