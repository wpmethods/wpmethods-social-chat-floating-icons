jQuery(function ($) {
    'use strict';
    const root = document.querySelector('.wpmesoch-admin');
    if (!root) return;
    const form = root.querySelector('.wpmesoch-settings-form');
    const container = root.querySelector('#wpmesoch-admin-repeater-fields');
    const add = root.querySelector('#wpmesoch-admin-add-field');
    const template = root.querySelector('#wpmesoch-channel-template');
    const data = window.wpmesoch_data;
    let initialized = false;
    let counter = Math.max(0, ...Array.from(container.querySelectorAll('input[name]')).map(function (input) {
        const match = input.name.match(/\[wpmesoch_links\]\[(\d+)\]/);
        return match ? Number(match[1]) + 1 : 0;
    }));

    function dirty() {
        if (!initialized) return;
        root.querySelectorAll('.wpmesoch-save-notice').forEach(function (notice) { notice.hidden = true; });
        root.querySelector('.wpmesoch-save-status').textContent = data.unsaved;
    }
    function updatePreview(row) {
        const enabled = row.querySelector('.wpmesoch-channel-enabled').checked;
        row.classList.toggle('wpmesoch-channel-disabled', !enabled);
        row.querySelector('.wpmesoch-channel-state').textContent = enabled ? data.enabled : data.disabled;
        const get = key => row.querySelector('[data-field="' + key + '"]').value.trim();
        row.querySelector('.wpmesoch-channel-name').textContent = get('label') || data.channelLabel;
        const preview = row.querySelector('.wpmesoch-admin-preview-icon');
        preview.querySelector('i').className = get('icon') || 'fab fa-whatsapp';
        const valid = value => /^#[\da-f]{3}(?:[\da-f]{3})?$/i.test(value);
        const bg = valid(get('bg_color')) ? get('bg_color') : '#00da62';
        preview.style.color = valid(get('color')) ? get('color') : '#ffffff';
        preview.style.background = valid(get('s_bg_color')) ? 'linear-gradient(120deg,' + bg + ',' + get('s_bg_color') + ')' : bg;
    }
    function colorPickers(scope) {
        $(scope).find('.color-field').not('.wp-color-picker').each(function () {
            const input = this;
            $(input).wpColorPicker({
                change: function (event, ui) {
                    input.value = ui.color.toString();
                    const row = input.closest('.wpmesoch-admin-field-group');
                    if (row) updatePreview(row);
                    dirty();
                },
                clear: function () {
                    input.value = '';
                    const row = input.closest('.wpmesoch-admin-field-group');
                    if (row) updatePreview(row);
                    dirty();
                }
            });
        });
    }

    function refresh() {
        const rows = Array.from(container.children);
        add.hidden = false;
        root.querySelector('#wpmesoch-channel-count').textContent = data.channelCount.replace('%d', rows.length);
        rows.forEach(function (row, index) {
            row.querySelector('.wpmesoch-move-up').disabled = index === 0;
            row.querySelector('.wpmesoch-move-down').disabled = index === rows.length - 1;
            updatePreview(row);
        });
    }

    const tabs = Array.from(root.querySelectorAll('.wpmesoch-admin-tab-button'));
    const panels = Array.from(root.querySelectorAll('.wpmesoch-admin-tab-content'));
    const nav = root.querySelector('.wpmesoch-admin-tabs');
    nav.setAttribute('role', 'tablist');
    tabs.forEach(function (tab) { tab.setAttribute('role', 'tab'); });
    panels.forEach(function (panel) { panel.setAttribute('role', 'tabpanel'); });
    function selectTab(tab, focus) {
        tabs.forEach(function (item) {
            const active = item === tab;
            item.classList.toggle('active', active);
            item.setAttribute('aria-selected', String(active));
            item.tabIndex = active ? 0 : -1;
        });
        panels.forEach(function (panel) { panel.classList.toggle('active', panel.id === tab.getAttribute('aria-controls')); });
        if (focus) tab.focus();
    }
    tabs.forEach(function (tab, index) {
        tab.addEventListener('click', function () { selectTab(tab, false); });
        tab.addEventListener('keydown', function (event) {
            let next;
            const rtl = document.documentElement.dir === 'rtl';
            if (event.key === 'ArrowRight') next = (index + (rtl ? -1 : 1) + tabs.length) % tabs.length;
            if (event.key === 'ArrowLeft') next = (index + (rtl ? 1 : -1) + tabs.length) % tabs.length;
            if (event.key === 'Home') next = 0;
            if (event.key === 'End') next = tabs.length - 1;
            if (next !== undefined) { event.preventDefault(); selectTab(tabs[next], true); }
        });
    });
    selectTab(tabs[0], false);
    root.classList.add('wpmesoch-enhanced');
    colorPickers(root);
    refresh();
    initialized = true;

    add.addEventListener('click', function () {
        const fragment = template.content.cloneNode(true);
        fragment.querySelectorAll('[id], [for], [name]').forEach(function (element) {
            ['id', 'for', 'name'].forEach(function (attribute) {
                if (element.hasAttribute(attribute)) element.setAttribute(attribute, element.getAttribute(attribute).replace(/__INDEX__/g, counter));
            });
        });
        const row = fragment.firstElementChild;
        container.appendChild(fragment);
        counter++;
        colorPickers(row);
        refresh();
        dirty();
        row.querySelector('[data-field="url"]').focus();
    });
    container.addEventListener('click', function (event) {
        const button = event.target.closest('button');
        if (!button) return;
        const row = button.closest('.wpmesoch-admin-field-group');
        if (button.classList.contains('wpmesoch-admin-remove-field')) {
            const next = row.nextElementSibling || row.previousElementSibling;
            row.remove();
            refresh();
            (next ? next.querySelector('[data-field="url"]') : add).focus();
        } else if (button.classList.contains('wpmesoch-move-up') && row.previousElementSibling) {
            container.insertBefore(row, row.previousElementSibling);
            refresh();
            row.querySelector('[data-field="url"]').focus();
        } else if (button.classList.contains('wpmesoch-move-down') && row.nextElementSibling) {
            container.insertBefore(row.nextElementSibling, row);
            refresh();
            row.querySelector('[data-field="url"]').focus();
        } else return;
        dirty();
    });
    form.addEventListener('input', function (event) {
        const row = event.target.closest('.wpmesoch-admin-field-group');
        if (row) updatePreview(row);
        dirty();
    });
    form.addEventListener('change', function (event) {
        const row = event.target.closest('.wpmesoch-admin-field-group');
        if (row) updatePreview(row);
        dirty();
    });
    if (typeof Sortable !== 'undefined') {
        new Sortable(container, { animation: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 150, handle: '.wpmesoch-drag-handle', ghostClass: 'sortable-ghost', onEnd: function () { refresh(); dirty(); } });
    }
});
