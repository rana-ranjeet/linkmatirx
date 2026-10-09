<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function index(Request $request): View
    {
        $tickets = ContactInquiry::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('publisher.support.index', compact('tickets'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        ContactInquiry::create([
            ...$validated,
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'user_id' => $request->user()->id,
            'status' => 'open',
        ]);

        return redirect()->route('publisher.support.index')
            ->with('status', 'Your support request has been submitted.');
    }
}
