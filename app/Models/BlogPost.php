<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogPost extends Model
{
    /** @use HasFactory<BlogPostFactory> */
    use HasFactory;

    protected $fillable = [
        'author_id',
        'assigned_writer_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content_brief',
        'content',
        'content_format',
        'featured_image_path',
        'target_keyword',
        'internal_links',
        'meta_title',
        'meta_description',
        'status',
        'published_at',
        'review_note',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'internal_links' => 'array',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function assignedWriter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_writer_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'category_id');
    }
}
