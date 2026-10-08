<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveWebsiteRequest;
use App\Models\Website;
use App\Models\WebsiteCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebsiteController extends Controller
{
    public function index(Request $request): View
    {
        $websites = Website::where('publisher_id', $request->user()->id)
            ->with('category')
            ->latest()
            ->paginate(10);

        return view('publisher.websites.index', compact('websites'));
    }

    public function create(): View
    {
        $this->authorize('create', Website::class);

        return view('publisher.websites.form', [
            'website' => new Website,
            'categories' => WebsiteCategory::orderBy('name')->get(),
            'formAction' => route('publisher.websites.store'),
            'formMethod' => 'POST',
        ]);
    }

    public function store(SaveWebsiteRequest $request): RedirectResponse
    {
        Website::create($this->attributes($request->validated()) + [
            'publisher_id' => $request->user()->id,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        return redirect()->route('publisher.websites.index')
            ->with('status', 'Website submitted for review.');
    }

    public function edit(Website $website): View
    {
        $this->authorize('update', $website);

        return view('publisher.websites.form', [
            'website' => $website,
            'categories' => WebsiteCategory::orderBy('name')->get(),
            'formAction' => route('publisher.websites.update', $website),
            'formMethod' => 'PUT',
        ]);
    }

    public function update(SaveWebsiteRequest $request, Website $website): RedirectResponse
    {
        $website->update($this->attributes($request->validated()) + [
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'moderation_note' => null,
        ]);

        return redirect()->route('publisher.websites.index')
            ->with('status', 'Website changes submitted for review.');
    }

    private function attributes(array $data): array
    {
        return [
            'domain' => $data['domain'],
            'category_id' => $data['category_id'] ?? null,
            'description' => $data['description'] ?? null,
            'country_code' => $data['country_code'] ?? null,
            'language_code' => $data['language_code'] ?? null,
            'guest_post_price_cents' => $this->toMinorUnits($data['guest_post_price'] ?? null),
            'link_insertion_price_cents' => $this->toMinorUnits($data['link_insertion_price'] ?? null),
        ];
    }

    private function toMinorUnits(?string $amount): ?int
    {
        if ($amount === null || $amount === '') {
            return null;
        }

        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    }
}
