<?php

namespace App\Http\Controllers\Publisher;

use App\Exceptions\WebsiteVerificationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveWebsiteRequest;
use App\Models\Website;
use App\Models\WebsiteCategory;
use App\Services\WebsiteOwnershipVerifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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
        $website = Website::create($this->attributes($request->validated()) + [
            'publisher_id' => $request->user()->id,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        return redirect()->to(route('publisher.websites.edit', $website).'#verification-heading')
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
        $domainChanged = $website->domain !== $request->validated('domain');

        if ($domainChanged) {
            $website->verification_token = bin2hex(random_bytes(32));
        }

        $website->update($this->attributes($request->validated()) + [
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'moderation_note' => null,
            ...($domainChanged ? [
                'verification_status' => 'pending',
                'verified_at' => null,
                'last_verification_attempt_at' => null,
                'verification_override_by' => null,
                'verification_override_at' => null,
                'verification_override_reason' => null,
            ] : []),
        ]);

        return redirect()->to(route('publisher.websites.edit', $website).'#verification-heading')
            ->with('status', 'Website changes submitted for review.');
    }

    public function verify(Request $request, Website $website, WebsiteOwnershipVerifier $verifier): RedirectResponse
    {
        $this->authorize('update', $website);

        if ($website->verification_status === 'verified') {
            return redirect()->route('publisher.websites.edit', $website)
                ->with('status', 'This website is already verified.');
        }

        $available = DB::transaction(function () use ($website) {
            $lockedWebsite = Website::query()->lockForUpdate()->findOrFail($website->id);

            if (
                $lockedWebsite->last_verification_attempt_at
                && $lockedWebsite->last_verification_attempt_at->isAfter(now()->subSeconds(60))
            ) {
                return false;
            }

            $lockedWebsite->update(['last_verification_attempt_at' => now()]);

            return true;
        });

        if (! $available) {
            return redirect()->route('publisher.websites.edit', $website)
                ->with('error', 'Please wait at least 60 seconds before trying website verification again.');
        }

        $website->refresh();

        try {
            $verifier->verify($website);
        } catch (WebsiteVerificationException $exception) {
            $website->update([
                'verification_status' => 'failed',
                'verified_at' => null,
            ]);

            return redirect()->route('publisher.websites.edit', $website)
                ->with('error', $exception->getMessage());
        }

        $website->update([
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        return redirect()->route('publisher.websites.edit', $website)
            ->with('status', 'Website ownership verified successfully.');
    }

    public function updateAvailability(Request $request, Website $website): RedirectResponse
    {
        $this->authorize('update', $website);

        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        if ($website->status !== 'approved') {
            throw ValidationException::withMessages([
                'is_active' => 'Only approved listings can be paused or made available.',
            ]);
        }

        if (
            $request->boolean('is_active')
            && $website->verification_status !== 'verified'
            && $website->verification_override_at === null
        ) {
            throw ValidationException::withMessages([
                'is_active' => 'Verify website ownership or ask an administrator to record an explicit override before making this listing available.',
            ]);
        }

        $website->update(['is_active' => $request->boolean('is_active')]);

        return redirect()->route('publisher.websites.index')
            ->with('status', $website->is_active ? 'Your listing is available in the marketplace.' : 'Your listing has been paused.');
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
