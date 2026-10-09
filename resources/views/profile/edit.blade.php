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
                    @if ($user->role === \App\Enums\UserRole::Publisher)
                        @php($publisherProfile = $user->publisherProfile)
                        <h3 class="profile-subheading">Publisher details</h3>
                        <div><label class="form-label" for="business_name">Business name</label><input class="form-control" id="business_name" name="business_name" maxlength="255" value="{{ old('business_name', $publisherProfile?->business_name) }}">@error('business_name')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <div class="form-row">
                            <div><label class="form-label" for="business_type">Business type</label><select class="form-select" id="business_type" name="business_type"><option value="">Select business type</option>@foreach (['brand', 'agency', 'publisher', 'ecommerce', 'other'] as $type)<option value="{{ $type }}" @selected(old('business_type', $publisherProfile?->business_type) === $type)>{{ str($type)->headline() }}</option>@endforeach</select>@error('business_type')<p class="field-error">{{ $message }}</p>@enderror</div>
                            <div><label class="form-label" for="country_code">Country code</label><input class="form-control" id="country_code" name="country_code" maxlength="2" value="{{ old('country_code', $publisherProfile?->country_code) }}">@error('country_code')<p class="field-error">{{ $message }}</p>@enderror</div>
                        </div>
                        <div class="form-row">
                            <div><label class="form-label" for="phone">Phone</label><input class="form-control" id="phone" name="phone" maxlength="32" value="{{ old('phone', $publisherProfile?->phone) }}">@error('phone')<p class="field-error">{{ $message }}</p>@enderror</div>
                            <div><label class="form-label" for="currency">Preferred currency</label><select class="form-select" id="currency" name="currency"><option value="">Select currency</option>@foreach (['EUR', 'USD', 'GBP', 'CAD', 'AUD', 'INR', 'SGD', 'AED'] as $currency)<option value="{{ $currency }}" @selected(old('currency', $publisherProfile?->currency) === $currency)>{{ $currency }}</option>@endforeach</select>@error('currency')<p class="field-error">{{ $message }}</p>@enderror</div>
                        </div>
                        <div class="form-row">
                            <div><label class="form-label" for="number_of_sites">Number of sites</label><input class="form-control" id="number_of_sites" name="number_of_sites" type="number" min="0" max="100000" value="{{ old('number_of_sites', $publisherProfile?->number_of_sites) }}">@error('number_of_sites')<p class="field-error">{{ $message }}</p>@enderror</div>
                            <div><label class="form-label" for="do_follow_links">Link types offered</label><select class="form-select" id="do_follow_links" name="do_follow_links"><option value="">Select</option>@foreach (['yes', 'no', 'both'] as $choice)<option value="{{ $choice }}" @selected(old('do_follow_links', $publisherProfile?->do_follow_links) === $choice)>{{ str($choice)->headline() }}</option>@endforeach</select>@error('do_follow_links')<p class="field-error">{{ $message }}</p>@enderror</div>
                        </div>
                        <div class="form-row">
                            <div><label class="form-label" for="owns_sites">Do you own the sites?</label><select class="form-select" id="owns_sites" name="owns_sites"><option value="">Select</option>@foreach (['yes', 'no', 'both'] as $choice)<option value="{{ $choice }}" @selected(old('owns_sites', $publisherProfile?->owns_sites) === $choice)>{{ str($choice)->headline() }}</option>@endforeach</select>@error('owns_sites')<p class="field-error">{{ $message }}</p>@enderror</div>
                            <div><label class="form-label" for="already_selling_articles">Already selling articles?</label><select class="form-select" id="already_selling_articles" name="already_selling_articles"><option value="">Select</option>@foreach (['yes', 'no'] as $choice)<option value="{{ $choice }}" @selected(old('already_selling_articles', $publisherProfile?->already_selling_articles) === $choice)>{{ str($choice)->headline() }}</option>@endforeach</select>@error('already_selling_articles')<p class="field-error">{{ $message }}</p>@enderror</div>
                        </div>
                    @endif
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
