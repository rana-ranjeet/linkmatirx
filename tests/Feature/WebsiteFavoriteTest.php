<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteFavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_advertiser_can_save_and_remove_an_approved_website(): void
    {
        $advertiser = User::factory()->create(['role' => UserRole::Advertiser]);
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);
        $website = Website::create([
            'publisher_id' => $publisher->id,
            'domain' => 'favorite.example',
            'guest_post_price_cents' => 12000,
            'status' => 'approved',
        ]);

        $this->actingAs($advertiser)
            ->from(route('marketplace.index'))
            ->post(route('advertiser.favorites.store', $website))
            ->assertRedirect(route('marketplace.index'));

        $this->post(route('advertiser.favorites.store', $website));

        $this->assertDatabaseCount('website_favorites', 1);
        $this->get(route('advertiser.favorites.index'))
            ->assertOk()
            ->assertSee('favorite.example')
            ->assertSee('Remove favorite');

        $this->from(route('advertiser.favorites.index'))
            ->delete(route('advertiser.favorites.destroy', $website))
            ->assertRedirect(route('advertiser.favorites.index'));

        $this->assertDatabaseCount('website_favorites', 0);
    }

    public function test_pending_websites_cannot_be_saved_as_favorites(): void
    {
        $advertiser = User::factory()->create(['role' => UserRole::Advertiser]);
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);
        $website = Website::create([
            'publisher_id' => $publisher->id,
            'domain' => 'pending-favorite.example',
            'guest_post_price_cents' => 12000,
            'status' => 'pending',
        ]);

        $this->actingAs($advertiser)
            ->post(route('advertiser.favorites.store', $website))
            ->assertNotFound();

        $this->assertDatabaseCount('website_favorites', 0);
    }

    public function test_non_advertisers_cannot_access_favorites(): void
    {
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);

        $this->actingAs($publisher)
            ->get(route('advertiser.favorites.index'))
            ->assertForbidden();
    }
}
