<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];

        if ($this->user()->role === UserRole::Publisher) {
            $rules += [
                'business_name' => ['nullable', 'string', 'max:255'],
                'business_type' => ['nullable', Rule::in(['brand', 'agency', 'publisher', 'ecommerce', 'other'])],
                'country_code' => ['nullable', 'string', 'size:2', 'alpha'],
                'phone' => ['nullable', 'string', 'max:32'],
                'currency' => ['nullable', Rule::in(['EUR', 'USD', 'GBP', 'CAD', 'AUD', 'INR', 'SGD', 'AED'])],
                'number_of_sites' => ['nullable', 'integer', 'min:0', 'max:100000'],
                'do_follow_links' => ['nullable', Rule::in(['yes', 'no', 'both'])],
                'owns_sites' => ['nullable', Rule::in(['yes', 'no', 'both'])],
                'already_selling_articles' => ['nullable', Rule::in(['yes', 'no'])],
            ];
        }

        return $rules;
    }
}
