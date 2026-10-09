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
            UserRole::Admin, UserRole::SuperAdmin => redirect()->route('admin.dashboard'),
            UserRole::SeoManager => redirect()->route('seo.dashboard'),
            UserRole::ContentWriter => redirect()->route('writer.dashboard'),
        };
    }

    public function show(Request $request): View
    {
        $websiteStats = [];
        $favoriteCount = 0;

        if ($request->user()->role->isSuperAdmin()) {
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
