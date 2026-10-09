<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_administrators_can_access_blog_management(): void
    {
        $this->get(route('admin.blog.index'))
            ->assertRedirect(route('login'));

        $advertiser = User::factory()->create(['role' => UserRole::Advertiser]);

        $this->actingAs($advertiser)
            ->get(route('admin.blog.index'))
            ->assertForbidden();
    }

    public function test_administrator_can_create_and_publish_an_seo_blog_post(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->post(route('admin.blog.store'), [
                'title' => 'A Better Publisher Strategy',
                'excerpt' => 'A practical guide to building better publisher partnerships.',
                'content' => "Start with relevant publishers.\n\nBe clear about your goals.",
                'meta_title' => 'Publisher Strategy Guide',
                'meta_description' => 'Learn a practical, sustainable approach to publisher partnerships.',
                'status' => 'published',
            ])
            ->assertRedirect();

        $post = BlogPost::where('title', 'A Better Publisher Strategy')->firstOrFail();

        $this->assertSame('a-better-publisher-strategy', $post->slug);
        $this->assertSame($admin->id, $post->author_id);
        $this->assertNotNull($post->published_at);
        $this->assertDatabaseHas('blog_posts', [
            'slug' => 'a-better-publisher-strategy',
            'status' => 'published',
        ]);

        $this->get(route('blog.show', $post->slug))
            ->assertOk()
            ->assertSee('<title>Publisher Strategy Guide | LINKVIO</title>', false)
            ->assertSee('name="description" content="Learn a practical, sustainable approach to publisher partnerships."', false)
            ->assertSee('rel="canonical" href="'.route('blog.show', $post->slug).'"', false)
            ->assertSee('"@type":"Article"', false)
            ->assertSee('Start with relevant publishers.')
            ->assertSee('Be clear about your goals.');

        $this->get(route('blog.sitemap'))
            ->assertOk()
            ->assertHeader('content-type', 'application/xml; charset=UTF-8')
            ->assertSee(route('blog.show', $post->slug));

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.blog.index'));
    }

    public function test_drafts_and_scheduled_posts_are_not_public_or_in_the_sitemap(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $draft = BlogPost::factory()->create([
            'author_id' => $admin->id,
            'status' => 'draft',
            'published_at' => null,
        ]);
        $scheduled = BlogPost::factory()->create([
            'author_id' => $admin->id,
            'status' => 'published',
            'published_at' => now()->addDay(),
        ]);

        $this->get(route('blog.show', $draft->slug))->assertNotFound();
        $this->get(route('blog.show', $scheduled->slug))->assertNotFound();
        $this->get(route('blog.index'))->assertDontSee($draft->title)->assertDontSee($scheduled->title);
        $this->get(route('blog.sitemap'))
            ->assertDontSee($draft->slug)
            ->assertDontSee($scheduled->slug);
    }

    public function test_blog_content_is_escaped_before_it_is_rendered(): void
    {
        $post = BlogPost::factory()->create([
            'status' => 'published',
            'published_at' => now(),
            'content' => '<script>alert("xss")</script>',
            'meta_title' => 'Article" onmouseover="alert(1)',
        ]);

        $this->get(route('blog.show', $post->slug))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false)
            ->assertSee('content="Article&quot; onmouseover=&quot;alert(1)"', false)
            ->assertDontSee('<script>alert("xss")</script>', false);
    }

    public function test_administrator_can_edit_unpublish_and_delete_a_post(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $post = BlogPost::factory()->create([
            'author_id' => $admin->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.blog.edit', $post))
            ->assertOk()
            ->assertSee($post->title);

        $this->put(route('admin.blog.update', $post), [
            'title' => 'Updated draft',
            'slug' => 'updated-draft',
            'excerpt' => 'An updated draft article summary.',
            'content' => 'Updated plain-text article content.',
            'status' => 'draft',
        ])->assertRedirect(route('admin.blog.edit', $post));

        $this->assertDatabaseHas('blog_posts', [
            'id' => $post->id,
            'title' => 'Updated draft',
            'status' => 'draft',
            'published_at' => null,
        ]);

        $this->delete(route('admin.blog.destroy', $post))
            ->assertRedirect(route('admin.blog.index'));

        $this->assertDatabaseMissing('blog_posts', ['id' => $post->id]);
    }

    public function test_robots_txt_points_search_engines_to_the_sitemap(): void
    {
        $this->get(route('robots'))
            ->assertOk()
            ->assertHeader('content-type', 'text/plain; charset=UTF-8')
            ->assertSee(url('/blog/sitemap.xml'));
    }

    public function test_verified_account_can_be_promoted_to_admin_from_the_console(): void
    {
        $user = User::factory()->create(['role' => UserRole::Advertiser]);

        $this->artisan('app:make-admin', ['email' => $user->email])
            ->expectsConfirmation("Grant super administrator access to {$user->email}?", 'yes')
            ->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => UserRole::SuperAdmin->value,
        ]);
    }
}
