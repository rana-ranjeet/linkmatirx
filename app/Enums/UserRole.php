<?php

namespace App\Enums;

enum UserRole: string
{
    case Advertiser = 'advertiser';
    case Publisher = 'publisher';
    case Admin = 'admin';
    case SuperAdmin = 'super_admin';
    case SeoManager = 'seo_manager';
    case ContentWriter = 'content_writer';

    public function isSuperAdmin(): bool
    {
        return in_array($this, [self::Admin, self::SuperAdmin], true);
    }

    public function canManageArticles(): bool
    {
        return $this->isSuperAdmin() || $this === self::SeoManager;
    }
}
