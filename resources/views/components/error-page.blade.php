@props([
    'code',
    'title',
    'message',
    'tone' => 'accent',      // accent | danger | warn
    'icon' => 'alert',       // search | clock | alert | wrench | hourglass
    'retryUrl' => null,
    'retryLabel' => 'Try again',
])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $code }} · {{ $title }}</title>
    <link rel="icon" type="image/png" href="{{ asset('icon.png') }}">
    <script>
        (function () {
            var stored = null;
            try { stored = localStorage.getItem('theme'); } catch (e) {}
            var theme = (stored === 'dark' || stored === 'light')
                ? stored
                : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('css/pixel-theme.css') }}?v=3">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            padding: 2rem 1.5rem; background: var(--paper); color: var(--ink);
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }
        .ledger-font { font-family: "Source Serif 4", Georgia, serif; }
        .ep-card {
            width: 100%; max-width: 30rem; text-align: center; position: relative;
            background: var(--surface); border: 1px solid var(--line);
            border-radius: 1rem; padding: 2.75rem 2rem;
            box-shadow: 0 10px 30px -18px rgba(0,0,0,.35);
        }
        .ep-icon {
            width: 5rem; height: 5rem; margin: 0 auto 1.25rem; border-radius: 9999px;
            display: flex; align-items: center; justify-content: center;
            border: 1px solid var(--line);
            animation: ep-pop .5s cubic-bezier(.2,.9,.3,1.3) both;
        }
        .ep-tone-accent .ep-icon { background: var(--accent-soft); color: var(--accent); }
        .ep-tone-danger .ep-icon { background: var(--danger-soft); color: var(--danger); }
        .ep-tone-warn   .ep-icon { background: var(--warn-soft);   color: var(--warn); }
        .ep-tone-accent .ep-code { color: var(--accent); }
        .ep-tone-danger .ep-code { color: var(--danger); }
        .ep-tone-warn   .ep-code { color: var(--warn); }
        @keyframes ep-pop { from { transform: scale(.6); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        .ep-code { font-size: .75rem; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; margin-bottom: .5rem; }
        .ep-title { font-size: 1.875rem; line-height: 1.2; margin: 0 0 .5rem; color: var(--ink); }
        .ep-msg { font-size: .875rem; line-height: 1.55; margin: 0; color: var(--ink-soft); }

        .ep-actions { display: flex; align-items: center; justify-content: center; gap: .75rem; flex-wrap: wrap; margin-top: 2rem; }
        .ep-btn {
            display: inline-flex; align-items: center; gap: .45rem;
            font-size: .875rem; font-weight: 600; padding: .6rem 1.2rem; border-radius: .5rem;
            cursor: pointer; text-decoration: none; font-family: inherit;
            transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
        }
        .ep-btn-primary { background: var(--accent); color: #fff; border: 1px solid var(--accent); }
        .ep-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 14px -6px var(--accent); }
        .ep-btn-ghost { background: transparent; color: var(--ink-soft); border: 1px solid var(--line); }
        .ep-btn-ghost:hover { background: var(--paper); color: var(--ink); }

        .ep-theme {
            position: absolute; top: .9rem; right: .9rem; width: 2.25rem; height: 2.25rem;
            border-radius: 9999px; border: 1px solid var(--line); background: transparent;
            color: var(--ink-soft); cursor: pointer; display: flex; align-items: center; justify-content: center;
        }
        .ep-theme:hover { background: var(--paper); color: var(--ink); }
        html[data-theme="dark"] .ep-sun { display: block; }
        html[data-theme="dark"] .ep-moon { display: none; }
        html:not([data-theme="dark"]) .ep-sun { display: none; }

        @media (prefers-reduced-motion: reduce) { .ep-icon { animation: none; } }
    </style>
</head>
<body>
    <div class="ep-card ep-tone-{{ $tone }}">

        {{-- Theme toggle (works for guests too) --}}
        <button type="button" class="ep-theme" title="Toggle light / dark mode" onclick="
            var r = document.documentElement;
            var next = r.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            r.setAttribute('data-theme', next);
            try { localStorage.setItem('theme', next); } catch (e) {}
        ">
            <svg class="ep-moon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
            <svg class="ep-sun" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
        </button>

        <div class="ep-icon">
            <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                @switch($icon)
                    @case('search')
                        <circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="8.5" y1="8.5" x2="13.5" y2="13.5"/><line x1="13.5" y1="8.5" x2="8.5" y2="13.5"/>
                        @break
                    @case('clock')
                        <circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15.5 14"/>
                        @break
                    @case('wrench')
                        <path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.6 2.6-2.4-.6-.6-2.4z"/>
                        @break
                    @case('hourglass')
                        <path d="M6 2h12M6 22h12"/><path d="M7 2v4a5 5 0 0 0 2 4l3 2-3 2a5 5 0 0 0-2 4v4"/><path d="M17 2v4a5 5 0 0 1-2 4l-3 2 3 2a5 5 0 0 1 2 4v4"/>
                        @break
                    @default
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                @endswitch
            </svg>
        </div>

        <div class="ep-code">Error {{ $code }}</div>
        <h1 class="ep-title ledger-font">{{ $title }}</h1>
        <p class="ep-msg">{!! $message !!}</p>

        <div class="ep-actions">
            @if ($retryUrl)
                <a href="{{ $retryUrl }}" class="ep-btn ep-btn-primary">{{ $retryLabel }}</a>
                <a href="{{ url('/') }}" class="ep-btn ep-btn-ghost">Home</a>
            @else
                <a href="{{ url('/') }}" class="ep-btn ep-btn-primary"
                   onclick="if (history.length > 1) { history.back(); return false; }">&larr; Go back</a>
                <a href="{{ url('/') }}" class="ep-btn ep-btn-ghost">Home</a>
            @endif
        </div>
    </div>
</body>
</html>