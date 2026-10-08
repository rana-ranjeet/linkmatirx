@extends('layouts.site')

@section('title', 'Profile settings')

@section('content')
    <section class="page-hero">
        <div class="container"><p class="eyebrow">Account settings</p><h1>Your profile</h1><p>Update your account details and security settings.</p></div>
    </section>
    <section class="section-space">
        <div class="container profile-wrap">
            <section class="profile-section">
                <h2>Profile information</h2>
                <p>Update the name and email address associated with your account.</p>
                <form method="POST" action="{{ route('profile.update') }}" class="form-stack">
                    @csrf
                    @method('PATCH')
                    <div><label class="form-label" for="name">Name</label><input class="form-control" id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name">@error('name')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div><label class="form-label" for="email">Email address</label><input class="form-control" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username">@error('email')<p class="field-error">{{ $message }}</p>@enderror</div>
                    @if (! $user->hasVerifiedEmail())
                        <p class="mb-0">Your email address is not verified. <button class="btn btn-link p-0 align-baseline" form="send-verification" type="submit">Resend verification email</button></p>
                    @endif
                    <button class="btn btn-primary align-self-start" type="submit">Save profile</button>
                </form>
                <form id="send-verification" method="POST" action="{{ route('verification.send') }}">@csrf</form>
            </section>
            <section class="profile-section">
                <h2>Change password</h2>
                <p>Use a strong, unique password to keep your account secure.</p>
                <form method="POST" action="{{ route('password.update') }}" class="form-stack">
                    @csrf
                    @method('PUT')
                    <div><label class="form-label" for="current_password">Current password</label><input class="form-control" id="current_password" name="current_password" type="password" autocomplete="current-password">@error('current_password', 'updatePassword')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div><label class="form-label" for="new_password">New password</label><input class="form-control" id="new_password" name="password" type="password" autocomplete="new-password">@error('password', 'updatePassword')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div><label class="form-label" for="password_confirmation">Confirm new password</label><input class="form-control" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"></div>
                    <button class="btn btn-primary align-self-start" type="submit">Update password</button>
                </form>
            </section>
            <section class="profile-section">
                <h2>Delete account</h2>
                <p>Deleting your account permanently removes it and associated profile data.</p>
                <form method="POST" action="{{ route('profile.destroy') }}" class="form-stack" onsubmit="return confirm('Permanently delete your account?')">
                    @csrf
                    @method('DELETE')
                    <div><label class="form-label" for="delete_password">Confirm with your password</label><input class="form-control" id="delete_password" name="password" type="password" required autocomplete="current-password">@error('password', 'userDeletion')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <button class="btn btn-danger align-self-start" type="submit">Delete account</button>
                </form>
            </section>
        </div>
    </section>
@endsection
