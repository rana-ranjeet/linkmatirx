@extends('layouts.site')

@section('title', 'Publisher support')

@section('content')
    <section class="page-hero">
        <div class="container">
            <a class="back-link" href="{{ route('publisher.dashboard') }}">&larr; Publisher dashboard</a>
            <p class="eyebrow mt-3">Publisher support</p>
            <h1>How can we help?</h1>
            <p>Send our team a support request. You can review your submitted requests below.</p>
        </div>
    </section>
    <section class="section-space">
        <div class="container support-layout">
            <form method="POST" action="{{ route('publisher.support.store') }}" class="form-panel form-stack">
                @csrf
                <h2>New support request</h2>
                <p>We’ll reply using the email address on your account: {{ auth()->user()->email }}</p>
                <div>
                    <label class="form-label" for="subject">Subject</label>
                    <input class="form-control" id="subject" name="subject" maxlength="160" value="{{ old('subject') }}" required>
                    @error('subject')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label" for="message">How can we help?</label>
                    <textarea class="form-control" id="message" name="message" rows="6" maxlength="5000" required>{{ old('message') }}</textarea>
                    @error('message')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <button class="btn btn-primary align-self-start" type="submit">Submit request</button>
            </form>
            <section class="admin-dashboard-panel">
                <h2>Your support requests</h2>
                @forelse ($tickets as $ticket)
                    <article class="publisher-ticket">
                        <div class="publisher-heading">
                            <strong>{{ $ticket->subject }}</strong>
                            <span class="status-badge {{ $ticket->status === 'open' ? 'status-pending' : 'status-approved' }}">{{ str($ticket->status)->headline() }}</span>
                        </div>
                        <p>{{ $ticket->message }}</p>
                        <small>Submitted {{ $ticket->created_at->toDayDateTimeString() }}</small>
                    </article>
                @empty
                    <p class="admin-empty-note">You have not submitted any support requests yet.</p>
                @endforelse
                <div class="pagination-wrap">{{ $tickets->links() }}</div>
            </section>
        </div>
    </section>
@endsection
