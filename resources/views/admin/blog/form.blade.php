@extends('layouts.site')

@section('title', $heading)

@section('content')
    <section class="page-hero">
        <div class="container">
            <p class="eyebrow"><a href="{{ route('admin.blog.index') }}">SEO blog</a> · Administration</p>
            <h1>{{ $heading }}</h1>
            <p>Use an original, useful title and a clear summary. Drafts are private until published.</p>
        </div>
    </section>
    <section class="section-space">
        <div class="container blog-editor-wrap">
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <p class="mb-1"><strong>Please fix the highlighted fields:</strong></p>
                    <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            <form method="POST" action="{{ $post->exists ? route('admin.blog.update', $post) : route('admin.blog.store') }}" class="form-panel form-stack blog-editor">
                @csrf
                @if ($post->exists)
                    @method('PUT')
                @endif

                <div class="form-row">
                    <div>
                        <label class="form-label" for="title">Article title</label>
                        <input class="form-control" id="title" name="title" value="{{ old('title', $post->title) }}" maxlength="255" required>
                        @error('title') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label" for="slug">URL slug</label>
                        <input class="form-control" id="slug" name="slug" value="{{ old('slug', $post->slug) }}" maxlength="255" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" placeholder="generated-from-title">
                        <span class="form-hint">Lowercase letters, numbers, and hyphens. Leave blank to generate from the title.</span>
                        @error('slug') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="form-label" for="excerpt">Article summary</label>
                    <textarea class="form-control blog-summary-input" id="excerpt" name="excerpt" rows="3" maxlength="320" required>{{ old('excerpt', $post->excerpt) }}</textarea>
                    <span class="form-hint">A useful summary for readers and search previews, up to 320 characters.</span>
                    @error('excerpt') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label" for="content">Article content</label>
                    <textarea class="form-control blog-content-input" id="content" name="content" rows="22" maxlength="100000" required>{{ old('content', $post->content) }}</textarea>
                    <span class="form-hint">Plain text; separate paragraphs with a blank line. Text is escaped when published.</span>
                    @error('content') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <section class="blog-seo-fields">
                    <h2>Search &amp; social preview</h2>
                    <div class="form-row">
                        <div>
                            <label class="form-label" for="meta_title">SEO title</label>
                            <input class="form-control" id="meta_title" name="meta_title" value="{{ old('meta_title', $post->meta_title) }}" maxlength="70">
                            <span class="form-hint">Up to 70 characters. Leave blank to use the article title.</span>
                            @error('meta_title') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label" for="meta_description">Meta description</label>
                            <textarea class="form-control" id="meta_description" name="meta_description" rows="3" maxlength="160">{{ old('meta_description', $post->meta_description) }}</textarea>
                            <span class="form-hint">Up to 160 characters. Leave blank to use the article summary.</span>
                            @error('meta_description') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                <div class="form-row">
                    <div>
                        <label class="form-label" for="status">Publishing status</label>
                        <select class="form-select" id="status" name="status" required>
                            @foreach (['draft' => 'Draft', 'published' => 'Published'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $post->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <span class="form-hint">Publishing makes this article public and eligible for the sitemap.</span>
                        @error('status') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label" for="published_at">Publish date</label>
                        <input class="form-control" id="published_at" name="published_at" type="datetime-local" value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}">
                        <span class="form-hint">Optional. Future dates schedule the article; leave blank to publish now.</span>
                        @error('published_at') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="form-actions">
                    <a class="btn btn-outline-secondary" href="{{ route('admin.blog.index') }}">Cancel</a>
                    <button class="btn btn-primary" type="submit">Save article</button>
                </div>
            </form>
        </div>
    </section>
@endsection
