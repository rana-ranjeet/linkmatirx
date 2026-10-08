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
        </div>
    </section>
@endsection
