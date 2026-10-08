@extends('layouts.site')

@section('title', $title)

@section('content')
    <section class="page-hero">
        <div class="container">
            <p class="eyebrow">{{ $eyebrow }}</p>
            <h1>{{ $title }}</h1>
            <p>{{ $intro }}</p>
        </div>
    </section>
    <section class="section-space">
        <div class="container">
            @if ($cards)
                <div class="steps-grid info-card-grid">
                    @foreach ($cards as $card)
                        <article class="step-card"><span class="step-number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><h2>{{ $card['title'] }}</h2><p>{{ $card['body'] }}</p></article>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <span class="empty-icon">✦</span>
                    <h2>We are getting this ready</h2>
                    <p>This page will be updated as LINKVIO’s marketplace launches. For policies, obtain a review appropriate to your operating region before production use.</p>
                </div>
            @endif
            <div class="info-page-cta"><a class="btn btn-primary" href="{{ route($route) }}">{{ $cta }} <span aria-hidden="true">&rarr;</span></a></div>
        </div>
    </section>
@endsection
