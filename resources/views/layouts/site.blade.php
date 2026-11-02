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
            <button class="nav-toggle" type="button" aria-label="Toggle navigation" aria-expanded="false" aria-controls="site-navigation" data-nav-toggle>
                <span></span><span></span><span></span>
            </button>
            <nav class="site-nav-links" id="site-navigation" data-nav-menu aria-label="Main navigation">
                <div class="nav-primary">
                    <a @class(['is-current' => request()->routeIs('home')]) href="{{ route('home') }}">Home</a>
                    <a @class(['is-current' => request()->routeIs('marketplace.*')]) href="{{ route('marketplace.index') }}">Marketplace</a>
                    <div class="nav-dropdown">
                        <button class="nav-dropdown-toggle" type="button" aria-expanded="false" aria-controls="solutions-menu" data-dropdown-toggle>Solutions <span class="nav-caret" aria-hidden="true"></span></button>
                        <div class="nav-dropdown-menu" id="solutions-menu">
                            <a href="{{ route('register', ['role' => 'advertiser']) }}">For advertisers</a>
                            <a href="{{ route('register', ['role' => 'publisher']) }}">For publishers</a>
                            <a @class(['is-current' => request()->routeIs('how-it-works')]) href="{{ route('how-it-works') }}">How it works</a>
                            @guest<a href="{{ route('register') }}">Campaign workspace</a>@else<a href="{{ route('dashboard') }}">Campaign workspace</a>@endguest
                        </div>
                    </div>
                    <div class="nav-dropdown nav-resources">
                        <button class="nav-dropdown-toggle" type="button" aria-expanded="false" aria-controls="resources-menu" data-dropdown-toggle>Resources <span class="nav-caret" aria-hidden="true"></span></button>
                        <div class="nav-dropdown-menu nav-dropdown-menu-right" id="resources-menu">
                            <a @class(['is-current' => request()->routeIs('blog.*')]) href="{{ route('blog.index') }}">Blog</a>
                            <a @class(['is-current' => request()->routeIs('about')]) href="{{ route('about') }}">About LINKVIO</a>
                            <a @class(['is-current' => request()->routeIs('faqs')]) href="{{ route('faqs') }}">Help center</a>
                            <a @class(['is-current' => request()->routeIs('contact')]) href="{{ route('contact') }}">Contact</a>
                        </div>
                    </div>
                    <a @class(['is-current' => request()->routeIs('pricing')]) href="{{ route('pricing') }}">Pricing</a>
                </div>
                <div class="nav-actions">
                    @auth
                        <a class="nav-login" href="{{ route('dashboard') }}">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}" class="nav-logout">
                            @csrf
                            <button type="submit">Log out</button>
                        </form>
                    @else
                        <a class="nav-login" href="{{ route('login') }}">Sign in</a>
                        <a class="btn nav-cta" href="{{ route('register') }}">Sign up <span aria-hidden="true">&rarr;</span></a>
                    @endauth
                </div>
            </nav>
        </div>
    </header>

    @if (session('status'))
        <div class="container mt-3"><div class="alert alert-success mb-0">{{ session('status') }}</div></div>
    @endif
    @if (session('error'))
        <div class="container mt-3"><div class="alert alert-danger mb-0" role="alert">{{ session('error') }}</div></div>
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
    @stack('scripts')
</body>
</html>
