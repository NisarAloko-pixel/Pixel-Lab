<div id="cancelReasonModal" class="fixed inset-0 z-50 hidden items-center justify-center" style="background:rgba(20,22,26,.45);">
    <div class="w-full max-w-sm mx-4 rounded-lg p-6" style="background:var(--surface); border:1px solid var(--line);">
        <h3 class="ledger-font text-lg mb-1">Cancel this sale?</h3>
        <p class="text-sm mb-4" style="color:var(--ink-soft);">This reverses inventory changes. You can note why below (optional).</p>
        <textarea id="cancelReasonInput" rows="3" placeholder="Reason for cancelling (optional)"
            class="w-full rounded-md px-3 py-2 text-sm mb-4 focus:outline-none focus:ring-2"
            style="border:1px solid var(--line); background:var(--paper); --tw-ring-color:var(--accent);"></textarea>
        <div class="flex justify-end gap-2">
            <button type="button" id="cancelReasonDismiss" class="text-sm font-medium px-4 py-2 rounded-md" style="background:var(--paper); color:var(--ink-soft); border:1px solid var(--line);">
                Keep sale
            </button>
            <button type="button" id="cancelReasonConfirm" class="text-sm font-semibold px-4 py-2 rounded-md" style="background:var(--danger); color:#FFF;">
                Cancel sale
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    const modal = document.getElementById('cancelReasonModal');
    const textarea = document.getElementById('cancelReasonInput');
    const dismissBtn = document.getElementById('cancelReasonDismiss');
    const confirmBtn = document.getElementById('cancelReasonConfirm');
    let activeForm = null;

    function openModal(form) {
        activeForm = form;
        textarea.value = '';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        activeForm = null;
    }

    document.querySelectorAll('.cancel-sale-form').forEach(form => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            openModal(form);
        });
    });

    dismissBtn.addEventListener('click', closeModal);
    confirmBtn.addEventListener('click', () => {
        if (!activeForm) return;
        const reasonField = activeForm.querySelector('input[name="reason"]');
        if (reasonField) reasonField.value = textarea.value;
        activeForm.submit();
    });

    modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
})();
</script>