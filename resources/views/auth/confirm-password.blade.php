<x-guest-layout>
    <div class="auth-heading">
        <p class="eyebrow">Security check</p>
        <h1>Confirm your password</h1>
        <p>This is a secure area. Confirm your password to continue.</p>
    </div>
    <form method="POST" action="{{ route('password.confirm') }}" class="form-stack">
        @csrf
        <div>
            <label class="form-label" for="password">Password</label>
            <input class="form-control" id="password" name="password" type="password" required autocomplete="current-password">
            @error('password') <p class="field-error">{{ $message }}</p> @enderror
        </div>
        <button class="btn btn-primary w-100" type="submit">Confirm</button>
    </form>
</x-guest-layout>
