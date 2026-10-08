<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvertiserProfile extends Model
{
    protected $fillable = [
        'company_name',
        'business_type',
        'country_code',
        'phone',
        'currency',
        'monthly_budget',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
