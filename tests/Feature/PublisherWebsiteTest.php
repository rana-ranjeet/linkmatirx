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
            ->assertRedirect(route('publisher.websites.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('websites', [
            'publisher_id' => $publisher->id,
            'domain' => 'example.com',
            'country_code' => 'US',
            'guest_post_price_cents' => 12005,
            'link_insertion_price_cents' => 5000,
            'currency' => 'USD',
            'status' => 'pending',
        ]);
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
            ->assertRedirect(route('publisher.websites.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('websites', [
            'id' => $website->id,
            'status' => 'pending',
            'guest_post_price_cents' => 12500,
            'reviewed_at' => null,
        ]);
    }
}
