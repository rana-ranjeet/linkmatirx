<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\ArticleCategory;
use App\Models\BlogPost;
use App\Models\User;
use Database\Seeders\ArticleCategorySeeder;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ArticleWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ArticleCategorySeeder::class);
    }

    public function test_super_admin_can_create_a_seo_manager_or_writer_account_but_not_an_admin_account(): void
    {
        Notification::fake();
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $this->actingAs($superAdmin)
            ->post(route('admin.writers.store'), [
                'name' => 'Article Writer',
                'email' => 'writer@example.test',
                'role' => 'content_writer',
                'password' => 'Writer-password-123!',
                'password_confirmation' => 'Writer-password-123!',
            ])
            ->assertRedirect(route('admin.writers.index'))
            ->assertSessionHasNoErrors();

        $writer = User::where('email', 'writer@example.test')->firstOrFail();
        $this->assertSame(UserRole::ContentWriter, $writer->role);
        $this->assertFalse($writer->hasVerifiedEmail());
        $this->assertTrue(password_verify('Writer-password-123!', $writer->password));
        Notification::assertSentTo($writer, VerifyEmail::class);

        $this->post(route('admin.writers.store'), [
            'name' => 'Invalid Admin',
            'email' => 'invalid-admin@example.test',
            'role' => 'super_admin',
            'password' => 'Writer-password-123!',
            'password_confirmation' => 'Writer-password-123!',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'invalid-admin@example.test']);
    }

    public function test_seo_manager_can_create_a_brief_and_assign_it_to_a_writer(): void
    {
        $seoManager = User::factory()->create(['role' => UserRole::SeoManager]);
        $writer = User::factory()->create(['role' => UserRole::ContentWriter]);
        $category = ArticleCategory::where('slug', 'seo')->firstOrFail();

        $this->actingAs($seoManager)
            ->post(route('admin.articles.store'), [
                'title' => 'A thoughtful SEO guide',
                'excerpt' => 'A useful guide for readers.',
                'content_brief' => 'Explain the research and editorial steps.',
                'assigned_writer_id' => $writer->id,
                'category_id' => $category->id,
                'target_keyword' => 'thoughtful SEO',
                'meta_title' => 'Thoughtful SEO',
                'meta_description' => 'A practical guide to SEO.',
                'internal_links' => "https://example.test/guide\nhttps://example.test/resources",
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $article = BlogPost::where('slug', 'a-thoughtful-seo-guide')->firstOrFail();
        $this->assertSame($writer->id, $article->assigned_writer_id);
        $this->assertSame($category->id, $article->category_id);
        $this->assertSame(['https://example.test/guide', 'https://example.test/resources'], $article->internal_links);
        $this->assertSame('draft', $article->status);

        $this->actingAs($writer)
            ->get(route('writer.dashboard'))
            ->assertOk()
            ->assertSee('A thoughtful SEO guide');

        $this->get(route('admin.articles.edit', $article))
            ->assertOk()
            ->assertSee('Explain the research and editorial steps.');
    }

    public function test_writer_can_only_edit_assigned_article_and_must_submit_for_review(): void
    {
        $writer = User::factory()->create(['role' => UserRole::ContentWriter]);
        $otherWriter = User::factory()->create(['role' => UserRole::ContentWriter]);
        $article = $this->article(['assigned_writer_id' => $writer->id]);
        $otherArticle = $this->article(['assigned_writer_id' => $otherWriter->id]);

        $this->actingAs($writer)
            ->get(route('admin.articles.edit', $otherArticle))
            ->assertForbidden();

        $this->actingAs($writer)
            ->put(route('admin.articles.update', $article), [
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'content' => '<p>Draft <strong>content</strong></p><script>alert(1)</script><a href="javascript:alert(2)">unsafe</a>',
                'target_keyword' => 'safe articles',
                'meta_title' => 'Safe articles',
                'meta_description' => 'A short and safe description.',
                'internal_links' => 'https://example.test/related',
            ])
            ->assertRedirect(route('admin.articles.edit', $article))
            ->assertSessionHasNoErrors();

        $article->refresh();
        $this->assertStringContainsString('<strong>content</strong>', $article->content);
        $this->assertStringNotContainsString('<script', $article->content);
        $this->assertStringNotContainsString('javascript:', $article->content);

        $this->post(route('admin.articles.submit', $article))
            ->assertRedirect(route('admin.articles.index'));
        $this->assertDatabaseHas('blog_posts', ['id' => $article->id, 'status' => 'in_review']);

        $this->get(route('admin.articles.edit', $article))->assertForbidden();
        $this->post(route('admin.articles.publish', $article))->assertForbidden();
    }

    public function test_seo_manager_can_request_changes_or_approve_but_only_super_admin_can_publish(): void
    {
        $seoManager = User::factory()->create(['role' => UserRole::SeoManager]);
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $writer = User::factory()->create(['role' => UserRole::ContentWriter]);
        $article = $this->article([
            'assigned_writer_id' => $writer->id,
            'status' => 'in_review',
            'content' => '<p>Sanitized article content.</p>',
            'content_format' => 'html',
        ]);

        $this->actingAs($seoManager)
            ->post(route('admin.articles.review', $article), [
                'action' => 'request_changes',
                'review_note' => 'Please add two supporting examples.',
            ])->assertRedirect(route('admin.articles.index'));

        $this->assertDatabaseHas('blog_posts', [
            'id' => $article->id,
            'status' => 'changes_requested',
            'review_note' => 'Please add two supporting examples.',
        ]);

        $this->actingAs($writer)
            ->get(route('admin.articles.edit', $article))
            ->assertOk()
            ->assertSee('Please add two supporting examples.');

        $article->refresh()->update(['status' => 'in_review']);
        $this->actingAs($seoManager)
            ->post(route('admin.articles.review', $article), ['action' => 'approve'])
            ->assertRedirect(route('admin.articles.index'));

        $this->post(route('admin.articles.publish', $article))->assertForbidden();
        $this->assertDatabaseHas('blog_posts', ['id' => $article->id, 'status' => 'approved']);

        $this->actingAs($superAdmin)
            ->post(route('admin.articles.publish', $article))
            ->assertRedirect(route('admin.articles.index'));

        $this->assertDatabaseHas('blog_posts', ['id' => $article->id, 'status' => 'published']);
        $this->get(route('blog.show', $article->slug))
            ->assertOk()
            ->assertSee('<p>Sanitized article content.</p>', false)
            ->assertDontSee('alert(1)', false)
            ->assertDontSee('javascript:', false);
    }

    public function test_seo_manager_and_writer_cannot_access_super_admin_writer_management(): void
    {
        $seoManager = User::factory()->create(['role' => UserRole::SeoManager]);
        $writer = User::factory()->create(['role' => UserRole::ContentWriter]);

        $this->actingAs($seoManager)->get(route('admin.writers.index'))->assertForbidden();
        $this->actingAs($writer)->get(route('admin.writers.index'))->assertForbidden();
        $this->actingAs($writer)->get(route('admin.dashboard'))->assertForbidden();
    }

    private function article(array $attributes = []): BlogPost
    {
        static $sequence = 0;
        $sequence++;

        return BlogPost::create(array_merge([
            'title' => 'Assigned Article '.$sequence,
            'slug' => 'assigned-article-'.$sequence,
            'excerpt' => 'Article summary.',
            'content' => '',
            'content_format' => 'html',
            'status' => 'draft',
        ], $attributes));
    }
}
