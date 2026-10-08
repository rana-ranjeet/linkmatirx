<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Website;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'approvedWebsiteCount' => Website::approved()->count(),
            'publisherCount' => User::where('role', UserRole::Publisher->value)
                ->where('is_suspended', false)
                ->count(),
            'featuredWebsites' => Website::approved()
                ->with(['category', 'publisher'])
                ->latest()
                ->limit(6)
                ->get(),
        ]);
    }
}
