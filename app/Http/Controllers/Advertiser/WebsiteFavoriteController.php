<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\Website;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebsiteFavoriteController extends Controller
{
    public function index(Request $request): View
    {
        $websites = $request->user()->favoriteWebsites()
            ->approved()
            ->with(['category', 'publisher'])
            ->latest('websites.created_at')
            ->paginate(12);

        return view('advertiser.favorites.index', compact('websites'));
    }

    public function store(Request $request, Website $website): RedirectResponse
    {
        abort_unless(Website::approved()->whereKey($website->getKey())->exists(), 404);

        $request->user()->favoriteWebsites()->syncWithoutDetaching([$website->id]);

        return back()->with('status', 'Website saved to your favorites.');
    }

    public function destroy(Request $request, Website $website): RedirectResponse
    {
        $request->user()->favoriteWebsites()->detach($website->id);

        return back()->with('status', 'Website removed from your favorites.');
    }
}
