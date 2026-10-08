<x-guest-layout>
    @php
        $selectedRole = old('role', request()->query('role') === 'publisher' ? 'publisher' : 'advertiser');
        $countries = [
            'AU' => 'Australia',
            'BR' => 'Brazil',
            'CA' => 'Canada',
            'CN' => 'China',
            'DK' => 'Denmark',
            'FR' => 'France',
            'DE' => 'Germany',
            'IN' => 'India',
            'ID' => 'Indonesia',
            'IE' => 'Ireland',
            'IT' => 'Italy',
            'JP' => 'Japan',
            'MY' => 'Malaysia',
            'MX' => 'Mexico',
            'NL' => 'Netherlands',
            'NZ' => 'New Zealand',
            'NG' => 'Nigeria',
            'PK' => 'Pakistan',
            'PH' => 'Philippines',
            'SG' => 'Singapore',
            'ZA' => 'South Africa',
            'ES' => 'Spain',
            'SE' => 'Sweden',
            'CH' => 'Switzerland',
            'AE' => 'United Arab Emirates',
            'GB' => 'United Kingdom',
            'US' => 'United States',
        ];
    @endphp

    <div class="auth-heading register-heading">
        <p class="eyebrow">Join LINKVIO</p>
        <h1>Create your account</h1>
        <p>Backlinks from around the world at your fingertips</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="register-form">
        @csrf

        <fieldset class="register-interest">
            <legend class="form-label">I am interested in</legend>
            <div class="interest-options" role="radiogroup" aria-label="I am interested in">
                <label class="interest-option">
                    <input type="radio" name="role" value="advertiser" @checked($selectedRole === 'advertiser')>
                    <span aria-hidden="true">✧</span>
                    <strong>Buying</strong>
                </label>
                <label class="interest-option">
                    <input type="radio" name="role" value="publisher" @checked($selectedRole === 'publisher')>
                    <span aria-hidden="true">◇</span>
                    <strong>Selling</strong>
                </label>
                <span class="interest-option interest-option-disabled" aria-disabled="true" title="Affiliate accounts are coming soon">
                    <span aria-hidden="true">⌘</span>
                    <strong>Affiliate links</strong>
                    <small>Coming soon</small>
                </span>
            </div>
            @error('role') <p class="field-error">{{ $message }}</p> @enderror
        </fieldset>

        <div class="register-grid">
            <div>
                <label class="form-label" for="first_name">First name <span class="required-mark">*</span></label>
                <input class="form-control" id="first_name" name="first_name" type="text" value="{{ old('first_name') }}" placeholder="Your first name" required maxlength="120" autocomplete="given-name" autofocus>
                @error('first_name') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label" for="last_name">Last name <span class="required-mark">*</span></label>
                <input class="form-control" id="last_name" name="last_name" type="text" value="{{ old('last_name') }}" placeholder="Your last name" required maxlength="120" autocomplete="family-name">
                @error('last_name') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label" for="email"><span data-contact-email-label>{{ $selectedRole === 'publisher' ? 'Contact email' : 'Email' }}</span> <span class="required-mark">*</span></label>
                <input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="{{ $selectedRole === 'publisher' ? 'Contact email' : 'Your email' }}" required autocomplete="username">
                @error('email') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label" for="password">Password <span class="required-mark">*</span></label>
                <div class="password-field">
                    <input class="form-control" id="password" name="password" type="password" placeholder="Use a strong password" required autocomplete="new-password">
                    <button class="password-toggle" type="button" aria-label="Show password" aria-controls="password" aria-pressed="false" data-password-toggle>Show</button>
                </div>
                @error('password') <p class="field-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="register-divider" aria-hidden="true"></div>

        <div class="register-grid">
            <div>
                <label class="form-label" for="business_name">Business name <span class="required-mark">*</span></label>
                <input class="form-control" id="business_name" name="business_name" type="text" value="{{ old('business_name') }}" placeholder="Your business name" required maxlength="255" autocomplete="organization">
                @error('business_name') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div data-buyer-only @if ($selectedRole === 'publisher') hidden @endif>
                <label class="form-label" for="business_type">Business type <span class="required-mark">*</span></label>
                <select class="form-select" id="business_type" name="business_type" @required($selectedRole === 'advertiser')>
                    <option value="" disabled @selected(old('business_type') === null)>Select type</option>
                    @foreach (['brand' => 'Brand / business', 'agency' => 'Marketing agency', 'publisher' => 'Publisher', 'ecommerce' => 'Online store', 'other' => 'Other'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('business_type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('business_type') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label" for="country_code">Country <span class="required-mark">*</span></label>
                <select class="form-select" id="country_code" name="country_code" required>
                    <option value="" disabled @selected(old('country_code') === null)>Select country</option>
                    @foreach ($countries as $code => $country)
                        <option value="{{ $code }}" @selected(old('country_code') === $code)>{{ $country }}</option>
                    @endforeach
                </select>
                @error('country_code') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label" for="phone">Phone number</label>
                <input class="form-control" id="phone" name="phone" type="tel" value="{{ old('phone') }}" placeholder="E.g. +45 53 57 12 56" maxlength="32" autocomplete="tel">
                @error('phone') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div data-buyer-only @if ($selectedRole === 'publisher') hidden @endif>
                <label class="form-label" for="currency">Currency <span class="required-mark">*</span></label>
                <select class="form-select" id="currency" name="currency" @required($selectedRole === 'advertiser')>
                    @foreach (['EUR' => 'EUR', 'USD' => 'USD', 'GBP' => 'GBP', 'CAD' => 'CAD', 'AUD' => 'AUD', 'INR' => 'INR', 'SGD' => 'SGD', 'AED' => 'AED'] as $code => $label)
                        <option value="{{ $code }}" @selected(old('currency', 'EUR') === $code)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('currency') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div data-buyer-only @if ($selectedRole === 'publisher') hidden @endif>
                <label class="form-label" for="monthly_budget">Monthly budget</label>
                <select class="form-select" id="monthly_budget" name="monthly_budget" @required($selectedRole === 'advertiser')>
                    @foreach (['0-500', '500-1,000', '1,000-5,000', '5,000-10,000', '10,000+'] as $budget)
                        <option value="{{ $budget }}" @selected(old('monthly_budget', '0-500') === $budget)>{{ $budget }}</option>
                    @endforeach
                </select>
                @error('monthly_budget') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div data-seller-only @if ($selectedRole !== 'publisher') hidden @endif>
                <label class="form-label" for="number_of_sites">Number of sites <span class="required-mark">*</span></label>
                <input class="form-control" id="number_of_sites" name="number_of_sites" type="number" min="0" max="100000" value="{{ old('number_of_sites', 0) }}" required>
                @error('number_of_sites') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <fieldset class="seller-choice-field" data-seller-only @if ($selectedRole !== 'publisher') hidden @endif>
                <legend class="form-label">Does your site have do-follow links? <span class="required-mark">*</span></legend>
                <div class="seller-radio-options">
                    @foreach (['yes' => 'Yes', 'no' => 'No', 'both' => 'Both'] as $value => $label)
                        <label class="seller-radio-option">
                            <input type="radio" name="do_follow_links" value="{{ $value }}" @checked(old('do_follow_links') === $value) @required($selectedRole === 'publisher')>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('do_follow_links') <p class="field-error">{{ $message }}</p> @enderror
            </fieldset>
            <fieldset class="seller-choice-field" data-seller-only @if ($selectedRole !== 'publisher') hidden @endif>
                <legend class="form-label">Do you own the sites? <span class="required-mark">*</span></legend>
                <div class="seller-radio-options">
                    @foreach (['yes' => 'Yes', 'no' => 'No', 'both' => 'Both'] as $value => $label)
                        <label class="seller-radio-option">
                            <input type="radio" name="owns_sites" value="{{ $value }}" @checked(old('owns_sites') === $value) @required($selectedRole === 'publisher')>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('owns_sites') <p class="field-error">{{ $message }}</p> @enderror
            </fieldset>
            <fieldset class="seller-choice-field" data-seller-only @if ($selectedRole !== 'publisher') hidden @endif>
                <legend class="form-label">Are you already selling articles? <span class="required-mark">*</span></legend>
                <div class="seller-radio-options">
                    @foreach (['yes' => 'Yes', 'no' => 'No'] as $value => $label)
                        <label class="seller-radio-option">
                            <input type="radio" name="already_selling_articles" value="{{ $value }}" @checked(old('already_selling_articles') === $value) @required($selectedRole === 'publisher')>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('already_selling_articles') <p class="field-error">{{ $message }}</p> @enderror
            </fieldset>
        </div>

        <button class="btn btn-primary w-100 register-submit" type="submit" data-register-submit>{{ $selectedRole === 'publisher' ? 'Request access' : 'Create account' }}</button>
    </form>

    <p class="auth-switch">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
</x-guest-layout>
