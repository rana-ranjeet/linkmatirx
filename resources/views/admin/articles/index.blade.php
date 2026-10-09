@extends('layouts.site')

@section('title', 'Content articles')

@section('content')
    <section class="page-hero">
        <div class="container publisher-heading">
            <div><p class="eyebrow">Content workflow</p><h1>Articles</h1><p>Assign briefs, review submissions, and track approval and publication.</p></div>
            @if (auth()->user()->role->canManageArticles())
                <a class="btn btn-primary" href="{{ route('admin.articles.create') }}">Create article brief</a>
            @endif
        </div>
    </section>
    <section class="section-space">
        <div class="container">
            <form method="GET" action="{{ route('admin.articles.index') }}" class="admin-filters">
                <div><label class="form-label" for="article-q">Search title or keyword</label><input class="form-control" id="article-q" name="q" value="{{ $filters['q'] ?? '' }}"></div>
                <div><label class="form-label" for="article-status">Status</label><select class="form-select" id="article-status" name="status"><option value="">All statuses</option>@foreach (['draft', 'in_review', 'changes_requested', 'approved', 'published'] as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ str($status)->replace('_', ' ')->headline() }}</option>@endforeach</select></div>
                <button class="btn btn-primary" type="submit">Filter</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.articles.index') }}">Clear</a>
            </form>

            <div class="listing-table-wrap table-responsive">
                <table class="table listing-table align-middle">
                    <thead><tr><th>Article</th><th>Keyword / category</th><th>Writer</th><th>Status</th><th>Updated</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($articles as $article)
                            <tr>
                                <td><strong>{{ $article->title }}</strong><small>{{ $article->slug }}</small></td>
                                <td>{{ $article->target_keyword ?: 'No target keyword' }}<small>{{ $article->category?->name ?: 'No category' }}</small></td>
                                <td>{{ $article->assignedWriter?->name ?: 'Unassigned' }}</td>
                                <td><span class="status-badge {{ $article->status === 'approved' || $article->status === 'published' ? 'status-approved' : ($article->status === 'changes_requested' ? 'status-rejected' : 'status-pending') }}">{{ str($article->status)->replace('_', ' ')->headline() }}</span></td>
                                <td>{{ $article->updated_at->toDateString() }}</td>
                                <td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.articles.edit', $article) }}">{{ auth()->user()->role === \App\Enums\UserRole::ContentWriter ? 'Edit' : 'Review / edit' }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><div class="empty-state"><h2>No articles found</h2><p>@if(auth()->user()->role === \App\Enums\UserRole::ContentWriter)No briefs have been assigned to you yet.@elseCreate a brief and assign it to a writer to begin.@endif</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $articles->links() }}</div>
        </div>
    </section>
@endsection
