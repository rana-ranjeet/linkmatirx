<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Exceptions\WebsiteVerificationException;
use App\Models\User;
use App\Models\Website;
use App\Services\WebsiteOwnershipVerifier;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebsiteOwnershipVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_website_submission_generates_an_unmodifiable_unique_token_and_shows_instructions(): void
    {
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);

        $this->actingAs($publisher)->post(route('publisher.websites.store'), [
            'domain' => 'one.example',
            'verification_token' => str_repeat('a', 64),
            'guest_post_price' => '100',
        ])->assertRedirectContains('#verification-heading');

        $first = Website::query()->where('domain', 'one.example')->firstOrFail();
        $second = Website::create([
            'publisher_id' => $publisher->id,
            'domain' => 'two.example',
            'guest_post_price_cents' => 10000,
            'verification_token' => $first->verification_token,
        ]);

        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $first->verification_token);
        $this->assertNotSame(str_repeat('a', 64), $first->verification_token);
        $this->assertNotSame($first->verification_token, $second->verification_token);
        $this->assertArrayNotHasKey('verification_token', $first->toArray());
        $this->get(route('publisher.websites.edit', $first))
            ->assertOk()
            ->assertSee('Ownership verification')
            ->assertSee('Copy meta tag')
            ->assertSee('WordPress')
            ->assertSee('Verify website')
            ->assertSee($first->verification_token);
    }

    public function test_publisher_can_verify_the_exact_homepage_meta_tag(): void
    {
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);
        $website = $this->website($publisher, 'ownership.example');
        $this->usePublicTestResolver();
        Http::fake([
            'https://ownership.example/*' => Http::response(
                '<html><head><meta name="yourmarketplace-verification" content="'.$website->verification_token.'"></head></html>',
                200,
                ['Content-Type' => 'text/html'],
            ),
        ]);

        $this->actingAs($publisher)
            ->post(route('publisher.websites.verify', $website))
            ->assertRedirect(route('publisher.websites.edit', $website))
            ->assertSessionHas('status', 'Website ownership verified successfully.');

        $this->assertDatabaseHas('websites', [
            'id' => $website->id,
            'verification_status' => 'verified',
        ]);
        $this->assertNotNull($website->fresh()->verified_at);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://ownership.example/');
    }

    public function test_verification_rejects_an_incorrect_token(): void
    {
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);
        $website = $this->website($publisher, 'incorrect.example');
        $this->usePublicTestResolver();
        Http::fake([
            'https://incorrect.example/*' => Http::response(
                '<head><meta name="yourmarketplace-verification" content="'.str_repeat('0', 64).'"></head>',
                200,
            ),
        ]);

        $this->actingAs($publisher)
            ->post(route('publisher.websites.verify', $website))
            ->assertRedirect(route('publisher.websites.edit', $website))
            ->assertSessionHas('error', 'The verification meta tag is present, but its token does not match this listing.');

        $this->assertDatabaseHas('websites', [
            'id' => $website->id,
            'verification_status' => 'failed',
            'verified_at' => null,
        ]);
    }

    public function test_verification_rejects_a_missing_meta_tag(): void
    {
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);
        $website = $this->website($publisher, 'missing.example');
        $this->usePublicTestResolver();
        Http::fake(['https://missing.example/*' => Http::response('<html><head></head></html>', 200)]);

        $this->actingAs($publisher)
            ->post(route('publisher.websites.verify', $website))
            ->assertRedirect(route('publisher.websites.edit', $website))
            ->assertSessionHas('error', 'The verification meta tag was not found on the homepage. Add it inside the <head> section and publish the change.');
    }

    public function test_a_publisher_cannot_verify_another_publishers_website(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Publisher]);
        $otherPublisher = User::factory()->create(['role' => UserRole::Publisher]);
        $website = $this->website($owner, 'private-owner.example');

        $this->actingAs($otherPublisher)
            ->post(route('publisher.websites.verify', $website))
            ->assertForbidden();

        $this->assertNull($website->fresh()->last_verification_attempt_at);
    }

    public function test_unsafe_listing_domains_and_private_addresses_are_blocked_before_fetching(): void
    {
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);
        $unsafeUrl = $this->website($publisher, 'https://example.com/path');
        $privateDomain = $this->website($publisher, 'internal.example');
        $this->usePublicTestResolver([
            'internal.example' => [['ip' => '127.0.0.1', 'family' => CURL_IPRESOLVE_V4]],
        ]);
        Http::fake();

        $this->actingAs($publisher)
            ->post(route('publisher.websites.verify', $unsafeUrl))
            ->assertSessionHas('error', 'The listing domain is invalid. Update the website domain and try again.');
        $this->post(route('publisher.websites.verify', $privateDomain))
            ->assertSessionHas('error', 'Verification was blocked because the website resolves to a non-public network address.');

        Http::assertNothingSent();
    }

    public function test_redirects_are_followed_only_after_the_destination_is_validated(): void
    {
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);
        $website = $this->website($publisher, 'redirect.example');
        $this->usePublicTestResolver();
        Http::fake([
            'https://redirect.example/*' => Http::response('', 302, ['Location' => 'https://www.redirect.example./home']),
            'https://www.redirect.example/*' => Http::response(
                '<head><meta name="yourmarketplace-verification" content="'.$website->verification_token.'"></head>',
                200,
            ),
        ]);

        $this->actingAs($publisher)
            ->post(route('publisher.websites.verify', $website))
            ->assertSessionHas('status', 'Website ownership verified successfully.');

        Http::assertSentCount(2);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://www.redirect.example/home');
    }

    public function test_http_only_homepages_can_be_verified_after_https_connection_failure(): void
    {
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);
        $website = $this->website($publisher, 'http-only.example');
        $this->usePublicTestResolver();
        Http::fake([
            'https://http-only.example/*' => Http::failedConnection('TLS connection failed.'),
            'http://http-only.example/*' => Http::response(
                '<head><meta name="yourmarketplace-verification" content="'.$website->verification_token.'"></head>',
                200,
            ),
        ]);

        $this->actingAs($publisher)
            ->post(route('publisher.websites.verify', $website))
            ->assertSessionHas('status', 'Website ownership verified successfully.');

        Http::assertSentCount(2);
        Http::assertSent(fn (Request $request) => $request->url() === 'http://http-only.example/');
    }

    public function test_redirects_to_private_network_addresses_are_blocked(): void
    {
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);
        $website = $this->website($publisher, 'redirect-private.example');
        $this->usePublicTestResolver([
            'private.example' => [['ip' => '10.0.0.12', 'family' => CURL_IPRESOLVE_V4]],
        ]);
        Http::fake([
            'https://redirect-private.example/*' => Http::response('', 302, ['Location' => 'https://private.example/']),
        ]);

        $this->actingAs($publisher)
            ->post(route('publisher.websites.verify', $website))
            ->assertSessionHas('error', 'Verification was blocked because the website resolves to a non-public network address.');

        Http::assertSentCount(1);
    }

    public function test_http_failures_and_timeouts_mark_verification_failed(): void
    {
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);
        $unavailable = $this->website($publisher, 'unavailable.example');
        $timedOut = $this->website($publisher, 'timeout.example');
        $oversized = $this->website($publisher, 'oversized.example');
        $this->usePublicTestResolver();
        Http::fake([
            'https://unavailable.example/*' => Http::response('', 503),
            'https://timeout.example/*' => Http::failedConnection('Connection timed out.'),
            'http://timeout.example/*' => Http::failedConnection('Connection timed out.'),
            'https://oversized.example/*' => Http::response('', 200, ['Content-Length' => '1048577']),
        ]);

        $this->actingAs($publisher)
            ->post(route('publisher.websites.verify', $unavailable))
            ->assertSessionHas('error', 'The homepage returned HTTP 503. Make sure it is publicly accessible and try again.');
        $this->post(route('publisher.websites.verify', $unavailable))
            ->assertSessionHas('error', 'Please wait at least 60 seconds before trying website verification again.');
        $this->travel(61)->seconds();
        $this->post(route('publisher.websites.verify', $timedOut))
            ->assertSessionHas('error', 'The homepage could not be reached before the connection timed out.');
        $this->travel(61)->seconds();
        $this->post(route('publisher.websites.verify', $oversized))
            ->assertSessionHas('error', 'The homepage HTML is too large to verify (maximum 1 MB).');

        $this->assertDatabaseHas('websites', ['id' => $unavailable->id, 'verification_status' => 'failed']);
        $this->assertDatabaseHas('websites', ['id' => $timedOut->id, 'verification_status' => 'failed']);
    }

    public function test_duplicate_verification_tokens_are_rejected_by_the_database(): void
    {
        $publisher = User::factory()->create(['role' => UserRole::Publisher]);
        $first = $this->website($publisher, 'first-unique.example');
        $second = $this->website($publisher, 'second-unique.example');
        $second->forceFill(['verification_token' => $first->verification_token]);

        $this->expectException(QueryException::class);
        $second->save();
    }

    public function test_private_ip_literals_are_rejected_by_the_real_address_guard(): void
    {
        $verifier = new class extends WebsiteOwnershipVerifier
        {
            public function resolve(string $host): array
            {
                return $this->resolvePublicAddresses($host);
            }

            public function isPublic(string $ip): bool
            {
                return $this->isPublicIp($ip);
            }
        };

        $this->expectException(WebsiteVerificationException::class);
        $verifier->resolve('192.168.1.1');
    }

    public function test_shared_documentation_multicast_and_mapped_addresses_are_not_public(): void
    {
        $verifier = new class extends WebsiteOwnershipVerifier
        {
            public function isPublic(string $ip): bool
            {
                return $this->isPublicIp($ip);
            }
        };

        foreach (['100.64.0.1', '192.0.2.1', '224.0.0.1', '::ffff:127.0.0.1'] as $ip) {
            $this->assertFalse($verifier->isPublic($ip), $ip.' must be blocked');
        }

        $this->assertTrue($verifier->isPublic('8.8.8.8'));
        $this->assertTrue($verifier->isPublic('2606:4700:4700::1111'));
    }

    private function website(User $publisher, string $domain): Website
    {
        return Website::create([
            'publisher_id' => $publisher->id,
            'domain' => $domain,
            'guest_post_price_cents' => 10000,
        ]);
    }

    private function usePublicTestResolver(array $resolutions = []): void
    {
        app()->instance(WebsiteOwnershipVerifier::class, new class($resolutions) extends WebsiteOwnershipVerifier
        {
            public function __construct(private array $resolutions) {}

            protected function resolvePublicAddresses(string $host): array
            {
                $addresses = $this->resolutions[$host] ?? [
                    ['ip' => '93.184.216.34', 'family' => CURL_IPRESOLVE_V4],
                ];

                foreach ($addresses as $address) {
                    if (! $this->isPublicIp($address['ip'])) {
                        throw new WebsiteVerificationException('Verification was blocked because the website resolves to a non-public network address.');
                    }
                }

                return $addresses;
            }
        });
    }
}
