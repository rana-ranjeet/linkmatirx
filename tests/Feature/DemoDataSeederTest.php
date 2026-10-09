<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_creates_verified_role_accounts_and_pending_sample_listings(): void
    {
        $previousPassword = getenv('DEMO_ACCOUNT_PASSWORD');
        putenv('DEMO_ACCOUNT_PASSWORD=local-demo-password-123');

        try {
            $this->seed(\Database\Seeders\DemoDataSeeder::class);
        } finally {
            putenv($previousPassword === false ? 'DEMO_ACCOUNT_PASSWORD' : 'DEMO_ACCOUNT_PASSWORD='.$previousPassword);
        }

        $this->assertDatabaseHas('users', [
            'email' => 'demo-admin@linkvio.test',
            'role' => UserRole::SuperAdmin->value,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'demo-publisher@linkvio.test',
            'role' => UserRole::Publisher->value,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'demo-seller@linkvio.test',
            'role' => UserRole::Advertiser->value,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'demo-seo-manager@linkvio.test',
            'role' => UserRole::SeoManager->value,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'demo-content-writer@linkvio.test',
            'role' => UserRole::ContentWriter->value,
        ]);

        $publisher = User::where('email', 'demo-publisher@linkvio.test')->firstOrFail();
        $this->assertTrue($publisher->hasVerifiedEmail());
        $this->assertTrue(Hash::check('local-demo-password-123', $publisher->password));

        $this->assertDatabaseCount('websites', 3);
        $this->assertDatabaseHas('websites', [
            'domain' => 'northstar-tech.test',
            'publisher_id' => $publisher->id,
            'status' => 'pending',
            'domain_rating' => null,
            'organic_traffic' => null,
        ]);
        $this->assertDatabaseMissing('websites', [
            'domain' => 'northstar-tech.test',
            'status' => 'approved',
        ]);
    }
}
