<x-guest-layout>
    <div class="auth-heading">
        <p class="eyebrow">Welcome back</p>
        <h1>Sign in to your account</h1>
        <p>Pick up where you left off in your LINKVIO workspace.</p>
    </div>
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <form method="POST" action="{{ route('login') }}" class="form-stack">
        @csrf
        <div>
            <label class="form-label" for="email">Email address</label>
            <input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            @error('email') <p class="field-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <div class="label-row">
                <label class="form-label" for="password">Password</label>
                <a href="{{ route('password.request') }}">Forgot password?</a>
            </div>
            <div class="password-field">
                <input class="form-control" id="password" name="password" type="password" required autocomplete="current-password">
                <button class="password-toggle" type="button" aria-controls="password" aria-pressed="false" data-password-toggle>Show</button>
            </div>
            @error('password') <p class="field-error">{{ $message }}</p> @enderror
        </div>
        <div class="form-check">
            <input class="form-check-input" id="remember_me" type="checkbox" name="remember">
            <label class="form-check-label" for="remember_me">Remember me</label>
        </div>
        <button class="btn btn-primary w-100" type="submit">Log in</button>
    </form>
    <p class="auth-switch">New to LINKVIO? <a href="{{ route('register', ['role' => 'advertiser']) }}">Join as an advertiser</a></p>
    <p class="auth-switch auth-switch-secondary">Publisher? <a href="{{ route('register', ['role' => 'publisher']) }}">Create a publisher account</a></p>
</x-guest-layout>
