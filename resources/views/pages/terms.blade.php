@extends('pages.layout')
@section('title', 'Terms of Service')
@section('content')
<h1>Terms of Service</h1>
<p class="muted">Last updated: {{ date('F j, Y') }} · {{ config('zodiac.company_name') }}</p>

<p>By creating an account or using {{ config('app.name') }} (“the app”) you agree to these terms.</p>

<h2>Entertainment only</h2>
<p>Zodiac predictions are provided for entertainment. They are not professional advice of any kind (medical, legal, financial or otherwise) and should not be the basis for important decisions.</p>

<h2>Your account</h2>
<ul>
    <li>You must be at least {{ config('zodiac.minimum_age') }} years old.</li>
    <li>Provide accurate information and keep your password private; you are responsible for activity on your account.</li>
    <li>You can delete your account at any time from the app or from <a href="{{ url('/delete-account') }}">the account-deletion page</a>.</li>
</ul>

<h2>Acceptable use</h2>
<p>Do not misuse the service: no attempts to break, overload or gain unauthorised access to it, no automated scraping, and no use that violates the law.</p>

<h2>Content and availability</h2>
<p>We may change, suspend or discontinue features at any time. We try to keep the app available but do not guarantee uninterrupted service.</p>

<h2>Liability</h2>
<p>To the extent permitted by law, the app is provided “as is” and we are not liable for indirect or consequential losses arising from its use.</p>

<h2>Termination</h2>
<p>We may suspend or remove accounts that break these terms. You may stop using the app and delete your account at any time.</p>

<h2>Privacy</h2>
<p>How we handle your data is described in our <a href="{{ url('/privacy') }}">Privacy Policy</a>.</p>

<h2>Changes and contact</h2>
<p>We may update these terms; the current version is always on this page. Questions: <a href="mailto:{{ config('zodiac.support_email') }}">{{ config('zodiac.support_email') }}</a>.</p>
@endsection
