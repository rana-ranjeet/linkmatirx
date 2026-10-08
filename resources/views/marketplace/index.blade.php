@extends('layouts.site')

@section('title', 'Publisher marketplace')

@section('content')
    <section class="page-hero">
        <div class="container">
            <p class="eyebrow">Publisher directory</p>
            <h1>Find your next publishing partner</h1>
            <p>Compare approved publisher websites. Unavailable third-party metrics are clearly marked.</p>
        </div>
    </section>
    <section class="section-space">
        <div class="container marketplace-layout">
            <aside class="filter-panel" id="pricing">
                <h2>Filter websites</h2>
                <form method="GET" action="{{ route('marketplace.index') }}" class="form-stack">
                    <div><label class="form-label" for="filter-q">Website or topic</label><input class="form-control" id="filter-q" name="q" value="{{ $filters['q'] ?? '' }}"></div>
                    <div>
                        <label class="form-label" for="filter-category">Niche</label>
                        <select class="form-select" id="filter-category" name="category">
                            <option value="">All niches</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div><label class="form-label" for="filter-country">Country code</label><input class="form-control" id="filter-country" name="country" maxlength="2" value="{{ $filters['country'] ?? '' }}" placeholder="US"></div>
                    <div><label class="form-label" for="filter-language">Language</label><input class="form-control" id="filter-language" name="language" maxlength="10" value="{{ $filters['language'] ?? '' }}" placeholder="en"></div>
                    <div class="filter-pair">
                        <div><label class="form-label" for="filter-dr">Minimum DR</label><input class="form-control" id="filter-dr" name="min_dr" type="number" min="0" max="100" value="{{ $filters['min_dr'] ?? '' }}"></div>
                        <div><label class="form-label" for="filter-da">Minimum DA</label><input class="form-control" id="filter-da" name="min_da" type="number" min="0" max="100" value="{{ $filters['min_da'] ?? '' }}"></div>
                    </div>
                    <div><label class="form-label" for="filter-traffic">Minimum traffic</label><input class="form-control" id="filter-traffic" name="min_traffic" type="number" min="0" value="{{ $filters['min_traffic'] ?? '' }}"></div>
                    <div><label class="form-label" for="filter-service">Service</label>
                        <select class="form-select" id="filter-service" name="service">
                            <option value="">Any service</option>
                            <option value="guest_post" @selected(($filters['service'] ?? '') === 'guest_post')>Guest post</option>
                            <option value="link_insertion" @selected(($filters['service'] ?? '') === 'link_insertion')>Link insertion</option>
                        </select>
                    </div>
                    <div><label class="form-label" for="filter-currency">Currency</label><input class="form-control" id="filter-currency" name="currency" maxlength="3" value="{{ $filters['currency'] ?? '' }}" placeholder="USD"></div>
                    <div><label class="form-label" for="filter-price">Maximum price (whole units)</label><input class="form-control" id="filter-price" name="max_price" type="number" min="0" step="1" value="{{ $filters['max_price'] ?? '' }}"></div>
                    <button class="btn btn-primary w-100" type="submit">Apply filters</button>
                    <a class="btn btn-outline-secondary w-100" href="{{ route('marketplace.index') }}">Clear filters</a>
                </form>
            </aside>
            <div class="marketplace-results">
                <div class="results-heading"><p>{{ $websites->total() }} approved {{ Str::plural('website', $websites->total()) }}</p></div>
                @if ($websites->isNotEmpty())
                    <div class="website-grid">
                        @foreach ($websites as $website)
                            @include('marketplace.partials.website-card', ['website' => $website])
                        @endforeach
                    </div>
                    <div class="pagination-wrap">{{ $websites->links() }}</div>
                @else
                    <div class="empty-state">
                        <span class="empty-icon">⌕</span>
                        <h2>No websites match these filters</h2>
                        <p>Try widening your criteria or clear the filters to see all approved listings.</p>
                        <a class="btn btn-outline-primary" href="{{ route('marketplace.index') }}">Clear filters</a>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
