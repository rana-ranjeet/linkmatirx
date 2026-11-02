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
            'verification_status' => ['nullable', 'in:pending,verified,failed'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $websites = Website::with(['publisher', 'category', 'reviewer', 'verificationOverrider'])
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['verification_status'] ?? null, fn ($query, string $status) => $query->where('verification_status', $status))
            ->when($filters['q'] ?? null, fn ($query, string $term) => $query->where('domain', 'like', '%'.$term.'%'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.websites.index', compact('websites', 'filters'));
    }

    public function update(ReviewWebsiteRequest $request, Website $website): RedirectResponse
    {
        $canRecordLegacyOverride = $website->status === 'approved'
            && $website->verification_status !== 'verified'
            && $website->verification_override_at === null
            && $request->validated('status') === 'approved';

        abort_unless($website->status === 'pending' || $canRecordLegacyOverride, 409, 'Only pending websites or approved, unverified websites without a recorded override can be reviewed.');

        $overrideRecorded = DB::transaction(function () use ($request, $website) {
            $website = Website::query()->lockForUpdate()->findOrFail($website->id);
            $isOverride = $request->validated('status') === 'approved'
                && $website->verification_status !== 'verified';
            $canRecordLegacyOverride = $website->status === 'approved'
                && $website->verification_status !== 'verified'
                && $website->verification_override_at === null
                && $request->validated('status') === 'approved';

            abort_unless(
                $website->status === 'pending' || $canRecordLegacyOverride,
                409,
                'Only pending websites or approved, unverified websites without a recorded override can be reviewed.',
            );

            if ($isOverride && (! $request->boolean('verification_override') || blank($request->validated('verification_override_reason')))) {
                abort(422, 'An explicit verification override and reason are required.');
            }

            $before = [
                'listing_status' => $website->status,
                'verification_status' => $website->verification_status,
            ];

            $website->update([
                'status' => $request->validated('status'),
                'moderation_note' => $request->validated('status') === 'rejected'
                    ? $request->validated('moderation_note')
                    : null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'verification_override_by' => $isOverride ? $request->user()->id : null,
                'verification_override_at' => $isOverride ? now() : null,
                'verification_override_reason' => $isOverride ? $request->validated('verification_override_reason') : null,
            ]);

            if ($isOverride) {
                DB::table('admin_audit_logs')->insert([
                    'actor_id' => $request->user()->id,
                    'target_user_id' => $website->publisher_id,
                    'action' => 'website_approval_override',
                    'reason' => $request->validated('verification_override_reason'),
                    'old_values' => json_encode($before, JSON_THROW_ON_ERROR),
                    'new_values' => json_encode([
                        'website_id' => $website->id,
                        'domain' => $website->domain,
                        'listing_status' => $website->status,
                        'verification_status' => $website->verification_status,
                        'override_at' => now()->toIso8601String(),
                    ], JSON_THROW_ON_ERROR),
                    'created_at' => now(),
                ]);
            }

            return $isOverride;
        });

        return redirect()->route('admin.websites.index')
            ->with('status', 'Website review saved.'.($overrideRecorded ? ' The verification override was recorded in the audit log.' : ''));
    }
}
