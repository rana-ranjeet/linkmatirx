<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Website;
use App\Models\WebsiteCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use RuntimeException;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Demo accounts can only be seeded in local or testing environments.');
        }

        $password = env('DEMO_ACCOUNT_PASSWORD');

        if (! is_string($password) || strlen($password) < 12) {
            throw new InvalidArgumentException('Set DEMO_ACCOUNT_PASSWORD to a value of at least 12 characters in your local .env file.');
        }

        DB::transaction(function () use ($password) {
            $this->call([
                WebsiteCategorySeeder::class,
                ArticleCategorySeeder::class,
            ]);

            $admin = $this->user('demo-admin@linkvio.test', 'Demo Super Administrator', UserRole::SuperAdmin, $password);
            $this->user('demo-seo-manager@linkvio.test', 'Demo SEO Manager', UserRole::SeoManager, $password);
            $this->user('demo-content-writer@linkvio.test', 'Demo Content Writer', UserRole::ContentWriter, $password);
            $publisher = $this->user('demo-publisher@linkvio.test', 'Demo Publisher', UserRole::Publisher, $password);
            $advertiser = $this->user('demo-seller@linkvio.test', 'Demo Seller', UserRole::Advertiser, $password);

            $publisher->publisherProfile()->updateOrCreate([], [
                'business_name' => 'Northstar Digital Publishing',
                'business_type' => 'publisher',
                'country_code' => 'US',
                'phone' => '+1 202 555 0142',
                'currency' => 'USD',
                'number_of_sites' => 3,
                'do_follow_links' => 'both',
                'owns_sites' => 'yes',
                'already_selling_articles' => 'yes',
            ]);

            $advertiser->advertiserProfile()->updateOrCreate([], [
                'company_name' => 'Acme Growth Studio',
                'business_type' => 'agency',
                'country_code' => 'US',
                'phone' => '+1 202 555 0186',
                'currency' => 'USD',
                'monthly_budget' => '1,000-5,000',
            ]);

            $listings = [
                [
                    'domain' => 'northstar-tech.test',
                    'category_slug' => 'technology',
                    'description' => 'A sample technology publication covering software, cybersecurity, and practical business technology.',
                    'country_code' => 'US',
                    'language_code' => 'en',
                    'guest_post_price_cents' => 18900,
                    'link_insertion_price_cents' => 9500,
                ],
                [
                    'domain' => 'brightpath-business.test',
                    'category_slug' => 'business',
                    'description' => 'A sample business publication focused on entrepreneurship, marketing, and small-business operations.',
                    'country_code' => 'GB',
                    'language_code' => 'en',
                    'guest_post_price_cents' => 24500,
                    'link_insertion_price_cents' => 12500,
                ],
                [
                    'domain' => 'wellness-journal.test',
                    'category_slug' => 'health-wellness',
                    'description' => 'A sample wellness publication covering healthy routines, wellbeing, and evidence-led lifestyle topics.',
                    'country_code' => 'CA',
                    'language_code' => 'en',
                    'guest_post_price_cents' => 16500,
                    'link_insertion_price_cents' => null,
                ],
            ];

            foreach ($listings as $listing) {
                $categoryId = WebsiteCategory::where('slug', $listing['category_slug'])->value('id');

                Website::updateOrCreate(
                    ['domain' => $listing['domain']],
                    [
                        'publisher_id' => $publisher->id,
                        'category_id' => $categoryId,
                        'description' => $listing['description'],
                        'country_code' => $listing['country_code'],
                        'language_code' => $listing['language_code'],
                        'status' => 'pending',
                        'is_active' => true,
                        'guest_post_price_cents' => $listing['guest_post_price_cents'],
                        'link_insertion_price_cents' => $listing['link_insertion_price_cents'],
                        'currency' => 'USD',
                        'domain_rating' => null,
                        'domain_authority' => null,
                        'organic_traffic' => null,
                        'referring_domains' => null,
                        'metrics_provider' => null,
                        'metrics_updated_at' => null,
                    ],
                );
            }
        });

        $this->command?->info('Demo accounts and pending sample listings are ready.');
        $this->command?->line('Use the same DEMO_ACCOUNT_PASSWORD for all five accounts:');
        $this->command?->line('  Admin:     demo-admin@linkvio.test');
        $this->command?->line('  SEO:       demo-seo-manager@linkvio.test');
        $this->command?->line('  Writer:    demo-content-writer@linkvio.test');
        $this->command?->line('  Publisher: demo-publisher@linkvio.test');
        $this->command?->line('  Seller:    demo-seller@linkvio.test');
        $this->command?->line('Sample websites remain pending review and are not shown in public marketplace listings.');
    }

    private function user(string $email, string $name, UserRole $role, string $password): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'email_verified_at' => now(),
                'password' => Hash::make($password),
                'role' => $role,
                'is_suspended' => false,
            ],
        );
    }
}
