/**
 * Panorama Fiscal reusável — hidrata 4 painéis (movimento mensal, mix CFOP,
 * concentração de contrapartes, saúde fiscal) a partir do endpoint
 * GET /app/panorama-fiscal, no 1º momento em que a raiz [data-panorama] fica visível.
 *
 * SPA-safe: instâncias ApexCharts e o IntersectionObserver vão em window._cleanupFunctions
 * (spa.js re-executa este IIFE a cada navegação; sem cleanup as instâncias vazam).
 * apexcharts.min.js pode ainda estar carregando quando este script roda → espera (whenApex).
 */
(function () {
    'use strict';

    window._panoramaCharts = window._panoramaCharts || [];

    function destroyAll() {
        (window._panoramaCharts || []).forEach(function (c) { try { c.destroy(); } catch (e) {} });
        window._panoramaCharts = [];
    }

    window._cleanupFunctions = window._cleanupFunctions || {};
    window._cleanupFunctions.panoramaFiscal = destroyAll;

    // Espera ApexCharts existir (carregado async em SPA). Desiste após ~5s.
    function whenApex(cb, tentativas) {
        tentativas = tentativas == null ? 25 : tentativas;
        if (typeof ApexCharts !== 'undefined') { cb(); return; }
        if (tentativas <= 0) { return; }
        setTimeout(function () { whenApex(cb, tentativas - 1); }, 200);
    }

    function setState(root, name) {
        ['loading', 'error'].forEach(function (s) {
            var el = root.querySelector('[data-pf-state="' + s + '"]');
            if (el) el.classList.toggle('hidden', s !== name);
        });
        var charts = root.querySelector('[data-pf-charts]');
        if (charts) charts.classList.toggle('hidden', name !== 'ok');
    }

    function brl(v) {
        return 'R$ ' + Number(v || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function push(chart) { chart.render(); window._panoramaCharts.push(chart); }

    function renderSerie(el, serie) {
        if (!el || !serie.length) { return; }
        push(new ApexCharts(el, {
            chart: { type: 'line', height: 200, toolbar: { show: false }, fontFamily: 'inherit' },
            series: [
                { name: 'Entradas', data: serie.map(function (p) { return p.entradas; }) },
                { name: 'Saídas', data: serie.map(function (p) { return p.saidas; }) },
            ],
            xaxis: { categories: serie.map(function (p) { return p.mes; }), labels: { style: { fontSize: '10px' } } },
            colors: ['#2563eb', '#0f766e'],
            stroke: { width: 2, curve: 'smooth' },
            dataLabels: { enabled: false },
            yaxis: { labels: { formatter: function (v) { return brl(v); }, style: { fontSize: '10px' } } },
            legend: { fontSize: '11px' },
            tooltip: { y: { formatter: function (v) { return brl(v); } } },
        }));
    }

    function renderDonut(el, mix) {
        if (!el || !mix.length) { return; }
        push(new ApexCharts(el, {
            chart: { type: 'donut', height: 200, fontFamily: 'inherit' },
            series: mix.map(function (c) { return c.valor; }),
            labels: mix.map(function (c) { return String(c.cfop) + ' · ' + c.descricao; }),
            legend: { fontSize: '10px', position: 'bottom' },
            dataLabels: { enabled: true, formatter: function (_v, o) { return mix[o.seriesIndex].pct + '%'; } },
            tooltip: { y: { formatter: function (v) { return brl(v); } } },
        }));
    }

    function renderConcentracao(el, conc) {
        if (!el || !conc.length) { return; }
        push(new ApexCharts(el, {
            chart: { type: 'bar', height: 200, toolbar: { show: false }, fontFamily: 'inherit' },
            plotOptions: { bar: { horizontal: true, barHeight: '60%' } },
            series: [{ name: '% do volume', data: conc.map(function (r) { return r.pct; }) }],
            xaxis: { categories: conc.map(function (r) { return r.nome; }), max: 100, labels: { style: { fontSize: '10px' } } },
            colors: ['#7c3aed'],
            dataLabels: { enabled: true, formatter: function (v) { return v + '%'; } },
            tooltip: { y: { formatter: function (v) { return v + '%'; } } },
        }));
    }

    function escapeHtml(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function renderSaude(root, saude) {
        var body = root.querySelector('[data-pf-saude-body]');
        if (!body) { return; }
        var parts = [];
        if (saude.score !== null && saude.score !== undefined) {
            parts.push('<span class="inline-flex items-center px-2 py-0.5 rounded text-white text-[10px] font-bold" style="background-color:#0f766e">Score ' + escapeHtml(saude.score) + '</span>');
        }
        if (saude.classificacao) { parts.push('<span class="text-[11px] text-gray-600">' + escapeHtml(saude.classificacao) + '</span>'); }
        parts.push('<span class="text-[11px] text-gray-500">' + escapeHtml(saude.divergencias_catalogo || 0) + ' item(ns) sem NCM</span>');
        body.innerHTML = parts.join('<br>');
    }

    function load(root) {
        if (root.dataset.pfLoaded === '1') { return; }
        root.dataset.pfLoaded = '1';
        setState(root, 'loading');

        var url = root.dataset.panoramaUrl + '?scope=' + encodeURIComponent(root.dataset.panoramaScope) +
            '&id=' + encodeURIComponent(root.dataset.panoramaId);

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { if (!r.ok) { throw new Error('http ' + r.status); } return r.json(); })
            .then(function (json) {
                var p = json.panorama;
                if (!p) {
                    setState(root, 'error');
                    var e = root.querySelector('[data-pf-state="error"]');
                    if (e) { e.textContent = 'Sem movimentação no acervo fiscal.'; }
                    return;
                }
                setState(root, 'ok');
                whenApex(function () {
                    renderSerie(root.querySelector('[data-pf-chart="serie"]'), p.serie_mensal || []);
                    renderDonut(root.querySelector('[data-pf-chart="cfop"]'), p.cfop_mix || []);
                    renderConcentracao(root.querySelector('[data-pf-chart="concentracao"]'), p.concentracao || []);
                });
                renderSaude(root, p.saude || {});
            })
            .catch(function () { root.dataset.pfLoaded = '0'; setState(root, 'error'); });
    }

    function observe() {
        var roots = document.querySelectorAll('[data-panorama]');
        if (!roots.length) { return; }
        if (typeof IntersectionObserver === 'undefined') {
            roots.forEach(load);
            return;
        }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) { if (e.isIntersecting) { load(e.target); io.unobserve(e.target); } });
        }, { threshold: 0.05 });
        roots.forEach(function (r) { io.observe(r); });
        window._cleanupFunctions.panoramaObserver = function () { io.disconnect(); };
    }

    destroyAll();
    observe();
})();
