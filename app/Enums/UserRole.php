<?php

namespace App\Enums;

enum UserRole: string
{
    case Advertiser = 'advertiser';
    case Publisher = 'publisher';
    case Admin = 'admin';
}
