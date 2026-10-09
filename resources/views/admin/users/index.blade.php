@extends('layouts.site')

@section('title', 'Users and roles')

@section('content')
    <section class="page-hero">
        <div class="container">
            <a class="back-link" href="{{ route('admin.dashboard') }}">&larr; Admin dashboard</a>
            <p class="eyebrow mt-3">Administration</p>
            <h1>Users and roles</h1>
            <p>Search accounts, manage advertiser/publisher access, and record the reason for each change. Administrator accounts are protected from this screen.</p>
        </div>
    </section>
    <section class="section-space">
        <div class="container">
            <form method="GET" action="{{ route('admin.users.index') }}" class="admin-filters">
                <div><label class="form-label" for="users-q">Search name or email</label><input class="form-control" id="users-q" name="q" value="{{ $filters['q'] ?? '' }}"></div>
                <div><label class="form-label" for="users-role">Role</label><select class="form-select" id="users-role" name="role"><option value="">All roles</option>@foreach (\App\Enums\UserRole::cases() as $role)<option value="{{ $role->value }}" @selected(($filters['role'] ?? '') === $role->value)>{{ str($role->value)->headline() }}</option>@endforeach</select></div>
                <div><label class="form-label" for="users-status">Account status</label><select class="form-select" id="users-status" name="status"><option value="">All statuses</option><option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option><option value="suspended" @selected(($filters['status'] ?? '') === 'suspended')>Suspended</option></select></div>
                <button class="btn btn-primary" type="submit">Search</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.users.index') }}">Clear</a>
            </form>

            <div class="listing-table-wrap table-responsive">
                <table class="table listing-table align-middle">
                    <thead><tr><th>Account</th><th>Role</th><th>Status</th><th>Joined</th><th>Access actions</th></tr></thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td><strong>{{ $user->name }}</strong><small>{{ $user->email }}</small></td>
                                <td>{{ str($user->role->value)->headline() }}</td>
                                <td><span class="status-badge {{ $user->is_suspended ? 'status-rejected' : 'status-approved' }}">{{ $user->is_suspended ? 'Suspended' : 'Active' }}</span></td>
                                <td>{{ $user->created_at->toDateString() }}</td>
                                <td>
                                    @if ($user->role === \App\Enums\UserRole::Admin)
                                        <small>Admin access is managed through the controlled operator command.</small>
                                    @elseif ($user->is(auth()->user()))
                                        <small>Your own account cannot be changed here.</small>
                                    @else
                                        <div class="admin-user-actions">
                                            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="admin-user-role-form">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="action" value="change_role">
                                                <label class="visually-hidden" for="role-{{ $user->id }}">Change role for {{ $user->name }}</label>
                                                <select class="form-select form-select-sm" id="role-{{ $user->id }}" name="role" required>
                                                    <option value="advertiser" @selected($user->role === \App\Enums\UserRole::Advertiser)>Seller / advertiser</option>
                                                    <option value="publisher" @selected($user->role === \App\Enums\UserRole::Publisher)>Publisher</option>
                                                </select>
                                                <input class="form-control form-control-sm" name="reason" required maxlength="1000" placeholder="Reason for role change" aria-label="Reason for role change">
                                                <button class="btn btn-sm btn-outline-primary" type="submit">Save role</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="admin-user-status-form">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="action" value="{{ $user->is_suspended ? 'reactivate' : 'suspend' }}">
                                                <label class="visually-hidden" for="reason-status-{{ $user->id }}">Reason for {{ $user->is_suspended ? 'reactivating' : 'suspending' }} {{ $user->name }}</label>
                                                <input class="form-control form-control-sm" id="reason-status-{{ $user->id }}" name="reason" required maxlength="1000" placeholder="Reason" aria-label="Reason for account status change">
                                                <button class="btn btn-sm {{ $user->is_suspended ? 'btn-success' : 'btn-outline-danger' }}" type="submit">{{ $user->is_suspended ? 'Reactivate' : 'Suspend' }}</button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="empty-state"><h2>No accounts found</h2><p>Change the search or filters and try again.</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $users->links() }}</div>
        </div>
    </section>
@endsection
