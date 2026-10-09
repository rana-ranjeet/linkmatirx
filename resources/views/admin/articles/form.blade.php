@extends('layouts.site')

@section('title', $article->exists ? 'Edit article' : 'Create article brief')

@section('content')
    <section class="page-hero">
        <div class="container">
            <a class="back-link" href="{{ route('admin.articles.index') }}">&larr; Articles</a>
            <p class="eyebrow mt-3">Article workflow · {{ str($article->status)->replace('_', ' ')->headline() }}</p>
            <h1>{{ $article->exists ? 'Edit article' : 'Create article brief' }}</h1>
            <p>Writers can only edit articles assigned to their account. SEO approval is required before a super administrator can publish.</p>
        </div>
    </section>
    <section class="section-space">
        <div class="container blog-editor-wrap">
            @if ($errors->any())
                <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            @if ($article->review_note)
                <div class="alert alert-warning"><strong>Reviewer feedback:</strong> {{ $article->review_note }}</div>
            @endif

            @if ($article->exists && $article->featured_image_path)
                <div class="mb-3"><img class="article-featured-preview" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($article->featured_image_path) }}" alt="Current featured image"></div>
            @endif

            <form method="POST" action="{{ $article->exists ? route('admin.articles.update', $article) : route('admin.articles.store') }}" enctype="multipart/form-data" class="form-panel form-stack article-editor-form">
                @csrf
                @if ($article->exists) @method('PUT') @endif
                <div class="form-row">
                    <div><label class="form-label" for="title">Title</label><input class="form-control" id="title" name="title" maxlength="255" value="{{ old('title', $article->title) }}" required>@error('title')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div><label class="form-label" for="slug">Slug</label><input class="form-control" id="slug" name="slug" maxlength="255" value="{{ old('slug', $article->slug) }}" placeholder="generated-from-title">@error('slug')<p class="field-error">{{ $message }}</p>@enderror</div>
                </div>
                <div><label class="form-label" for="excerpt">Summary</label><textarea class="form-control" id="excerpt" name="excerpt" rows="3" maxlength="320" required>{{ old('excerpt', $article->excerpt) }}</textarea>@error('excerpt')<p class="field-error">{{ $message }}</p>@enderror</div>

                @if ($canManageBrief)
                    <div><label class="form-label" for="content_brief">Writer brief / instructions</label><textarea class="form-control" id="content_brief" name="content_brief" rows="5" maxlength="10000">{{ old('content_brief', $article->content_brief) }}</textarea>@error('content_brief')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="form-row">
                        <div><label class="form-label" for="assigned_writer_id">Assigned writer</label><select class="form-select" id="assigned_writer_id" name="assigned_writer_id"><option value="">Unassigned</option>@foreach ($writers as $writer)<option value="{{ $writer->id }}" @selected((string) old('assigned_writer_id', $article->assigned_writer_id) === (string) $writer->id)>{{ $writer->name }} · {{ $writer->email }}</option>@endforeach</select>@error('assigned_writer_id')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <div><label class="form-label" for="category_id">Category</label><select class="form-select" id="category_id" name="category_id"><option value="">Select category</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id', $article->category_id) === (string) $category->id)>{{ $category->name }}</option>@endforeach</select>@error('category_id')<p class="field-error">{{ $message }}</p>@enderror</div>
                    </div>
                @elseif ($article->content_brief)
                    <div class="article-brief-panel"><strong>SEO manager brief</strong><p>{{ $article->content_brief }}</p></div>
                    <p class="form-hint">Category: {{ $article->category?->name ?? 'Not specified' }}</p>
                @endif

                <div>
                    <label class="form-label" for="content-editor">Article content</label>
                    <div class="rich-editor-toolbar" role="toolbar" aria-label="Article formatting">
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-editor-command="bold"><strong>B</strong></button>
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-editor-command="italic"><em>I</em></button>
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-editor-command="formatBlock" data-editor-value="h2">Heading</button>
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-editor-command="insertUnorderedList">Bullets</button>
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-editor-command="formatBlock" data-editor-value="blockquote">Quote</button>
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-editor-command="createLink" data-editor-value="prompt">Link</button>
                    </div>
                    <div id="content-editor" class="rich-editor" data-rich-editor role="textbox" aria-multiline="true" aria-label="Article content">{!! old('content') ? app(\App\Support\ArticleContentSanitizer::class)->sanitize(old('content')) : $editorContent !!}</div>
                    <textarea class="form-control rich-editor-fallback" id="content" name="content" data-rich-editor-source rows="12">{{ old('content', $article->content) }}</textarea>
                    <span class="form-hint">Formatting is sanitized before saving. Only safe headings, paragraphs, lists, emphasis, quotes, code, and links are retained.</span>
                    @error('content')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-row">
                    <div><label class="form-label" for="target_keyword">Target keyword</label><input class="form-control" id="target_keyword" name="target_keyword" maxlength="255" value="{{ old('target_keyword', $article->target_keyword) }}">@error('target_keyword')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div><label class="form-label" for="featured_image">Featured image</label><input class="form-control" id="featured_image" name="featured_image" type="file" accept="image/jpeg,image/png,image/webp">@error('featured_image')<p class="field-error">{{ $message }}</p>@enderror</div>
                </div>
                <div class="form-row">
                    <div><label class="form-label" for="meta_title">Meta title</label><input class="form-control" id="meta_title" name="meta_title" maxlength="70" value="{{ old('meta_title', $article->meta_title) }}">@error('meta_title')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div><label class="form-label" for="meta_description">Meta description</label><textarea class="form-control" id="meta_description" name="meta_description" rows="2" maxlength="160">{{ old('meta_description', $article->meta_description) }}</textarea>@error('meta_description')<p class="field-error">{{ $message }}</p>@enderror</div>
                </div>
                <div><label class="form-label" for="internal_links">Internal links</label><textarea class="form-control" id="internal_links" name="internal_links" rows="3" placeholder="https://example.com/related-article&#10;https://example.com/another-page">{{ old('internal_links', implode("\n", $article->internal_links ?? [])) }}</textarea><span class="form-hint">One complete URL per line, up to 20 links.</span>@error('internal_links')<p class="field-error">{{ $message }}</p>@enderror</div>

                <div class="form-actions">
                    <a class="btn btn-outline-secondary" href="{{ route('admin.articles.index') }}">Back to articles</a>
                    <button class="btn btn-primary" type="submit">Save article</button>
                </div>
            </form>

            @if ($article->exists && auth()->user()->role === \App\Enums\UserRole::ContentWriter && in_array($article->status, ['draft', 'changes_requested'], true))
                <form method="POST" action="{{ route('admin.articles.submit', $article) }}" class="article-workflow-action">@csrf<button class="btn btn-success" type="submit">Submit to SEO review</button></form>
            @endif
            @if ($article->exists && auth()->user()->role->canManageArticles() && $article->status === 'in_review')
                <div class="article-review-actions">
                    <form method="POST" action="{{ route('admin.articles.review', $article) }}" class="form-panel form-stack">
                        @csrf<input type="hidden" name="action" value="request_changes">
                        <label class="form-label" for="review_note">Request changes</label>
                        <textarea class="form-control" id="review_note" name="review_note" rows="3" maxlength="2000" required placeholder="Explain the changes needed"></textarea>
                        <button class="btn btn-outline-danger align-self-start" type="submit">Request changes</button>
                    </form>
                    <form method="POST" action="{{ route('admin.articles.review', $article) }}" class="article-workflow-action">
                        @csrf<input type="hidden" name="action" value="approve">
                        <button class="btn btn-success" type="submit">Approve article</button>
                    </form>
                </div>
            @endif
            @if ($article->exists && auth()->user()->role->isSuperAdmin() && $article->status === 'approved')
                <form method="POST" action="{{ route('admin.articles.publish', $article) }}" class="article-workflow-action">
                    @csrf<button class="btn btn-primary" type="submit">Publish article</button>
                </form>
            @endif
        </div>
    </section>
@endsection
