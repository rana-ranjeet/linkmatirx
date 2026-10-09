@extends('layouts.site')

@section('title', 'SEO manager dashboard')

@section('content')
    <section class="page-hero">
        <div class="container"><p class="eyebrow">SEO manager workspace</p><h1>Content operations</h1><p>Create briefs, assign writers, review submissions, and manage article SEO metadata.</p></div>
    </section>
    <section class="section-space">
        <div class="container">
            <div class="admin-overview-grid">
                @foreach (['draft' => 'Drafts', 'in_review' => 'Awaiting review', 'changes_requested' => 'Changes requested', 'approved' => 'Awaiting publication'] as $status => $label)
                    <a class="dashboard-card seo-status-card" href="{{ route('admin.articles.index', ['status' => $status]) }}"><span class="dashboard-icon">A</span><h2>{{ $label }}</h2><p class="admin-stat-value">{{ number_format($counts[$status]) }}</p><span class="text-link">View articles &rarr;</span></a>
                @endforeach
            </div>
            <section class="admin-modules mt-4">
                <div class="publisher-heading"><div><p class="eyebrow">Team</p><h2>Content writers</h2></div><a class="text-link" href="{{ route('admin.articles.index') }}">Manage articles</a></div>
                @forelse ($writers as $writer)
                    <div class="admin-dashboard-row"><div><strong>{{ $writer->name }}</strong><small>{{ $writer->email }}</small></div><span class="status-label">{{ $writer->assigned_articles_count }} assigned</span></div>
                @empty
                    <p class="admin-empty-note">No writers have been created yet.</p>
                @endforelse
            </section>
            <div class="admin-module-grid mt-3">
                <a class="admin-module-card" href="{{ route('admin.articles.index') }}"><strong>Article workflow</strong><span>Manage briefs, writer assignments, SEO review, and approvals.</span><small>Open articles</small></a>
                <a class="admin-module-card" href="{{ route('admin.articles.create') }}"><strong>Create a content brief</strong><span>Set the keyword, category, internal links, metadata, and assigned writer.</span><small>Available</small></a>
            </div>
        </div>
    </section>
@endsection
