<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class BlogController extends Controller
{
    public function index(): View
    {
        $posts = BlogPost::published()
            ->with('author')
            ->latest('published_at')
            ->paginate(12);

        return view('public.blog.index', compact('posts'));
    }

    public function show(string $slug): View
    {
        $post = BlogPost::published()->where('slug', $slug)->firstOrFail();

        return view('public.blog.show', compact('post'));
    }

    public function sitemap(): Response
    {
        $posts = BlogPost::published()->latest('published_at')->get(['slug', 'updated_at']);

        return response()
            ->view('public.blog.sitemap', compact('posts'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
