<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlogPostController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['draft', 'published'])],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $posts = BlogPost::with('author')
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['q'] ?? null, fn ($query, string $term) => $query->where(function ($query) use ($term) {
                $query->where('title', 'like', '%'.$term.'%')
                    ->orWhere('slug', 'like', '%'.$term.'%');
            }))
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.blog.index', compact('posts', 'filters'));
    }

    public function create(): View
    {
        return view('admin.blog.form', [
            'post' => new BlogPost(['status' => 'draft']),
            'heading' => 'Write a blog post',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePost($request);
        $validated['author_id'] = $request->user()->id;
        $validated['slug'] = $validated['slug'] ?? $this->uniqueSlug($validated['title']);
        $validated['published_at'] = $validated['status'] === 'published'
            ? ($validated['published_at'] ?? now())
            : null;

        $post = BlogPost::create($validated);

        return redirect()->route('admin.blog.edit', $post)
            ->with('status', 'Blog post saved.');
    }

    public function edit(BlogPost $post): View
    {
        return view('admin.blog.form', [
            'post' => $post,
            'heading' => 'Edit blog post',
        ]);
    }

    public function update(Request $request, BlogPost $post): RedirectResponse
    {
        $validated = $this->validatePost($request, $post);
        $validated['slug'] = $validated['slug'] ?? $this->uniqueSlug($validated['title'], $post);
        $validated['published_at'] = $validated['status'] === 'published'
            ? ($validated['published_at'] ?? $post->published_at ?? now())
            : null;

        $post->update($validated);

        return redirect()->route('admin.blog.edit', $post)
            ->with('status', 'Blog post updated.');
    }

    public function destroy(BlogPost $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('admin.blog.index')
            ->with('status', 'Blog post deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePost(Request $request, ?BlogPost $post = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('blog_posts', 'slug')->ignore($post),
            ],
            'excerpt' => ['required', 'string', 'max:320'],
            'content' => ['required', 'string', 'max:100000'],
            'meta_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:160'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
        ]);
    }

    private function uniqueSlug(string $title, ?BlogPost $post = null): string
    {
        $baseSlug = Str::slug($title) ?: 'article';
        $slug = $baseSlug;
        $suffix = 2;

        while (BlogPost::where('slug', $slug)
            ->when($post, fn ($query) => $query->where('id', '!=', $post->getKey()))
            ->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        return $slug;
    }
}
