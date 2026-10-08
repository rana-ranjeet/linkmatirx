<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Account' }} | {{ config('app.name', 'LINKVIO') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="auth-page">
        <main class="auth-shell {{ request()->routeIs('login') ? 'auth-shell-login' : '' }}">
            <a class="brand brand-auth" href="{{ route('home') }}"><span class="brand-mark">L</span> LINKVIO</a>
            @if (request()->routeIs('login'))
                <div class="auth-pitch">
                    <p class="eyebrow">One marketplace. Two ways to grow.</p>
                    <h2>Make your next<br><span>connection count.</span></h2>
                    <p>Discover vetted publishers, compare real listing details, and manage your work from one secure account.</p>
                    <div class="auth-pitch-points">
                        <span><b>01</b> Browse approved publisher websites</span>
                        <span><b>02</b> Save sites for your next campaign</span>
                        <span><b>03</b> Manage your publisher listings</span>
                    </div>
                    <div class="auth-pitch-footer">Built for advertisers and publishers</div>
                </div>
            @endif
            <section class="auth-card">
                {{ $slot }}
            </section>
            <p class="auth-back"><a href="{{ route('home') }}">Back to LINKVIO</a></p>
        </main>
    </body>
</html>
