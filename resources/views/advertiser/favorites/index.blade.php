@extends('layouts.site')

@section('title', 'Favorite websites')

@section('content')
    <section class="page-hero">
        <div class="container"><p class="eyebrow">Advertiser workspace</p><h1>Your favorite websites</h1><p>Keep approved publisher listings together while you compare your options.</p></div>
    </section>
    <section class="section-space">
        <div class="container">
            @if ($websites->isNotEmpty())
                <div class="website-grid">
                    @foreach ($websites as $website)
                        @include('marketplace.partials.website-card', ['website' => $website, 'favoriteRemove' => true])
                    @endforeach
                </div>
                <div class="pagination-wrap">{{ $websites->links() }}</div>
            @else
                <div class="empty-state">
                    <span class="empty-icon">☆</span>
                    <h2>No favorites saved yet</h2>
                    <p>Save approved websites from the marketplace to find them here.</p>
                    <a class="btn btn-primary" href="{{ route('marketplace.index') }}">Browse marketplace</a>
                </div>
            @endif
        </div>
    </section>
@endsection
