/**
 * Analytics Dashboard - JavaScript
 * Gerencia os graficos e filtros do BI Fiscal
 */

(function() {
    'use strict';

    // Estado global
    let charts = {};
    let currentTab = 'faturamento';
    let csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // Inicializacao
    function init() {
        setupTabs();
        setupFilters();
        loadData(currentTab);
    }

    // Configura as tabs
    function setupTabs() {
        document.querySelectorAll('.analytics-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const tabName = this.dataset.tab;
                switchTab(tabName);
            });
        });
    }

    // Troca de tab
    function switchTab(tabName) {
        // Atualiza tabs
        document.querySelectorAll('.analytics-tab').forEach(tab => {
            if (tab.dataset.tab === tabName) {
                tab.classList.add('active', 'border-blue-500', 'text-blue-600');
                tab.classList.remove('border-transparent', 'text-gray-500');
            } else {
                tab.classList.remove('active', 'border-blue-500', 'text-blue-600');
                tab.classList.add('border-transparent', 'text-gray-500');
            }
        });

        // Atualiza conteudo
        document.querySelectorAll('.analytics-tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        const targetContent = document.getElementById('tab-' + tabName);
        if (targetContent) {
            targetContent.classList.remove('hidden');
        }

        currentTab = tabName;
        loadData(tabName);
    }

    // Configura filtros
    function setupFilters() {
        const filtroCliente = document.getElementById('filtro-cliente');
        const filtroPeriodo = document.getElementById('filtro-periodo');

        if (filtroCliente) {
            filtroCliente.addEventListener('change', () => loadData(currentTab));
        }

        if (filtroPeriodo) {
            filtroPeriodo.addEventListener('change', () => loadData(currentTab));
        }
    }

    // Obtem parametros de filtro
    function getFilterParams() {
        const clienteId = document.getElementById('filtro-cliente')?.value;
        const meses = parseInt(document.getElementById('filtro-periodo')?.value || 12);

        const dataFim = new Date();
        const dataInicio = new Date();
        dataInicio.setMonth(dataInicio.getMonth() - meses);

        const params = new URLSearchParams();
        if (clienteId) params.append('cliente_id', clienteId);
        params.append('data_inicio', dataInicio.toISOString().split('T')[0]);
        params.append('data_fim', dataFim.toISOString().split('T')[0]);

        return params.toString();
    }

    // Carrega dados da API
    async function loadData(tabName) {
        const params = getFilterParams();
        let endpoint = '';

        switch (tabName) {
            case 'faturamento':
                endpoint = '/app/analytics/faturamento';
                break;
            case 'compras':
                endpoint = '/app/analytics/compras';
                break;
            case 'tributos':
                endpoint = '/app/analytics/tributos';
                break;
        }

        try {
            const response = await fetch(`${endpoint}?${params}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Erro ao carregar dados');

            const data = await response.json();
            renderCharts(tabName, data);
            hideEmptyState();
        } catch (error) {
            console.error('Erro:', error);
            showEmptyState();
        }
    }

    // Renderiza graficos baseado na tab
    function renderCharts(tabName, data) {
        switch (tabName) {
            case 'faturamento':
                renderFaturamentoCharts(data);
                break;
            case 'compras':
                renderComprasCharts(data);
                break;
            case 'tributos':
                renderTributosCharts(data);
                break;
        }
    }

    // Graficos de Faturamento
    function renderFaturamentoCharts(data) {
        // Faturamento Mensal
        if (data.faturamento_mensal && data.faturamento_mensal.length > 0) {
            renderChart('chart-faturamento', {
                chart: { type: 'area', height: 320, toolbar: { show: false } },
                series: [{
                    name: 'Faturamento',
                    data: data.faturamento_mensal.map(d => d.faturamento)
                }],
                xaxis: {
                    categories: data.faturamento_mensal.map(d => d.mes_formatado)
                },
                yaxis: {
                    labels: {
                        formatter: (val) => formatCurrency(val)
                    }
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.1 } },
                colors: ['#3b82f6'],
                tooltip: {
                    y: { formatter: (val) => formatCurrency(val) }
                }
            });
        }

        // Top Clientes
        if (data.top_clientes && data.top_clientes.length > 0) {
            renderChart('chart-top-clientes', {
                chart: { type: 'bar', height: 320, toolbar: { show: false } },
                series: [{
                    name: 'Valor',
                    data: data.top_clientes.map(d => d.total)
                }],
                xaxis: {
                    categories: data.top_clientes.map(d => truncate(d.razao_social || d.cnpj, 20)),
                    labels: { rotate: -45, style: { fontSize: '10px' } }
                },
                yaxis: {
                    labels: { formatter: (val) => formatCurrency(val) }
                },
                plotOptions: { bar: { horizontal: false, columnWidth: '60%' } },
                dataLabels: { enabled: false },
                colors: ['#22c55e'],
                tooltip: {
                    y: { formatter: (val) => formatCurrency(val) }
                }
            });
        }

        // Faturamento por UF
        if (data.faturamento_por_uf && data.faturamento_por_uf.length > 0) {
            renderChart('chart-faturamento-uf', {
                chart: { type: 'donut', height: 320 },
                series: data.faturamento_por_uf.map(d => d.total),
                labels: data.faturamento_por_uf.map(d => d.uf),
                legend: { position: 'bottom' },
                tooltip: {
                    y: { formatter: (val) => formatCurrency(val) }
                }
            });
        }
    }

    // Graficos de Compras
    function renderComprasCharts(data) {
        // Entradas vs Saidas
        if (data.entradas_vs_saidas && data.entradas_vs_saidas.length > 0) {
            renderChart('chart-entradas-saidas', {
                chart: { type: 'bar', height: 320, stacked: false, toolbar: { show: false } },
                series: [
                    { name: 'Entradas', data: data.entradas_vs_saidas.map(d => d.entradas) },
                    { name: 'Saidas', data: data.entradas_vs_saidas.map(d => d.saidas) }
                ],
                xaxis: {
                    categories: data.entradas_vs_saidas.map(d => d.mes_formatado)
                },
                yaxis: {
                    labels: { formatter: (val) => formatCurrency(val) }
                },
                plotOptions: { bar: { horizontal: false, columnWidth: '50%' } },
                dataLabels: { enabled: false },
                colors: ['#ef4444', '#22c55e'],
                tooltip: {
                    y: { formatter: (val) => formatCurrency(val) }
                }
            });
        }

        // Top Fornecedores
        if (data.top_fornecedores && data.top_fornecedores.length > 0) {
            renderChart('chart-top-fornecedores', {
                chart: { type: 'bar', height: 320, toolbar: { show: false } },
                series: [{
                    name: 'Valor',
                    data: data.top_fornecedores.map(d => d.total)
                }],
                xaxis: {
                    categories: data.top_fornecedores.map(d => truncate(d.razao_social || d.cnpj, 20)),
                    labels: { rotate: -45, style: { fontSize: '10px' } }
                },
                yaxis: {
                    labels: { formatter: (val) => formatCurrency(val) }
                },
                plotOptions: { bar: { horizontal: false, columnWidth: '60%' } },
                dataLabels: { enabled: false },
                colors: ['#f97316'],
                tooltip: {
                    y: { formatter: (val) => formatCurrency(val) }
                }
            });
        }

        // Devolucoes
        if (data.devolucoes && data.devolucoes.length > 0) {
            renderChart('chart-devolucoes', {
                chart: { type: 'line', height: 320, toolbar: { show: false } },
                series: [{
                    name: 'Devolucoes',
                    data: data.devolucoes.map(d => d.valor_devolucoes)
                }],
                xaxis: {
                    categories: data.devolucoes.map(d => d.mes_formatado)
                },
                yaxis: {
                    labels: { formatter: (val) => formatCurrency(val) }
                },
                stroke: { curve: 'smooth', width: 2 },
                markers: { size: 4 },
                colors: ['#8b5cf6'],
                tooltip: {
                    y: { formatter: (val) => formatCurrency(val) }
                }
            });
        }
    }

    // Graficos de Tributos
    function renderTributosCharts(data) {
        // Carga Tributaria Mensal
        if (data.carga_tributaria && data.carga_tributaria.length > 0) {
            renderChart('chart-carga-tributaria', {
                chart: { type: 'line', height: 320, toolbar: { show: false } },
                series: [
                    { name: 'Faturamento', data: data.carga_tributaria.map(d => d.faturamento), type: 'column' },
                    { name: 'Tributos', data: data.carga_tributaria.map(d => d.tributos_total), type: 'column' }
                ],
                xaxis: {
                    categories: data.carga_tributaria.map(d => d.mes_formatado)
                },
                yaxis: {
                    labels: { formatter: (val) => formatCurrency(val) }
                },
                plotOptions: { bar: { columnWidth: '50%' } },
                dataLabels: { enabled: false },
                colors: ['#3b82f6', '#ef4444'],
                tooltip: {
                    y: { formatter: (val) => formatCurrency(val) }
                }
            });
        }

        // Tributos por Tipo
        if (data.tributos_por_tipo && data.tributos_por_tipo.length > 0) {
            const tributosFiltrados = data.tributos_por_tipo.filter(d => d.valor > 0);
            if (tributosFiltrados.length > 0) {
                renderChart('chart-tributos-tipo', {
                    chart: { type: 'pie', height: 320 },
                    series: tributosFiltrados.map(d => d.valor),
                    labels: tributosFiltrados.map(d => d.tipo),
                    legend: { position: 'bottom' },
                    colors: ['#3b82f6', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6'],
                    tooltip: {
                        y: { formatter: (val) => formatCurrency(val) }
                    }
                });
            }
        }

        // Aliquota Efetiva
        if (data.carga_tributaria && data.carga_tributaria.length > 0) {
            renderChart('chart-aliquota-efetiva', {
                chart: { type: 'area', height: 320, toolbar: { show: false } },
                series: [{
                    name: 'Aliquota Efetiva',
                    data: data.carga_tributaria.map(d => d.aliquota_efetiva)
                }],
                xaxis: {
                    categories: data.carga_tributaria.map(d => d.mes_formatado)
                },
                yaxis: {
                    labels: { formatter: (val) => val.toFixed(2) + '%' },
                    min: 0,
                    max: 50
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.1 } },
                colors: ['#8b5cf6'],
                tooltip: {
                    y: { formatter: (val) => val.toFixed(2) + '%' }
                }
            });
        }
    }

    // Renderiza um grafico (cria ou atualiza)
    function renderChart(elementId, options) {
        const element = document.getElementById(elementId);
        if (!element) return;

        // Destroi grafico existente
        if (charts[elementId]) {
            charts[elementId].destroy();
        }

        // Cria novo grafico
        charts[elementId] = new ApexCharts(element, options);
        charts[elementId].render();
    }

    // Utilitarios
    function formatCurrency(value) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(value);
    }

    function truncate(str, length) {
        if (!str) return '';
        return str.length > length ? str.substring(0, length) + '...' : str;
    }

    function showEmptyState() {
        const emptyState = document.getElementById('analytics-empty');
        if (emptyState) emptyState.classList.remove('hidden');
        document.querySelectorAll('.analytics-tab-content').forEach(el => el.classList.add('hidden'));
    }

    function hideEmptyState() {
        const emptyState = document.getElementById('analytics-empty');
        if (emptyState) emptyState.classList.add('hidden');
    }

    // Inicializa quando o DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expoe funcao de inicializacao para SPA
    window.initAnalytics = init;
})();
