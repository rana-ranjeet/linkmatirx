<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewWebsiteRequest;
use App\Models\Website;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WebsiteModerationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:pending,approved,rejected'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $websites = Website::with(['publisher', 'category', 'reviewer'])
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['q'] ?? null, fn ($query, string $term) => $query->where('domain', 'like', '%'.$term.'%'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.websites.index', compact('websites', 'filters'));
    }

    public function update(ReviewWebsiteRequest $request, Website $website): RedirectResponse
    {
        abort_unless($website->status === 'pending', 409, 'Only pending websites can be reviewed.');

        DB::transaction(function () use ($request, $website) {
            $website->update([
                'status' => $request->validated('status'),
                'moderation_note' => $request->validated('status') === 'rejected'
                    ? $request->validated('moderation_note')
                    : null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);
        });

        return redirect()->route('admin.websites.index')
            ->with('status', 'Website review saved.');
    }
}
