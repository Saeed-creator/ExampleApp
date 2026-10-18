@extends('pages.layout')
@section('title', 'Privacy Policy')
@section('content')
<h1>Privacy Policy</h1>
<p class="muted">Last updated: {{ date('F j, Y') }} · {{ config('zodiac.company_name') }}</p>

<p>This policy explains what {{ config('app.name') }} (“the app”) collects, why, where it is kept, and how you can delete it.</p>

<h2>What we collect</h2>
<ul>
    <li><strong>Name</strong> and <strong>email address</strong> — to create your account and sign you in.</li>
    <li><strong>Password</strong> — stored only as a one-way hash; we cannot read it.</li>
    <li><strong>Date of birth</strong> — to work out your Western zodiac sign and Chinese zodiac animal.</li>
    <li><strong>Zodiac signs</strong> (Western sign and Chinese animal) — calculated from your date of birth, used to choose your daily prediction.</li>
    <li><strong>Language</strong> and <strong>time zone</strong> — to show predictions in your language and for the correct day, and to send your daily notification at a suitable local time.</li>
    <li><strong>Login token</strong> — a random token that keeps you signed in on your device.</li>
    <li><strong>Push-notification token and platform</strong> (Android/iOS) — only if you allow notifications, so we can deliver your daily prediction through Google Firebase Cloud Messaging / Apple Push Notification service.</li>
</ul>
<p>We do not collect your precise location, contacts, photos or payment information, and we do not sell your personal data.</p>

<h2>Why we use it</h2>
<p>Only to provide the app: creating your account, showing your daily prediction, sending the notifications you allowed, and keeping the service secure (for example, limiting repeated login attempts).</p>

<h2>Where it is stored and who processes it</h2>
<p>Your data is stored on our servers and database with our hosting provider. Push tokens are shared with Google (Firebase) and Apple solely to deliver notifications. We do not share your data with anyone else except when required by law.</p>

<h2>How long we keep it</h2>
<p>We keep your data for as long as your account exists. When you delete your account, your profile, date of birth, zodiac data, login tokens, push tokens are permanently deleted right away. We do not retain anything after deletion.</p>

<h2>Your choices and rights</h2>
<ul>
    <li><strong>Delete your account</strong> in the app (Profile → Delete Account) or on <a href="{{ url('/delete-account') }}">this page</a> without installing the app.</li>
    <li><strong>Correct</strong> your date of birth and language in the app’s profile page.</li>
    <li>Turn notifications off at any time in your device settings.</li>
    <li>Ask any privacy question, or request access to your data, at <a href="mailto:{{ config('zodiac.support_email') }}">{{ config('zodiac.support_email') }}</a>.</li>
</ul>

<h2>Children</h2>
<p>The app is not for people under {{ config('zodiac.minimum_age') }}. Registrations below that age are rejected. If you believe a child has created an account, contact us and we will delete it.</p>

<h2>Security</h2>
<p>All communication with our servers uses HTTPS and passwords are hashed. No system is perfectly secure, but we work to protect your data.</p>

<h2>Changes</h2>
<p>If this policy changes, the new version will be posted here with a new date.</p>

<h2>Contact</h2>
<p><a href="mailto:{{ config('zodiac.support_email') }}">{{ config('zodiac.support_email') }}</a></p>
@endsection
