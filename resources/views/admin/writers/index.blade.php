@extends('layouts.site')

@section('title', 'Content team')

@section('content')
    <section class="page-hero">
        <div class="container"><a class="back-link" href="{{ route('admin.dashboard') }}">&larr; Admin dashboard</a><p class="eyebrow mt-3">Super administrator</p><h1>Content team and roles</h1><p>Create SEO manager or writer accounts. New users receive email verification and use their own login credentials.</p></div>
    </section>
    <section class="section-space">
        <div class="container writer-management-layout">
            <form method="POST" action="{{ route('admin.writers.store') }}" class="form-panel form-stack">
                @csrf
                <h2>Create content team account</h2>
                <div><label class="form-label" for="name">Name</label><input class="form-control" id="name" name="name" value="{{ old('name') }}" maxlength="255" required>@error('name')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div><label class="form-label" for="email">Email</label><input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" required>@error('email')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div><label class="form-label" for="role">Role</label><select class="form-select" id="role" name="role" required><option value="content_writer" @selected(old('role') === 'content_writer')>Content Writer</option><option value="seo_manager" @selected(old('role') === 'seo_manager')>SEO Manager</option></select>@error('role')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div><label class="form-label" for="password">Initial password</label><input class="form-control" id="password" name="password" type="password" autocomplete="new-password" required>@error('password')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div><label class="form-label" for="password_confirmation">Confirm password</label><input class="form-control" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required></div>
                <button class="btn btn-primary align-self-start" type="submit">Create account</button>
            </form>

            <section class="admin-dashboard-panel">
                <h2>Content team accounts</h2>
                <form method="GET" action="{{ route('admin.writers.index') }}" class="admin-filters writer-filters">
                    <div><label class="form-label" for="writer-q">Search</label><input class="form-control" id="writer-q" name="q" value="{{ $filters['q'] ?? '' }}"></div>
                    <div><label class="form-label" for="writer-role">Role</label><select class="form-select" id="writer-role" name="role"><option value="">All</option><option value="seo_manager" @selected(($filters['role'] ?? '') === 'seo_manager')>SEO Manager</option><option value="content_writer" @selected(($filters['role'] ?? '') === 'content_writer')>Content Writer</option></select></div>
                    <button class="btn btn-primary" type="submit">Search</button>
                </form>
                @forelse ($users as $user)
                    <article class="writer-account-row">
                        <div class="publisher-heading"><div><strong>{{ $user->name }}</strong><small>{{ $user->email }} · {{ $user->assigned_articles_count }} assigned articles</small></div><span class="status-badge {{ $user->hasVerifiedEmail() ? 'status-approved' : 'status-pending' }}">{{ $user->hasVerifiedEmail() ? 'Verified' : 'Verification required' }}</span></div>
                        <form method="POST" action="{{ route('admin.writers.role', $user) }}" class="writer-role-form">
                            @csrf @method('PATCH')
                            <label class="visually-hidden" for="role-{{ $user->id }}">Account role</label><select class="form-select form-select-sm" id="role-{{ $user->id }}" name="role"><option value="content_writer" @selected($user->role === \App\Enums\UserRole::ContentWriter)>Content Writer</option><option value="seo_manager" @selected($user->role === \App\Enums\UserRole::SeoManager)>SEO Manager</option><option value="advertiser">Advertiser</option><option value="publisher">Publisher</option></select>
                            <input class="form-control form-control-sm" name="reason" maxlength="1000" placeholder="Reason for role change" required aria-label="Reason for role change">
                            <button class="btn btn-sm btn-outline-primary" type="submit">Update role</button>
                        </form>
                    </article>
                @empty
                    <p class="admin-empty-note">No content team accounts found.</p>
                @endforelse
                <div class="pagination-wrap">{{ $users->links() }}</div>
            </section>
        </div>
    </section>
@endsection
