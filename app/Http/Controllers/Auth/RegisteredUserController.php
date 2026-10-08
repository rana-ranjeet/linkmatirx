<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $isPublisher = $request->input('role') === UserRole::Publisher->value;

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', Rules\Password::defaults()],
            'role' => ['sometimes', Rule::in([UserRole::Advertiser->value, UserRole::Publisher->value])],
            'business_name' => ['required', 'string', 'max:255'],
            'business_type' => [Rule::requiredIf(! $isPublisher), 'nullable', Rule::in(['brand', 'agency', 'publisher', 'ecommerce', 'other'])],
            'country_code' => ['required', 'string', 'size:2', 'alpha'],
            'phone' => ['nullable', 'string', 'max:32'],
            'currency' => [Rule::requiredIf(! $isPublisher), 'nullable', Rule::in(['EUR', 'USD', 'GBP', 'CAD', 'AUD', 'INR', 'SGD', 'AED'])],
            'monthly_budget' => [Rule::requiredIf(! $isPublisher), 'nullable', Rule::in(['0-500', '500-1,000', '1,000-5,000', '5,000-10,000', '10,000+'])],
            'number_of_sites' => [Rule::requiredIf($isPublisher), 'nullable', 'integer', 'min:0', 'max:100000'],
            'do_follow_links' => [Rule::requiredIf($isPublisher), 'nullable', Rule::in(['yes', 'no', 'both'])],
            'owns_sites' => [Rule::requiredIf($isPublisher), 'nullable', Rule::in(['yes', 'no', 'both'])],
            'already_selling_articles' => [Rule::requiredIf($isPublisher), 'nullable', Rule::in(['yes', 'no'])],
        ]);

        $user = User::create([
            'name' => trim($validated['first_name'].' '.$validated['last_name']),
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => UserRole::from($validated['role'] ?? UserRole::Advertiser->value),
        ]);

        $profile = [
            'business_type' => $validated['business_type'] ?? null,
            'country_code' => strtoupper($validated['country_code']),
            'phone' => $validated['phone'] ?? null,
            'currency' => $validated['currency'] ?? null,
        ];

        if ($user->role === UserRole::Publisher) {
            $user->publisherProfile()->create([
                ...$profile,
                'business_name' => $validated['business_name'],
                'number_of_sites' => $validated['number_of_sites'],
                'do_follow_links' => $validated['do_follow_links'],
                'owns_sites' => $validated['owns_sites'],
                'already_selling_articles' => $validated['already_selling_articles'],
            ]);
        } else {
            $user->advertiserProfile()->create([
                ...$profile,
                'company_name' => $validated['business_name'],
                'monthly_budget' => $validated['monthly_budget'],
            ]);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
