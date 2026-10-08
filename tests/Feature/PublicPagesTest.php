<?php

namespace Tests\Feature;

use App\Models\ContactInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_navigation_pages_render(): void
    {
        foreach ([
            'services' => 'Publishing services, in one place',
            'how-it-works' => 'A clear path from discovery to delivery',
            'pricing' => 'Compare prices set by publishers',
            'about' => 'Better publisher partnerships start with clarity',
            'faqs' => 'A few things to know',
            'blog.index' => 'Ideas for better publisher partnerships',
            'privacy' => 'Privacy notice',
            'terms' => 'Terms of service',
            'refund-policy' => 'Refund policy',
            'cookie-policy' => 'Cookie notice',
        ] as $route => $heading) {
            $this->get(route($route))
                ->assertOk()
                ->assertSee($heading);
        }
    }

    public function test_contact_inquiry_is_validated_and_saved(): void
    {
        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('How can we help?');

        $this->post(route('contact.store'), [
            'name' => 'Jamie Example',
            'email' => 'jamie@example.test',
            'subject' => 'Publisher listing question',
            'message' => 'Can you explain the listing review process?',
        ])->assertRedirect(route('contact'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('contact_inquiries', [
            'name' => 'Jamie Example',
            'email' => 'jamie@example.test',
            'subject' => 'Publisher listing question',
            'status' => 'open',
        ]);
    }

    public function test_contact_inquiry_rejects_invalid_email_and_missing_subject(): void
    {
        $this->post(route('contact.store'), [
            'name' => 'Jamie Example',
            'email' => 'not-an-email',
            'subject' => '',
            'message' => 'A message.',
        ])->assertSessionHasErrors(['email', 'subject']);

        $this->assertSame(0, ContactInquiry::count());
    }
}
