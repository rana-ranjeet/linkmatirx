<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Website;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        return match ($request->user()->role) {
            UserRole::Advertiser => redirect()->route('advertiser.dashboard'),
            UserRole::Publisher => redirect()->route('publisher.dashboard'),
            UserRole::Admin => redirect()->route('admin.dashboard'),
        };
    }

    public function show(Request $request): View
    {
        $websiteStats = [];
        $favoriteCount = 0;

        if ($request->user()->role === UserRole::Publisher) {
            $websites = Website::where('publisher_id', $request->user()->id);
            $websiteStats = [
                'total' => (clone $websites)->count(),
                'pending' => (clone $websites)->where('status', 'pending')->count(),
                'approved' => (clone $websites)->where('status', 'approved')->where('is_active', true)->count(),
            ];
        } elseif ($request->user()->role === UserRole::Admin) {
            $websiteStats = [
                'pending' => Website::where('status', 'pending')->count(),
            ];
        } elseif ($request->user()->role === UserRole::Advertiser) {
            $favoriteCount = $request->user()->favoriteWebsites()->count();
        }

        return view('dashboard', [
            'role' => $request->user()->role,
            'websiteStats' => $websiteStats,
            'favoriteCount' => $favoriteCount,
        ]);
    }
}
