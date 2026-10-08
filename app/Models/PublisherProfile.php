<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublisherProfile extends Model
{
    protected $fillable = [
        'business_name',
        'business_type',
        'country_code',
        'phone',
        'currency',
        'number_of_sites',
        'do_follow_links',
        'owns_sites',
        'already_selling_articles',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
