<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $publisher = $request->user();
        $websites = Website::where('publisher_id', $publisher->id);
        $marketplaceWebsites = Website::approved()->where('publisher_id', $publisher->id);

        return view('publisher.dashboard', [
            'websiteStats' => [
                'total' => (clone $websites)->count(),
                'pending' => (clone $websites)->where('status', 'pending')->count(),
                'approved' => (clone $websites)->where('status', 'approved')->count(),
                'available' => (clone $marketplaceWebsites)->where('is_active', true)->count(),
                'paused' => (clone $marketplaceWebsites)->where('is_active', false)->count(),
            ],
            'profile' => $publisher->publisherProfile,
            'openSupportCount' => ContactInquiry::where('user_id', $publisher->id)->where('status', 'open')->count(),
            'recentWebsites' => (clone $websites)->latest()->limit(5)->get(),
        ]);
    }
}
