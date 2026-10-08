<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $defaultMetaDescription = 'Discover vetted publisher websites and build better partnerships with LINKVIO.';
        $seoTitle = html_entity_decode(trim($__env->yieldContent('title', 'SEO publishing marketplace')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $seoDescription = html_entity_decode(trim($__env->yieldContent('meta_description', $defaultMetaDescription)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $ogTitle = html_entity_decode(trim($__env->yieldContent('og_title', $seoTitle)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $ogDescription = html_entity_decode(trim($__env->yieldContent('og_description', $seoDescription)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $canonicalUrl = trim($__env->yieldContent('canonical_url', url()->current()));
    @endphp
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="robots" content="{{ trim($__env->yieldContent('meta_robots', 'index,follow')) }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:type" content="{{ trim($__env->yieldContent('og_type', 'website')) }}">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <title>{{ $seoTitle }} | {{ config('app.name', 'LINKVIO') }}</title>
    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="site-header">
        <div class="container site-nav">
            <a class="brand" href="{{ route('home') }}"><span class="brand-mark">L</span> LINKVIO</a>
            <button class="nav-toggle" type="button" aria-label="Toggle navigation" aria-expanded="false" data-nav-toggle>
                <span></span><span></span><span></span>
            </button>
            <nav class="site-nav-links" data-nav-menu aria-label="Main navigation">
                <div class="nav-primary">
                    <div class="nav-dropdown">
                        <button class="nav-dropdown-toggle" type="button" aria-expanded="false" aria-controls="solutions-menu" data-dropdown-toggle>Solutions <span class="nav-caret" aria-hidden="true"></span></button>
                        <div class="nav-dropdown-menu" id="solutions-menu">
                            <a href="{{ route('marketplace.index') }}" @class(['is-current' => request()->routeIs('marketplace.*')])>Publisher marketplace</a>
                            <a href="{{ route('register', ['role' => 'advertiser']) }}">For advertisers</a>
                            <a href="{{ route('register', ['role' => 'publisher']) }}">For publishers</a>
                        </div>
                    </div>
                    <div class="nav-dropdown">
                        <button class="nav-dropdown-toggle" type="button" aria-expanded="false" aria-controls="features-menu" data-dropdown-toggle>Features <span class="nav-caret" aria-hidden="true"></span></button>
                        <div class="nav-dropdown-menu" id="features-menu">
                            <a href="{{ route('marketplace.index') }}">Website discovery</a>
                            <a href="{{ route('how-it-works') }}">Publisher partnerships</a>
                            @guest<a href="{{ route('register') }}">Campaign workspace</a>@else<a href="{{ route('dashboard') }}">Campaign workspace</a>@endguest
                        </div>
                    </div>
                    <a class="nav-content-link" href="{{ route('marketplace.index') }}">Content marketplace <span class="nav-new">Explore</span></a>
                    <div class="nav-dropdown nav-resources">
                        <button class="nav-dropdown-toggle" type="button" aria-expanded="false" aria-controls="resources-menu" data-dropdown-toggle>Resources <span class="nav-caret" aria-hidden="true"></span></button>
                        <div class="nav-dropdown-menu nav-dropdown-menu-right" id="resources-menu">
                            <a href="{{ route('blog.index') }}" @class(['is-current' => request()->routeIs('blog.*')])>LINKVIO journal</a>
                            <a href="{{ route('contact') }}" @class(['is-current' => request()->routeIs('contact')])>Get in touch</a>
                            <a href="{{ route('about') }}" @class(['is-current' => request()->routeIs('about')])>Our story</a>
                            <a href="{{ route('faqs') }}" @class(['is-current' => request()->routeIs('faqs')])>Help center</a>
                            <a href="{{ route('how-it-works') }}" @class(['is-current' => request()->routeIs('how-it-works')])>How it works</a>
                            <a href="{{ route('register', ['role' => 'publisher']) }}">Become a publisher</a>
                        </div>
                    </div>
                    <a href="{{ route('pricing') }}">Pricing</a>
                </div>
                <div class="nav-actions">
                    @auth
                        <a class="nav-login" href="{{ route('dashboard') }}">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}" class="nav-logout">
                            @csrf
                            <button type="submit">Log out</button>
                        </form>
                    @else
                        <a class="nav-login" href="{{ route('login') }}">Log in</a>
                        <a class="btn nav-cta" href="{{ route('register') }}">Get started <span aria-hidden="true">&rarr;</span></a>
                        <a class="btn nav-cta-secondary" href="{{ route('contact') }}">Talk to us</a>
                    @endauth
                </div>
            </nav>
        </div>
        <button class="nav-drawer-backdrop" type="button" aria-label="Close navigation menu" data-drawer-close hidden></button>
    </header>

    @if (session('status'))
        <div class="container mt-3"><div class="alert alert-success mb-0">{{ session('status') }}</div></div>
    @endif

    <main>
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container footer-inner">
            <a class="brand brand-footer" href="{{ route('home') }}"><span class="brand-mark">L</span> LINKVIO</a>
            <p>Thoughtful connections between brands and publishers.</p>
            <nav class="footer-links" aria-label="Footer navigation">
                <a href="{{ route('about') }}">About</a>
                <a href="{{ route('contact') }}">Contact</a>
                <a href="{{ route('blog.index') }}">Blog</a>
                <a href="{{ route('privacy') }}">Privacy</a>
                <a href="{{ route('terms') }}">Terms</a>
                <a href="{{ route('refund-policy') }}">Refunds</a>
                <a href="{{ route('cookie-policy') }}">Cookies</a>
            </nav>
            <span>&copy; {{ now()->year }} LINKVIO</span>
        </div>
    </footer>
</body>
</html>
