@extends('layouts.site')

@section('title', 'Your dashboard')

@section('content')
    <section class="page-hero">
        <div class="container">
            <p class="eyebrow">{{ str($role->value)->headline() }} workspace</p>
            <h1>Your dashboard</h1>
            <p>Welcome to LINKVIO. Your account is ready to set up.</p>
        </div>
    </section>
    <section class="section-space">
        <div class="container dashboard-grid">
            <article class="dashboard-card">
                <span class="dashboard-icon">01</span>
                <h2>Complete your profile</h2>
                <p>Add the information that helps your marketplace partners work with you.</p>
                <a class="text-link" href="{{ route('profile.edit') }}">Manage profile <span aria-hidden="true">&rarr;</span></a>
            </article>
            @if ($role->value === 'publisher')
                <article class="dashboard-card">
                    <span class="dashboard-icon">02</span>
                    <h2>Your website listings</h2>
                    <p>Manage submissions and see your current review status.</p>
                    <a class="text-link" href="{{ route('publisher.websites.index') }}">Manage websites <span aria-hidden="true">&rarr;</span></a>
                </article>
            @elseif ($role->value === 'advertiser')
                <article class="dashboard-card">
                    <span class="dashboard-icon">02</span>
                    <h2>Explore publisher sites</h2>
                    <p>Browse approved website listings and review the available metrics and pricing.</p>
                    <a class="text-link" href="{{ route('marketplace.index') }}">Open marketplace <span aria-hidden="true">&rarr;</span></a>
                </article>
                <article class="dashboard-card">
                    <span class="dashboard-icon">03</span>
                    <h2>Saved websites</h2>
                    <p>{{ $favoriteCount }} {{ Str::plural('website', $favoriteCount) }} saved to your favorites.</p>
                    <a class="text-link" href="{{ route('advertiser.favorites.index') }}">View favorites <span aria-hidden="true">&rarr;</span></a>
                </article>
            @else
                <article class="dashboard-card">
                    <span class="dashboard-icon">02</span>
                    <h2>Admin operations</h2>
                    <p>Review marketplace activity, manage accounts, and open the administration workspace.</p>
                    <a class="text-link" href="{{ route('admin.dashboard') }}">Open admin dashboard <span aria-hidden="true">&rarr;</span></a>
                </article>
                <article class="dashboard-card">
                    <span class="dashboard-icon">03</span>
                    <h2>SEO blog</h2>
                    <p>Write, optimize, and publish articles to the public LINKVIO journal.</p>
                    <a class="text-link" href="{{ route('admin.blog.index') }}">Manage blog posts <span aria-hidden="true">&rarr;</span></a>
                </article>
            @endif
            <article class="dashboard-card dashboard-card-wide">
                <span class="dashboard-icon">✓</span>
                <h2>Email verified</h2>
                <p>Your account can access its role-specific workspace.</p>
            </article>
        </div>
    </section>
@endsection
