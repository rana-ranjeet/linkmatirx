<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_displays_live_marketplace_counts_and_approved_websites_only(): void
    {
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);
        $this->createWebsite($publisher, 'approved.example', 'approved', 12500);
        $this->createWebsite($publisher, 'pending.example', 'pending', 8000);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('1</strong> approved websites', false)
            ->assertSee('approved.example')
            ->assertDontSee('pending.example');
    }

    public function test_marketplace_hides_unapproved_websites_and_marks_missing_metrics(): void
    {
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);
        $this->createWebsite($publisher, 'listed.example', 'approved', null);
        $this->createWebsite($publisher, 'not-listed.example', 'pending', 10000);

        $this->get(route('marketplace.index'))
            ->assertOk()
            ->assertSee('listed.example')
            ->assertSee('Not available')
            ->assertDontSee('not-listed.example');
    }

    public function test_marketplace_filters_by_service_and_zero_price(): void
    {
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);
        $free = $this->createWebsite($publisher, 'free.example', 'approved', 0);
        $paid = $this->createWebsite($publisher, 'paid.example', 'approved', 10000);
        $this->createWebsite($publisher, 'no-service.example', 'approved', null);

        $this->get(route('marketplace.index', ['max_price' => 0, 'currency' => 'USD']))
            ->assertOk()
            ->assertSee('free.example')
            ->assertDontSee('paid.example')
            ->assertDontSee('no-service.example');

        $this->get(route('marketplace.index', ['service' => 'link_insertion']))
            ->assertOk()
            ->assertDontSee('free.example')
            ->assertDontSee('paid.example');

        $this->assertNotNull($free->fresh());
        $this->assertNotNull($paid->fresh());
    }

    private function createWebsite(User $publisher, string $domain, string $status, ?int $priceCents): Website
    {
        return Website::create([
            'publisher_id' => $publisher->id,
            'domain' => $domain,
            'status' => $status,
            'guest_post_price_cents' => $priceCents,
        ]);
    }
}
