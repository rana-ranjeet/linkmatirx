<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublisherWebsiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_publisher_can_open_website_management_pages(): void
    {
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);

        $this->actingAs($publisher)
            ->get(route('publisher.websites.index'))
            ->assertOk()
            ->assertSee('Your first website listing starts here');

        $this->get(route('publisher.websites.create'))
            ->assertOk()
            ->assertSee('Submit website for review');
    }

    public function test_publisher_can_submit_a_website_with_minor_unit_prices(): void
    {
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);

        $this->actingAs($publisher)
            ->post(route('publisher.websites.store'), [
                'domain' => 'Example.com',
                'description' => 'A publication for business readers.',
                'country_code' => 'us',
                'language_code' => 'en',
                'guest_post_price' => '120.05',
                'link_insertion_price' => '50',
            ])
            ->assertRedirectContains('#verification-heading')
            ->assertSessionHasNoErrors();

        $website = Website::query()->where('domain', 'example.com')->firstOrFail();
        $this->assertDatabaseHas('websites', [
            'publisher_id' => $publisher->id,
            'domain' => 'example.com',
            'country_code' => 'US',
            'guest_post_price_cents' => 12005,
            'link_insertion_price_cents' => 5000,
            'currency' => 'USD',
            'status' => 'pending',
        ]);
        $this->get(route('publisher.websites.edit', $website))
            ->assertOk()
            ->assertSee('Ownership verification')
            ->assertSee('Pending')
            ->assertSee('HTML Meta Tag')
            ->assertSee($website->verification_token)
            ->assertSee('value="&lt;meta name=&quot;yourmarketplace-verification&quot; content=&quot;'.$website->verification_token.'&quot;&gt;"', false)
            ->assertSee('Copy token')
            ->assertSee('Copy meta tag')
            ->assertSee('Verify website');
        $this->get(route('publisher.websites.index'))
            ->assertOk()
            ->assertSee('Ownership verification')
            ->assertSee('Verify website');
    }

    public function test_publisher_must_set_at_least_one_positive_service_price(): void
    {
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);

        $this->actingAs($publisher)
            ->from(route('publisher.websites.create'))
            ->post(route('publisher.websites.store'), [
                'domain' => 'example.com',
                'guest_post_price' => '',
                'link_insertion_price' => '',
            ])
            ->assertRedirect(route('publisher.websites.create'))
            ->assertSessionHasErrors(['guest_post_price', 'link_insertion_price']);
    }

    public function test_publishers_cannot_edit_another_publishers_website(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Publisher]);
        $otherPublisher = User::factory()->create(['role' => UserRole::Publisher]);
        $website = Website::create([
            'publisher_id' => $owner->id,
            'domain' => 'owned.example',
            'guest_post_price_cents' => 10000,
            'status' => 'pending',
        ]);

        $this->actingAs($otherPublisher)
            ->get(route('publisher.websites.edit', $website))
            ->assertForbidden();
    }

    public function test_website_changes_return_an_approved_listing_to_review(): void
    {
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);
        $website = Website::create([
            'publisher_id' => $publisher->id,
            'domain' => 'approved.example',
            'guest_post_price_cents' => 10000,
            'status' => 'approved',
        ]);

        $this->actingAs($publisher)
            ->put(route('publisher.websites.update', $website), [
                'domain' => 'approved.example',
                'guest_post_price' => '125.00',
            ])
            ->assertRedirectContains('#verification-heading')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('websites', [
            'id' => $website->id,
            'status' => 'pending',
            'guest_post_price_cents' => 12500,
            'reviewed_at' => null,
        ]);
    }

    public function test_changing_a_verified_website_domain_rotates_its_token_and_resets_verification(): void
    {
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);
        $website = Website::create([
            'publisher_id' => $publisher->id,
            'domain' => 'old-domain.example',
            'guest_post_price_cents' => 10000,
            'status' => 'approved',
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);
        $oldToken = $website->verification_token;

        $this->actingAs($publisher)
            ->put(route('publisher.websites.update', $website), [
                'domain' => 'new-domain.example',
                'guest_post_price' => '125',
            ])
            ->assertRedirectContains('#verification-heading')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('websites', [
            'id' => $website->id,
            'domain' => 'new-domain.example',
            'status' => 'pending',
            'verification_status' => 'pending',
            'verified_at' => null,
        ]);
        $this->assertNotSame($oldToken, $website->fresh()->verification_token);
    }
}
