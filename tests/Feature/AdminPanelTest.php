<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_real_marketplace_counts_and_unavailable_financial_totals(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);
        User::factory()->create(['role' => UserRole::Advertiser]);
        Website::create([
            'publisher_id' => $publisher->id,
            'domain' => 'waiting.example',
            'guest_post_price_cents' => 15000,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Admin dashboard')
            ->assertSee('3', false)
            ->assertSee('waiting.example')
            ->assertSee('Not available')
            ->assertSee(route('admin.users.index'), false);
    }

    public function test_admin_can_search_users_and_change_a_non_admin_role_with_audit_record(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $user = User::factory()->create([
            'name' => 'Publisher Candidate',
            'email' => 'candidate@example.com',
            'role' => UserRole::Advertiser,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['q' => 'candidate']))
            ->assertOk()
            ->assertSee('candidate@example.com')
            ->assertSee('Publisher Candidate');

        $this->patch(route('admin.users.update', $user), [
            'action' => 'change_role',
            'role' => 'publisher',
            'reason' => 'User requested publisher access.',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => UserRole::Publisher->value,
        ]);
        $this->assertDatabaseHas('admin_audit_logs', [
            'actor_id' => $admin->id,
            'target_user_id' => $user->id,
            'action' => 'change_role',
            'reason' => 'User requested publisher access.',
        ]);
    }

    public function test_admin_can_suspend_and_reactivate_non_admin_accounts_with_reasons(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $user = User::factory()->create(['role' => UserRole::Publisher]);

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $user), [
                'action' => 'suspend',
                'reason' => 'Repeated policy violations.',
            ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', ['id' => $user->id, 'is_suspended' => true]);

        $this->patch(route('admin.users.update', $user), [
            'action' => 'reactivate',
            'reason' => 'Issue resolved after review.',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', ['id' => $user->id, 'is_suspended' => false]);
        $this->assertDatabaseCount('admin_audit_logs', 2);
    }

    public function test_admin_cannot_change_own_or_another_admin_account_through_user_management(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $otherAdmin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $admin), [
                'action' => 'suspend',
                'reason' => 'Test self-protection.',
            ])->assertSessionHasErrors('action');

        $this->patch(route('admin.users.update', $otherAdmin), [
            'action' => 'suspend',
            'reason' => 'Test admin protection.',
        ])->assertSessionHasErrors('action');

        $this->assertDatabaseCount('admin_audit_logs', 0);
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'is_suspended' => false]);
        $this->assertDatabaseHas('users', ['id' => $otherAdmin->id, 'is_suspended' => false]);
    }

    public function test_non_admins_cannot_access_user_management(): void
    {
        $advertiser = User::factory()->create(['role' => UserRole::Advertiser]);

        $this->actingAs($advertiser)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_admin_can_review_account_access_audit_history(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $user = User::factory()->create(['role' => UserRole::Advertiser]);

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $user), [
                'action' => 'suspend',
                'reason' => 'Audit history test reason.',
            ]);

        $this->get(route('admin.audit.index', ['action' => 'suspend']))
            ->assertOk()
            ->assertSee('Account access audit log')
            ->assertSee('Audit history test reason.')
            ->assertSee($admin->email)
            ->assertSee($user->email);
    }
}
