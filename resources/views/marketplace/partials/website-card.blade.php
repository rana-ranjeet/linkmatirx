<article class="website-card">
    <div class="website-card-top">
        <div class="domain-avatar" aria-hidden="true">{{ strtoupper(substr($website->domain, 0, 1)) }}</div>
        <span class="verified-badge"><span aria-hidden="true">✓</span> Approved</span>
    </div>
    <h3><a href="{{ route('marketplace.show', $website) }}">{{ $website->domain }}</a></h3>
    <p class="website-category">{{ $website->category?->name ?? 'Uncategorized' }}</p>
    <p class="website-description">{{ Str::limit($website->description ?: 'Publisher listing with service details available on request.', 100) }}</p>
    <div class="metric-row">
        <div><span>DR</span><strong>{{ $website->domain_rating ?? 'Not available' }}</strong></div>
        <div><span>DA</span><strong>{{ $website->domain_authority ?? 'Not available' }}</strong></div>
        <div><span>Traffic</span><strong>{{ $website->organic_traffic !== null ? number_format($website->organic_traffic) : 'Not available' }}</strong></div>
    </div>
    <div class="website-card-bottom">
        <span>{{ $website->guest_post_price_cents !== null ? $website->currency.' '.number_format($website->guest_post_price_cents / 100, 2).' guest post' : 'Pricing on details' }}</span>
        <a href="{{ route('marketplace.show', $website) }}" aria-label="View {{ $website->domain }}">View <span aria-hidden="true">&rarr;</span></a>
    </div>
    @auth
        @if (auth()->user()->role->value === 'advertiser')
            @if ($favoriteRemove ?? false)
                <form method="POST" action="{{ route('advertiser.favorites.destroy', $website) }}" class="favorite-action">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-secondary" type="submit">Remove favorite</button>
                </form>
            @else
                <form method="POST" action="{{ route('advertiser.favorites.store', $website) }}" class="favorite-action">
                    @csrf
                    <button class="btn btn-sm btn-outline-primary" type="submit"><span aria-hidden="true">☆</span> Save favorite</button>
                </form>
            @endif
        @endif
    @endauth
</article>
