<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WriterManagementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::in([UserRole::SeoManager->value, UserRole::ContentWriter->value])],
        ]);

        $users = User::whereIn('role', [UserRole::SeoManager, UserRole::ContentWriter])
            ->when($filters['q'] ?? null, fn ($query, string $term) => $query->where(fn ($query) => $query
                ->where('name', 'like', '%'.$term.'%')
                ->orWhere('email', 'like', '%'.$term.'%')))
            ->when($filters['role'] ?? null, fn ($query, string $role) => $query->where('role', $role))
            ->withCount('assignedArticles')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.writers.index', compact('users', 'filters'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in([UserRole::SeoManager->value, UserRole::ContentWriter->value])],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => UserRole::from($validated['role']),
            'password' => $validated['password'],
        ]);

        event(new Registered($user));

        return redirect()->route('admin.writers.index')
            ->with('status', 'Content team account created. A verification email has been sent.');
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in([
                UserRole::Advertiser->value,
                UserRole::Publisher->value,
                UserRole::SeoManager->value,
                UserRole::ContentWriter->value,
            ])],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $user, $validated) {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($lockedUser->role->isSuperAdmin() || $lockedUser->is($request->user())) {
                throw ValidationException::withMessages([
                    'role' => 'Super administrator access cannot be changed from this screen.',
                ]);
            }

            $oldRole = $lockedUser->role->value;
            $lockedUser->role = UserRole::from($validated['role']);
            $lockedUser->save();

            DB::table('admin_audit_logs')->insert([
                'actor_id' => $request->user()->id,
                'target_user_id' => $lockedUser->id,
                'action' => 'change_role',
                'reason' => $validated['reason'],
                'old_values' => json_encode(['role' => $oldRole], JSON_THROW_ON_ERROR),
                'new_values' => json_encode(['role' => $lockedUser->role->value], JSON_THROW_ON_ERROR),
                'created_at' => now(),
            ]);
        });

        return redirect()->route('admin.writers.index')
            ->with('status', 'Role updated and recorded in the audit log.');
    }
}
