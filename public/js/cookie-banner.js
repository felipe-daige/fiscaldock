(function () {
    'use strict';

    var STORAGE_KEY = 'fd-cookies-consent';
    var SCHEMA_VERSION = 1;
    var MAX_AGE_MS = 365 * 24 * 60 * 60 * 1000; // 12 meses

    function readStorage() {
        try {
            var raw = window.localStorage.getItem(STORAGE_KEY);
            if (!raw) return null;
            var parsed = JSON.parse(raw);
            if (!parsed || parsed.v !== SCHEMA_VERSION) return null;
            if (!parsed.ts) return null;
            var age = Date.now() - new Date(parsed.ts).getTime();
            if (isNaN(age) || age > MAX_AGE_MS) return null;
            return parsed;
        } catch (e) {
            return null;
        }
    }

    function writeStorage(choices) {
        var payload = {
            v: SCHEMA_VERSION,
            ts: new Date().toISOString(),
            choices: {
                necessarios: true,
                analise: !!choices.analise,
                marketing: !!choices.marketing,
            },
        };
        try {
            window.localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
        } catch (e) {
            // localStorage indisponivel (modo privado): banner reaparecera na proxima visita
        }
        window.fdConsent = {
            has: function (cat) { return !!payload.choices[cat]; },
            all: function () { return payload.choices; },
        };
        document.dispatchEvent(new CustomEvent('fd:consent-changed', { detail: payload.choices }));
    }

    function showEl(el) { if (!el) return; el.classList.remove('hidden'); el.classList.add('flex'); }
    function hideEl(el) { if (!el) return; el.classList.add('hidden'); el.classList.remove('flex'); }
    function showBanner(el) { if (!el) return; el.classList.remove('hidden'); }
    function hideBanner(el) { if (!el) return; el.classList.add('hidden'); }

    function syncModalFromStorage(modal, stored) {
        if (!modal) return;
        var analise = modal.querySelector('[data-category="analise"]');
        var marketing = modal.querySelector('[data-category="marketing"]');
        if (analise) analise.checked = !!(stored && stored.choices && stored.choices.analise);
        if (marketing) marketing.checked = !!(stored && stored.choices && stored.choices.marketing);
    }

    function init() {
        var banner = document.getElementById('fd-cookie-banner');
        var modal = document.getElementById('fd-cookie-modal');

        // Estado inicial
        var stored = readStorage();
        if (stored) {
            window.fdConsent = {
                has: function (cat) { return !!stored.choices[cat]; },
                all: function () { return stored.choices; },
            };
        } else {
            showBanner(banner);
        }

        // Botoes do banner
        document.addEventListener('click', function (event) {
            var target = event.target.closest('[data-action]');
            if (!target) return;
            var action = target.getAttribute('data-action');

            if (action === 'accept-all') {
                writeStorage({ analise: true, marketing: true });
                hideBanner(banner);
                hideEl(modal);
            } else if (action === 'reject-optional') {
                writeStorage({ analise: false, marketing: false });
                hideBanner(banner);
                hideEl(modal);
            } else if (action === 'open-cookie-settings') {
                syncModalFromStorage(modal, readStorage());
                showEl(modal);
            } else if (action === 'close-cookie-settings') {
                hideEl(modal);
            } else if (action === 'save-preferences') {
                var analise = modal && modal.querySelector('[data-category="analise"]');
                var marketing = modal && modal.querySelector('[data-category="marketing"]');
                writeStorage({
                    analise: !!(analise && analise.checked),
                    marketing: !!(marketing && marketing.checked),
                });
                hideEl(modal);
                hideBanner(banner);
            }
        });

        // Fechar modal clicando fora
        if (modal) {
            modal.addEventListener('click', function (event) {
                if (event.target === modal) hideEl(modal);
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
