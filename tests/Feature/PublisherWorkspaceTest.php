<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\ContactInquiry;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublisherWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_publisher_can_update_their_publisher_profile(): void
    {
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);

        $this->actingAs($publisher)
            ->patch(route('profile.update'), [
                'name' => $publisher->name,
                'email' => $publisher->email,
                'business_name' => 'Northstar Publishing',
                'business_type' => 'publisher',
                'country_code' => 'in',
                'phone' => '+91 98765 43210',
                'currency' => 'INR',
                'number_of_sites' => 8,
                'do_follow_links' => 'both',
                'owns_sites' => 'yes',
                'already_selling_articles' => 'yes',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('publisher_profiles', [
            'user_id' => $publisher->id,
            'business_name' => 'Northstar Publishing',
            'business_type' => 'publisher',
            'country_code' => 'IN',
            'phone' => '+91 98765 43210',
            'currency' => 'INR',
            'number_of_sites' => 8,
            'do_follow_links' => 'both',
            'owns_sites' => 'yes',
            'already_selling_articles' => 'yes',
        ]);
    }

    public function test_publisher_can_pause_and_resume_only_their_approved_website(): void
    {
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);
        $website = Website::create([
            'publisher_id' => $publisher->id,
            'domain' => 'approved.example',
            'guest_post_price_cents' => 15000,
            'status' => 'approved',
        ]);

        $this->actingAs($publisher)
            ->patch(route('publisher.websites.availability', $website), ['is_active' => '0'])
            ->assertRedirect(route('publisher.websites.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('websites', ['id' => $website->id, 'is_active' => false]);
        $this->get(route('marketplace.index'))->assertDontSee('approved.example');

        $this->patch(route('publisher.websites.availability', $website), ['is_active' => '1'])
            ->assertRedirect(route('publisher.websites.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('websites', ['id' => $website->id, 'is_active' => true]);
        $this->get(route('marketplace.index'))->assertSee('approved.example');
    }

    public function test_publisher_cannot_pause_another_publishers_site_or_unreviewed_listing(): void
    {
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);
        $otherPublisher = User::factory()->create(['role' => UserRole::Publisher]);
        $ownedPending = Website::create([
            'publisher_id' => $publisher->id,
            'domain' => 'pending.example',
            'guest_post_price_cents' => 15000,
            'status' => 'pending',
        ]);
        $othersApproved = Website::create([
            'publisher_id' => $otherPublisher->id,
            'domain' => 'others.example',
            'guest_post_price_cents' => 15000,
            'status' => 'approved',
        ]);

        $this->actingAs($publisher)
            ->patch(route('publisher.websites.availability', $othersApproved), ['is_active' => '0'])
            ->assertForbidden();

        $this->patch(route('publisher.websites.availability', $ownedPending), ['is_active' => '0'])
            ->assertSessionHasErrors('is_active');

        $this->assertDatabaseHas('websites', ['id' => $ownedPending->id, 'is_active' => true]);
        $this->assertDatabaseHas('websites', ['id' => $othersApproved->id, 'is_active' => true]);
    }

    public function test_publisher_can_submit_and_review_only_their_support_requests(): void
    {
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);
        $otherPublisher = User::factory()->create(['role' => UserRole::Publisher]);
        ContactInquiry::create([
            'user_id' => $otherPublisher->id,
            'name' => $otherPublisher->name,
            'email' => $otherPublisher->email,
            'subject' => 'Other publisher request',
            'message' => 'This request is private.',
            'status' => 'open',
        ]);

        $this->actingAs($publisher)
            ->get(route('publisher.dashboard'))
            ->assertOk()
            ->assertSee('Publisher workspace')
            ->assertSee('Orders and earnings');

        $this->post(route('publisher.support.store'), [
            'subject' => 'Listing review question',
            'message' => 'Could you explain the requested changes?',
        ])->assertRedirect(route('publisher.support.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('contact_inquiries', [
            'user_id' => $publisher->id,
            'name' => $publisher->name,
            'email' => $publisher->email,
            'subject' => 'Listing review question',
            'status' => 'open',
        ]);

        $this->get(route('publisher.support.index'))
            ->assertOk()
            ->assertSee('Listing review question')
            ->assertDontSee('Other publisher request')
            ->assertDontSee('This request is private.');
    }
}
