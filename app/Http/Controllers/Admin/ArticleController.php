<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\ArticleCategory;
use App\Models\BlogPost;
use App\Models\User;
use App\Support\ArticleContentSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['draft', 'in_review', 'changes_requested', 'approved', 'published'])],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $articles = $this->visibleArticles($request->user())
            ->with(['assignedWriter', 'category'])
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['q'] ?? null, fn ($query, string $term) => $query->where(fn ($query) => $query
                ->where('title', 'like', '%'.$term.'%')
                ->orWhere('target_keyword', 'like', '%'.$term.'%')))
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.articles.index', compact('articles', 'filters'));
    }

    public function create(Request $request): View
    {
        $this->authorizeArticleManagement($request->user());

        return view('admin.articles.form', [
            'article' => new BlogPost(['status' => 'draft', 'content_format' => 'html']),
            'categories' => ArticleCategory::orderBy('name')->get(),
            'writers' => $this->writers(),
            'editorContent' => '',
            'canManageBrief' => true,
        ]);
    }

    public function store(Request $request, ArticleContentSanitizer $sanitizer): RedirectResponse
    {
        $this->authorizeArticleManagement($request->user());
        $validated = $this->validateArticle($request);
        $attributes = [
            ...$this->articleAttributes($request, $validated),
            'author_id' => $request->user()->id,
            'status' => 'draft',
            'content_format' => 'html',
            'content' => $sanitizer->sanitize($validated['content'] ?? ''),
        ];

        if ($request->hasFile('featured_image')) {
            $attributes['featured_image_path'] = $request->file('featured_image')->store('articles', 'public');
        }

        $article = BlogPost::create($attributes);

        return redirect()->route('admin.articles.edit', $article)
            ->with('status', 'Article brief saved.');
    }

    public function edit(Request $request, BlogPost $article, ArticleContentSanitizer $sanitizer): View
    {
        $this->authorizeArticleEdit($request->user(), $article);

        return view('admin.articles.form', [
            'article' => $article,
            'categories' => ArticleCategory::orderBy('name')->get(),
            'writers' => $this->writers(),
            'editorContent' => $sanitizer->sanitize($article->content),
            'canManageBrief' => $request->user()->role->canManageArticles(),
        ]);
    }

    public function update(Request $request, BlogPost $article, ArticleContentSanitizer $sanitizer): RedirectResponse
    {
        $this->authorizeArticleEdit($request->user(), $article);
        $validated = $this->validateArticle($request);
        $attributes = $this->articleAttributes($request, $validated);
        $attributes['content'] = $sanitizer->sanitize($validated['content'] ?? '');
        $attributes['content_format'] = 'html';

        if ($article->status === 'published' && ! $request->user()->role->isSuperAdmin()) {
            throw ValidationException::withMessages([
                'status' => 'Published content can only be edited by a super administrator.',
            ]);
        }

        if ($request->user()->role === UserRole::ContentWriter) {
            unset($attributes['assigned_writer_id'], $attributes['category_id'], $attributes['content_brief']);
        } elseif ($article->status === 'in_review') {
            throw ValidationException::withMessages([
                'status' => 'An article in review cannot be edited until the reviewer requests changes.',
            ]);
        }

        if ($request->hasFile('featured_image')) {
            $oldImage = $article->featured_image_path;
            $attributes['featured_image_path'] = $request->file('featured_image')->store('articles', 'public');
            if ($oldImage) {
                Storage::disk('public')->delete($oldImage);
            }
        }

        $article->update($attributes);

        return redirect()->route('admin.articles.edit', $article)
            ->with('status', 'Article saved.');
    }

    public function submit(Request $request, BlogPost $article): RedirectResponse
    {
        abort_unless(
            $request->user()->role === UserRole::ContentWriter
                && $article->assigned_writer_id === $request->user()->id,
            403,
        );

        if (! in_array($article->status, ['draft', 'changes_requested'], true)) {
            throw ValidationException::withMessages(['status' => 'Only draft or changes-requested articles can be submitted.']);
        }

        if (trim(strip_tags($article->content)) === '') {
            throw ValidationException::withMessages(['content' => 'Add article content before submitting it for review.']);
        }

        $article->update(['status' => 'in_review']);

        return redirect()->route('admin.articles.index')->with('status', 'Article submitted for SEO review.');
    }

    public function review(Request $request, BlogPost $article): RedirectResponse
    {
        abort_unless($request->user()->role->canManageArticles(), 403);

        $validated = $request->validate([
            'action' => ['required', Rule::in(['approve', 'request_changes'])],
            'review_note' => ['required_if:action,request_changes', 'nullable', 'string', 'max:2000'],
        ]);

        if ($article->status !== 'in_review') {
            throw ValidationException::withMessages(['status' => 'Only submitted articles can be reviewed.']);
        }

        $article->update([
            'status' => $validated['action'] === 'approve' ? 'approved' : 'changes_requested',
            'review_note' => $validated['review_note'] ?? null,
        ]);

        return redirect()->route('admin.articles.index')->with(
            'status',
            $validated['action'] === 'approve' ? 'Article approved for publishing.' : 'Changes requested from the writer.',
        );
    }

    public function publish(Request $request, BlogPost $article): RedirectResponse
    {
        abort_unless($request->user()->role->isSuperAdmin(), 403);

        if ($article->status !== 'approved') {
            throw ValidationException::withMessages(['status' => 'Only SEO-approved articles can be published.']);
        }

        $article->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return redirect()->route('admin.articles.index')->with('status', 'Article published.');
    }

    public function writerDashboard(Request $request): View
    {
        $articles = BlogPost::where('assigned_writer_id', $request->user()->id)
            ->with('category')
            ->latest('updated_at')
            ->paginate(15);

        return view('writer.dashboard', compact('articles'));
    }

    public function seoDashboard(): View
    {
        return view('seo.dashboard', [
            'counts' => [
                'draft' => BlogPost::where('status', 'draft')->count(),
                'in_review' => BlogPost::where('status', 'in_review')->count(),
                'changes_requested' => BlogPost::where('status', 'changes_requested')->count(),
                'approved' => BlogPost::where('status', 'approved')->count(),
                'published' => BlogPost::where('status', 'published')->count(),
            ],
            'writers' => User::where('role', UserRole::ContentWriter)->withCount('assignedArticles')->orderBy('name')->get(),
        ]);
    }

    private function visibleArticles(User $user)
    {
        return $user->role === UserRole::ContentWriter
            ? BlogPost::where('assigned_writer_id', $user->id)
            : BlogPost::query();
    }

    private function writers()
    {
        return User::where('role', UserRole::ContentWriter)->orderBy('name')->get(['id', 'name', 'email']);
    }

    private function authorizeArticleManagement(User $user): void
    {
        abort_unless($user->role->canManageArticles(), 403);
    }

    private function authorizeArticleEdit(User $user, BlogPost $article): void
    {
        if ($user->role === UserRole::ContentWriter) {
            abort_unless($article->assigned_writer_id === $user->id, 403);
            abort_unless(in_array($article->status, ['draft', 'changes_requested'], true), 403);

            return;
        }

        $this->authorizeArticleManagement($user);

        if ($user->role === UserRole::SeoManager) {
            abort_unless(in_array($article->status, ['draft', 'changes_requested'], true), 403);
        }
    }

    private function validateArticle(Request $request): array
    {
        $manager = $request->user()->role->canManageArticles();

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('blog_posts', 'slug')->ignore($request->route('article'))],
            'excerpt' => ['required', 'string', 'max:320'],
            'content_brief' => [$manager ? 'nullable' : 'prohibited', 'string', 'max:10000'],
            'content' => ['nullable', 'string', 'max:150000'],
            'category_id' => [$manager ? 'nullable' : 'prohibited', 'integer', 'exists:article_categories,id'],
            'target_keyword' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:160'],
            'internal_links' => ['nullable', 'string', 'max:10000'],
            'assigned_writer_id' => [$manager ? 'nullable' : 'prohibited', 'integer', Rule::exists('users', 'id')->where('role', UserRole::ContentWriter->value)],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
    }

    private function articleAttributes(Request $request, array $validated): array
    {
        $slug = $validated['slug'] ?? $this->uniqueSlug($validated['title'], $request->route('article'));
        $links = preg_split('/\r\n|\r|\n/', $validated['internal_links'] ?? '', -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $links = array_map('trim', $links);
        $links = array_values(array_filter($links, fn (string $link) => $link !== ''));

        validator(['links' => $links], [
            'links' => ['array', 'max:20'],
            'links.*' => ['url', 'max:2048'],
        ])->validate();

        return [
            'title' => $validated['title'],
            'slug' => $slug,
            'excerpt' => $validated['excerpt'],
            'content_brief' => $validated['content_brief'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'target_keyword' => $validated['target_keyword'] ?? null,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'internal_links' => $links,
            'assigned_writer_id' => $validated['assigned_writer_id'] ?? null,
        ];
    }

    private function uniqueSlug(string $title, ?BlogPost $article = null): string
    {
        $baseSlug = Str::slug($title) ?: 'article';
        $slug = $baseSlug;
        $suffix = 2;

        while (BlogPost::where('slug', $slug)
            ->when($article, fn ($query) => $query->where('id', '!=', $article->id))
            ->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        return $slug;
    }
}
