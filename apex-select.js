/**
 * ApexSpend — apex-select.js
 * Upgrades every .input-shell category <select> into a shadcn-style dropdown:
 *   - floating dark popper panel (hairline border, soft shadow, rounded)
 *   - per-option category icon in its stored color
 *   - check mark on the selected row, hover/keyboard focus highlight
 *   - full keyboard support (arrows, Home/End, Enter, typeahead, Esc) + a11y
 *
 * The native <select> stays in the DOM (visually hidden). All existing page
 * JS keeps reading/writing .value as before — a value-setter bridge and a
 * mutation observer keep the UI in sync when options are rebuilt.
 */
(function () {
    'use strict';

    function esc(s) {
        return String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
    }

    function catIcon(name) {
        return window.ApexCategories ? window.ApexCategories.icon(name) : 'fa-tag';
    }

    function catColor(name) {
        return window.ApexCategories ? window.ApexCategories.color(name) : '#f59e0b';
    }

    function mount(select) {
        if (select.dataset.apexSelect) return;
        const shell = select.closest('.input-shell');
        if (!shell) return;
        select.dataset.apexSelect = '1';
        shell.classList.add('apx-select-shell');

        // Hide the native control but keep it functional for form logic.
        select.classList.add('apx-native-hidden');

        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'apx-trigger';
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');

        const panel = document.createElement('div');
        panel.className = 'apx-panel';
        panel.setAttribute('role', 'listbox');
        panel.hidden = true;

        shell.insertBefore(trigger, select);
        shell.appendChild(panel);

        let open = false;
        let focusIdx = -1;
        let typeQ = '';
        let typeT = 0;

        const opts = () => [...select.options];
        const selectedValue = () => (select.selectedIndex >= 0 && select.options[select.selectedIndex]) ? select.options[select.selectedIndex].value : '';

        function renderTrigger() {
            const v = selectedValue();
            const opt = opts().find((o) => o.value === v);
            trigger.innerHTML = ''
                + (opt ? `<i class="fa-solid ${esc(catIcon(opt.value))} apx-trig-ic" style="color:${esc(catColor(opt.value))}" aria-hidden="true"></i>` : '')
                + `<span class="apx-value">${esc(opt ? opt.textContent : 'Select…')}</span>`
                + `<i class="fa-solid fa-chevron-down apx-chev" aria-hidden="true"></i>`;
            trigger.classList.toggle('apx-is-placeholder', !opt);
        }

        function renderPanel() {
            const v = selectedValue();
            panel.innerHTML = opts().map((o, i) => ''
                + `<div class="apx-opt${o.value === v ? ' is-selected' : ''}" role="option" data-i="${i}" tabindex="-1">`
                +   `<i class="fa-solid ${esc(catIcon(o.value))}" style="color:${esc(catColor(o.value))}" aria-hidden="true"></i>`
                +   `<span class="apx-opt-label">${esc(o.textContent)}</span>`
                +   `<i class="fa-solid fa-check apx-check" aria-hidden="true"></i>`
                + `</div>`
            ).join('');
        }

        function syncLabel() {
            const cur = (select.selectedIndex >= 0 && select.options[select.selectedIndex]) ? select.options[select.selectedIndex].textContent : 'none';
            trigger.setAttribute('aria-label', 'Category: ' + cur);
        }

        function setFocus(i, scroll) {
            const els = panel.querySelectorAll('.apx-opt');
            if (!els.length) return;
            focusIdx = (i + els.length) % els.length;
            els.forEach((el, j) => el.classList.toggle('is-focused', j === focusIdx));
            if (scroll !== false) els[focusIdx].scrollIntoView({ block: 'nearest' });
        }

        function openPanel() {
            if (open) return;
            renderPanel();
            panel.classList.remove('is-anim');
            void panel.offsetWidth; // restart the pop-in animation
            panel.classList.add('is-anim');
            panel.hidden = false;
            open = true;
            trigger.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');

            // Flip above the trigger when there isn't room below.
            const r = shell.getBoundingClientRect();
            const vh = window.innerHeight || document.documentElement.clientHeight;
            panel.classList.toggle('apx-flip', vh - r.bottom < 300 && r.top > 300);

            const v = selectedValue();
            const idx = Math.max(0, opts().findIndex((o) => o.value === v));
            setFocus(idx, false);
            setTimeout(() => document.addEventListener('click', outsideClose), 0);
            document.addEventListener('keydown', keyNav);
        }

        function closePanel() {
            if (!open) return;
            panel.hidden = true;
            open = false;
            trigger.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
            document.removeEventListener('click', outsideClose);
            document.removeEventListener('keydown', keyNav);
        }

        function outsideClose(e) {
            if (!shell.contains(e.target)) closePanel();
        }

        function choose(i) {
            const list = opts();
            if (!list[i]) return;
            select.selectedIndex = i;
            renderTrigger();
            renderPanel();
            closePanel();
            trigger.focus();
            // Let existing page code react, same as a native change.
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function keyNav(e) {
            if (!open) return;
            const count = opts().length;
            if (e.key === 'Escape') { e.stopPropagation(); closePanel(); trigger.focus(); return; }
            if (e.key === 'ArrowDown') { e.preventDefault(); setFocus(focusIdx + 1); return; }
            if (e.key === 'ArrowUp') { e.preventDefault(); setFocus(focusIdx - 1); return; }
            if (e.key === 'Home') { e.preventDefault(); setFocus(0); return; }
            if (e.key === 'End') { e.preventDefault(); setFocus(count - 1); return; }
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); choose(focusIdx); return; }

            // Typeahead — jump to the next option matching the typed prefix.
            if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
                const now = Date.now();
                if (now - typeT > 600) typeQ = '';
                typeT = now;
                typeQ += e.key.toLowerCase();
                const start = focusIdx;
                for (let k = 1; k <= count; k++) {
                    const j = (start + k) % count;
                    if (select.options[j].text.toLowerCase().startsWith(typeQ)) {
                        setFocus(j);
                        break;
                    }
                }
            }
        }

        trigger.addEventListener('click', () => (open ? closePanel() : openPanel()));
        panel.addEventListener('click', (e) => {
            const row = e.target.closest('.apx-opt');
            if (row) choose(Number(row.dataset.i));
        });

        // External .value writes (e.g. edit-modal prefill) keep the UI in sync.
        const desc = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'value');
        Object.defineProperty(select, 'value', {
            get() { return desc.get.call(select); },
            set(v) {
                desc.set.call(select, v);
                renderTrigger();
                renderPanel();
                syncLabel();
            },
        });

        // Options rebuilt (populateSelect after quick-add) → re-render.
        const mo = new MutationObserver(() => {
            renderTrigger();
            renderPanel();
            syncLabel();
        });
        mo.observe(select, { childList: true });

        renderTrigger();
        renderPanel();
        syncLabel();
    }

    function upgradeAll() {
        document.querySelectorAll('.input-shell select[id$="Category"], .input-shell select[data-apex-select]').forEach(mount);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', upgradeAll);
    } else {
        upgradeAll();
    }

    // Selects injected later (modals built by JS, e.g. quickadd) get picked up.
    new MutationObserver(() => {
        document.querySelectorAll('.input-shell select[id$="Category"], .input-shell select[data-apex-select]').forEach((s) => {
            if (!s.dataset.apexSelect) mount(s);
        });
    }).observe(document.body, { childList: true, subtree: true });
})();
