@extends('layouts.site')

@section('title', 'Website approvals')

@section('content')
    <section class="page-hero">
        <div class="container"><p class="eyebrow">Administration</p><h1>Website approvals</h1><p>Review publisher submissions before they become visible in the marketplace.</p></div>
    </section>
    <section class="section-space">
        <div class="container">
            <form method="GET" action="{{ route('admin.websites.index') }}" class="admin-filters">
                <div><label class="form-label" for="admin-q">Search domain</label><input class="form-control" id="admin-q" name="q" value="{{ $filters['q'] ?? '' }}"></div>
                <div><label class="form-label" for="admin-status">Status</label><select class="form-select" id="admin-status" name="status"><option value="">All statuses</option>@foreach (['pending', 'approved', 'rejected'] as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
                <button class="btn btn-primary" type="submit">Filter</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.websites.index') }}">Clear</a>
            </form>
            @if ($websites->isNotEmpty())
                <div class="admin-website-list">
                    @foreach ($websites as $website)
                        <article class="admin-website-card">
                            <div class="admin-website-head">
                                <div><span class="status-badge status-{{ $website->status }}">{{ ucfirst($website->status) }}</span><h2>{{ $website->domain }}</h2><p>Publisher: {{ $website->publisher->name }} · {{ $website->category?->name ?? 'No niche selected' }}</p></div>
                                <a class="text-link" href="https://{{ $website->domain }}" target="_blank" rel="noopener noreferrer">Visit website <span aria-hidden="true">↗</span></a>
                            </div>
                            <p class="admin-website-description">{{ $website->description ?: 'No description submitted.' }}</p>
                            <div class="admin-detail-row">
                                <span>Audience: {{ $website->country_code ?? 'Not set' }} / {{ $website->language_code ?? 'Not set' }}</span>
                                <span>Guest post: {{ $website->guest_post_price_cents !== null ? $website->currency.' '.number_format($website->guest_post_price_cents / 100, 2) : 'Not offered' }}</span>
                                <span>Link insertion: {{ $website->link_insertion_price_cents !== null ? $website->currency.' '.number_format($website->link_insertion_price_cents / 100, 2) : 'Not offered' }}</span>
                            </div>
                            @if ($website->moderation_note)<p class="review-note"><strong>Previous note:</strong> {{ $website->moderation_note }}</p>@endif
                            @if ($website->status === 'pending')
                                <div class="review-actions">
                                    <form method="POST" action="{{ route('admin.websites.review', $website) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="approved"><button class="btn btn-success" type="submit">Approve listing</button></form>
                                    <form method="POST" action="{{ route('admin.websites.review', $website) }}" class="reject-form">@csrf @method('PATCH')<input type="hidden" name="status" value="rejected"><label class="visually-hidden" for="reason-{{ $website->id }}">Rejection reason</label><input class="form-control" id="reason-{{ $website->id }}" name="moderation_note" required maxlength="1000" placeholder="Reason for rejection"><button class="btn btn-outline-danger" type="submit">Reject</button></form>
                                </div>
                            @else
                                <p class="review-meta">Reviewed {{ $website->reviewed_at?->toDateString() ?? '—' }} by {{ $website->reviewer?->name ?? 'Unknown reviewer' }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
                <div class="pagination-wrap">{{ $websites->links() }}</div>
            @else
                <div class="empty-state"><span class="empty-icon">✓</span><h2>No website submissions found</h2><p>Try another status filter or search term.</p></div>
            @endif
        </div>
    </section>
@endsection
