@extends('pages.layout')
@section('title', 'Support')
@section('content')
<h1>Support</h1>
<p>Need help with {{ config('app.name') }}? Write to us and we will reply as soon as we can.</p>
<div class="card">
    <p><strong>Email:</strong> <a href="mailto:{{ config('zodiac.support_email') }}">{{ config('zodiac.support_email') }}</a></p>
</div>
<p>To delete your account, see <a href="{{ url('/delete-account') }}">Delete account</a>.</p>
@endsection
