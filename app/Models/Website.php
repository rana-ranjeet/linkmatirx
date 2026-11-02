<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Website extends Model
{
    protected $hidden = [
        'verification_token',
    ];

    protected $fillable = [
        'publisher_id',
        'category_id',
        'domain',
        'description',
        'country_code',
        'language_code',
        'status',
        'is_active',
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
        'verification_status',
        'verified_at',
        'last_verification_attempt_at',
        'verification_override_by',
        'verification_override_at',
        'verification_override_reason',
    ];

    protected static function booted(): void
    {
        static::creating(function (Website $website) {
            $website->verification_token = bin2hex(random_bytes(32));
        });
    }

    protected function casts(): array
    {
        return [
            'metrics_updated_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'verified_at' => 'datetime',
            'last_verification_attempt_at' => 'datetime',
            'verification_override_at' => 'datetime',
        ];
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved')
            ->where('is_active', true)
            ->where(function (Builder $query) {
                $query->where('verification_status', 'verified')
                    ->orWhereNotNull('verification_override_at');
            });
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

    public function verificationOverrider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verification_override_by');
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'website_favorites', 'website_id', 'advertiser_id')
            ->withTimestamps();
    }
}
