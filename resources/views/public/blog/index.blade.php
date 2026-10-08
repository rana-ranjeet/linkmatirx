@extends('layouts.site')

@section('title', 'SEO and publishing guides')
@section('meta_description', 'Practical, original guides on publisher partnerships, backlinks, guest posts, and SEO from the LINKVIO editorial team.')
@section('canonical_url', route('blog.index'))
@section('og_title', 'SEO and publishing guides')
@section('og_description', 'Practical, original guides on publisher partnerships, backlinks, guest posts, and SEO from LINKVIO.')

@section('content')
    <section class="page-hero">
        <div class="container">
            <p class="eyebrow">LINKVIO journal</p>
            <h1>Ideas for better publisher partnerships</h1>
            <p>Original guides on publisher partnerships, backlinks, and sustainable SEO.</p>
        </div>
    </section>
    <section class="section-space">
        <div class="container">
            @if ($posts->isNotEmpty())
                <div class="blog-post-grid">
                    @foreach ($posts as $post)
                        <article class="blog-post-card">
                            <p class="blog-post-date"><time datetime="{{ $post->published_at->toAtomString() }}">{{ $post->published_at->toFormattedDateString() }}</time></p>
                            <h2><a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a></h2>
                            <p>{{ $post->excerpt }}</p>
                            <a class="text-link" href="{{ route('blog.show', $post->slug) }}">Read article <span aria-hidden="true">→</span></a>
                        </article>
                    @endforeach
                </div>
                <div class="pagination-wrap">{{ $posts->links() }}</div>
            @else
                <div class="empty-state">
                    <span class="empty-icon">✦</span>
                    <h2>New guides are on the way</h2>
                    <p>Check back soon for original guidance on building sustainable publisher partnerships.</p>
                    <a class="btn btn-primary" href="{{ route('marketplace.index') }}">Explore the marketplace</a>
                </div>
            @endif
        </div>
    </section>
@endsection
