<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · {{ config('app.name') }}</title>
    <style>
        :root { --bg:#fafaf7; --fg:#1d1d22; --muted:#5f6068; --card:#fff; --line:#e4e3dc; --accent:#4b3fb5; --err:#b3261e; }
        @media (prefers-color-scheme: dark) {
            :root { --bg:#14141a; --fg:#ececf1; --muted:#a3a4b0; --card:#1d1d26; --line:#32323f; --accent:#9d93ff; --err:#ff8a80; }
        }
        * { box-sizing: border-box; }
        body { margin:0; background:var(--bg); color:var(--fg); font:16px/1.65 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif; }
        main { max-width:720px; margin:0 auto; padding:32px 16px 64px; }
        h1 { font-size:1.9rem; line-height:1.25; margin:0 0 .5rem; }
        h2 { font-size:1.2rem; margin:2rem 0 .5rem; }
        p, li { color:var(--fg); }
        .muted { color:var(--muted); font-size:.9rem; }
        a { color:var(--accent); }
        .card { background:var(--card); border:1px solid var(--line); border-radius:12px; padding:20px; margin:1.25rem 0; }
        label { display:block; font-weight:600; margin:14px 0 4px; }
        input { width:100%; padding:11px 12px; font:inherit; color:var(--fg); background:var(--bg); border:1px solid var(--line); border-radius:8px; }
        button { margin-top:18px; padding:11px 20px; font:inherit; font-weight:600; color:#fff; background:var(--accent); border:0; border-radius:8px; cursor:pointer; }
        button.danger { background:var(--err); }
        .error { color:var(--err); margin:6px 0 0; font-size:.92rem; }
        nav { margin-top:3rem; font-size:.9rem; display:flex; gap:16px; flex-wrap:wrap; }
    </style>
</head>
<body>
<main>
    @yield('content')
    <nav>
        <a href="{{ url('/privacy') }}">Privacy Policy</a>
        <a href="{{ url('/terms') }}">Terms of Service</a>
        <a href="{{ url('/delete-account') }}">Delete account</a>
        <a href="{{ url('/support') }}">Support</a>
    </nav>
</main>
</body>
</html>
