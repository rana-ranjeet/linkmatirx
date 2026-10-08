<x-guest-layout>
    <div class="auth-heading">
        <p class="eyebrow">Account recovery</p>
        <h1>Reset your password</h1>
        <p>Enter your account email and we will send a secure reset link.</p>
    </div>
    @if (session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif
    <form method="POST" action="{{ route('password.email') }}" class="form-stack">
        @csrf
        <div>
            <label class="form-label" for="email">Email address</label>
            <input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            @error('email') <p class="field-error">{{ $message }}</p> @enderror
        </div>
        <button class="btn btn-primary w-100" type="submit">Send reset link</button>
    </form>
    <p class="auth-switch"><a href="{{ route('login') }}">Return to sign in</a></p>
</x-guest-layout>
