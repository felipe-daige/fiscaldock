(function () {
    'use strict';

    function countActiveControls(container) {
        var total = 0;

        container.querySelectorAll('input, select').forEach(function (control) {
            if (control.disabled || control.type === 'hidden' || control.type === 'submit' || control.type === 'button') return;

            if (control.type === 'checkbox' || control.type === 'radio') {
                if (control.checked) total += 1;
                return;
            }

            var value = String(control.value || '').trim().toLowerCase();
            if (value !== '' && value !== 'todos' && value !== 'todas' && value !== '0') total += 1;
        });

        return total;
    }

    function enhance(container) {
        if (container.dataset.mobileFiltersReady === '1' || container.hasAttribute('data-mobile-filters-native')) return;

        container.dataset.mobileFiltersReady = '1';

        var children = Array.prototype.slice.call(container.childNodes);
        var panel = document.createElement('div');
        panel.className = 'mobile-filter-panel';

        children.forEach(function (child) {
            panel.appendChild(child);
        });

        var firstElement = panel.firstElementChild;
        if (firstElement) {
            var heading = firstElement.textContent.trim().toLowerCase();
            if (heading === 'filtro' || heading === 'filtros' || heading.indexOf('filtros\n') === 0) {
                firstElement.classList.add('mobile-filter-original-header');
            }
        }

        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'mobile-filter-toggle';
        button.setAttribute('aria-expanded', 'false');

        var active = countActiveControls(panel);
        button.innerHTML = '<span class="mobile-filter-toggle__label">Filtros' +
            (active > 0 ? '<span class="mobile-filter-toggle__count">' + active + '</span>' : '') +
            '</span><svg class="mobile-filter-toggle__chevron" aria-hidden="true" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>';

        button.addEventListener('click', function () {
            var expanded = container.classList.toggle('mobile-filters-open');
            button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        });

        container.appendChild(button);
        container.appendChild(panel);
        container.classList.add('mobile-filters-enhanced');
    }

    function init(root) {
        var scope = root && root.querySelectorAll ? root : document;
        scope.querySelectorAll('[data-mobile-filters]').forEach(enhance);
    }

    function boot() {
        init(document);

        var app = document.getElementById('app');
        if (!app || typeof MutationObserver === 'undefined') return;

        var queued = false;
        new MutationObserver(function () {
            if (queued) return;
            queued = true;
            window.setTimeout(function () {
                queued = false;
                init(app);
            }, 0);
        }).observe(app, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, { once: true });
    } else {
        boot();
    }
})();
