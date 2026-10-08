@extends('layouts.site')

@section('title', 'Manage blog posts')

@section('content')
    <section class="page-hero">
        <div class="container">
            <p class="eyebrow">Administration</p>
            <h1>SEO blog</h1>
            <p>Write search-friendly articles and manage what appears in the public journal.</p>
        </div>
    </section>
    <section class="section-space">
        <div class="container">
            <div class="blog-admin-toolbar">
                <form method="GET" action="{{ route('admin.blog.index') }}" class="admin-filters">
                    <div><label class="form-label" for="blog-q">Search title or slug</label><input class="form-control" id="blog-q" name="q" value="{{ $filters['q'] ?? '' }}"></div>
                    <div>
                        <label class="form-label" for="blog-status">Status</label>
                        <select class="form-select" id="blog-status" name="status">
                            <option value="">All statuses</option>
                            @foreach (['draft', 'published'] as $status)
                                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-primary" type="submit">Filter</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.blog.index') }}">Clear</a>
                </form>
                <a class="btn btn-primary blog-create-button" href="{{ route('admin.blog.create') }}">Write an article</a>
            </div>

            @if ($posts->isNotEmpty())
                <div class="blog-admin-list">
                    @foreach ($posts as $post)
                        <article class="admin-website-card blog-admin-card">
                            <div class="admin-website-head">
                                <div>
                                    <span class="status-badge status-{{ $post->status === 'published' ? 'approved' : 'pending' }}">{{ ucfirst($post->status) }}</span>
                                    <h2>{{ $post->title }}</h2>
                                    <p>/blog/{{ $post->slug }} · Updated {{ $post->updated_at->toFormattedDateString() }}</p>
                                </div>
                                @if ($post->status === 'published' && $post->published_at?->isPast())
                                    <a class="text-link" href="{{ route('blog.show', $post->slug) }}">View article <span aria-hidden="true">↗</span></a>
                                @endif
                            </div>
                            <p class="blog-admin-excerpt">{{ $post->excerpt }}</p>
                            <div class="blog-admin-actions">
                                <a class="btn btn-outline-primary" href="{{ route('admin.blog.edit', $post) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.blog.destroy', $post) }}" onsubmit="return confirm('Permanently delete this article?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="pagination-wrap">{{ $posts->links() }}</div>
            @else
                <div class="empty-state">
                    <span class="empty-icon">✎</span>
                    <h2>No blog posts yet</h2>
                    <p>Start with an original, helpful article for advertisers or publishers.</p>
                    <a class="btn btn-primary" href="{{ route('admin.blog.create') }}">Write your first article</a>
                </div>
            @endif
        </div>
    </section>
@endsection
