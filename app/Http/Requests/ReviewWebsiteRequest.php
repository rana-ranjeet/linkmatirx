<?php

namespace App\Http\Requests;

use App\Models\Website;
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
            'verification_override' => ['sometimes', 'boolean'],
            'verification_override_reason' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this->input('status') === 'rejected' && blank($this->input('moderation_note'))) {
                    $validator->errors()->add('moderation_note', 'Add a reason when rejecting a website.');
                }

                $website = $this->route('website');

                if (
                    $this->input('status') === 'approved'
                    && $website instanceof Website
                    && $website->verification_status !== 'verified'
                ) {
                    if (! $this->boolean('verification_override')) {
                        $validator->errors()->add('verification_override', 'Ownership verification is incomplete. Select the explicit override to approve this listing.');
                    }

                    if (blank($this->input('verification_override_reason'))) {
                        $validator->errors()->add('verification_override_reason', 'Explain why this unverified listing is being approved.');
                    }
                }
            },
        ];
    }
}
