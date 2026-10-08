<x-guest-layout>
    <div class="auth-heading">
        <p class="eyebrow">Account recovery</p>
        <h1>Choose a new password</h1>
        <p>Use a strong password you do not use on other sites.</p>
    </div>
    <form method="POST" action="{{ route('password.store') }}" class="form-stack">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div>
            <label class="form-label" for="email">Email address</label>
            <input class="form-control" id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
            @error('email') <p class="field-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="form-label" for="password">New password</label>
            <input class="form-control" id="password" name="password" type="password" required autocomplete="new-password">
            @error('password') <p class="field-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="form-label" for="password_confirmation">Confirm new password</label>
            <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
        </div>
        <button class="btn btn-primary w-100" type="submit">Reset password</button>
    </form>
</x-guest-layout>
