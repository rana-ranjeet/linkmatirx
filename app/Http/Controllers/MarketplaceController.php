<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Models\WebsiteCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'exists:website_categories,slug'],
            'country' => ['nullable', 'string', 'size:2', 'alpha'],
            'language' => ['nullable', 'string', 'max:10'],
            'min_dr' => ['nullable', 'integer', 'between:0,100'],
            'min_da' => ['nullable', 'integer', 'between:0,100'],
            'min_traffic' => ['nullable', 'integer', 'min:0'],
            'max_price' => ['nullable', 'integer', 'between:0,100000000'],
            'currency' => ['nullable', 'required_with:max_price', 'string', 'size:3', 'alpha'],
            'service' => ['nullable', Rule::in(['guest_post', 'link_insertion'])],
        ]);

        $websites = Website::approved()
            ->with(['category', 'publisher'])
            ->when($filters['q'] ?? null, function ($query, string $term) {
                $query->where(function ($query) use ($term) {
                    $query->where('domain', 'like', '%'.$term.'%')
                        ->orWhere('description', 'like', '%'.$term.'%');
                });
            })
            ->when($filters['category'] ?? null, function ($query, string $slug) {
                $query->whereHas('category', fn ($category) => $category->where('slug', $slug));
            })
            ->when($filters['country'] ?? null, fn ($query, string $country) => $query->where('country_code', strtoupper($country)))
            ->when($filters['language'] ?? null, fn ($query, string $language) => $query->where('language_code', $language))
            ->when($filters['currency'] ?? null, fn ($query, string $currency) => $query->where('currency', strtoupper($currency)))
            ->when(array_key_exists('min_dr', $filters), fn ($query) => $query->where('domain_rating', '>=', $filters['min_dr']))
            ->when(array_key_exists('min_da', $filters), fn ($query) => $query->where('domain_authority', '>=', $filters['min_da']))
            ->when(array_key_exists('min_traffic', $filters), fn ($query) => $query->where('organic_traffic', '>=', $filters['min_traffic']))
            ->when(array_key_exists('max_price', $filters), function ($query) use ($filters) {
                $column = ($filters['service'] ?? null) === 'link_insertion'
                    ? 'link_insertion_price_cents'
                    : 'guest_post_price_cents';

                $query->where('currency', strtoupper($filters['currency']))
                    ->whereNotNull($column)
                    ->where($column, '<=', $filters['max_price'] * 100);
            })
            ->when(($filters['service'] ?? null) === 'guest_post', fn ($query) => $query->whereNotNull('guest_post_price_cents'))
            ->when(($filters['service'] ?? null) === 'link_insertion', fn ($query) => $query->whereNotNull('link_insertion_price_cents'))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('marketplace.index', [
            'websites' => $websites,
            'categories' => WebsiteCategory::orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function show(Website $website): View
    {
        abort_unless($website->status === 'approved' && $website->is_active, 404);

        $website->load(['category', 'publisher']);

        return view('marketplace.show', compact('website'));
    }
}
