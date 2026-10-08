@extends('layouts.site')

@section('title', 'A better way to find publishers')
@section('meta_description', 'Discover vetted publisher websites for guest posts and link placements on LINKVIO.')

@section('content')
    <section class="hero-section">
        <div class="container hero-grid">
            <div class="hero-copy">
                <p class="eyebrow">The publisher marketplace built for clarity</p>
                <h1>Find the right sites.<br><span>Build real reach.</span></h1>
                <p class="hero-intro">Discover publisher websites, compare transparent details, and manage your outreach in one considered workspace.</p>
                <form class="hero-search" method="GET" action="{{ route('marketplace.index') }}">
                    <label class="visually-hidden" for="hero-search">Search by website or topic</label>
                    <input id="hero-search" name="q" type="search" placeholder="Search websites or topics">
                    <button class="btn btn-primary" type="submit">Explore marketplace <span aria-hidden="true">&rarr;</span></button>
                </form>
                <div class="hero-proof">
                    <span><strong>{{ number_format($approvedWebsiteCount) }}</strong> approved websites</span>
                    <span><strong>{{ number_format($publisherCount) }}</strong> publishers</span>
                </div>
            </div>
            <div class="hero-art" aria-label="Marketplace overview">
                <div class="art-orbit orbit-one"></div>
                <div class="art-orbit orbit-two"></div>
                <div class="art-card art-card-main">
                    <span class="art-kicker">Publisher match</span>
                    <strong>Curated for your next campaign</strong>
                    <div class="art-line"><i></i><i></i><i></i></div>
                    <span class="art-status"><b></b> Verified listing</span>
                </div>
                <div class="art-card art-card-small"><span class="art-icon">↗</span><span><strong>Clear details</strong><small>Know what you are choosing</small></span></div>
                <div class="art-sparkle sparkle-one">✦</div>
                <div class="art-sparkle sparkle-two">✦</div>
            </div>
        </div>
    </section>

    <section class="trust-strip">
        <div class="container trust-inner">
            <span>Built for teams who value</span>
            <strong>Transparency</strong><strong>Quality</strong><strong>Better partnerships</strong>
        </div>
    </section>

    <section class="section-space" id="marketplace">
        <div class="container">
            <div class="section-heading">
                <div><p class="eyebrow">A marketplace with context</p><h2>Publisher sites, clearly presented</h2></div>
                <a class="text-link" href="{{ route('marketplace.index') }}">Browse all websites <span aria-hidden="true">&rarr;</span></a>
            </div>
            @if ($featuredWebsites->isNotEmpty())
                <div class="website-grid">
                    @foreach ($featuredWebsites as $website)
                        @include('marketplace.partials.website-card', ['website' => $website])
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <span class="empty-icon">↗</span>
                    <h3>New publisher sites are on the way</h3>
                    <p>Approved websites will appear here with their verified listing details. No metrics are shown until a provider supplies them.</p>
                    <a class="btn btn-outline-primary" href="{{ route('register', ['role' => 'publisher']) }}">Become a publisher</a>
                </div>
            @endif
        </div>
    </section>

    <section class="section-space section-tint" id="how-it-works">
        <div class="container">
            <div class="section-heading section-heading-center">
                <div><p class="eyebrow">Straightforward by design</p><h2>From discovery to delivery</h2><p>Keep every step of a publishing partnership easy to follow.</p></div>
            </div>
            <div class="steps-grid">
                <article class="step-card"><span class="step-number">01</span><h3>Explore</h3><p>Search approved websites and compare their available services and details.</p></article>
                <article class="step-card"><span class="step-number">02</span><h3>Connect</h3><p>Choose a publisher and save websites you want to consider.</p></article>
                <article class="step-card"><span class="step-number">03</span><h3>Collaborate</h3><p>Publisher listing and advertiser workspaces keep your marketplace activity organized.</p></article>
            </div>
            <div class="info-page-cta"><a class="text-link" href="{{ route('how-it-works') }}">See how LINKVIO works <span aria-hidden="true">&rarr;</span></a></div>
        </div>
    </section>

    <section class="section-space">
        <div class="container value-grid">
            <article class="value-panel value-advertiser">
                <p class="eyebrow">For advertisers</p>
                <h2>Make confident choices for your next campaign.</h2>
                <p>Browse publisher listings with clear pricing and the details you need to evaluate fit.</p>
                <a class="btn btn-primary" href="{{ route('marketplace.index') }}">Explore websites</a>
            </article>
            <article class="value-panel value-publisher">
                <p class="eyebrow">For publishers</p>
                <h2>Bring your publication to the right partners.</h2>
                <p>Build a listing, set your service pricing, and submit it for review through your publisher dashboard.</p>
                <a class="btn btn-outline-primary" href="{{ route('register', ['role' => 'publisher']) }}">List your website</a>
            </article>
        </div>
    </section>

    <section class="cta-section">
        <div class="container cta-inner">
            <div><p class="eyebrow">Your next partnership starts here</p><h2>Good work begins with a good match.</h2></div>
            <a class="btn btn-light" href="{{ route('register') }}">Create your free account <span aria-hidden="true">&rarr;</span></a>
        </div>
    </section>
@endsection
