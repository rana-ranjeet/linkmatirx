@extends('layouts.site')

@section('title', 'Your websites')

@section('content')
    <section class="page-hero">
        <div class="container publisher-heading">
            <div><p class="eyebrow">Publisher workspace</p><h1>Your websites</h1><p>Manage listings and follow their review status.</p></div>
            <a class="btn btn-primary" href="{{ route('publisher.websites.create') }}">Add a website <span aria-hidden="true">+</span></a>
        </div>
    </section>
    <section class="section-space">
        <div class="container">
            @if ($websites->isNotEmpty())
                <div class="table-responsive listing-table-wrap">
                    <table class="table listing-table align-middle">
                        <thead><tr><th>Website</th><th>Category</th><th>Services</th><th>Listing status</th><th>Ownership verification</th><th>Review note</th><th>Actions</th></tr></thead>
                        <tbody>
                        @foreach ($websites as $website)
                            @php($marketplaceEligible = $website->verification_status === 'verified' || $website->verification_override_at !== null)
                            <tr>
                                <td><strong>{{ $website->domain }}</strong><small>{{ $website->country_code ?? 'Country not set' }} · {{ $website->language_code ?? 'Language not set' }}</small></td>
                                <td>{{ $website->category?->name ?? 'Not selected' }}</td>
                                <td>
                                    @if ($website->guest_post_price_cents !== null)<small>Guest post: {{ $website->currency }} {{ number_format($website->guest_post_price_cents / 100, 2) }}</small>@endif
                                    @if ($website->link_insertion_price_cents !== null)<small>Link insertion: {{ $website->currency }} {{ number_format($website->link_insertion_price_cents / 100, 2) }}</small>@endif
                                </td>
                                <td>
                                    <span class="status-badge {{ $website->status === 'approved' && $website->is_active && $marketplaceEligible ? 'status-approved' : ($website->status === 'rejected' ? 'status-rejected' : 'status-pending') }}">
                                        {{ $website->status === 'approved' ? ($marketplaceEligible ? ($website->is_active ? 'Available' : 'Paused') : ($website->is_active ? 'Needs verification' : 'Paused · needs verification')) : ucfirst($website->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-{{ $website->verification_status }}">{{ ucfirst($website->verification_status) }}</span>
                                    <small>HTML Meta Tag</small>
                                    @if ($website->verified_at)<small>{{ $website->verified_at->format('Y-m-d H:i T') }}</small>@endif
                                </td>
                                <td>{{ $website->moderation_note ?: '—' }}</td>
                                <td>
                                    <div class="publisher-listing-actions">
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('publisher.websites.edit', $website) }}">Edit</a>
                                        <a class="btn btn-sm {{ $website->verification_status === 'verified' ? 'btn-outline-secondary' : 'btn-primary' }}" href="{{ route('publisher.websites.edit', $website) }}#verification-heading">
                                            {{ $website->verification_status === 'verified' ? 'Verification details' : 'Verify website' }}
                                        </a>
                                        @if ($website->status === 'approved')
                                            <form method="POST" action="{{ route('publisher.websites.availability', $website) }}">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="is_active" value="{{ $website->is_active ? '0' : '1' }}">
                                                <button class="btn btn-sm {{ $website->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}" type="submit">{{ $website->is_active ? 'Pause' : 'Make available' }}</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="pagination-wrap">{{ $websites->links() }}</div>
            @else
                <div class="empty-state">
                    <span class="empty-icon">↗</span>
                    <h2>Your first website listing starts here</h2>
                    <p>Add your publication details and prices. New listings are reviewed before they appear in the public marketplace.</p>
                    <a class="btn btn-primary" href="{{ route('publisher.websites.create') }}">Add a website</a>
                </div>
            @endif
        </div>
    </section>
@endsection
