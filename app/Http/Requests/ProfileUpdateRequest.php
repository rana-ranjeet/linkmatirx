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

            $bankDetailsSupplied = collect([
                'payout_account_holder_name',
                'payout_bank_name',
                'payout_account_number',
                'payout_ifsc_code',
                'payout_account_type',
            ])->contains(fn (string $field): bool => $this->filled($field));

            if ($bankDetailsSupplied) {
                $rules += [
                    'payout_account_holder_name' => ['required', 'string', 'max:255'],
                    'payout_bank_name' => ['required', 'string', 'max:255'],
                    'payout_account_number' => ['required', 'string', 'regex:/^[0-9]{6,34}$/'],
                    'payout_account_number_confirmation' => ['required', 'same:payout_account_number'],
                    'payout_ifsc_code' => ['required', 'string', 'regex:/^[A-Z]{4}0[A-Z0-9]{6}$/i'],
                    'payout_account_type' => ['required', Rule::in(['savings', 'current'])],
                ];
            }
        }

        return $rules;
    }
}
