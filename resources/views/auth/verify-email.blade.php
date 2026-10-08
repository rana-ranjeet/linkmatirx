<x-guest-layout>
    <div class="auth-heading">
        <p class="eyebrow">One last step</p>
        <h1>Verify your email</h1>
        <p>Follow the verification link we sent before opening your dashboard.</p>
    </div>
    @if (session('status') === 'verification-link-sent')
        <div class="alert alert-success">A fresh verification link has been sent to your email address.</div>
    @endif
    <div class="form-stack">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button class="btn btn-primary w-100" type="submit">Resend verification email</button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-outline-secondary w-100" type="submit">Log out</button>
        </form>
    </div>
</x-guest-layout>
