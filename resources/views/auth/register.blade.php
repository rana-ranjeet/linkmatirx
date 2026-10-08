<x-guest-layout>
    @php($selectedRole = old('role', request()->query('role') === 'publisher' ? 'publisher' : 'advertiser'))
    <div class="auth-heading">
        <p class="eyebrow">Join LINKVIO</p>
        <h1>Create your account</h1>
        <p>Choose how you want to use the marketplace.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="form-stack">
        @csrf
        <div>
            <label class="form-label" for="name">Full name</label>
            <input class="form-control" id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name">
            @error('name') <p class="field-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="form-label" for="email">Email address</label>
            <input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username">
            @error('email') <p class="field-error">{{ $message }}</p> @enderror
        </div>
        <fieldset>
            <legend class="form-label">I want to</legend>
            <div class="role-options">
                <label class="role-option">
                    <input type="radio" name="role" value="advertiser" @checked($selectedRole === 'advertiser')>
                    <span><strong>Buy placements</strong><small>Find publishers and grow your reach.</small></span>
                </label>
                <label class="role-option">
                    <input type="radio" name="role" value="publisher" @checked($selectedRole === 'publisher')>
                    <span><strong>Sell placements</strong><small>List your websites and receive orders.</small></span>
                </label>
            </div>
            @error('role') <p class="field-error">{{ $message }}</p> @enderror
        </fieldset>
        <div>
            <label class="form-label" for="password">Password</label>
            <input class="form-control" id="password" name="password" type="password" required autocomplete="new-password">
            @error('password') <p class="field-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="form-label" for="password_confirmation">Confirm password</label>
            <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
        </div>
        <button class="btn btn-primary w-100" type="submit">Create account</button>
    </form>
    <p class="auth-switch">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
</x-guest-layout>
