@extends('layouts.site')

@section('title', 'Publisher dashboard')

@section('content')
    <section class="page-hero">
        <div class="container publisher-heading">
            <div>
                <p class="eyebrow">Publisher workspace</p>
                <h1>Welcome, {{ auth()->user()->name }}</h1>
                <p>Manage your publisher profile, listings, and support requests.</p>
            </div>
            <a class="btn btn-primary" href="{{ route('publisher.websites.create') }}">Submit a website <span aria-hidden="true">+</span></a>
        </div>
    </section>
    <section class="section-space">
        <div class="container">
            <div class="admin-overview-grid publisher-overview-grid">
                <article class="dashboard-card">
                    <span class="dashboard-icon">W</span>
                    <h2>Your websites</h2>
                    <p class="admin-stat-value">{{ number_format($websiteStats['total']) }}</p>
                    <p>{{ $websiteStats['available'] }} available · {{ $websiteStats['paused'] }} paused · {{ $websiteStats['pending'] }} under review</p>
                    <a class="text-link" href="{{ route('publisher.websites.index') }}">Manage listings <span aria-hidden="true">&rarr;</span></a>
                </article>
                <article class="dashboard-card">
                    <span class="dashboard-icon">P</span>
                    <h2>Publisher profile</h2>
                    <p>{{ $profile?->business_name ?: 'Add your business details to complete your publisher profile.' }}</p>
                    <a class="text-link" href="{{ route('profile.edit') }}">Complete profile <span aria-hidden="true">&rarr;</span></a>
                </article>
                <article class="dashboard-card">
                    <span class="dashboard-icon">?</span>
                    <h2>Support requests</h2>
                    <p>{{ $openSupportCount }} open {{ \Illuminate\Support\Str::plural('request', $openSupportCount) }}.</p>
                    <a class="text-link" href="{{ route('publisher.support.index') }}">Contact support <span aria-hidden="true">&rarr;</span></a>
                </article>
                <article class="dashboard-card">
                    <span class="dashboard-icon">E</span>
                    <h2>Orders and earnings</h2>
                    <p>Order acceptance, delivery, earnings, and payouts will appear here when those workflows are enabled.</p>
                    <span class="status-label">Not active yet</span>
                </article>
            </div>

            <section class="admin-dashboard-panel mt-4">
                <div class="publisher-heading">
                    <div><p class="eyebrow">Listing overview</p><h2>Recent websites</h2></div>
                    <a class="text-link" href="{{ route('publisher.websites.index') }}">View all websites</a>
                </div>
                @forelse ($recentWebsites as $website)
                    <div class="admin-dashboard-row">
                        <div>
                            <strong>{{ $website->domain }}</strong>
                            <small>
                                @if ($website->status === 'approved')
                                    {{ $website->is_active ? 'Available in the marketplace' : 'Paused by you' }}
                                @elseif ($website->status === 'rejected')
                                    Rejected · {{ $website->moderation_note ?: 'Contact support for details.' }}
                                @else
                                    Pending admin review
                                @endif
                            </small>
                        </div>
                        <span class="status-badge {{ $website->status === 'approved' && $website->is_active ? 'status-approved' : ($website->status === 'rejected' ? 'status-rejected' : 'status-pending') }}">
                            {{ $website->status === 'approved' ? ($website->is_active ? 'Available' : 'Paused') : str($website->status)->headline() }}
                        </span>
                    </div>
                @empty
                    <p class="admin-empty-note">No websites submitted yet. Add your first listing to begin admin review.</p>
                @endforelse
            </section>

            <section class="admin-modules mt-4">
                <p class="eyebrow">Publisher tools</p>
                <h2>Manage your publisher account</h2>
                <div class="admin-module-grid">
                    <a class="admin-module-card" href="{{ route('profile.edit') }}"><strong>Publisher profile</strong><span>Edit your business details and publisher information.</span><small>Available</small></a>
                    <a class="admin-module-card" href="{{ route('publisher.websites.index') }}"><strong>Website inventory</strong><span>Submit sites, edit pricing, view admin review notes, and pause approved listings.</span><small>Available</small></a>
                    <a class="admin-module-card" href="{{ route('publisher.support.index') }}"><strong>Support</strong><span>Raise a ticket and see your submitted support requests.</span><small>Available</small></a>
                    <div class="admin-module-card is-planned"><strong>Orders and content files</strong><span>Accept or reject assigned work, view briefs, download files, and respond to revisions.</span><small>Planned phase</small></div>
                    <div class="admin-module-card is-planned"><strong>Publication delivery</strong><span>Submit live URLs and track publication verification.</span><small>Planned phase</small></div>
                    <div class="admin-module-card is-planned"><strong>Earnings and payouts</strong><span>View the earnings ledger, request payouts, and track settlement status.</span><small>Planned phase · payment rules required</small></div>
                </div>
            </section>
        </div>
    </section>
@endsection
