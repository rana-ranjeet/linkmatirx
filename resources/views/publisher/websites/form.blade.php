@extends('layouts.site')

@php($isEditing = $website->exists)

@section('title', $isEditing ? 'Edit website' : 'Add a website')

@section('content')
    <section class="page-hero">
        <div class="container">
            <a class="back-link" href="{{ route('publisher.websites.index') }}">&larr; Your websites</a>
            <p class="eyebrow mt-3">Publisher workspace</p>
            <h1>{{ $isEditing ? 'Edit website listing' : 'Add a website' }}</h1>
            <p>Provide accurate listing details. SEO metrics are managed separately and will never be invented.</p>
        </div>
    </section>
    <section class="section-space">
        <div class="container form-page">
            @if ($isEditing && $website->status === 'approved')
                <div class="alert alert-info">Submitting changes to an approved listing will return it to review before it is public again.</div>
            @endif
            <form method="POST" action="{{ $formAction }}" class="form-panel form-stack">
                @csrf
                @if ($formMethod === 'PUT') @method('PUT') @endif
                <div>
                    <label class="form-label" for="domain">Website domain</label>
                    <input class="form-control" id="domain" name="domain" type="text" value="{{ old('domain', $website->domain) }}" placeholder="example.com" required autocomplete="url">
                    <small class="form-hint">Enter the domain only, without https:// or a page path.</small>
                    @error('domain')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label" for="category_id">Niche</label>
                    <select class="form-select" id="category_id" name="category_id">
                        <option value="">Select a category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('category_id', $website->category_id) === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="5" maxlength="10000" placeholder="Describe the publication and the topics it covers.">{{ old('description', $website->description) }}</textarea>
                    @error('description')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-row">
                    <div><label class="form-label" for="country_code">Audience country</label><input class="form-control" id="country_code" name="country_code" type="text" maxlength="2" value="{{ old('country_code', $website->country_code) }}" placeholder="US">@error('country_code')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div><label class="form-label" for="language_code">Language code</label><input class="form-control" id="language_code" name="language_code" type="text" maxlength="10" value="{{ old('language_code', $website->language_code) }}" placeholder="en">@error('language_code')<p class="field-error">{{ $message }}</p>@enderror</div>
                </div>
                <div class="form-row">
                    <div>
                        <label class="form-label" for="guest_post_price">Guest post price (USD)</label>
                        <input class="form-control" id="guest_post_price" name="guest_post_price" type="number" min="0.01" max="1000000" step="0.01" value="{{ old('guest_post_price', $website->guest_post_price_cents !== null ? number_format($website->guest_post_price_cents / 100, 2, '.', '') : '') }}">
                        @error('guest_post_price')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label" for="link_insertion_price">Link insertion price (USD)</label>
                        <input class="form-control" id="link_insertion_price" name="link_insertion_price" type="number" min="0.01" max="1000000" step="0.01" value="{{ old('link_insertion_price', $website->link_insertion_price_cents !== null ? number_format($website->link_insertion_price_cents / 100, 2, '.', '') : '') }}">
                        @error('link_insertion_price')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                </div>
                @if ($errors->has('guest_post_price') || $errors->has('link_insertion_price'))
                    <p class="form-hint">Enter at least one service price. You may leave the other service empty.</p>
                @endif
                <div class="form-actions">
                    <a class="btn btn-outline-secondary" href="{{ route('publisher.websites.index') }}">Cancel</a>
                    <button class="btn btn-primary" type="submit">{{ $isEditing ? 'Save and submit for review' : 'Submit website for review' }}</button>
                </div>
            </form>
            @if ($isEditing)
                @php($verificationStatus = $website->verification_status ?? 'pending')
                @php($metaTag = '<meta name="yourmarketplace-verification" content="'.$website->verification_token.'">')
                <section class="profile-section verification-panel" aria-labelledby="verification-heading">
                    <p class="eyebrow">Website ownership</p>
                    <h2 id="verification-heading">Ownership verification</h2>
                    <div class="verification-summary">
                        <div><strong>Website URL</strong><a href="https://{{ $website->domain }}/" target="_blank" rel="noopener noreferrer">https://{{ $website->domain }}/</a></div>
                        <div><strong>Status</strong><span class="status-badge status-{{ $verificationStatus }}">{{ ucfirst($verificationStatus) }}</span></div>
                        <div><strong>Method</strong><span>HTML Meta Tag</span></div>
                        @if ($website->verified_at)<div><strong>Verified at</strong><span>{{ $website->verified_at->format('Y-m-d H:i T') }}</span></div>@endif
                    </div>
                    <div class="verification-token">
                        <label class="form-label" for="verification-token">Unique verification token</label>
                        <div class="verification-copy-row">
                            <input class="form-control" id="verification-token" type="text" value="{{ $website->verification_token }}" readonly>
                            <button class="btn btn-outline-secondary" type="button" data-copy-value="{{ $website->verification_token }}" aria-label="Copy verification token">Copy token</button>
                        </div>
                        <label class="form-label mt-3" for="verification-meta">Meta tag to add to your homepage</label>
                        <div class="verification-copy-row">
                            <input class="form-control" id="verification-meta" type="text" value="{{ $metaTag }}" readonly>
                            <button class="btn btn-outline-secondary" type="button" data-copy-value="{{ $metaTag }}" aria-label="Copy verification meta tag">Copy meta tag</button>
                        </div>
                        <p class="form-hint" data-copy-feedback role="status" aria-live="polite"></p>
                    </div>
                    <h3 class="verification-instructions-title">How to verify</h3>
                    <ol class="verification-instructions">
                        <li>Copy the generated meta tag above.</li>
                        <li>Open the homepage source or template for <strong>{{ $website->domain }}</strong>.</li>
                        <li>Place the tag inside the homepage <code>&lt;head&gt;</code> section. In WordPress, edit your theme/header or use a trusted header-code plugin; in Laravel, add it to the homepage Blade layout; for custom HTML, edit the homepage file.</li>
                        <li>Publish the change and confirm the tag is present in the publicly accessible homepage HTML.</li>
                        <li>Return here and select Verify website.</li>
                    </ol>
                    @if ($verificationStatus !== 'verified')
                        <form method="POST" action="{{ route('publisher.websites.verify', $website) }}" data-verification-form>
                            @csrf
                            <button class="btn btn-primary" type="submit" data-verification-submit>
                                <span data-verification-label>Verify website</span>
                                <span class="verification-spinner" data-verification-spinner hidden aria-hidden="true"></span>
                            </button>
                            <span class="visually-hidden" data-verification-progress role="status" aria-live="polite"></span>
                            <p class="form-hint">You can retry after a failed check; verification attempts are limited to one per minute.</p>
                        </form>
                    @else
                        <p class="verification-confirmation">Ownership was verified. Listing approval is a separate review.</p>
                    @endif
                </section>
            @endif
        </div>
    </section>
@endsection

@push('scripts')
    @if ($isEditing)
        <script>
            document.querySelectorAll('[data-copy-value]').forEach((button) => {
                button.addEventListener('click', async () => {
                    const feedback = document.querySelector('[data-copy-feedback]');

                    try {
                        await navigator.clipboard.writeText(button.dataset.copyValue);
                        feedback.textContent = 'Copied to clipboard.';
                    } catch {
                        feedback.textContent = 'Clipboard access failed. Select and copy the value manually.';
                    }
                });
            });

            document.querySelector('[data-verification-form]')?.addEventListener('submit', () => {
                const button = document.querySelector('[data-verification-submit]');
                button.disabled = true;
                document.querySelector('[data-verification-label]').textContent = 'Checking website…';
                document.querySelector('[data-verification-spinner]').hidden = false;
                document.querySelector('[data-verification-progress]').textContent = 'Website verification is in progress.';
            });
        </script>
    @endif
@endpush
