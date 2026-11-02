<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();
        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($user->role === UserRole::Publisher) {
            $payoutDetails = isset($validated['payout_account_number']) ? [
                'payout_account_holder_name' => $validated['payout_account_holder_name'],
                'payout_bank_name' => $validated['payout_bank_name'],
                'payout_account_number' => $validated['payout_account_number'],
                'payout_ifsc_code' => strtoupper($validated['payout_ifsc_code']),
                'payout_account_type' => $validated['payout_account_type'],
            ] : [];

            $user->publisherProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'business_name' => $validated['business_name'] ?? null,
                    'business_type' => $validated['business_type'] ?? null,
                    'country_code' => isset($validated['country_code']) ? strtoupper($validated['country_code']) : null,
                    'phone' => $validated['phone'] ?? null,
                    'currency' => $validated['currency'] ?? null,
                    'number_of_sites' => $validated['number_of_sites'] ?? null,
                    'do_follow_links' => $validated['do_follow_links'] ?? null,
                    'owns_sites' => $validated['owns_sites'] ?? null,
                    'already_selling_articles' => $validated['already_selling_articles'] ?? null,
                    ...$payoutDetails,
                ],
            );
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
