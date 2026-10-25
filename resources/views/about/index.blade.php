@php
    $version = config('pixel.version');

    $features = [
        ['Sales', 'Create, track and invoice sales, payments and cancellations.',
            '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>'],
        ['Customers', 'Keep customer details and history organized.',
            '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
        ['Suppliers', 'Manage suppliers and what you owe them.',
            '<rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>'],
        ['Purchases', 'Record purchase orders, arrivals and payments.',
            '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>'],
        ['Filament Inventory', 'Monitor filament stock and usage levels.',
            '<path d="M12 2v6"/><circle cx="12" cy="14" r="7"/><circle cx="12" cy="14" r="3"/>'],
        ['Product Inventory', 'Track products, stock, damaged items and value.',
            '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>'],
        ['Supplies', 'Manage supplies and apply purchases to stock.',
            '<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>'],
        ['Expenses', 'Monitor operational expenses and spending.',
            '<path d="M20 12V8H6a2 2 0 0 1 0-4h12v4"/><path d="M4 6v12a2 2 0 0 0 2 2h14v-4"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/>'],
        ['Balance & Shareholders', 'Track balances, deposits, transfers and shareholders.',
            '<path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/>'],
        ['Transactions', 'A clear record of all financial transactions.',
            '<polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/>'],
        ['Employees & Advances', 'Manage employees and their advance payments.',
            '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>'],
        ['Reports & Export', 'Sales, profit and expense reports with PDF, Excel and CSV export.',
            '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>'],
        ['Users & Roles', 'Control access with users, roles and permissions.',
            '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>'],
        ['Audit Logs', 'Review who did what across the system.',
            '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>'],
        ['Backup & Restore', 'Back up your data and restore it when needed.',
            '<line x1="22" y1="12" x2="2" y2="12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/><line x1="6" y1="16" x2="6.01" y2="16"/><line x1="10" y1="16" x2="10.01" y2="16"/>'],
        ['Cloud Sync', 'Keep your data synchronized in the background.',
            '<path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"/>'],
    ];

    $why = [
        ['Centralized Data', 'Keep your important business information organized in one place.',
            '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14a9 3 0 0 0 18 0V5"/><path d="M3 12a9 3 0 0 0 18 0"/>'],
        ['Real-Time Visibility', 'Quickly understand sales, inventory and financial activity.',
            '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>'],
        ['Simple & Practical', 'Designed around real business workflows without unnecessary complexity.',
            '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>'],
        ['Built for Growth', 'A scalable foundation that can evolve with your business.',
            '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>'],
    ];

    $premium = [
        ['Desktop Application', 'Run the system as a dedicated desktop application.',
            '<rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>'],
        ['Mobile Application', 'Access your business system from supported mobile devices.',
            '<rect x="5" y="2" width="14" height="20" rx="2"/><path d="M12 18h.01"/>'],
        ['Multi-Computer Setup', 'Run the system across multiple computers with centralized shared data.',
            '<rect x="2" y="3" width="8" height="6" rx="1"/><rect x="14" y="3" width="8" height="6" rx="1"/><rect x="8" y="15" width="8" height="6" rx="1"/><path d="M6 9v3h12V9M12 12v3"/>'],
    ];
@endphp

<x-layouts.pixel>
<style>
    .ab-page { background: var(--paper); color: var(--ink); font-size: 14px; }
    .ab-wrap {
        max-width: 980px; margin: 0 auto; padding: 1.25rem 1rem 2.5rem;
        display: flex; flex-direction: column; gap: 1.75rem;
    }

    /* ---------- Subtle fade-in ---------- */
    @keyframes abFade { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
    .ab-wrap > * { animation: abFade .45s ease both; }
    .ab-wrap > *:nth-child(2) { animation-delay: .05s; }
    .ab-wrap > *:nth-child(3) { animation-delay: .1s; }
    .ab-wrap > *:nth-child(4) { animation-delay: .15s; }
    .ab-wrap > *:nth-child(5) { animation-delay: .2s; }

    /* ---------- Hero ---------- */
    .ab-hero {
        position: relative; isolation: isolate; overflow: hidden; text-align: center;
        padding: 1.25rem 1.25rem 1.15rem; border-radius: 14px;
        background: var(--surface); border: 1px solid var(--line);
        box-shadow: 0 14px 28px -20px rgba(0,0,0,.5);
    }
    .ab-hero::before {
        content: ''; position: absolute; inset: 0; z-index: -2; opacity: .3;
        background-image:
            linear-gradient(var(--line) 1px, transparent 1px),
            linear-gradient(90deg, var(--line) 1px, transparent 1px);
        background-size: 32px 32px;
        -webkit-mask-image: radial-gradient(ellipse at 50% 30%, #000 20%, transparent 75%);
                mask-image: radial-gradient(ellipse at 50% 30%, #000 20%, transparent 75%);
    }
    .ab-hero::after {
        content: ''; position: absolute; left: 50%; top: -50%; z-index: -1;
        width: 460px; max-width: 120%; height: 280px; transform: translateX(-50%);
        background: radial-gradient(closest-side, color-mix(in srgb, var(--accent) 26%, transparent), transparent);
        pointer-events: none;
    }
    .ab-eyebrow {
        font-size: .72rem; font-weight: 700; letter-spacing: .16em; text-transform: uppercase;
        color: var(--accent);
    }
    .ab-hero h1 {
        font-size: clamp(1.5rem, 3.4vw, 2.2rem); line-height: 1.15; letter-spacing: -.015em;
        margin: .35rem auto .5rem; max-width: 40ch;
    }
    .ab-hero h1 span { color: var(--accent); }
    .ab-hero p { max-width: 38rem; margin: 0 auto; font-size: .95rem; line-height: 1.55; color: var(--ink-soft); }
    .ab-pills { display: flex; flex-wrap: wrap; justify-content: center; gap: .45rem; margin-top: .8rem; }
    .ab-pill {
        display: inline-flex; align-items: center; gap: .35rem; padding: .25rem .7rem;
        border-radius: 9999px; font-size: .76rem; font-weight: 600;
        background: var(--paper); border: 1px solid var(--line); color: var(--ink-soft);
    }
    .ab-pill.ok { background: var(--success-soft); border-color: transparent; color: var(--success); }
    .ab-dot { width: 6px; height: 6px; border-radius: 9999px; background: currentColor; animation: abPulse 2.4s ease-in-out infinite; }
    @keyframes abPulse { 0%,100% { opacity: 1; } 50% { opacity: .35; } }

    /* ---------- Section heads ---------- */
    .ab-head { text-align: center; max-width: 34rem; margin: 0 auto 1.1rem; }
    .ab-head h2 { font-size: clamp(1.1rem, 2.2vw, 1.4rem); font-weight: 600; letter-spacing: -.01em; }
    .ab-head p { margin-top: .3rem; font-size: .84rem; color: var(--ink-soft); line-height: 1.5; }

    /* ---------- Cards ---------- */
    .ab-grid { display: grid; gap: .8rem; }
    .ab-grid.g3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .ab-grid.g4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .ab-grid.g2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }

    .ab-card {
        background: var(--surface); border: 1px solid var(--line); border-radius: 12px;
        padding: 1rem; box-shadow: 0 1px 0 rgba(255,255,255,.05) inset, 0 6px 14px -12px rgba(0,0,0,.35);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .ab-card:hover {
        transform: translateY(-2px);
        border-color: color-mix(in srgb, var(--accent) 45%, var(--line));
        box-shadow: 0 1px 0 rgba(255,255,255,.05) inset, 0 12px 22px -14px rgba(0,0,0,.5);
    }
    .ab-icon {
        width: 34px; height: 34px; border-radius: 10px; margin-bottom: .65rem;
        display: flex; align-items: center; justify-content: center;
        background: var(--accent-soft); color: var(--accent-ink);
        box-shadow: 0 1px 4px rgba(0,0,0,.15), 0 1px 0 rgba(255,255,255,.2) inset;
        transition: transform .18s ease;
    }
    .ab-icon svg { width: 16px; height: 16px; }
    .ab-card:hover .ab-icon { transform: scale(1.06); }
    .ab-card h3 { font-size: .88rem; font-weight: 600; }
    .ab-card p { margin-top: .25rem; font-size: .78rem; line-height: 1.5; color: var(--ink-soft); }

    /* compact feature cards */
    .ab-feat { display: flex; align-items: flex-start; gap: .6rem; padding: .65rem .75rem; border-radius: 10px; }
    .ab-feat .ab-icon { margin: 0; width: 30px; height: 30px; border-radius: 8px; flex-shrink: 0; }
    .ab-feat .ab-icon svg { width: 15px; height: 15px; }
    .ab-feat h3 { font-size: .8rem; line-height: 1.25; }
    .ab-feat p { margin-top: .15rem; font-size: .7rem; line-height: 1.4; }

    /* ---------- Panels ---------- */
    .ab-panel {
        border-radius: 14px; border: 1px solid var(--line); background: var(--surface);
        padding: 1.25rem; box-shadow: 0 12px 24px -20px rgba(0,0,0,.45);
    }
    .ab-panel h2 { font-size: 1.1rem; font-weight: 600; letter-spacing: -.01em; }
    .ab-panel > p, .ab-lead { margin-top: .35rem; font-size: .84rem; color: var(--ink-soft); line-height: 1.5; }

    .ab-tip {
        position: relative; overflow: hidden;
        background: linear-gradient(135deg, var(--accent-soft), var(--surface) 75%);
        border-color: color-mix(in srgb, var(--accent) 40%, var(--line));
        display: flex; flex-direction: column; justify-content: center;
    }
    .ab-tip::after {
        content: ''; position: absolute; right: -60px; bottom: -70px; width: 200px; height: 200px;
        background: radial-gradient(closest-side, color-mix(in srgb, var(--accent) 26%, transparent), transparent);
        pointer-events: none;
    }
    .ab-tip > * { position: relative; z-index: 1; }
/* ---------- Premium section ---------- */
.ab-premium {
    position: relative; isolation: isolate; overflow: hidden;
    padding: 1.6rem; border: 1px solid transparent; border-radius: 18px;
    background:
        linear-gradient(var(--surface), var(--surface)) padding-box,
        linear-gradient(135deg,
            color-mix(in srgb, var(--accent) 90%, #fff),
            color-mix(in srgb, var(--accent) 12%, var(--line)) 38%,
            color-mix(in srgb, var(--accent) 12%, var(--line)) 62%,
            color-mix(in srgb, var(--accent) 80%, #fff)) border-box;
    box-shadow:
        0 28px 56px -32px color-mix(in srgb, var(--accent) 60%, transparent),
        0 1px 0 rgba(255,255,255,.06) inset;
}
.ab-premium::before {            /* warm glow */
    content: ''; position: absolute; z-index: -1; right: -90px; top: -110px;
    width: 340px; height: 300px; pointer-events: none;
    background: radial-gradient(closest-side, color-mix(in srgb, var(--accent) 30%, transparent), transparent);
}
.ab-premium::after {             /* slow shimmer sweep */
    content: ''; position: absolute; z-index: -1; top: 0; bottom: 0; left: -40%; width: 30%;
    pointer-events: none; transform: skewX(-18deg);
    background: linear-gradient(90deg, transparent, color-mix(in srgb, var(--accent) 12%, transparent), transparent);
    animation: pmShine 7s ease-in-out infinite;
}
@keyframes pmShine { 0%, 55% { left: -40%; } 100% { left: 140%; } }

.pm-head { display: flex; align-items: center; gap: .9rem; }
.pm-crown {
    width: 46px; height: 46px; border-radius: 14px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; color: #FFF;
    background: linear-gradient(135deg, color-mix(in srgb, var(--accent) 85%, #fff), var(--accent-ink));
    box-shadow: 0 10px 22px -10px var(--accent), 0 1px 0 rgba(255,255,255,.35) inset;
}
.pm-crown svg { width: 22px; height: 22px; }
.pm-eyebrow {
    display: inline-block; margin-bottom: .2rem; padding: .15rem .6rem; border-radius: 9999px;
    font-size: .6rem; font-weight: 700; letter-spacing: .18em; text-transform: uppercase;
    color: var(--accent-ink); background: var(--accent-soft);
    border: 1px solid color-mix(in srgb, var(--accent) 35%, transparent);
}
.pm-title {
    font-size: clamp(1.15rem, 2.4vw, 1.5rem); font-weight: 700; letter-spacing: -.01em;
    background: linear-gradient(90deg, var(--ink), color-mix(in srgb, var(--accent) 75%, var(--ink)));
    -webkit-background-clip: text; background-clip: text; color: transparent;
}
.pm-lead { margin-top: .9rem; max-width: 40rem; font-size: .86rem; line-height: 1.6; color: var(--ink-soft); }

.pm-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .8rem; margin-top: 1.25rem; }
.pm-card {
    position: relative; overflow: hidden; padding: 1rem 1rem 1.05rem; border-radius: 14px;
    background: color-mix(in srgb, var(--paper) 70%, transparent);
    border: 1px solid color-mix(in srgb, var(--accent) 18%, var(--line));
    backdrop-filter: blur(6px);
    transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
}
.pm-card::before {               /* gold hairline on top */
    content: ''; position: absolute; left: 12%; right: 12%; top: 0; height: 1px;
    background: linear-gradient(90deg, transparent, var(--accent), transparent); opacity: .7;
}
.pm-card:hover {
    transform: translateY(-3px);
    border-color: color-mix(in srgb, var(--accent) 55%, var(--line));
    box-shadow: 0 18px 30px -20px color-mix(in srgb, var(--accent) 70%, transparent);
}
.pm-ico {
    width: 40px; height: 40px; margin-bottom: .75rem; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; color: var(--accent);
    background: radial-gradient(circle at 30% 25%, color-mix(in srgb, var(--accent) 28%, transparent), var(--accent-soft));
    border: 1px solid color-mix(in srgb, var(--accent) 35%, transparent);
    box-shadow: 0 1px 0 rgba(255,255,255,.12) inset;
    transition: transform .2s ease;
}
.pm-ico svg { width: 18px; height: 18px; }
.pm-card:hover .pm-ico { transform: scale(1.08) rotate(-3deg); }
.pm-card h3 { font-size: .9rem; font-weight: 600; }
.pm-card p { margin-top: .25rem; font-size: .78rem; line-height: 1.5; color: var(--ink-soft); }

.pm-contacts { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .8rem; margin-top: 1.35rem; }
.pm-contacts .ab-contact {
    padding: .8rem 1rem; border-radius: 14px;
    background: color-mix(in srgb, var(--paper) 70%, transparent);
    border-color: color-mix(in srgb, var(--accent) 25%, var(--line));
}
.pm-contacts .ab-contact:hover {
    border-color: color-mix(in srgb, var(--accent) 60%, var(--line));
    box-shadow: 0 14px 26px -18px color-mix(in srgb, var(--accent) 70%, transparent);
}
.pm-contacts .ab-contact strong { font-size: .9rem; }

@media (max-width: 640px) { .pm-contacts { grid-template-columns: minmax(0, 1fr); } }
@media (max-width: 1024px) { .pm-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 640px) {
    .ab-premium { padding: 1.1rem; }
    .pm-grid { grid-template-columns: minmax(0, 1fr); }
    
}
@media (prefers-reduced-motion: reduce) {
    .ab-premium::after { animation: none; display: none; }
    .pm-card, .pm-ico { transition: none; }
}
    
    .ab-badge {
        display: inline-block; margin-bottom: .6rem; padding: .18rem .6rem; border-radius: 9999px;
        font-size: .62rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase;
        background: var(--accent-soft); color: var(--accent-ink);
    }
    

    /* ---------- Contact cards ---------- */
    .ab-contact {
        display: flex; align-items: center; gap: .7rem; padding: .65rem .8rem; min-width: 0;
        border-radius: 11px; border: 1px solid var(--line); background: var(--paper); color: var(--ink);
        transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
    }
    .ab-contact:hover {
        transform: translateY(-1px);
        border-color: color-mix(in srgb, var(--accent) 45%, var(--line));
        box-shadow: 0 8px 16px -12px rgba(0,0,0,.5);
    }
    .ab-contact .ab-icon { margin: 0; width: 32px; height: 32px; flex-shrink: 0; }
    .ab-contact small { display: block; font-size: .6rem; font-weight: 700; letter-spacing: .1em; color: var(--ink-soft); }
    .ab-contact strong { display: block; font-size: .82rem; font-weight: 600; overflow-wrap: anywhere; }

    /* ---------- Buttons ---------- */
    .ab-actions { display: flex; flex-wrap: wrap; gap: .5rem; }
    .ab-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: .4rem;
        padding: .5rem 1rem; border-radius: 9px; font-size: .78rem; font-weight: 600;
        text-align: center; cursor: pointer; border: 1px solid transparent;
        transition: transform .15s ease, filter .15s ease, box-shadow .15s ease, background .15s ease;
    }
    .ab-btn:hover { transform: translateY(-1px); }
    .ab-btn.primary {
        background: var(--accent); color: #FFF;
        box-shadow: 0 6px 14px -8px color-mix(in srgb, var(--accent) 80%, transparent);
    }
    .ab-btn.primary:hover { filter: brightness(1.08); }
    .ab-btn.ghost { background: var(--paper); border-color: var(--line); color: var(--ink); }
    .ab-btn.ghost:hover { border-color: color-mix(in srgb, var(--accent) 45%, var(--line)); }

    /* ---------- Developer + final CTA ---------- */
    .ab-dev { display: flex; align-items: center; gap: 1.1rem; }
    .ab-devcard { padding: 1.4rem 1.5rem; border-color: color-mix(in srgb, var(--accent) 40%, var(--line)); }
    .ab-built { font-size: .72rem; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; color: var(--accent); }
    .ab-devname { font-size: 1.6rem; font-weight: 700; line-height: 1.15; letter-spacing: -.01em; color: var(--ink); margin-top: .1rem; }
    .ab-devcard .ab-lead { font-size: .9rem; }
    .ab-avatar {
        width: 60px; height: 60px; border-radius: 16px; flex-shrink: 0; font-size: 1.15rem;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; color: #FFF; background: linear-gradient(135deg, var(--accent), var(--accent-ink));
    }
    .ab-cta {
        text-align: center; padding: 1.75rem 1.25rem; position: relative; overflow: hidden; isolation: isolate;
        background: linear-gradient(135deg, var(--accent-soft), var(--surface) 70%);
        border-color: color-mix(in srgb, var(--accent) 40%, var(--line));
    }
    .ab-cta .ab-actions { justify-content: center; margin-top: 1rem; }

    /* ---------- Tip popup ---------- */
    .tp-backdrop {
        position: fixed; inset: 0; z-index: 50; display: flex; align-items: center; justify-content: center;
        padding: 1rem; background: rgba(0,0,0,.5); backdrop-filter: blur(3px);
        overflow-y: auto;
    }
    .tp-box {
        position: relative; width: 100%; max-width: 340px; overflow: hidden;
        background: var(--surface); border: 1px solid var(--line); border-radius: 16px;
        box-shadow: 0 20px 40px -12px rgba(0,0,0,.55);
    }
    .tp-box::before {
        content: ''; position: absolute; left: 50%; top: -70px; width: 220px; height: 140px; transform: translateX(-50%);
        background: radial-gradient(closest-side, color-mix(in srgb, var(--accent) 28%, transparent), transparent);
        pointer-events: none;
    }
    .tp-body { position: relative; padding: 1.25rem 1.1rem 1rem; text-align: center; }
    .tp-heart {
        width: 38px; height: 38px; margin: 0 auto .6rem; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        background: var(--accent); color: #FFF;
        box-shadow: 0 6px 14px -6px color-mix(in srgb, var(--accent) 80%, transparent);
    }
    .tp-title { font-size: 1rem; font-weight: 600; }
    .tp-sub { margin-top: .2rem; font-size: .76rem; line-height: 1.45; color: var(--ink-soft); }
    .tp-close {
        position: absolute; top: .55rem; right: .55rem; width: 26px; height: 26px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center; color: var(--ink-soft);
        background: transparent; border: 0; cursor: pointer; z-index: 2; transition: background .15s ease;
    }
    .tp-close:hover { background: var(--paper); }

    /* PayPal hosted button container (white so PayPal buttons stay readable in dark mode) */
    .tp-pay {
        margin-top: .9rem; padding: .7rem; border-radius: 10px;
        background: #fff; border: 1px solid var(--line);
        min-height: 70px; text-align: center;
        color: #2c2e2f; color-scheme: light;
    }
    /* PayPal renders its title, label and amount field inline, so they inherit the dark theme's light text. Force dark text on the white card. */
    .tp-pay input,
    .tp-pay label,
    .tp-pay select {
        color: #2c2e2f !important; background-color: #fff !important;
        -webkit-text-fill-color: #2c2e2f;
    }
    .tp-pay input::placeholder { color: #6c7378 !important; -webkit-text-fill-color: #6c7378; }
    .tp-pay > div:first-child,
    .tp-pay > div:first-child > div,
    .tp-pay > div:first-child span,
    .tp-pay > div:first-child p,
    .tp-pay > div:first-child h1,
    .tp-pay > div:first-child h2,
    .tp-pay > div:first-child h3 { color: #2c2e2f; }
    /* keep the pay buttons' own colors */
    .tp-pay iframe { color-scheme: light; }

    .tp-foot { padding: .55rem 1rem; text-align: center; font-size: .66rem; color: var(--ink-soft); border-top: 1px solid var(--line); background: var(--paper); }

    /* ---------- Responsive ---------- */
    @media (max-width: 1024px) {
        .ab-grid.g3, .ab-grid.g4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 640px) {
        .ab-wrap { padding: .75rem .75rem 2rem; gap: 1.4rem; }
        .ab-grid.g3, .ab-grid.g2 { grid-template-columns: minmax(0, 1fr); }
        .ab-hero { padding: 1.5rem .9rem 1.4rem; }
        .ab-panel { padding: 1rem; }
        .ab-actions { flex-direction: column; }
        .ab-btn { width: 100%; }
        .ab-dev { flex-direction: column; text-align: center; }
    }
    @media (prefers-reduced-motion: reduce) {
        .ab-wrap > *, .ab-dot { animation: none; }
        .ab-card, .ab-icon, .ab-btn, .ab-contact { transition: none; }
    }
</style>

<script>
    function aboutPage() {
        return {
            tipOpen: false,
            ppReady: false,
            ppError: false,

            openTip() {
                this.tipOpen = true;
                this.$nextTick(() => this.renderPaypal());
            },

            renderPaypal() {
                if (this.ppReady) return;
                this.ppError = false;

                const render = () => {
                    const el = document.getElementById('paypal-container-YZ8X3J48DPRPG');
                    if (!el) return;
                    el.innerHTML = '';
                    window.paypal.HostedButtons({ hostedButtonId: 'YZ8X3J48DPRPG' })
                        .render('#paypal-container-YZ8X3J48DPRPG')
                        .then(() => { this.ppReady = true; })
                        .catch(() => { this.ppError = true; });
                };

                if (window.paypal && window.paypal.HostedButtons) {
                    render();
                    return;
                }

                const s = document.createElement('script');
                s.src = 'https://www.paypal.com/sdk/js?client-id=BAAc1XAD-KqMcyZCGr4HEetT4xjgmriywx06x9JVcJu579UldIJy4v-7ptlixmWgt4on5HB9Xcym7GwTHY&components=hosted-buttons&enable-funding=venmo&currency=USD';
                s.onload = render;
                s.onerror = () => { this.ppError = true; };
                document.head.appendChild(s);
            },
        };
    }
</script>

<div class="ab-page" x-data="aboutPage()" @keydown.escape.window="tipOpen = false">
    <div class="ab-wrap">

        {{-- 1. HERO --}}
        <header class="ab-hero">
            <img src="{{ asset('images/logo.png') }}" alt="Pixel Lab"
                 class="w-10 h-10 rounded-lg object-contain mx-auto mb-2">
            <div class="ab-eyebrow">About the application</div>
            <h1 class="ledger-font">Powerful management. <span>Simple workflow.</span></h1>
            <p>
                One centralized system for managing your business operations: sales, customers,
                suppliers, products, inventory and expenses, all in a single organized workspace.
            </p>
            <div class="ab-pills">
                <span class="ab-pill">Version {{ $version }}</span>
                <span class="ab-pill ok"><span class="ab-dot"></span> Actively maintained</span>
            </div>
        </header>

        {{-- 2. FEATURES --}}
        <section>
            <div class="ab-head">
                <h2 class="ledger-font">Everything you need in one place</h2>
                <p>Manage your business operations from a single, organized workspace.</p>
            </div>
            <div class="ab-grid g4">
                @foreach ($features as $f)
                    <article class="ab-card ab-feat">
                        <div class="ab-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $f[2] !!}</svg>
                        </div>
                        <div>
                            <h3>{{ $f[0] }}</h3>
                            <p>{{ $f[1] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- 4 + 5. SUPPORT and SUPPORT THE PROJECT --}}
        <div class="ab-grid g2">

            <section class="ab-panel">
                <h2 class="ledger-font">Need help?</h2>
                <p>Found a problem or something isn't working correctly? Contact us and we'll help resolve it.</p>

                <div class="space-y-2 mt-4">
                    <a href="mailto:nisaraymal@gmail.com" class="ab-contact">
                        <span class="ab-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                        </span>
                        <span>
                            <small>EMAIL</small>
                            <strong>nisaraymal@gmail.com</strong>
                        </span>
                    </a>
                    <a href="https://wa.me/93764000785" target="_blank" rel="noopener" class="ab-contact">
                        <span class="ab-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.5 8.5 0 0 1-12.4 7.5L3 20.5l1.6-5.4A8.5 8.5 0 1 1 21 11.5z"/></svg>
                        </span>
                        <span>
                            <small>WHATSAPP</small>
                            <strong>+93 76 4000 785</strong>
                        </span>
                    </a>
                </div>
            </section>

            <section class="ab-panel ab-tip">
                <div class="ab-icon" style="background:var(--accent); color:#FFF;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                </div>
                <h2 class="ledger-font">Support the project</h2>
                <p class="ab-lead" style="max-width:24rem;">
                    If this system has made your work easier, your support helps fund future updates,
                    maintenance and new features.
                </p>
                <div class="mt-4">
                    <button type="button" @click="openTip()" class="ab-btn primary">Leave a Tip</button>
                </div>
            </section>
        </div>
{{-- 6. PREMIUM SERVICES --}}
<section class="ab-premium">
    <div class="pm-head">
        <div class="pm-crown">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7l4.5 4L12 4l4.5 7L21 7l-2 11H5L3 7z"/><path d="M5 21h14"/></svg>
        </div>
        <div>
            <span class="pm-eyebrow">Premium</span>
            <h2 class="pm-title ledger-font">Take your system further</h2>
        </div>
    </div>

    <p class="pm-lead">
        Need the system as a desktop or mobile application, or want to run it across multiple
        computers with shared data? Professional deployment and custom setups are available.
    </p>

    <div class="pm-grid">
        @foreach ($premium as $p)
            <article class="pm-card">
                <div class="pm-ico">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $p[2] !!}</svg>
                </div>
                <h3>{{ $p[0] }}</h3>
                <p>{{ $p[1] }}</p>
            </article>
        @endforeach
    </div>

    <div class="pm-contacts">
    <a href="mailto:nisaraymal@gmail.com?subject=Premium%20setup%20inquiry" class="ab-contact">
        <span class="ab-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
        </span>
        <span>
            <small>EMAIL</small>
            <strong>nisaraymal@gmail.com</strong>
        </span>
    </a>
    <a href="https://wa.me/93764000785" target="_blank" rel="noopener" class="ab-contact">
        <span class="ab-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.5 8.5 0 0 1-12.4 7.5L3 20.5l1.6-5.4A8.5 8.5 0 1 1 21 11.5z"/></svg>
        </span>
        <span>
            <small>WHATSAPP</small>
            <strong>+93 76 4000 785</strong>
        </span>
    </a>
</div>
</section>

        {{-- 7. DEVELOPER --}}
        <section class="ab-panel ab-devcard">
            <div class="ab-dev">
                <div class="ab-avatar">NA</div>
                <div>
                    <div class="ab-built">Built by</div>
                    <div class="ab-devname ledger-font">NiSaR Aloko</div>
                    <p class="ab-lead" style="margin-top:.3rem;">
                        Designed &amp; developed with purpose: practical, reliable and modern business management solutions.
                    </p>
                </div>
            </div>
        </section>

    </div>

    {{-- Tip popup --}}
    <div x-show="tipOpen" x-cloak x-transition.opacity
         class="tp-backdrop"
         @click.self="tipOpen = false"
         role="dialog" aria-modal="true" aria-labelledby="tip-title">
        <div x-show="tipOpen" x-transition.scale.95 class="tp-box">

            <button type="button" @click="tipOpen = false" aria-label="Close" class="tp-close">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>

            <div class="tp-body">
                <div class="tp-heart">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                </div>
                <h3 id="tip-title" class="tp-title ledger-font">Leave a tip</h3>
                <p class="tp-sub">Choose how you'd like to send it.</p>

                {{-- PayPal hosted button --}}
                <div class="tp-pay">
                    <div id="paypal-container-YZ8X3J48DPRPG"></div>
                    <p x-show="!ppReady && !ppError" class="tp-sub" style="color:#555;">Loading secure payment…</p>
                    <p x-show="ppError" x-cloak class="tp-sub" style="color:#b00020;">
                        Couldn't load PayPal. Check your internet connection and try again.
                    </p>
                </div>
            </div>

            <div class="tp-foot">Thank you for the support</div>
        </div>
    </div>
</div>
</x-layouts.pixel>