// ============================================================
//  ApexSpend — transactions.js
//  Client-side search + filter for the Ledger page.
// ============================================================

(function () {
    'use strict';

    const searchInput = document.getElementById('txSearch');
    const filterBtns  = document.querySelectorAll('.filters button[data-filter]');
    const rows        = Array.from(document.querySelectorAll('#ledgerBody .led-grid'));
    const visibleEl   = document.getElementById('visibleCount');
    const emptyMsg    = document.getElementById('emptyMsg');
    const endMsg      = document.getElementById('endMsg');
    const total       = rows.length;

    let activeFilter = 'all';
    let activeQuery  = '';

    function applyFilters() {
        let visible = 0;

        rows.forEach((row) => {
            const matchesCategory =
                activeFilter === 'all' ||
                row.getAttribute('data-category') === activeFilter ||
                row.getAttribute('data-type') === activeFilter;

            const hay = row.getAttribute('data-hay') || '';
            const matchesQuery = activeQuery === '' || hay.indexOf(activeQuery) !== -1;

            const show = matchesCategory && matchesQuery;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        if (visibleEl) visibleEl.textContent = String(visible);

        // Empty / end messaging
        if (emptyMsg) emptyMsg.hidden = visible !== 0;
        if (endMsg)   endMsg.hidden   = visible === 0;
    }

    // ---- Search ----
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            activeQuery = (e.target.value || '').trim().toLowerCase();
            applyFilters();
        });
    }

    // ---- Filter chips ----
    filterBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            filterBtns.forEach((b) => b.classList.remove('is-on'));
            btn.classList.add('is-on');
            activeFilter = btn.getAttribute('data-filter') || 'all';
            applyFilters();
        });
    });

    // Initial state
    applyFilters();
})();
