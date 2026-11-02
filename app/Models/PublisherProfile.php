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
        'payout_account_holder_name',
        'payout_bank_name',
        'payout_account_number',
        'payout_ifsc_code',
        'payout_account_type',
    ];

    protected function casts(): array
    {
        return [
            'payout_account_holder_name' => 'encrypted',
            'payout_account_number' => 'encrypted',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
