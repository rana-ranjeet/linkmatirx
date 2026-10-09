<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'action' => ['nullable', Rule::in(['change_role', 'suspend', 'reactivate'])],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $logs = DB::table('admin_audit_logs')
            ->leftJoin('users as actors', 'admin_audit_logs.actor_id', '=', 'actors.id')
            ->leftJoin('users as targets', 'admin_audit_logs.target_user_id', '=', 'targets.id')
            ->select([
                'admin_audit_logs.*',
                'actors.name as actor_name',
                'actors.email as actor_email',
                'targets.name as target_name',
                'targets.email as target_email',
            ])
            ->when($filters['action'] ?? null, fn ($query, string $action) => $query->where('admin_audit_logs.action', $action))
            ->when($filters['q'] ?? null, fn ($query, string $term) => $query->where(function ($query) use ($term) {
                $query->where('actors.email', 'like', '%'.$term.'%')
                    ->orWhere('targets.email', 'like', '%'.$term.'%')
                    ->orWhere('admin_audit_logs.reason', 'like', '%'.$term.'%');
            }))
            ->latest('admin_audit_logs.created_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.audit.index', compact('logs', 'filters'));
    }
}
