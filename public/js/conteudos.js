(function () {
    'use strict';

    document.documentElement.classList.add('js');

    function normalize(value) {
        return (value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

    function initReveals(root) {
        var items = Array.from(root.querySelectorAll('[data-content-reveal]'));
        if (!items.length) return;

        if (!('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            items.forEach(function (item) { item.classList.add('is-visible'); });
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -30px' });

        items.forEach(function (item) { observer.observe(item); });
    }

    function initLibrary(root) {
        var input = root.querySelector('#content-search');
        var buttons = Array.from(root.querySelectorAll('[data-content-filter]'));
        var cards = Array.from(root.querySelectorAll('[data-content-card]'));
        var counter = root.querySelector('#content-result-count');
        var empty = root.querySelector('#content-empty');
        if (!input || !buttons.length || !cards.length) return;

        var activeTopic = 'all';

        function applyFilters() {
            var term = normalize(input.value);
            var visible = 0;

            cards.forEach(function (card) {
                var topicMatches = activeTopic === 'all' || card.dataset.topic === activeTopic;
                var textMatches = !term || normalize(card.dataset.search).includes(term);
                var show = topicMatches && textMatches;
                card.hidden = !show;
                if (show) visible += 1;
            });

            counter.textContent = visible + (visible === 1 ? ' conteúdo encontrado' : ' conteúdos encontrados');
            empty.hidden = visible !== 0;
        }

        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                activeTopic = button.dataset.contentFilter;
                buttons.forEach(function (candidate) {
                    candidate.setAttribute('aria-pressed', candidate === button ? 'true' : 'false');
                });
                applyFilters();
            });
        });

        input.addEventListener('input', applyFilters);
    }

    function initHeroCard(root) {
        var card = root.querySelector('.ct-cover');
        if (!card || card.classList.contains('ct-cover--in-view')) return;
        root.classList.add('content-motion-ready');
        var isMobile = window.matchMedia('(max-width: 680px)').matches;
        var mobileActivationRatio = Number.parseFloat(card.dataset.mobileActivationRatio || '0.45');
        var mobileActivationLine = Number.parseFloat(card.dataset.mobileActivationLine || '0.72');

        var reveal = function () {
            card.classList.add('ct-cover--in-view');
        };

        if (!('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            reveal();
            return;
        }

        if (isMobile) {
            var checkMobilePosition = function () {
                var rect = card.getBoundingClientRect();
                var visibleHeight = Math.max(0, Math.min(rect.bottom, window.innerHeight) - Math.max(rect.top, 0));
                var visibleRatio = rect.height > 0 ? visibleHeight / rect.height : 0;
                var crossedActivationLine = rect.top <= window.innerHeight * mobileActivationLine;
                var userHasScrolled = window.scrollY > 48;

                if (!userHasScrolled || !crossedActivationLine || visibleRatio < mobileActivationRatio) return;

                window.removeEventListener('scroll', checkMobilePosition);
                reveal();
            };

            window.addEventListener('scroll', checkMobilePosition, { passive: true });

            if (window.scrollY > 48) {
                window.requestAnimationFrame(checkMobilePosition);
            }

            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            var reachedActivationArea = entries.some(function (entry) { return entry.isIntersecting; });

            if (!reachedActivationArea) return;
            observer.disconnect();
            reveal();
        }, {
            threshold: [0.18],
            rootMargin: '0px 0px -8% 0px'
        });

        observer.observe(card);
    }

    function initialize() {
        var root = document.querySelector('.content-hub');
        if (!root || root.dataset.initialized === 'true') return;
        root.dataset.initialized = 'true';
        initReveals(root);
        initHeroCard(root);
        initLibrary(root);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize, { once: true });
    } else {
        initialize();
    }

    document.addEventListener('spa:page-loaded', initialize);
})();
