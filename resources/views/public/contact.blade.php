@extends('layouts.site')

@section('title', 'Contact LINKVIO')

@section('content')
    <section class="page-hero">
        <div class="container"><p class="eyebrow">Get in touch</p><h1>How can we help?</h1><p>Send the LINKVIO team a message. We will keep your inquiry securely in the platform for follow-up.</p></div>
    </section>
    <section class="section-space">
        <div class="container form-page">
            <form method="POST" action="{{ route('contact.store') }}" class="form-panel form-stack">
                @csrf
                <div><label class="form-label" for="name">Your name</label><input class="form-control" id="name" name="name" value="{{ old('name', auth()->user()?->name) }}" required maxlength="120" autocomplete="name">@error('name')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div><label class="form-label" for="email">Email address</label><input class="form-control" id="email" name="email" type="email" value="{{ old('email', auth()->user()?->email) }}" required maxlength="255" autocomplete="email">@error('email')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div><label class="form-label" for="subject">Subject</label><input class="form-control" id="subject" name="subject" value="{{ old('subject') }}" required maxlength="160">@error('subject')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div><label class="form-label" for="message">Message</label><textarea class="form-control" id="message" name="message" rows="6" required maxlength="5000">{{ old('message') }}</textarea>@error('message')<p class="field-error">{{ $message }}</p>@enderror</div>
                <button class="btn btn-primary align-self-start" type="submit">Send message</button>
            </form>
        </div>
    </section>
@endsection
