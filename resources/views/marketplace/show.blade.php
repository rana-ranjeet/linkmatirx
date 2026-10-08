@extends('layouts.site')

@section('title', $website->domain)
@section('meta_description', Str::limit($website->description ?: 'Publisher website listing for '.$website->domain, 155))

@section('content')
    <section class="page-hero">
        <div class="container">
            <a class="back-link" href="{{ route('marketplace.index') }}">&larr; Back to marketplace</a>
            <p class="eyebrow mt-3">Approved publisher website</p>
            <h1>{{ $website->domain }}</h1>
            <p>{{ $website->category?->name ?? 'Category not specified' }}@if($website->country_code) · {{ $website->country_code }}@endif</p>
        </div>
    </section>
    <section class="section-space">
        <div class="container detail-grid">
            <article class="detail-main">
                <div class="detail-panel">
                    <h2>About this website</h2>
                    <p>{{ $website->description ?: 'The publisher has not added a description yet.' }}</p>
                    <div class="metric-row metric-row-large">
                        <div><span>Domain Rating</span><strong>{{ $website->domain_rating ?? 'Not available' }}</strong></div>
                        <div><span>Domain Authority</span><strong>{{ $website->domain_authority ?? 'Not available' }}</strong></div>
                        <div><span>Organic traffic</span><strong>{{ $website->organic_traffic !== null ? number_format($website->organic_traffic) : 'Not available' }}</strong></div>
                        <div><span>Referring domains</span><strong>{{ $website->referring_domains !== null ? number_format($website->referring_domains) : 'Not available' }}</strong></div>
                    </div>
                    @if ($website->metrics_provider && $website->metrics_updated_at)
                        <p class="metric-source">Metrics from {{ $website->metrics_provider }} · updated {{ $website->metrics_updated_at->toDateString() }}</p>
                    @else
                        <p class="metric-source">No provider metrics have been submitted for this listing.</p>
                    @endif
                </div>
            </article>
            <aside class="detail-panel price-panel">
                <span class="verified-badge"><span aria-hidden="true">✓</span> Approved listing</span>
                <h2>Available services</h2>
                @if ($website->guest_post_price_cents !== null)
                    <p class="price-line"><span>Guest post</span><strong>{{ $website->currency }} {{ number_format($website->guest_post_price_cents / 100, 2) }}</strong></p>
                @endif
                @if ($website->link_insertion_price_cents !== null)
                    <p class="price-line"><span>Link insertion</span><strong>{{ $website->currency }} {{ number_format($website->link_insertion_price_cents / 100, 2) }}</strong></p>
                @endif
                @if ($website->guest_post_price_cents === null && $website->link_insertion_price_cents === null)
                    <p>Pricing has not been added yet.</p>
                @endif
                <a class="btn btn-primary w-100" href="{{ route('register') }}">Create an account to get started</a>
                <p class="detail-note">Orders and checkout will be introduced in a later marketplace phase.</p>
            </aside>
        </div>
    </section>
@endsection
