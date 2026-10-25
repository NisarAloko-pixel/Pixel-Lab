
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <script id="guest-theme-init">
    // Same logic as the dashboard layout: saved choice first, otherwise the system setting.
    (function () {
        var stored = null;
        try { stored = localStorage.getItem('theme'); } catch (e) {}
        var saved = (stored === 'dark' || stored === 'light');
        var mq = window.matchMedia('(prefers-color-scheme: dark)');
        function apply() {
            document.documentElement.setAttribute('data-theme', saved ? stored : (mq.matches ? 'dark' : 'light'));
        }
        apply();
        if (!saved && mq.addEventListener) mq.addEventListener('change', apply);
    })();
</script>
        <title>{{ config('app.name', 'Pixel Lab') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
        <!-- Fonts -->

        <!-- Icons -->
        <script src="{{ asset('vendor/lucide/lucide.js') }}"></script>

        <!-- Scripts (Alpine ships with Breeze's app.js; okay if Tailwind in here isn't compiling —
             none of the styling below depends on it) -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            /* restored :root */
            :root{
                --paper:#F6F8FB; --surface:#FFFFFF; --surface-2:#F0F3F9;
                --ink:#12181F; --ink-soft:#4B5565; --ink-faint:#8A93A3;
                --line:#E1E6EF; --line-soft:rgba(30,58,110,0.07);
                --cyan:#12A3B8; --cyan-deep:#0C7F91; --violet:#6C5CE7; --coral:#FF6B5B;
            }
            /* ============================================================
               Everything in this block is plain CSS — it does not depend
               on Tailwind being compiled, so the page renders correctly
               even if your Vite/Tailwind build isn't wired up yet.
               ============================================================ */
            *{ box-sizing:border-box; }
            html,body{ margin:0; height:100%; }
            body{
                background:var(--paper);
                color:var(--ink);
                font-family:'Inter',sans-serif;
                -webkit-font-smoothing:antialiased;
            }
            a{ text-decoration:none; }
            .font-display{ font-family:'Space Grotesk',sans-serif; }
            .font-mono{ font-family:'JetBrains Mono',monospace; }

            /* ---------- Page shell ---------- */
            .split{ min-height:100vh; display:grid; grid-template-columns: 1.15fr 1fr; }
            @media (max-width: 1023px){ .split{ grid-template-columns: 1fr; } }

            /* ---------- Scene (left) panel ---------- */
            .scene-panel{
                position:relative; overflow:hidden;
                display:flex; align-items:center; justify-content:center;
                padding: 48px; min-height: 100vh;
                background: linear-gradient(165deg, #eef3fa, #e7ecf4 55%, #eef1f8);
            }
            @media (max-width: 1023px){ .scene-panel{ display:none; } }

            .scene-glow{
                position:absolute; inset:0;
                background:
                    radial-gradient(560px 420px at 46% 46%, rgba(18,163,184,0.12), transparent 65%),
                    radial-gradient(420px 320px at 78% 20%, rgba(108,92,231,0.10), transparent 60%);
                pointer-events:none;
            }
            .grid-floor{
                position:absolute; inset:0;
                background-image:
                    linear-gradient(var(--line-soft) 1px, transparent 1px),
                    linear-gradient(90deg, var(--line-soft) 1px, transparent 1px);
                background-size: 42px 42px;
                -webkit-mask-image: radial-gradient(560px 460px at 50% 56%, black 30%, transparent 78%);
                        mask-image: radial-gradient(560px 460px at 50% 56%, black 30%, transparent 78%);
            }

            .brand{
                position:absolute; top:40px; left:48px; z-index:20;
                display:flex; align-items:center; gap:10px;
            }
            .brand span{ color:var(--ink); }
            .brand-mobile{
                display:none;
                position:absolute; top:32px; left:50%; transform:translateX(-50%);
                align-items:center; gap:8px; z-index:20;
            }
            @media (max-width: 1023px){ .brand-mobile{ display:flex; } }

            .illustration{ position:relative; z-index:10; width:min(460px, 90%); }

            .vaseFill{ animation: growVase 6s ease-in-out infinite; transform-box: fill-box; transform-origin: bottom center; }
            @keyframes growVase{
                0%{ transform: scaleY(0.08); opacity:.4; }
                45%{ transform: scaleY(1); opacity:1; }
                82%{ transform: scaleY(1); opacity:1; }
                100%{ transform: scaleY(0.08); opacity:.4; }
            }
            .head-travel{ animation: headMove 6s ease-in-out infinite; transform-box: fill-box; transform-origin:center; }
            @keyframes headMove{
                0%{ transform: translateX(-42px); }
                45%{ transform: translateX(40px); }
                50%{ transform: translateX(40px); }
                82%{ transform: translateX(-6px); }
                100%{ transform: translateX(-42px); }
            }
            .spool-spin{ animation: spin 9s linear infinite; transform-box: fill-box; transform-origin: center; }
            @keyframes spin{ to{ transform: rotate(360deg);} }

            .particle{ position:absolute; border-radius:999px; background:var(--cyan); opacity:.55; animation: drift 10s ease-in-out infinite; }
            @keyframes drift{
                0%,100%{ transform:translate(0,0); opacity:.25;}
                50%{ transform:translate(6px,-16px); opacity:.7;}
            }
            .float-shape{ position:absolute; animation: floaty 7s ease-in-out infinite; }
            @keyframes floaty{
                0%,100%{ transform: translateY(0) rotate(0deg); }
                50%{ transform: translateY(-10px) rotate(6deg); }
            }

            .tele-card{
                position:absolute; z-index:20;
                background: rgba(255,255,255,0.82);
                border:1px solid var(--line);
                backdrop-filter: blur(14px);
                -webkit-backdrop-filter: blur(14px);
                border-radius: 12px;
                box-shadow: 0 14px 30px -14px rgba(15,23,42,0.22), inset 0 1px 0 rgba(255,255,255,0.6);
                animation: cardFloat 8s ease-in-out infinite;
            }
            .tele-card.d2{ animation-delay: -2.3s; }
            .tele-card.d3{ animation-delay: -4.6s; }
            .tele-card.d4{ animation-delay: -1.1s; }
            @keyframes cardFloat{
                0%,100%{ transform: translateY(0); }
                50%{ transform: translateY(-7px); }
            }
            .tele-label{ font-size:11px; color:var(--ink-soft); }
            .tele-value{ font-family:'JetBrains Mono',monospace; color:var(--ink); }
            .tele-dot{ width:6px; height:6px; border-radius:999px; display:inline-block; }
            .tele-bar-track{ margin-top:8px; height:4px; border-radius:999px; background: var(--surface-2); }
            .tele-bar-fill{ height:4px; border-radius:999px; background:linear-gradient(90deg,var(--cyan),var(--violet)); }

            .scene-caption{
                position:absolute; bottom:32px; left:48px; right:48px;
                font-size:14px; line-height:1.5; color:var(--ink-soft); max-width:320px;
            }

            /* ---------- Form (right) panel ---------- */
            .form-panel{
                position:relative; display:flex; align-items:center; justify-content:center;
                padding: 48px 24px; min-height:100vh;
                background: radial-gradient(700px 500px at 50% 0%, rgba(18,163,184,0.05), transparent 60%), var(--surface);
            }

            .card-wrap{ width:100%; max-width:400px; margin-top:64px; }
            @media (min-width: 1024px){ .card-wrap{ margin-top:0; } }

            .card{
                background: linear-gradient(165deg, rgba(255,255,255,0.9), rgba(255,255,255,0.72));
                border:1px solid var(--line);
                backdrop-filter: blur(18px);
                -webkit-backdrop-filter: blur(18px);
                border-radius:20px;
                padding: 34px;
                box-shadow:
                    0 1px 0 rgba(255,255,255,0.6) inset,
                    0 26px 54px -22px rgba(15,23,42,0.20),
                    0 10px 26px -14px rgba(15,23,42,0.12);
            }
            @media (max-width: 420px){ .card{ padding:26px; } }

            .card-head{ margin-bottom: 26px; }
            .card-head h1{ margin:0; font-size:26px; font-weight:600; line-height:1.25; color:var(--ink); }
            .card-head p{ margin:8px 0 0; font-size:13.5px; color:var(--ink-soft); }

            .banner-error{
                margin-bottom:20px; padding:11px 14px; border-radius:10px;
                background: rgba(214,69,69,0.08); border:1px solid rgba(214,69,69,0.26);
                color:var(--err-ink,#a83636); font-size:13px; display:flex; align-items:flex-start; gap:8px;
            }
            .banner-error svg{ flex-shrink:0; margin-top:1px; }

            .form-fields > * + *{ margin-top:20px; }

            .label{ display:block; font-size:13px; font-weight:500; color:var(--ink-soft); margin-bottom:6px; }
            .label-row{ display:flex; align-items:center; justify-content:space-between; margin-bottom:6px; }
            .link-accent{ font-size:12.5px; font-weight:600; color:var(--cyan-deep); }
            .link-accent:hover{ opacity:.8; }

            .field-group{
                border-radius:12px; border:1px solid var(--line); background:#fff;
                display:flex; align-items:center; gap:10px; padding:0 14px; height:46px;
                transition: border-color .18s ease, box-shadow .18s ease;
            }
            .field-group:focus-within{
                border-color: rgba(18,163,184,0.55);
                box-shadow: 0 0 0 4px rgba(18,163,184,0.12);
            }
            .field-group svg{ flex-shrink:0; color:var(--ink-faint); }
            .field-group input{
                width:100%; height:100%; border:0; background:transparent; outline:none;
                font-size:14.5px; color:var(--ink); font-family:'Inter',sans-serif; padding:0;
            }
            .field-group input::placeholder{ color:var(--ink-faint); }
            .field-group.has-error{ border-color: rgba(214,69,69,0.5); }
            .field-toggle{ background:none; border:0; padding:0; display:flex; cursor:pointer; }

            .error-text{ margin-top:6px; font-size:12.5px; color:var(--coral); }

            .checkbox-row{ display:flex; align-items:center; padding-top:2px; }
            .checkbox-label{ display:inline-flex; align-items:center; gap:8px; cursor:pointer; user-select:none; font-size:13px; color:var(--ink-soft); }
            .checkbox-3d{
                appearance:none; width:18px; height:18px; border-radius:5px;
                border:1px solid var(--line); background: #fff;
                position:relative; cursor:pointer; margin:0;
                transition: background .15s ease, border-color .15s ease;
            }
            .checkbox-3d:checked{ background: linear-gradient(180deg, var(--cyan), var(--cyan-deep)); border-color: var(--cyan-deep); }
            .checkbox-3d:checked::after{
                content:''; position:absolute; left:5px; top:1px; width:5px; height:9px;
                border-right:2px solid #fff; border-bottom:2px solid #fff; transform: rotate(45deg);
            }

            .btn-primary{
                width:100%; height:47px; border:0; border-radius:12px;
                font-weight:600; font-size:14.5px; color:#fff; cursor:pointer;
                background: linear-gradient(180deg, var(--cyan), var(--cyan-deep));
                box-shadow:
                    0 1px 0 rgba(255,255,255,0.25) inset,
                    0 14px 28px -12px rgba(10,110,128,0.5);
                display:flex; align-items:center; justify-content:center; gap:8px;
                transition: transform .12s ease, box-shadow .12s ease, filter .12s ease;
            }
            .btn-primary:hover{ filter:brightness(1.06); }
            .btn-primary:active{
                transform: translateY(2px);
                box-shadow: 0 1px 0 rgba(255,255,255,0.18) inset, 0 6px 14px -8px rgba(10,110,128,0.45);
            }
            .btn-primary:disabled{ opacity:.7; cursor:default; transform:none; }

            .btn-ghost{
                width:100%; height:46px; border-radius:12px;
                background:#fff; border:1px solid var(--line); cursor:pointer;
                display:flex; align-items:center; justify-content:center; gap:10px;
                font-size:14px; font-weight:500; color:var(--ink);
                transition: background .15s ease, border-color .15s ease, transform .12s ease;
            }
            .btn-ghost:hover{ background: var(--surface-2); }
            .btn-ghost:active{ transform: translateY(1px); }

            .divider-row{ display:flex; align-items:center; gap:12px; padding:2px 0; }
            .divider-line{ height:1px; flex:1; background:var(--line); }
            .divider-text{ font-size:12px; color:var(--ink-faint); }

            .below-card{ text-align:center; font-size:13.5px; color:var(--ink-soft); margin-top:24px; }
            .below-card a{ font-weight:600; color:var(--cyan-deep); }
            .below-card a:hover{ opacity:.8; }
            .fineprint{ text-align:center; font-size:11.5px; color:var(--ink-faint); margin-top:32px; }

            .spinner{
                width:16px; height:16px; border-radius:999px;
                border:2px solid rgba(255,255,255,0.35); border-top-color:#fff;
                animation: spin .7s linear infinite;
            }

            a:focus-visible, button:focus-visible, input:focus-visible{
                outline: 2px solid var(--cyan-deep); outline-offset: 2px; border-radius: 6px;
            }

            @media (prefers-reduced-motion: reduce){
                *{ animation-duration: 0.001ms !important; animation-iteration-count: 1 !important; transition-duration: 0.001ms !important; }
            }
        
            /* guest-dark-auto */
            [data-theme="dark"] .scene-panel { background: linear-gradient(165deg, #1E212B, #262A35 55%, #262A35); }
            [data-theme="dark"] .tele-card { background: rgba(30,33,43,0.82); }
            [data-theme="dark"] .card { background: linear-gradient(165deg, rgba(30,33,43,0.9), rgba(30,33,43,0.72)); }
            [data-theme="dark"] .field-group { background: #1E212B; }
            [data-theme="dark"] .checkbox-3d { background: #1E212B; }
            [data-theme="dark"] .checkbox-3d:checked::after { border-right: 2px solid rgba(255,255,255,.1); border-bottom: 2px solid rgba(255,255,255,.1); }
            [data-theme="dark"] .btn-ghost { background: #1E212B; }
            [data-theme="dark"] .spinner { border: 2px solid rgba(255,255,255,0.053); border-top-color: rgba(255,255,255,.1); }
            [data-theme="dark"] input[type="email"],
            [data-theme="dark"] input[type="password"],
            [data-theme="dark"] input[type="text"] { background-color: transparent; color: var(--ink); }
            [data-theme="dark"] input[type="email"]:focus,
            [data-theme="dark"] input[type="password"]:focus,
            [data-theme="dark"] input[type="text"]:focus { outline: none !important; box-shadow: none !important; border-color: transparent !important; }
            [data-theme="dark"] input::placeholder { color: var(--ink-faint); opacity: 1; }
            [data-theme="dark"] input:-webkit-autofill {
                -webkit-box-shadow: 0 0 0 1000px #262A35 inset !important;
                -webkit-text-fill-color: var(--ink) !important; caret-color: var(--ink);
            }
            [data-theme="dark"] input[type="checkbox"]:not(:checked) { background-color: var(--surface-2); border-color: var(--line); }
            /* ---------- Dark mode ---------- */
            :root{ color-scheme:light; --err-ink:#A83636; --ok-ink:#227A4C; }
            [data-theme="dark"]{
                color-scheme:dark;
                --paper:#15171C; --surface:#1E212B; --surface-2:#262A35;
                --ink:#E7E5DE; --ink-soft:#9CA3AF; --ink-faint:#6B7280;
                --line:#2C303B; --line-soft:rgba(150,185,245,0.08);
                --err-ink:#FF9D93; --ok-ink:#7BD6A3;
            }
            [data-theme="dark"] .scene-panel{
                background: linear-gradient(165deg, #171B24, #12151C 55%, #161A23);
            }</style>
    </head>
     <body>
        {{ $slot }}

        <script>
            document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
        </script>
    </body>
</html>
