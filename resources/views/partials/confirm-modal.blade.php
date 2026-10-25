<style>
    .pxc-backdrop {
        position: fixed; inset: 0; z-index: 9999;
        display: flex; align-items: center; justify-content: center; padding: 1.25rem;
        background: rgba(15, 17, 22, 0.55);
        backdrop-filter: blur(3px);
        opacity: 0; transition: opacity .16s ease;
    }
    .pxc-backdrop[hidden] { display: none; }
    .pxc-backdrop.show { opacity: 1; }

    .pxc-card {
        width: 100%; max-width: 24rem; text-align: center;
        padding: 1.75rem 1.5rem 1.4rem;
        background: var(--surface); color: var(--ink);
        border: 1px solid var(--line); border-radius: 16px;
        box-shadow: 0 1px 0 rgba(255,255,255,0.06) inset, 0 24px 48px -12px rgba(0,0,0,0.45);
        transform: translateY(8px) scale(.97);
        transition: transform .18s cubic-bezier(.2, .8, .2, 1);
    }
    .pxc-backdrop.show .pxc-card { transform: none; }

    .pxc-icon {
        width: 52px; height: 52px; margin: 0 auto 1rem; border-radius: 9999px;
        display: flex; align-items: center; justify-content: center;
        background: var(--danger-soft); color: var(--danger);
        box-shadow: 0 2px 6px rgba(33,38,47,0.15), 0 1px 0 rgba(255,255,255,0.4) inset;
    }
    .pxc-backdrop.warn .pxc-icon { background: var(--warn-soft); color: var(--warn); }
    .pxc-icon svg { display: none; }
    .pxc-backdrop:not(.warn) .pxc-ico-trash { display: block; }
    .pxc-backdrop.warn .pxc-ico-warn { display: block; }

    .pxc-title { font-size: 1.25rem; line-height: 1.3; margin: 0 0 .4rem; }
    .pxc-text  { font-size: .875rem; line-height: 1.5; margin: 0 0 1.4rem; color: var(--ink-soft); }

    .pxc-actions { display: flex; gap: .6rem; }
    .pxc-btn {
        flex: 1; padding: .65rem 1rem; border-radius: .6rem;
        font-size: .875rem; font-weight: 600; cursor: pointer;
        transition: transform .1s ease, background .15s ease, box-shadow .15s ease;
    }
    .pxc-btn:active { transform: scale(.97); }
    .pxc-cancel { background: var(--paper); color: var(--ink); border: 1px solid var(--line); }
    .pxc-cancel:hover { background: var(--surface); }
    .pxc-ok { background: var(--danger); color: #FFF; border: 1px solid transparent; }
    .pxc-backdrop.warn .pxc-ok { background: var(--accent); }
    .pxc-ok:hover { box-shadow: 0 6px 14px -6px rgba(0,0,0,0.45); }
    .pxc-btn:focus-visible { outline: none; box-shadow: 0 0 0 2px var(--accent); }
</style>

<div id="pxConfirmRoot" class="pxc-backdrop" hidden>
    <div class="pxc-card" role="alertdialog" aria-modal="true" aria-labelledby="pxcTitle" aria-describedby="pxcText">
        <div class="pxc-icon">
            <svg class="pxc-ico-trash" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6"/>
                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                <path d="M10 11v6"/><path d="M14 11v6"/>
                <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
            </svg>
            <svg class="pxc-ico-warn" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
        </div>
        <h3 id="pxcTitle" class="pxc-title ledger-font"></h3>
        <p id="pxcText" class="pxc-text"></p>
        <div class="pxc-actions">
            <button type="button" id="pxcCancel" class="pxc-btn pxc-cancel">Cancel</button>
            <button type="button" id="pxcOk" class="pxc-btn pxc-ok">Confirm</button>
        </div>
    </div>
</div>

<script>
(function () {
    var root = document.getElementById('pxConfirmRoot');
    if (!root) return;

    var titleEl = document.getElementById('pxcTitle');
    var textEl  = document.getElementById('pxcText');
    var okBtn   = document.getElementById('pxcOk');
    var cancel  = document.getElementById('pxcCancel');
    var pending = null;      // { run, onCancel }
    var lastFocus = null;

    // Finds confirm('...') or confirm("...") inside an inline onsubmit / onclick attribute
    var RE = /confirm\(\s*(['"])((?:\\.|(?!\1)[^\\])*)\1\s*\)/;

    function unescapeJs(s) {
        return s.replace(/\\u([0-9a-fA-F]{4})/g, function (_, h) { return String.fromCharCode(parseInt(h, 16)); })
                .replace(/\\n/g, ' ')
                .replace(/\\(.)/g, '$1');
    }

    // "Delete Jamey? This cannot be undone." -> title + text + button label + style
    function describe(msg) {
        var m = msg.match(/^([^?]*\?)\s*([\s\S]*)$/);
        var verbMatch = msg.match(/^\s*(delete|remove|cancel|void|discard|clear|reset|erase|deactivate|reactivate|restore|archive|approve|pay|send|mark|apply)\b/i);
        var danger = /\b(delete|remove|cancel|void|discard|erase|replace|permanent|cannot be undone|can't be undone)\b/i.test(msg);
        return {
            title:  m ? m[1] : msg,
            text:   m ? m[2] : '',
            button: verbMatch ? verbMatch[1].charAt(0).toUpperCase() + verbMatch[1].slice(1).toLowerCase() : 'Confirm',
            type:   danger ? 'danger' : 'warning'
        };
    }

    function fromForm(form) {
        if (form.hasAttribute('data-confirm')) {
            return {
                title:  form.dataset.confirm || 'Are you sure?',
                text:   form.dataset.confirmText || '',
                button: form.dataset.confirmButton || 'Confirm',
                type:   form.dataset.confirmType === 'warning' ? 'warning' : 'danger'
            };
        }
        var m = (form.getAttribute('onsubmit') || '').match(RE);
        return m ? describe(unescapeJs(m[2])) : null;
    }

    function open(info, run, onCancel) {
        if (pending && pending.onCancel) pending.onCancel();   // a popup was already open
        pending = { run: run, onCancel: onCancel || null };
        lastFocus = document.activeElement;

        titleEl.textContent = info.title;
        textEl.textContent  = info.text;
        textEl.style.display = info.text ? '' : 'none';
        okBtn.textContent   = info.button;
        root.classList.toggle('warn', info.type === 'warning');

        root.hidden = false;
        requestAnimationFrame(function () { root.classList.add('show'); });
        cancel.focus();   // safe default: pressing Enter does not confirm
    }

    function close(confirmed) {
        var p = pending;
        pending = null;
        root.classList.remove('show');
        setTimeout(function () { root.hidden = true; }, 160);
        if (lastFocus && lastFocus.focus) lastFocus.focus();
        if (!confirmed && p && p.onCancel) p.onCancel();
    }

    // For your own JavaScript:  if (!(await pxConfirm('Remove this employee?'))) return;
    window.pxConfirm = function (message, opts) {
        opts = opts || {};
        return new Promise(function (resolve) {
            var info = describe(String(message));
            if (opts.title)  info.title  = opts.title;
            if (opts.text !== undefined) info.text = opts.text;
            if (opts.button) info.button = opts.button;
            if (opts.type)   info.type   = opts.type;
            open(info, function () { resolve(true); }, function () { resolve(false); });
        });
    };

    // Forms: data-confirm, or onsubmit="return confirm('...')"
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || !form.getAttribute) return;
        if (form.__pxOk) { form.__pxOk = false; return; }   // already confirmed

        var info = fromForm(form);
        if (!info) return;

        e.preventDefault();
        e.stopImmediatePropagation();
        var submitter = e.submitter || null;

        open(info, function () {
            form.__pxOk = true;
            form.removeAttribute('onsubmit');   // so the native confirm() doesn't run again
            if (form.requestSubmit) form.requestSubmit(submitter || undefined);
            else form.submit();
        });
    }, true);

    // Buttons and links: onclick="return confirm('...')"
    document.addEventListener('click', function (e) {
        var el = e.target.closest ? e.target.closest('[onclick*="confirm("]') : null;
        if (!el) return;

        var m = (el.getAttribute('onclick') || '').match(RE);
        if (!m) return;   // not a simple confirm('...'): leave it alone

        e.preventDefault();
        e.stopImmediatePropagation();

        open(describe(unescapeJs(m[2])), function () {
            el.removeAttribute('onclick');
            if (el.tagName === 'A' && el.href) {
                window.location.href = el.href;
            } else if (el.form) {
                if (el.form.requestSubmit) el.form.requestSubmit(el); else el.form.submit();
            } else {
                el.click();
            }
        });
    }, true);

    okBtn.addEventListener('click', function () {
        var p = pending;
        if (!p) return;
        close(true);
        p.run();
    });
    cancel.addEventListener('click', function () { close(false); });
    root.addEventListener('click', function (e) { if (e.target === root) close(false); });
    document.addEventListener('keydown', function (e) {
        if (!pending) return;
        if (e.key === 'Escape') close(false);
        if (e.key === 'Tab') {                    // keep focus inside the dialog
            e.preventDefault();
            (document.activeElement === cancel ? okBtn : cancel).focus();
        }
    });
})();
</script>