<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::in(array_column(UserRole::cases(), 'value'))],
            'status' => ['nullable', Rule::in(['active', 'suspended'])],
        ]);

        $users = User::query()
            ->when($filters['q'] ?? null, fn ($query, string $term) => $query->where(function ($query) use ($term) {
                $query->where('name', 'like', '%'.$term.'%')
                    ->orWhere('email', 'like', '%'.$term.'%');
            }))
            ->when($filters['role'] ?? null, fn ($query, string $role) => $query->where('role', $role))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('is_suspended', $status === 'suspended'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'filters'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['change_role', 'suspend', 'reactivate'])],
            'role' => ['required_if:action,change_role', 'nullable', Rule::in([UserRole::Advertiser->value, UserRole::Publisher->value])],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $user, $validated) {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($lockedUser->is($request->user())) {
                throw ValidationException::withMessages([
                    'action' => 'You cannot change your own account access from this screen.',
                ]);
            }

            if ($lockedUser->role === UserRole::Admin) {
                throw ValidationException::withMessages([
                    'action' => 'Administrator accounts cannot be changed here. Use the controlled administrator access process.',
                ]);
            }

            $before = [
                'role' => $lockedUser->role->value,
                'is_suspended' => $lockedUser->is_suspended,
            ];

            match ($validated['action']) {
                'change_role' => $lockedUser->role = UserRole::from($validated['role']),
                'suspend' => $lockedUser->is_suspended = true,
                'reactivate' => $lockedUser->is_suspended = false,
            };

            $lockedUser->save();

            DB::table('admin_audit_logs')->insert([
                'actor_id' => $request->user()->id,
                'target_user_id' => $lockedUser->id,
                'action' => $validated['action'],
                'reason' => $validated['reason'],
                'old_values' => json_encode($before, JSON_THROW_ON_ERROR),
                'new_values' => json_encode([
                    'role' => $lockedUser->role->value,
                    'is_suspended' => $lockedUser->is_suspended,
                ], JSON_THROW_ON_ERROR),
                'created_at' => now(),
            ]);
        });

        return redirect()->route('admin.users.index')
            ->with('status', 'Account access updated and recorded in the audit log.');
    }
}
