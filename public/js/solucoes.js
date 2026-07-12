(function () {
    'use strict';

    document.documentElement.classList.add('js');

    const state = {
        revealObserver: null,
        sectionObserver: null,
        listeners: [],
        initialized: false,
    };

    function cleanupSolucoes() {
        if (state.revealObserver) {
            state.revealObserver.disconnect();
            state.revealObserver = null;
        }

        if (state.sectionObserver) {
            state.sectionObserver.disconnect();
            state.sectionObserver = null;
        }

        state.listeners.forEach(({ element, event, handler }) => {
            element.removeEventListener(event, handler);
        });

        state.listeners = [];
        state.initialized = false;
        window._solucoesInitialized = false;
    }

    function initReveal() {
        const elements = document.querySelectorAll('[data-sol-reveal]');

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) {
            elements.forEach((element) => element.classList.add('is-visible'));
            return;
        }

        state.revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, {
            rootMargin: '0px 0px -8% 0px',
            threshold: 0.08,
        });

        elements.forEach((element) => state.revealObserver.observe(element));
    }

    function initSectionNavigation() {
        const links = [...document.querySelectorAll('.sol-anchor-link[href^="#"]')];
        const linkById = new Map(links.map((link) => [link.getAttribute('href').slice(1), link]));
        const sections = [...linkById.keys()]
            .map((id) => document.getElementById(id))
            .filter(Boolean);

        const activate = (id) => {
            links.forEach((link) => {
                const active = link.getAttribute('href') === `#${id}`;
                link.classList.toggle('is-active', active);
                if (active) {
                    link.setAttribute('aria-current', 'true');
                    link.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                } else {
                    link.removeAttribute('aria-current');
                }
            });
        };

        links.forEach((link) => {
            const handler = () => activate(link.getAttribute('href').slice(1));
            link.addEventListener('click', handler);
            state.listeners.push({ element: link, event: 'click', handler });
        });

        if (!('IntersectionObserver' in window)) return;

        state.sectionObserver = new IntersectionObserver((entries) => {
            const visible = entries
                .filter((entry) => entry.isIntersecting)
                .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];

            if (visible) activate(visible.target.id);
        }, {
            rootMargin: '-20% 0px -62% 0px',
            threshold: [0, 0.1, 0.25],
        });

        sections.forEach((section) => state.sectionObserver.observe(section));
    }

    function initSolucoes() {
        if (!document.querySelector('.solutions-page')) return;

        if (state.initialized) cleanupSolucoes();

        initReveal();
        initSectionNavigation();

        state.initialized = true;
        window._solucoesInitialized = true;
    }

    window.initSolucoes = initSolucoes;
    window.cleanupSolucoes = cleanupSolucoes;
    window._cleanupFunctions = window._cleanupFunctions || {};
    window._cleanupFunctions.initSolucoes = cleanupSolucoes;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSolucoes, { once: true });
    } else {
        initSolucoes();
    }
})();
