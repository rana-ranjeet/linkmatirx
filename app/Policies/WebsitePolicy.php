<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Website;

class WebsitePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Publisher || $user->role->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Publisher;
    }

    public function update(User $user, Website $website): bool
    {
        return $user->role === UserRole::Publisher && $website->publisher_id === $user->id;
    }
}
