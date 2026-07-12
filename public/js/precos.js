(function () {
    'use strict';

    const state = {
        handlers: [],
        initialized: false,
    };

    function formatBrl(value, decimals) {
        return Number(value).toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL',
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        });
    }

    function setCycle(cycle) {
        const isAnnual = cycle === 'annual';

        document.querySelectorAll('[data-billing-cycle]').forEach((button) => {
            button.setAttribute('aria-pressed', button.dataset.billingCycle === cycle ? 'true' : 'false');
        });

        document.querySelectorAll('[data-plan-price]').forEach((price) => {
            const value = isAnnual ? price.dataset.annualMonthly : price.dataset.monthly;
            const number = Number(value || 0);
            const decimals = Number.isInteger(number) ? 0 : 2;
            const formatted = formatBrl(number, decimals).replace(/^R\$\s?/, 'R$\u00A0');
            price.innerHTML = `${formatted} <span>/mês</span>`;
        });

        document.querySelectorAll('[data-plan-billing-note]').forEach((note) => {
            note.textContent = isAnnual ? note.dataset.annualNote : note.dataset.monthlyNote;
        });
    }

    function cleanupPrecos() {
        state.handlers.forEach(({ element, handler }) => {
            element.removeEventListener('click', handler);
        });
        state.handlers = [];
        state.initialized = false;
        window._precosInitialized = false;
    }

    function initPrecos() {
        if (!document.querySelector('.pricing-page')) {
            return;
        }

        if (state.initialized) {
            cleanupPrecos();
        }

        document.querySelectorAll('[data-billing-cycle]').forEach((button) => {
            const handler = () => setCycle(button.dataset.billingCycle);
            button.addEventListener('click', handler);
            state.handlers.push({ element: button, handler });
        });

        setCycle('monthly');
        state.initialized = true;
        window._precosInitialized = true;
    }

    window.initPrecos = initPrecos;
    window.cleanupPrecos = cleanupPrecos;
    window._cleanupFunctions = window._cleanupFunctions || {};
    window._cleanupFunctions.initPrecos = cleanupPrecos;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPrecos, { once: true });
    } else {
        initPrecos();
    }
})();
