<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Website;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'userCounts' => [
                'total' => User::count(),
                'advertisers' => User::where('role', UserRole::Advertiser)->count(),
                'publishers' => User::where('role', UserRole::Publisher)->count(),
                'suspended' => User::where('is_suspended', true)->count(),
            ],
            'websiteCounts' => [
                'total' => Website::count(),
                'pending' => Website::where('status', 'pending')->count(),
                'approved' => Website::where('status', 'approved')->where('is_active', true)->count(),
                'rejected' => Website::where('status', 'rejected')->count(),
            ],
            'recentUsers' => User::latest()->limit(5)->get(),
            'pendingWebsites' => Website::with('publisher')
                ->where('status', 'pending')
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}
