<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_a_website_and_make_it_public(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);
        $website = Website::create([
            'publisher_id' => $publisher->id,
            'domain' => 'reviewed.example',
            'guest_post_price_cents' => 15000,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.websites.index'))
            ->assertOk()
            ->assertSee('Website approvals');

        $this->patch(route('admin.websites.review', $website), ['status' => 'approved'])
            ->assertRedirect(route('admin.websites.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('websites', [
            'id' => $website->id,
            'status' => 'approved',
            'reviewed_by' => $admin->id,
        ]);

        $this->get(route('marketplace.index'))->assertSee('reviewed.example');
    }

    public function test_rejected_websites_require_a_reason_and_are_not_public(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);
        $website = Website::create([
            'publisher_id' => $publisher->id,
            'domain' => 'rejected.example',
            'guest_post_price_cents' => 15000,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.websites.index'))
            ->patch(route('admin.websites.review', $website), ['status' => 'rejected'])
            ->assertRedirect(route('admin.websites.index'))
            ->assertSessionHasErrors('moderation_note');

        $this->patch(route('admin.websites.review', $website), [
            'status' => 'rejected',
            'moderation_note' => 'Please confirm website ownership.',
        ])->assertRedirect(route('admin.websites.index'));

        $this->assertDatabaseHas('websites', [
            'id' => $website->id,
            'status' => 'rejected',
            'moderation_note' => 'Please confirm website ownership.',
        ]);

        $this->get(route('marketplace.index'))->assertDontSee('rejected.example');
    }

    public function test_non_admin_users_cannot_review_website_submissions(): void
    {
        $advertiser = User::factory()->create(['role' => UserRole::Advertiser]);
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);
        $website = Website::create([
            'publisher_id' => $publisher->id,
            'domain' => 'unauthorized.example',
            'guest_post_price_cents' => 15000,
            'status' => 'pending',
        ]);

        $this->actingAs($advertiser)
            ->patch(route('admin.websites.review', $website), ['status' => 'approved'])
            ->assertForbidden();
    }

    public function test_publisher_dashboard_uses_website_counts_from_database(): void
    {
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);
        Website::create([
            'publisher_id' => $publisher->id,
            'domain' => 'pending.example',
            'guest_post_price_cents' => 15000,
            'status' => 'pending',
        ]);

        $this->actingAs($publisher)
            ->get(route('publisher.dashboard'))
            ->assertOk()
            ->assertSee('1 total · 0 approved · 1 pending review');
    }
}
