<!-- Resolve Duplicates Modal (Standalone for Edit Page) -->
<div class="inquiries-assign-modal" id="inquiryDuplicatesModalStandalone">
    <div class="inquiries-assign-backdrop" id="duplicatesCloseBackdropStandalone"></div>
    <div class="inquiries-assign-window" role="dialog" aria-modal="true" style="width: min(850px, calc(100vw - 32px)); max-height: 85vh; display: flex; flex-direction: column;">
        <div class="inquiries-assign-header">
            <div class="inquiries-assign-title" style="display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-exclamation-triangle-fill" style="color: #f59e0b;"></i>
                <span>Resolve Duplicate Inquiries</span>
            </div>
            <button type="button" class="inquiries-assign-close" id="duplicatesCloseBtnStandalone">&times;</button>
        </div>
        <div class="inquiries-assign-body" style="overflow-y: auto; flex: 1; padding: 20px;">
            <p style="margin-top: 0; font-size: 13px; color: #4b5563; margin-bottom: 20px;">
                The following incoming inquiries share the same **Company Name**. 
                The system recommends keeping the latest inquiry and deleting the older ones. Review and choose which ones to remove.
            </p>
            
            <div class="duplicates-groups-container">
                @include('admin.partials.resolve_duplicates_group', ['group' => $group, 'groupIdx' => 0])
            </div>
        </div>
        <div class="inquiries-assign-footer" style="padding: 14px 20px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; align-items: center; background: #f9fafb; gap: 12px;">
            <button type="button" class="inquiries-btn inquiries-btn-secondary" id="duplicatesCancelBtnStandalone" style="min-width: 90px;">Cancel</button>
            <button type="button" class="inquiries-btn inquiries-btn-primary" id="duplicatesBypassBtnStandalone" style="background: #3b82f6; border-color: #3b82f6; min-width: 100px; font-weight: 600;">
                <i class="bi bi-skip-forward-fill" style="margin-right: 4px;"></i>
                <span>Bypass</span>
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    var modal = document.getElementById('inquiryDuplicatesModalStandalone');
    if (!modal) return;
    
    function close() {
        modal.remove(); // completely remove from DOM when closed
    }

    document.getElementById('duplicatesCloseBtnStandalone').addEventListener('click', close);
    document.getElementById('duplicatesCancelBtnStandalone').addEventListener('click', close);
    document.getElementById('duplicatesCloseBackdropStandalone').addEventListener('click', close);
    
    document.getElementById('duplicatesBypassBtnStandalone').addEventListener('click', function() {
        close();
        if (window.__pendingAssignLeadBtn) {
            window.__bypassDuplicateCheck = true;
            window.__pendingAssignLeadBtn.click();
            window.__pendingAssignLeadBtn = null;
        }
    });

    // Single Delete Button Logic
    modal.addEventListener('click', function(e) {
        var btn = e.target.closest('.duplicate-single-delete-btn');
        if (!btn) return;

        var leadId = parseInt(btn.getAttribute('data-lead-id'), 10);
        if (isNaN(leadId)) return;

        if (!confirm('Are you sure you want to delete inquiry #SQL-' + leadId + '?')) return;

        btn.disabled = true;
        btn.style.opacity = '0.5';

        var csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content;
        fetch('{{ route("admin.inquiries.batch-delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ lead_ids: [leadId] })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                var row = btn.closest('tr');
                if (row) row.remove();
                
                // If only 1 row left (the current lead), close modal and auto-bypass
                var tbody = modal.querySelector('tbody');
                if (tbody && tbody.querySelectorAll('tr').length <= 1) {
                    document.getElementById('duplicatesBypassBtnStandalone').click();
                }
            } else {
                alert(data.message || 'An error occurred during deletion.');
                btn.disabled = false;
                btn.style.opacity = '1';
            }
        })
        .catch(function(err) {
            alert('An error occurred during deletion.');
            btn.disabled = false;
            btn.style.opacity = '1';
        });
    });
})();
</script>
