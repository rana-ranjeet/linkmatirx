@extends('layouts.site')

@section('title', 'Writer dashboard')

@section('content')
    <section class="page-hero">
        <div class="container"><p class="eyebrow">Content writer workspace</p><h1>Your assigned articles</h1><p>Only articles assigned to your account are shown here. Submit completed work for SEO review.</p></div>
    </section>
    <section class="section-space">
        <div class="container">
            <div class="listing-table-wrap table-responsive">
                <table class="table listing-table align-middle">
                    <thead><tr><th>Article</th><th>Category</th><th>Status</th><th>Updated</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($articles as $article)
                            <tr>
                                <td><strong>{{ $article->title }}</strong><small>{{ $article->target_keyword ?: 'No target keyword' }}</small></td>
                                <td>{{ $article->category?->name ?? 'Not specified' }}</td>
                                <td><span class="status-badge {{ $article->status === 'changes_requested' ? 'status-rejected' : (in_array($article->status, ['approved', 'published']) ? 'status-approved' : 'status-pending') }}">{{ str($article->status)->replace('_', ' ')->headline() }}</span></td>
                                <td>{{ $article->updated_at->toDateString() }}</td>
                                <td>@if(in_array($article->status, ['draft', 'changes_requested'], true))<a class="btn btn-sm btn-outline-primary" href="{{ route('admin.articles.edit', $article) }}">Edit / continue</a>@else<span class="form-hint">Awaiting SEO workflow</span>@endif</td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="empty-state"><h2>No assigned articles yet</h2><p>Your SEO manager will assign briefs to your account.</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $articles->links() }}</div>
        </div>
    </section>
@endsection
