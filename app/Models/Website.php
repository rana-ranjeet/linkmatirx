<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Website extends Model
{
    protected $fillable = [
        'publisher_id',
        'category_id',
        'domain',
        'description',
        'country_code',
        'language_code',
        'status',
        'guest_post_price_cents',
        'link_insertion_price_cents',
        'currency',
        'domain_rating',
        'domain_authority',
        'organic_traffic',
        'referring_domains',
        'metrics_provider',
        'metrics_updated_at',
        'reviewed_by',
        'reviewed_at',
        'moderation_note',
    ];

    protected function casts(): array
    {
        return [
            'metrics_updated_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved')->where('is_active', true);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'publisher_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(WebsiteCategory::class, 'category_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'website_favorites', 'website_id', 'advertiser_id')
            ->withTimestamps();
    }
}
