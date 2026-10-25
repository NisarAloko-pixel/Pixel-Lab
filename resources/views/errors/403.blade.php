<x-layouts.pixel>
@php
    $previous = url()->previous();
    $backUrl = ($previous && $previous !== url()->current()) ? $previous : route('dashboard');
    $detail = isset($exception) ? trim((string) $exception->getMessage()) : '';
    // Hide Laravel's default technical text, show only a custom abort(403, '...') message
    if ($detail === '' || str_contains($detail, 'This action is unauthorized')) {
        $detail = '';
    }
@endphp

<style>
    .ua-wrap {
        min-height: calc(100vh - 12rem);
        display: flex; align-items: center; justify-content: center;
        padding: 2rem 1.5rem;
    }
    .ua-card {
        width: 100%; max-width: 30rem; text-align: center;
        background: var(--surface); border: 1px solid var(--line);
        border-radius: 1rem; padding: 2.75rem 2rem;
        box-shadow: 0 10px 30px -18px rgba(0,0,0,.35);
    }
    .ua-icon {
        width: 5rem; height: 5rem; margin: 0 auto 1.25rem;
        border-radius: 9999px; display: flex; align-items: center; justify-content: center;
        background: var(--danger-soft); color: var(--danger);
        border: 1px solid var(--line);
        animation: ua-pop .5s cubic-bezier(.2,.9,.3,1.3) both;
    }
    @keyframes ua-pop { from { transform: scale(.6); opacity: 0; } to { transform: scale(1); opacity: 1; } }

    .ua-code {
        font-size: .75rem; font-weight: 600; letter-spacing: .12em;
        text-transform: uppercase; color: var(--danger); margin-bottom: .5rem;
    }
    .ua-detail {
        margin-top: 1rem; font-size: .8rem; padding: .6rem .8rem; border-radius: .5rem;
        background: var(--danger-soft); color: var(--danger); border: 1px solid var(--line);
    }
    .ua-btn {
        display: inline-flex; align-items: center; gap: .45rem;
        font-size: .875rem; font-weight: 600; padding: .6rem 1.2rem;
        border-radius: .5rem; cursor: pointer; text-decoration: none;
        transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
    }
    .ua-btn-primary { background: var(--accent); color: #fff; border: 1px solid var(--accent); }
    .ua-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 14px -6px var(--accent); }
    .ua-btn-ghost { background: transparent; color: var(--ink-soft); border: 1px solid var(--line); }
    .ua-btn-ghost:hover { background: var(--paper); color: var(--ink); }

    @media (prefers-reduced-motion: reduce) { .ua-icon { animation: none; } }
</style>

<div style="background:var(--paper); color:var(--ink);">
    <div class="ua-wrap">
        <div class="ua-card">

            <div class="ua-icon">
                <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="4" y="11" width="16" height="10" rx="2"/>
                    <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                    <line x1="12" y1="15" x2="12" y2="17"/>
                </svg>
            </div>

            <div class="ua-code">Error 403</div>
            <h1 class="ledger-font text-3xl mb-2" style="color:var(--ink);">Access denied</h1>
            <p class="text-sm" style="color:var(--ink-soft);">
                You don't have permission to open this page.<br>
                If you think this is a mistake, ask an administrator to update your role.
            </p>

            @if ($detail !== '')
                <div class="ua-detail">{{ $detail }}</div>
            @endif

            <div class="flex items-center justify-center gap-3 mt-8">
                <a href="{{ $backUrl }}" class="ua-btn ua-btn-primary">
                  Go back
                </a>
               
            </div>
        </div>
    </div>
</div>
</x-layouts.pixel>