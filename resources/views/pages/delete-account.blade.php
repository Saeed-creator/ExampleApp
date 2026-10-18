@extends('pages.layout')
@section('title', 'Delete your account')
@section('content')
<h1>Delete your account</h1>
<p>You can permanently delete your {{ config('app.name') }} account and all data linked to it.</p>

<h2>In the app</h2>
<p>Open <strong>Profile</strong> and tap <strong>Delete Account</strong>, then confirm.</p>

<h2>Without the app</h2>
<p>Enter the email and password of your account below. Your account is deleted immediately.</p>

<div class="card">
    <form method="POST" action="{{ url('/delete-account') }}">
        @csrf
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
        <label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        @foreach ($errors->all() as $error)
            <p class="error">{{ $error }}</p>
        @endforeach
        <button class="danger" type="submit">Permanently delete my account</button>
    </form>
</div>
<p class="muted">Forgot your password? Email <a href="mailto:{{ config('zodiac.support_email') }}">{{ config('zodiac.support_email') }}</a> from the address you registered with.</p>

<h2>What is deleted</h2>
<p>Your profile (name, email, date of birth, language, time zone), zodiac data, login tokens, and push-notification tokens are deleted immediately and permanently. Nothing is retained.</p>
@endsection
