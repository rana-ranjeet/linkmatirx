@extends('layouts.site')

@section('title', 'Admin dashboard')

@section('content')
    <section class="page-hero">
        <div class="container">
            <p class="eyebrow">LINKVIO administration</p>
            <h1>Admin dashboard</h1>
            <p>Marketplace operations at a glance. Sales and payment totals will appear when order and payment workflows are implemented.</p>
        </div>
    </section>
    <section class="section-space">
        <div class="container">
            <div class="admin-overview-grid">
                <article class="dashboard-card">
                    <span class="dashboard-icon">U</span>
                    <h2>Accounts</h2>
                    <p class="admin-stat-value">{{ number_format($userCounts['total']) }}</p>
                    <p>{{ number_format($userCounts['publishers']) }} publishers · {{ number_format($userCounts['advertisers']) }} sellers · {{ number_format($userCounts['suspended']) }} suspended</p>
                    <a class="text-link" href="{{ route('admin.users.index') }}">Manage users and roles <span aria-hidden="true">&rarr;</span></a>
                </article>
                <article class="dashboard-card">
                    <span class="dashboard-icon">W</span>
                    <h2>Website inventory</h2>
                    <p class="admin-stat-value">{{ number_format($websiteCounts['total']) }}</p>
                    <p>{{ number_format($websiteCounts['approved']) }} approved · {{ number_format($websiteCounts['pending']) }} awaiting review · {{ number_format($websiteCounts['rejected']) }} rejected</p>
                    <a class="text-link" href="{{ route('admin.websites.index') }}">Review publisher listings <span aria-hidden="true">&rarr;</span></a>
                </article>
                <article class="dashboard-card">
                    <span class="dashboard-icon">S</span>
                    <h2>Gross sales and commission</h2>
                    <p class="admin-stat-value">Not available</p>
                    <p>Order and payment processing is not active; no financial totals are estimated or fabricated.</p>
                </article>
                <article class="dashboard-card">
                    <span class="dashboard-icon">P</span>
                    <h2>Publisher amounts and overdue orders</h2>
                    <p class="admin-stat-value">Not available</p>
                    <p>Publisher payable amounts and order deadlines depend on the upcoming order and payout modules.</p>
                </article>
            </div>

            <div class="admin-dashboard-columns">
                <section class="admin-dashboard-panel">
                    <div class="publisher-heading">
                        <div><p class="eyebrow">Needs attention</p><h2>Pending website reviews</h2></div>
                        <a class="text-link" href="{{ route('admin.websites.index', ['status' => 'pending']) }}">View all</a>
                    </div>
                    @forelse ($pendingWebsites as $website)
                        <div class="admin-dashboard-row">
                            <div><strong>{{ $website->domain }}</strong><small>{{ $website->publisher->name }} · Submitted {{ $website->created_at->toDateString() }}</small></div>
                            <span class="status-badge status-pending">Pending</span>
                        </div>
                    @empty
                        <p class="admin-empty-note">No websites are waiting for review.</p>
                    @endforelse
                </section>

                <section class="admin-dashboard-panel">
                    <div class="publisher-heading">
                        <div><p class="eyebrow">Recently joined</p><h2>Latest accounts</h2></div>
                        <a class="text-link" href="{{ route('admin.users.index') }}">View all</a>
                    </div>
                    @forelse ($recentUsers as $user)
                        <div class="admin-dashboard-row">
                            <div><strong>{{ $user->name }}</strong><small>{{ $user->email }}</small></div>
                            <span class="status-badge {{ $user->is_suspended ? 'status-rejected' : 'status-approved' }}">{{ $user->is_suspended ? 'Suspended' : str($user->role->value)->headline() }}</span>
                        </div>
                    @empty
                        <p class="admin-empty-note">No accounts found.</p>
                    @endforelse
                </section>
            </div>

            <section class="admin-modules">
                <p class="eyebrow">Administration</p>
                <h2>Modules</h2>
                <div class="admin-module-grid">
                    <a class="admin-module-card" href="{{ route('admin.users.index') }}"><strong>Users and roles</strong><span>Search, change permitted roles, suspend/reactivate, and audit access changes.</span><small>Available</small></a>
                    <a class="admin-module-card" href="{{ route('admin.websites.index') }}"><strong>Publisher review and inventory</strong><span>Review listings and manage the approved marketplace inventory.</span><small>Available</small></a>
                    <a class="admin-module-card" href="{{ route('admin.blog.index') }}"><strong>CMS and SEO</strong><span>Manage blog content, publication status, metadata, and sitemap entries.</span><small>Available</small></a>
                    <a class="admin-module-card" href="{{ route('admin.articles.index') }}"><strong>Writer articles</strong><span>Assign content briefs, review submitted work, and approve articles for publication.</span><small>Available</small></a>
                    <a class="admin-module-card" href="{{ route('admin.writers.index') }}"><strong>Content team and roles</strong><span>Create SEO manager and writer accounts and manage content-team role assignments.</span><small>Super administrator</small></a>
                    <div class="admin-module-card is-planned"><strong>Orders and verification</strong><span>Order lifecycle, delivery checks, deadlines, and disputes.</span><small>Planned phase</small></div>
                    <div class="admin-module-card is-planned"><strong>Payments, commissions, and payouts</strong><span>Gateway transactions, refunds, financial ledger, and publisher settlements.</span><small>Planned phase · provider/business rules required</small></div>
                    <div class="admin-module-card is-planned"><strong>Discounts, support, and reports</strong><span>Agency discounts, tickets, operational reports, and exports.</span><small>Planned phase</small></div>
                    <a class="admin-module-card" href="{{ route('admin.audit.index') }}"><strong>Security and audit</strong><span>Review role and account status changes with actor, reason, and before/after values.</span><small>Available · access audit</small></a>
                </div>
            </section>
        </div>
    </section>
@endsection
