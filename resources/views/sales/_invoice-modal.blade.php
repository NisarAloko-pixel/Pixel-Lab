{{-- Invoice popup. Include once per page: @include('sales._invoice-modal')
     Open it from any element that has a data-invoice-url="..." attribute. --}}

<div id="invoice_modal" role="dialog" aria-modal="true" aria-labelledby="invoice_modal_title"
    style="display:none; position:fixed; inset:0; z-index:50; align-items:center; justify-content:center; padding:16px; background:rgba(15,18,25,.65);">

    <div id="invoice_modal_panel"
        style="background:var(--surface); border-radius:10px; width:100%; max-width:900px; height:92vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 20px 50px rgba(0,0,0,.4);">

        <div class="flex items-center justify-between px-5 py-3" style="border-bottom:1px solid var(--line);">
            <h2 id="invoice_modal_title" class="ledger-font text-lg">Invoice</h2>
            <div class="flex gap-2">
                <button type="button" id="invoice_print_btn"
                    class="text-sm font-semibold px-4 py-2 rounded-md"
                    style="background:var(--ink); color:var(--paper);">Print</button>
                <button type="button" id="invoice_close_btn"
                    class="text-sm font-medium px-4 py-2 rounded-md"
                    style="border:1px solid var(--line); color:var(--ink);">Close</button>
            </div>
        </div>

        <iframe id="invoice_frame" title="Invoice preview"
            style="flex:1; width:100%; border:0; background:transparent;"></iframe>
    </div>
</div>

<script>
    (function () {
        const modal = document.getElementById('invoice_modal');
        const panel = document.getElementById('invoice_modal_panel');
        const frame = document.getElementById('invoice_frame');
        const printBtn = document.getElementById('invoice_print_btn');
        const closeBtn = document.getElementById('invoice_close_btn');

        // Reads the popup's own background, which already follows the app's theme,
        // so this works no matter how the light/dark switch is implemented.
        function isDark() {
            const bg = getComputedStyle(panel).backgroundColor.match(/[\d.]+/g);
            if (!bg) return false;
            const [r, g, b] = bg.map(Number);
            return (0.2126 * r + 0.7152 * g + 0.0722 * b) < 128;
        }

        function openInvoice(url) {
            const sep = url.includes('?') ? '&' : '?';
            frame.src = url + sep + 'embed=1&theme=' + (isDark() ? 'dark' : 'light');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            closeBtn.focus();
        }

        function closeInvoice() {
            modal.style.display = 'none';
            frame.src = 'about:blank';
            document.body.style.overflow = '';
        }

        // Works for buttons added later too (e.g. after pagination or filtering).
        document.addEventListener('click', function (e) {
            const trigger = e.target.closest('[data-invoice-url]');
            if (trigger) {
                e.preventDefault();
                openInvoice(trigger.dataset.invoiceUrl);
            }
        });

        // Prints only the invoice, never the page behind it.
        printBtn.addEventListener('click', function () {
            frame.contentWindow.focus();
            frame.contentWindow.print();
        });

        closeBtn.addEventListener('click', closeInvoice);
        modal.addEventListener('click', function (e) { if (e.target === modal) closeInvoice(); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.style.display !== 'none') closeInvoice();
        });

        // Lets other scripts open it, e.g. right after a sale is created.
        window.openInvoice = openInvoice;
    })();
</script>