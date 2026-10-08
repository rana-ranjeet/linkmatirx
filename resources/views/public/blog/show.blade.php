@extends('layouts.site')

@section('title', $post->meta_title ?: $post->title)
@section('meta_description', $post->meta_description ?: $post->excerpt)
@section('canonical_url', route('blog.show', $post->slug))
@section('og_type', 'article')
@section('og_title', $post->meta_title ?: $post->title)
@section('og_description', $post->meta_description ?: $post->excerpt)

@php
    $articleSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $post->meta_title ?: $post->title,
        'description' => $post->meta_description ?: $post->excerpt,
        'datePublished' => $post->published_at->toAtomString(),
        'dateModified' => $post->updated_at->toAtomString(),
        'mainEntityOfPage' => route('blog.show', $post->slug),
        'author' => [
            '@type' => 'Organization',
            'name' => $post->author?->name ?? config('app.name'),
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => config('app.name'),
        ],
    ];
@endphp

@push('head')
    <meta property="article:published_time" content="{{ $post->published_at->toAtomString() }}">
    <meta property="article:modified_time" content="{{ $post->updated_at->toAtomString() }}">
    <script type="application/ld+json">{!! json_encode($articleSchema, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
    <article class="blog-article">
        <header class="page-hero">
            <div class="container blog-article-header">
                <p class="eyebrow"><a href="{{ route('blog.index') }}">LINKVIO journal</a></p>
                <h1>{{ $post->title }}</h1>
                <p class="blog-article-excerpt">{{ $post->excerpt }}</p>
                <p class="blog-post-date">
                    <time datetime="{{ $post->published_at->toAtomString() }}">{{ $post->published_at->toFormattedDateString() }}</time>
                    · {{ $post->author?->name ?? config('app.name') }}
                </p>
            </div>
        </header>
        <div class="container section-space">
            <div class="blog-article-content">{!! nl2br(e($post->content)) !!}</div>
            <a class="back-link" href="{{ route('blog.index') }}">← Back to all articles</a>
        </div>
    </article>
@endsection
