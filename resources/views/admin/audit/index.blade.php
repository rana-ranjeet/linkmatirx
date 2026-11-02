@extends('layouts.site')

@section('title', 'Security and audit log')

@section('content')
    <section class="page-hero">
        <div class="container">
            <a class="back-link" href="{{ route('admin.dashboard') }}">&larr; Admin dashboard</a>
            <p class="eyebrow mt-3">Security and audit</p>
            <h1>Administrator audit log</h1>
            <p>Review administrator-recorded account changes and website ownership-verification overrides, including who made each change and why.</p>
        </div>
    </section>
    <section class="section-space">
        <div class="container">
            <form method="GET" action="{{ route('admin.audit.index') }}" class="admin-filters">
                <div><label class="form-label" for="audit-q">Search account or reason</label><input class="form-control" id="audit-q" name="q" value="{{ $filters['q'] ?? '' }}"></div>
                <div><label class="form-label" for="audit-action">Action</label><select class="form-select" id="audit-action" name="action"><option value="">All actions</option>@foreach (['change_role', 'suspend', 'reactivate', 'website_approval_override'] as $action)<option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ str($action)->replace('_', ' ')->headline() }}</option>@endforeach</select></div>
                <button class="btn btn-primary" type="submit">Filter</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.audit.index') }}">Clear</a>
            </form>

            <div class="listing-table-wrap table-responsive">
                <table class="table listing-table align-middle">
                    <thead><tr><th>When</th><th>Administrator</th><th>Account / seller</th><th>Action</th><th>Reason</th><th>Change</th></tr></thead>
                    <tbody>
                        @forelse ($logs as $log)
                            @php($oldValues = json_decode($log->old_values, true))
                            @php($newValues = json_decode($log->new_values, true))
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($log->created_at)->format('Y-m-d H:i') }}</td>
                                <td><strong>{{ $log->actor_name ?? 'Deleted administrator' }}</strong><small>{{ $log->actor_email ?? 'Account no longer exists' }}</small></td>
                                <td><strong>{{ $log->target_name ?? 'Deleted account' }}</strong><small>{{ $log->target_email ?? 'Account no longer exists' }}</small></td>
                                <td>{{ str($log->action)->replace('_', ' ')->headline() }}</td>
                                <td>{{ $log->reason }}</td>
                                <td><small>
                                    @if ($log->action === 'website_approval_override')
                                        Website #{{ $newValues['website_id'] ?? '—' }}: {{ $newValues['domain'] ?? 'unknown domain' }} · verification {{ ucfirst($newValues['verification_status'] ?? 'unknown') }}
                                    @else
                                        {{ isset($oldValues['role']) ? str($oldValues['role'])->headline() : '—' }}{{ !empty($oldValues['is_suspended']) ? ' · suspended' : '' }} &rarr; {{ isset($newValues['role']) ? str($newValues['role'])->headline() : '—' }}{{ !empty($newValues['is_suspended']) ? ' · suspended' : '' }}
                                    @endif
                                </small></td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><div class="empty-state"><h2>No audited actions recorded</h2><p>Account changes and website ownership-verification overrides will appear here after an administrator makes a change.</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $logs->links() }}</div>
        </div>
    </section>
@endsection
