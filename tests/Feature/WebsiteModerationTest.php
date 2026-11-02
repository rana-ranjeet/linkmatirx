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
            'verification_status' => 'verified',
            'verified_at' => now(),
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

    public function test_unverified_listing_requires_an_explicit_audited_admin_override(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);
        $website = Website::create([
            'publisher_id' => $publisher->id,
            'domain' => 'override.example',
            'guest_post_price_cents' => 15000,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.websites.index'))
            ->patch(route('admin.websites.review', $website), ['status' => 'approved'])
            ->assertRedirect(route('admin.websites.index'))
            ->assertSessionHasErrors(['verification_override', 'verification_override_reason']);

        $this->patch(route('admin.websites.review', $website), [
            'status' => 'approved',
            'verification_override' => '1',
            'verification_override_reason' => 'Ownership reviewed through supporting documentation.',
        ])->assertRedirect(route('admin.websites.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('websites', [
            'id' => $website->id,
            'status' => 'approved',
            'verification_override_by' => $admin->id,
            'verification_override_reason' => 'Ownership reviewed through supporting documentation.',
        ]);
        $this->assertDatabaseHas('admin_audit_logs', [
            'actor_id' => $admin->id,
            'target_user_id' => $publisher->id,
            'action' => 'website_approval_override',
            'reason' => 'Ownership reviewed through supporting documentation.',
        ]);
        $this->get(route('marketplace.index'))->assertSee('override.example');
        $this->get(route('marketplace.show', $website))->assertOk();
        $this->get(route('admin.audit.index'))->assertOk()->assertSee('Website Approval Override');
    }

    public function test_previously_approved_unverified_listings_can_only_be_published_after_an_audited_override(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);
        $website = Website::create([
            'publisher_id' => $publisher->id,
            'domain' => 'legacy-approved.example',
            'guest_post_price_cents' => 15000,
            'status' => 'approved',
        ]);

        $this->get(route('marketplace.index'))->assertDontSee('legacy-approved.example');
        $this->actingAs($admin)
            ->get(route('admin.websites.index', ['status' => 'approved']))
            ->assertOk()
            ->assertSee('Record an explicit ownership-verification override');

        $this->patch(route('admin.websites.review', $website), [
            'status' => 'approved',
            'verification_override' => '1',
            'verification_override_reason' => 'Legacy listing manually reviewed.',
        ])->assertRedirect(route('admin.websites.index'));

        $this->assertDatabaseHas('admin_audit_logs', [
            'target_user_id' => $publisher->id,
            'action' => 'website_approval_override',
            'reason' => 'Legacy listing manually reviewed.',
        ]);
        $this->get(route('marketplace.index'))->assertSee('legacy-approved.example');
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
            ->assertSee('0 available · 0 paused · 1 under review');
    }
}
