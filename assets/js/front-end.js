(function () {
    'use strict';
    const container = document.getElementById('lcWpmethodsChatContainer');
    if (!container) {
        return;
    }
    const toggle = container.querySelector('.wpmesoch-chat-toggle');
    const options = container.querySelector('.wpmesoch-chat-options');
    const panel = container.querySelector('.wpmesoch-panel');
    const form = panel.querySelector('form');
    const messageInput = form.querySelector('textarea');
    const messenger = panel.querySelector('.wpmesoch-messenger');
    const single = container.classList.contains('wpmesoch-single');
    let activeLink = null;
    const welcome = container.querySelector('.wpmesoch-welcome');
    const badge = container.querySelector('.wpmesoch-welcome-badge');
    let welcomeTimer = null;
    const welcomeKey = 'wpmesoch-welcome-dismissed';

    function dismissWelcome() {
        if (!welcome) return;
        window.clearTimeout(welcomeTimer);
        welcome.hidden = true;
        if (badge) badge.hidden = true;
        container.classList.remove('wpmesoch-has-welcome');
        if (welcome.dataset.once === '1') {
            try { window.sessionStorage.setItem(welcomeKey, '1'); } catch (error) { /* Storage may be unavailable. */ }
        }
    }

    if (welcome) {
        let dismissed = false;
        if (welcome.dataset.once === '1') {
            try { dismissed = window.sessionStorage.getItem(welcomeKey) === '1'; } catch (error) { /* Use page-only behavior. */ }
        }
        if (!dismissed) {
            welcomeTimer = window.setTimeout(function () {
                if (container.classList.contains('wpmesoch-open')) return;
                welcome.hidden = false;
                if (badge) badge.hidden = false;
                container.classList.add('wpmesoch-has-welcome');
            }, Math.max(0, Math.min(300, Number(welcome.dataset.delay) || 0)) * 1000);
        }
        welcome.querySelector('.wpmesoch-welcome-message').addEventListener('click', function () {
            const launcher = toggle || container.querySelector('.wpmesoch-chat-btn');
            launcher.click();
            if (toggle) container.querySelector('.wpmesoch-chat-btn').focus();
        });
        welcome.querySelector('.wpmesoch-welcome-dismiss').addEventListener('click', function () {
            (toggle || container.querySelector('.wpmesoch-chat-btn')).focus();
        });
        container.addEventListener('click', dismissWelcome, true);
        window.addEventListener('pagehide', function () { window.clearTimeout(welcomeTimer); });
    }

    function setOpen(open) {
        container.classList.toggle('wpmesoch-open', open);
        if (toggle) {
            toggle.setAttribute('aria-expanded', String(open));
            toggle.querySelector('i').hidden = open;
            toggle.querySelector('span').hidden = !open;
        }
        options.hidden = single ? false : !open;
        if (activeLink) activeLink.setAttribute('aria-expanded', 'false');
        panel.hidden = true;
    }

    function closePanel() {
        panel.hidden = true;
        options.hidden = false;
        container.classList.toggle('wpmesoch-open', !single);
        if (activeLink) {
            activeLink.setAttribute('aria-expanded', 'false');
            activeLink.focus();
        }
    }

    function context(link) {
        let message = link.dataset.baseMessage || '';
        if (link.dataset.isProductTrack === '1') {
            const selects = Array.from(document.querySelectorAll('.variations_form .variations select'));
            if (selects.length && selects.every(function (select) { return select.value; })) {
                const attributes = selects.map(function (select) {
                    const row = select.closest('tr');
                    const label = row && row.querySelector('label');
                    return (label ? label.textContent.trim() : select.name) + ': ' + select.options[select.selectedIndex].text;
                });
                const price = document.querySelector('.woocommerce-variation-price .price');
                message += '\n' + attributes.join('\n') + (price ? '\n' + price.textContent.trim() : '');
            }
        }
        return message.trim();
    }

    function messageUrl(link, typedMessage) {
        const url = new URL(link.href);
        const parts = [url.searchParams.get('text') || '', context(link), typedMessage || ''].filter(Boolean);
        if (parts.length) {
            url.searchParams.set('text', parts.join('\n\n'));
        }
        return url.href;
    }

    setOpen(false);
    if (toggle) {
        toggle.hidden = false;
        toggle.addEventListener('click', function () {
            setOpen(toggle.getAttribute('aria-expanded') !== 'true');
        });
    }

    container.querySelectorAll('.wpmesoch-chat-btn').forEach(function (link) {

        const hasPanel = link.dataset.chatbox === '1';
        if (hasPanel) {
            link.setAttribute('aria-haspopup', 'dialog');
            link.setAttribute('aria-controls', 'wpmesoch-panel');
            link.setAttribute('aria-expanded', 'false');
        }
        link.addEventListener('click', function (event) {
            if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) {
                return;
            }
            if (!hasPanel) {
                if (link.dataset.service === 'whatsapp' || link.dataset.service === 'telegram') {
                    // Keep a real link for popup blockers, keyboard use and no-JS fallback.
                    const original = link.href;
                    link.href = messageUrl(link, '');
                    window.setTimeout(function () { link.href = original; }, 0);
                }
                return;
            }
            event.preventDefault();
            if (single && !panel.hidden) {
                closePanel();
                return;
            }
            activeLink = link;
            activeLink.setAttribute('aria-expanded', 'true');
            container.classList.add('wpmesoch-open');
            options.hidden = !single;
            panel.hidden = false;
            const hasComposer = link.dataset.service === 'whatsapp' || link.dataset.service === 'telegram';
            panel.dataset.service = link.dataset.service;
            panel.querySelector('#wpmesoch-panel-channel').textContent = link.dataset.label;
            form.hidden = !hasComposer;
            messenger.hidden = hasComposer;
            if (hasComposer) {
                form.querySelector('.wpmesoch-panel-note').textContent = form.querySelector('.wpmesoch-panel-note').dataset[link.dataset.service];
                form.querySelector('.wpmesoch-send').textContent = form.querySelector('.wpmesoch-send').dataset[link.dataset.service];
            }
            const continueLink = messenger.querySelector('a');
            continueLink.href = link.href;


            if (hasComposer) {
                messageInput.focus();
            } else {
                continueLink.focus();
            }
        });
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        if (activeLink) {
            window.open(messageUrl(activeLink, messageInput.value.trim()), '_blank', 'noopener,noreferrer');
        }
    });
    panel.querySelector('.wpmesoch-panel-close').addEventListener('click', closePanel);
    document.addEventListener('click', function (event) {
        if (!container.contains(event.target)) {
            const hadFocus = container.contains(document.activeElement);
            setOpen(false);
            if (hadFocus) {
                (toggle || container.querySelector('.wpmesoch-chat-btn')).focus();
            }
        }
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && welcome && !welcome.hidden) {
            const hadFocus = welcome.contains(document.activeElement);
            dismissWelcome();
            if (hadFocus) (toggle || container.querySelector('.wpmesoch-chat-btn')).focus();
        }
        if (event.key === 'Escape' && container.classList.contains('wpmesoch-open')) {
            if (!panel.hidden) {
                closePanel();
            } else {
                setOpen(false);
                (toggle || container.querySelector('.wpmesoch-chat-btn')).focus();
            }
        }
    });
}());
