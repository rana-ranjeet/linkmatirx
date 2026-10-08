<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200)
            ->assertSee('Affiliate links')
            ->assertSee('First name')
            ->assertSee('Monthly budget')
            ->assertDontSee('name="password_confirmation"', false);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'password',
            'role' => 'advertiser',
            'business_name' => 'Test Business',
            'business_type' => 'brand',
            'country_code' => 'US',
            'phone' => '+1 555 0100',
            'currency' => 'EUR',
            'monthly_budget' => '0-500',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'advertiser',
        ]);
        $this->assertDatabaseHas('advertiser_profiles', [
            'company_name' => 'Test Business',
            'business_type' => 'brand',
            'country_code' => 'US',
            'phone' => '+1 555 0100',
            'currency' => 'EUR',
            'monthly_budget' => '0-500',
        ]);
    }

    public function test_publishers_can_register_with_business_details(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'Pat',
            'last_name' => 'Publisher',
            'email' => 'publisher@example.com',
            'password' => 'password',
            'role' => 'publisher',
            'business_name' => 'Publisher Studio',
            'country_code' => 'GB',
            'number_of_sites' => 3,
            'do_follow_links' => 'both',
            'owns_sites' => 'yes',
            'already_selling_articles' => 'no',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertDatabaseHas('publisher_profiles', [
            'business_name' => 'Publisher Studio',
            'country_code' => 'GB',
            'phone' => null,
            'number_of_sites' => 3,
            'do_follow_links' => 'both',
            'owns_sites' => 'yes',
            'already_selling_articles' => 'no',
        ]);
        $this->assertDatabaseMissing('advertiser_profiles', [
            'user_id' => User::where('email', 'publisher@example.com')->value('id'),
        ]);
    }

    public function test_registration_rejects_unsupported_account_roles(): void
    {
        $response = $this->from('/register')->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'affiliate@example.com',
            'password' => 'password',
            'role' => 'affiliate',
            'business_name' => 'Test Business',
            'business_type' => 'brand',
            'country_code' => 'US',
            'currency' => 'USD',
            'monthly_budget' => '0-500',
        ]);

        $response->assertRedirect('/register')
            ->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'affiliate@example.com']);
    }

    public function test_publisher_registration_requires_site_information(): void
    {
        $response = $this->from('/register')->post('/register', [
            'first_name' => 'Pat',
            'last_name' => 'Publisher',
            'email' => 'incomplete-publisher@example.com',
            'password' => 'password',
            'role' => 'publisher',
            'business_name' => 'Publisher Studio',
            'country_code' => 'GB',
        ]);

        $response->assertRedirect('/register')
            ->assertSessionHasErrors([
                'number_of_sites',
                'do_follow_links',
                'owns_sites',
                'already_selling_articles',
            ]);
        $this->assertDatabaseMissing('users', ['email' => 'incomplete-publisher@example.com']);
    }
}
