<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_dashboards_are_restricted_to_their_role(): void
    {
        $advertiser = User::factory()->create(['role' => UserRole::Advertiser]);

        $this->actingAs($advertiser)
            ->get(route('advertiser.dashboard'))
            ->assertOk();

        $this->get(route('publisher.dashboard'))->assertForbidden();
        $this->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_admin_role_cannot_be_selected_during_public_registration(): void
    {
        $this->post(route('register'), [
            'name' => 'Unprivileged User',
            'email' => 'unprivileged@example.com',
            'role' => UserRole::Admin->value,
            'password' => 'Password-123!',
            'password_confirmation' => 'Password-123!',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'unprivileged@example.com']);
    }

    public function test_suspended_accounts_are_logged_out_of_protected_pages(): void
    {
        $user = User::factory()->create(['is_suspended' => true]);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
