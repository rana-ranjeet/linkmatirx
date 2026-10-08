<?php

namespace App\Http\Requests;

use App\Models\Website;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveWebsiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $website = $this->route('website');

        return $website instanceof Website
            ? $this->user()?->can('update', $website) ?? false
            : $this->user()?->can('create', Website::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('domain')) {
            $this->merge(['domain' => strtolower(trim($this->input('domain')))]);
        }

        if ($this->filled('country_code')) {
            $this->merge(['country_code' => strtoupper(trim($this->input('country_code')))]);
        }
    }

    public function rules(): array
    {
        $website = $this->route('website');
        $uniqueDomain = Rule::unique('websites', 'domain');

        if ($website instanceof Website) {
            $uniqueDomain->ignore($website->id);
        }

        return [
            'domain' => [
                'required',
                'string',
                'max:253',
                'regex:/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\\.)+[a-z]{2,63}$/i',
                $uniqueDomain,
            ],
            'category_id' => ['nullable', 'integer', 'exists:website_categories,id'],
            'description' => ['nullable', 'string', 'max:10000'],
            'country_code' => ['nullable', 'string', 'size:2', 'alpha'],
            'language_code' => ['nullable', 'string', 'max:10', 'regex:/^[a-zA-Z]{2,3}(?:-[a-zA-Z0-9]{2,8})*$/'],
            'guest_post_price' => ['nullable', 'required_without:link_insertion_price', 'decimal:0,2', 'min:0.01', 'max:1000000'],
            'link_insertion_price' => ['nullable', 'required_without:guest_post_price', 'decimal:0,2', 'min:0.01', 'max:1000000'],
        ];
    }
}
