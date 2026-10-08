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
                        <thead><tr><th>Website</th><th>Category</th><th>Services</th><th>Status</th><th>Review note</th><th></th></tr></thead>
                        <tbody>
                        @foreach ($websites as $website)
                            <tr>
                                <td><strong>{{ $website->domain }}</strong><small>{{ $website->country_code ?? 'Country not set' }} · {{ $website->language_code ?? 'Language not set' }}</small></td>
                                <td>{{ $website->category?->name ?? 'Not selected' }}</td>
                                <td>
                                    @if ($website->guest_post_price_cents !== null)<small>Guest post: {{ $website->currency }} {{ number_format($website->guest_post_price_cents / 100, 2) }}</small>@endif
                                    @if ($website->link_insertion_price_cents !== null)<small>Link insertion: {{ $website->currency }} {{ number_format($website->link_insertion_price_cents / 100, 2) }}</small>@endif
                                </td>
                                <td><span class="status-badge status-{{ $website->status }}">{{ ucfirst($website->status) }}</span></td>
                                <td>{{ $website->moderation_note ?: '—' }}</td>
                                <td><a class="btn btn-sm btn-outline-primary" href="{{ route('publisher.websites.edit', $website) }}">Edit</a></td>
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
