{{-- Central de Alertas --}}
<div class="min-h-screen bg-gray-50" id="alertas-central-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">

        <style>
            @keyframes card-slide-in {
                from { opacity: 0; transform: translateY(60px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .dash-animate {
                opacity: 0;
                animation: card-slide-in 0.65s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            }
            @media (prefers-reduced-motion: reduce) {
                .dash-animate { opacity: 1; animation: none; }
            }
            .alerta-skeleton {
                background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
                background-size: 200% 100%;
                animation: alerta-shimmer 1.5s infinite;
                border-radius: 0.25rem;
            }
            @keyframes alerta-shimmer {
                0% { background-position: 200% 0; }
                100% { background-position: -200% 0; }
            }
        </style>

        {{-- Page Header --}}
        <div class="mb-4 sm:mb-8 dash-animate">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-amber-50 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">Central de Alertas</h1>
                        <p class="mt-0.5 text-sm text-gray-500">Monitore alertas fiscais e de compliance dos seus clientes</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span id="alerta-ultima-atualizacao" class="text-xs text-gray-400 hidden sm:inline"></span>
                    <button id="btn-recalcular" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 transition-colors">
                        <svg id="recalcular-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <svg id="recalcular-spinner" class="hidden animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Recalcular
                    </button>
                </div>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-6 mb-6 sm:mb-8">
            {{-- Total Alertas --}}
            <div id="kpi-total" class="bg-white rounded-lg border border-gray-200 p-3 sm:p-6 dash-animate min-h-[88px] sm:min-h-[112px] cursor-pointer hover:border-blue-400 transition-colors" style="animation-delay: 0.1s" data-filtro-severidade="">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1 sm:mb-2 whitespace-nowrap">Total Alertas</p>
                <p class="text-lg sm:text-xl lg:text-2xl font-semibold text-blue-600 whitespace-nowrap" id="kpi-total-valor">
                    <span class="alerta-skeleton inline-block w-12 h-7 sm:h-9">&nbsp;</span>
                </p>
                <div class="mt-1 sm:mt-2">
                    <span id="kpi-novos-hoje" class="hidden text-xs font-medium bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full"></span>
                </div>
            </div>

            {{-- Alta Severidade --}}
            <div id="kpi-alta" class="bg-red-50 rounded-lg border border-red-200 p-3 sm:p-6 dash-animate min-h-[88px] sm:min-h-[112px] cursor-pointer hover:border-red-400 transition-colors" style="animation-delay: 0.15s" data-filtro-severidade="alta">
                <div class="flex items-center gap-1.5 mb-1 sm:mb-2">
                    <span class="w-2 h-2 bg-red-500 rounded-full flex-shrink-0"></span>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide whitespace-nowrap">Alta Severidade</p>
                </div>
                <p class="text-lg sm:text-xl lg:text-2xl font-semibold text-red-600 whitespace-nowrap" id="kpi-alta-valor">
                    <span class="alerta-skeleton inline-block w-12 h-7 sm:h-9">&nbsp;</span>
                </p>
            </div>

            {{-- Media Severidade --}}
            <div id="kpi-media" class="bg-yellow-50 rounded-lg border border-yellow-200 p-3 sm:p-6 dash-animate min-h-[88px] sm:min-h-[112px] cursor-pointer hover:border-yellow-400 transition-colors" style="animation-delay: 0.2s" data-filtro-severidade="media">
                <div class="flex items-center gap-1.5 mb-1 sm:mb-2">
                    <span class="w-2 h-2 bg-yellow-500 rounded-full flex-shrink-0"></span>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide whitespace-nowrap">Média</p>
                </div>
                <p class="text-lg sm:text-xl lg:text-2xl font-semibold text-yellow-600 whitespace-nowrap" id="kpi-media-valor">
                    <span class="alerta-skeleton inline-block w-12 h-7 sm:h-9">&nbsp;</span>
                </p>
            </div>

            {{-- Baixa Severidade --}}
            <div id="kpi-baixa" class="bg-gray-50 rounded-lg border border-gray-200 p-3 sm:p-6 dash-animate min-h-[88px] sm:min-h-[112px] cursor-pointer hover:border-gray-400 transition-colors" style="animation-delay: 0.25s" data-filtro-severidade="baixa">
                <div class="flex items-center gap-1.5 mb-1 sm:mb-2">
                    <span class="w-2 h-2 bg-gray-400 rounded-full flex-shrink-0"></span>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide whitespace-nowrap">Baixa Severidade</p>
                </div>
                <p class="text-lg sm:text-xl lg:text-2xl font-semibold text-gray-500 whitespace-nowrap" id="kpi-baixa-valor">
                    <span class="alerta-skeleton inline-block w-12 h-7 sm:h-9">&nbsp;</span>
                </p>
            </div>
        </div>

        {{-- Evolution Chart --}}
        <div id="alertas-evolucao-wrapper" class="bg-white rounded-lg border border-gray-200 p-5 sm:p-6 mb-6 sm:mb-8 dash-animate" style="animation-delay: 0.3s">
            <h3 class="text-sm font-medium text-gray-700 mb-1">Evolução de Alertas</h3>
            <p class="text-xs text-gray-400 mb-4">Últimas 12 semanas</p>
            <div id="alertas-evolucao-chart" class="h-64">
                <div class="flex items-center justify-center h-full text-gray-400">
                    <svg class="animate-spin h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Carregando...
                </div>
            </div>
        </div>

        {{-- Filters + View Toggle --}}
        <div class="bg-white rounded-lg border border-gray-200 p-5 sm:p-6 mb-6 sm:mb-8 dash-animate" style="animation-delay: 0.35s">
            <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-end gap-3">
                {{-- Severidade --}}
                <div class="flex-1 min-w-[140px]">
                    <label for="alerta-filtro-severidade" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Severidade</label>
                    <select id="alerta-filtro-severidade" class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                        <option value="">Todas</option>
                        <option value="alta">Alta</option>
                        <option value="media">Média</option>
                        <option value="baixa">Baixa</option>
                    </select>
                </div>

                {{-- Categoria --}}
                <div class="flex-1 min-w-[140px]">
                    <label for="alerta-filtro-categoria" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Categoria</label>
                    <select id="alerta-filtro-categoria" class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                        <option value="">Todas</option>
                        <option value="notas_fiscais">Notas Fiscais</option>
                        <option value="compliance">Compliance</option>
                        <option value="importacao">Importação</option>
                    </select>
                </div>

                {{-- Cliente --}}
                <div class="flex-1 min-w-[180px]">
                    <label for="alerta-filtro-cliente" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Cliente</label>
                    <select id="alerta-filtro-cliente" class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                        <option value="">Todos os Clientes</option>
                        @foreach($clientes ?? [] as $cliente)
                            <option value="{{ $cliente->id }}">{{ $cliente->razao_social }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div class="flex-1 min-w-[140px]">
                    <label for="alerta-filtro-status" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Status</label>
                    <select id="alerta-filtro-status" class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                        <option value="ativo">Ativos</option>
                        <option value="visto">Vistos</option>
                        <option value="resolvido">Resolvidos</option>
                        <option value="ignorado">Ignorados</option>
                        <option value="">Todos</option>
                    </select>
                </div>

                {{-- View Toggle --}}
                <div class="flex-shrink-0">
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Agrupar</label>
                    <div class="inline-flex rounded-lg border border-gray-300 overflow-hidden">
                        <button id="vista-tipo" class="px-3 py-2 text-sm font-medium bg-amber-600 text-white transition-colors" data-vista="tipo">Por Tipo</button>
                        <button id="vista-cliente" class="px-3 py-2 text-sm font-medium bg-white text-gray-700 hover:bg-gray-50 transition-colors" data-vista="cliente">Por Cliente</button>
                    </div>
                </div>

                {{-- Botao Filtrar --}}
                <div class="flex-shrink-0">
                    <button id="btn-filtrar-alertas" class="w-full sm:w-auto px-5 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 transition-colors">
                        Filtrar
                    </button>
                </div>
            </div>
        </div>

        {{-- Alert List --}}
        <div id="alertas-lista" class="dash-animate" style="animation-delay: 0.4s">
            {{-- Skeleton loading --}}
            <div class="space-y-4" id="alertas-skeleton">
                @for($i = 0; $i < 3; $i++)
                <div class="bg-white rounded-lg border border-gray-200 p-5 sm:p-6">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="alerta-skeleton w-3 h-3 rounded-full">&nbsp;</div>
                        <div class="alerta-skeleton h-5 w-48">&nbsp;</div>
                        <div class="ml-auto alerta-skeleton h-5 w-20">&nbsp;</div>
                    </div>
                    <div class="alerta-skeleton h-4 w-full mb-2">&nbsp;</div>
                    <div class="alerta-skeleton h-4 w-2/3">&nbsp;</div>
                </div>
                @endfor
            </div>
        </div>

        {{-- Pagination --}}
        <div id="alertas-paginacao" class="mt-4 sm:mt-6 hidden"></div>

    </div>
</div>

<script src="/js/apexcharts.min.js"></script>
<script>
(function() {
    'use strict';

    // ─── State ────────────────────────────────────────────────
    var resumoData = @json($resumo ?? []);
    var alertasData = null;
    var evolucaoChart = null;
    var filtros = { severidade: '', categoria: '', cliente_id: '', status: 'ativo' };
    var vistaAtual = 'tipo';
    var paginaAtual = 1;
    var isRecalculando = false;
    var expandedAlerts = {};

    // ─── Helpers ──────────────────────────────────────────────

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        var s = String(str);
        var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return s.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function formatarMoeda(val) {
        if (val === null || val === undefined) return 'R$ 0,00';
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val);
    }

    function formatarData(dateStr) {
        if (!dateStr) return '-';
        var d = new Date(dateStr);
        if (isNaN(d.getTime())) return escapeHtml(dateStr);
        return d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function formatarDataHora(dateStr) {
        if (!dateStr) return '-';
        var d = new Date(dateStr);
        if (isNaN(d.getTime())) return escapeHtml(dateStr);
        return d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' ' + d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    }

    function severidadeBadge(sev) {
        var classes = {
            alta: 'bg-red-100 text-red-800',
            media: 'bg-yellow-100 text-yellow-800',
            baixa: 'bg-gray-100 text-gray-600'
        };
        var label = { alta: 'Alta', media: 'Média', baixa: 'Baixa' };
        var cls = classes[sev] || 'bg-gray-100 text-gray-600';
        var lbl = label[sev] || escapeHtml(sev);
        return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ' + cls + '">' + escapeHtml(lbl) + '</span>';
    }

    function severidadeDot(sev) {
        var colors = { alta: 'bg-red-500', media: 'bg-yellow-500', baixa: 'bg-gray-400' };
        var cls = colors[sev] || 'bg-gray-400';
        return '<span class="w-2.5 h-2.5 ' + cls + ' rounded-full flex-shrink-0"></span>';
    }

    function categoriaBadge(cat) {
        var labels = { notas_fiscais: 'Notas Fiscais', compliance: 'Compliance', importacao: 'Importação' };
        return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">' + escapeHtml(labels[cat] || cat) + '</span>';
    }

    async function fetchJson(url, options) {
        options = options || {};
        var headers = Object.assign({
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }, options.headers || {});
        var r = await fetch(url, Object.assign({}, options, { headers: headers }));
        if (!r.ok) throw new Error('Erro na requisição: ' + r.status);
        return r.json();
    }

    async function postJson(url, body) {
        return fetchJson(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(body)
        });
    }

    // ─── Render KPIs ──────────────────────────────────────────

    function renderKpis(resumo) {
        if (!resumo) return;

        var totalEl = document.getElementById('kpi-total-valor');
        var altaEl = document.getElementById('kpi-alta-valor');
        var mediaEl = document.getElementById('kpi-media-valor');
        var baixaEl = document.getElementById('kpi-baixa-valor');
        var novosEl = document.getElementById('kpi-novos-hoje');
        var atualizacaoEl = document.getElementById('alerta-ultima-atualizacao');

        if (totalEl) totalEl.textContent = resumo.total_ativos || 0;
        if (altaEl) altaEl.textContent = (resumo.por_severidade && resumo.por_severidade.alta) || 0;
        if (mediaEl) mediaEl.textContent = (resumo.por_severidade && resumo.por_severidade.media) || 0;
        if (baixaEl) baixaEl.textContent = (resumo.por_severidade && resumo.por_severidade.baixa) || 0;

        if (novosEl) {
            var novos = resumo.novos_hoje || 0;
            if (novos > 0) {
                novosEl.textContent = '+' + novos + ' novos hoje';
                novosEl.classList.remove('hidden');
            } else {
                novosEl.classList.add('hidden');
            }
        }

        if (atualizacaoEl && resumo.ultima_atualizacao) {
            atualizacaoEl.textContent = 'Última atualização: ' + formatarDataHora(resumo.ultima_atualizacao);
            atualizacaoEl.classList.remove('hidden');
        }
    }

    // ─── Evolution Chart ──────────────────────────────────────

    async function loadEvolucao() {
        var container = document.getElementById('alertas-evolucao-chart');
        if (!container) return;

        // Aguardar a animação dash-animate do wrapper terminar antes de renderizar
        var wrapper = document.getElementById('alertas-evolucao-wrapper');
        if (wrapper) {
            await new Promise(function(resolve) {
                // Se a animação já terminou (elemento visível e sem animação pendente), seguir
                var animations = wrapper.getAnimations ? wrapper.getAnimations() : [];
                if (animations.length === 0) {
                    resolve();
                } else {
                    Promise.all(animations.map(function(a) { return a.finished; })).then(resolve).catch(resolve);
                }
            });
        }

        // Aguardar ApexCharts carregar (SPA carrega scripts externos de forma assíncrona)
        var tentativas = 0;
        while (typeof ApexCharts === 'undefined' && tentativas < 50) {
            await new Promise(function(r) { setTimeout(r, 100); });
            tentativas++;
        }
        if (typeof ApexCharts === 'undefined') {
            container.innerHTML = '<div class="flex items-center justify-center h-full text-red-400 text-sm">Erro ao carregar gráfico</div>';
            return;
        }

        try {
            var data = await fetchJson('/app/alertas/evolucao');
            if (!data || !data.categorias || !data.series || data.categorias.length === 0) {
                container.innerHTML = '<div class="flex items-center justify-center h-full text-gray-400 text-sm">Sem dados para exibir</div>';
                return;
            }

            // Verificar se todas as séries têm apenas zeros
            var temDados = data.series.some(function(s) {
                return s.data.some(function(v) { return v > 0; });
            });
            if (!temDados) {
                container.innerHTML = '<div class="flex items-center justify-center h-full text-gray-400 text-sm">Sem alertas nas últimas 12 semanas</div>';
                return;
            }

            if (evolucaoChart) {
                evolucaoChart.destroy();
                evolucaoChart = null;
            }

            var options = {
                chart: {
                    type: 'bar',
                    height: 256,
                    stacked: true,
                    toolbar: { show: false },
                    fontFamily: 'inherit',
                    animations: { enabled: true, easing: 'easeinout', speed: 600 }
                },
                plotOptions: {
                    bar: { horizontal: false, columnWidth: '55%', borderRadius: 4 }
                },
                dataLabels: { enabled: false },
                stroke: { show: true, width: 2, colors: ['transparent'] },
                xaxis: {
                    categories: data.categorias,
                    labels: { style: { fontSize: '11px', colors: '#6b7280' } }
                },
                yaxis: {
                    labels: { style: { fontSize: '11px', colors: '#6b7280' } }
                },
                fill: { opacity: 1 },
                tooltip: {
                    y: { formatter: function(val) { return val + ' alertas'; } }
                },
                colors: data.series.map(function(s) { return s.color || '#6b7280'; }),
                series: data.series.map(function(s) {
                    return { name: s.name, data: s.data };
                }),
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    fontSize: '12px',
                    labels: { colors: '#6b7280' },
                    offsetY: -4,
                    itemMargin: { horizontal: 12, vertical: 8 }
                },
                grid: {
                    borderColor: '#f3f4f6',
                    strokeDashArray: 4
                },
                responsive: [{
                    breakpoint: 640,
                    options: {
                        chart: { height: 200 },
                        plotOptions: { bar: { columnWidth: '70%' } },
                        legend: { position: 'bottom', horizontalAlign: 'center' }
                    }
                }]
            };

            evolucaoChart = new ApexCharts(container, options);
            evolucaoChart.render().then(function() {
                // Forçar recalculo de dimensões após render completo
                requestAnimationFrame(function() {
                    window.dispatchEvent(new Event('resize'));
                });
            });
        } catch (e) {
            container.innerHTML = '<div class="flex items-center justify-center h-full text-red-400 text-sm">Erro ao carregar gráfico</div>';
        }
    }

    // ─── Load Alertas ─────────────────────────────────────────

    async function loadAlertas(page) {
        page = page || 1;
        paginaAtual = page;
        var listaEl = document.getElementById('alertas-lista');
        if (!listaEl) return;

        listaEl.innerHTML = renderSkeleton();

        var params = new URLSearchParams();
        if (filtros.severidade) params.append('severidade', filtros.severidade);
        if (filtros.categoria) params.append('categoria', filtros.categoria);
        if (filtros.cliente_id) params.append('cliente_id', filtros.cliente_id);
        if (filtros.status) params.append('status', filtros.status);
        params.append('page', page);

        try {
            var data = await fetchJson('/app/alertas/dados?' + params.toString());
            alertasData = data;

            if (!data || !data.data || data.data.length === 0) {
                listaEl.innerHTML = renderEmptyState();
                var pagEl = document.getElementById('alertas-paginacao');
                if (pagEl) { pagEl.innerHTML = ''; pagEl.classList.add('hidden'); }
                return;
            }

            expandedAlerts = {};

            if (vistaAtual === 'cliente') {
                listaEl.innerHTML = renderAlertasPorCliente(data.data);
            } else {
                listaEl.innerHTML = renderAlertasPorTipo(data.data);
            }

            renderPaginacao(data);
            setupAlertaActions();
            setupExpandToggle();
        } catch (e) {
            listaEl.innerHTML = '<div class="bg-white rounded-lg border border-red-200 p-6 text-center text-red-500 text-sm">Erro ao carregar alertas. Tente novamente.</div>';
        }
    }

    // ─── Render: Por Tipo ─────────────────────────────────────

    function renderAlertasPorTipo(alertas) {
        var grouped = {};
        alertas.forEach(function(a) {
            var tipo = a.tipo || 'outros';
            if (!grouped[tipo]) grouped[tipo] = [];
            grouped[tipo].push(a);
        });

        var html = '<div class="space-y-4">';
        Object.keys(grouped).forEach(function(tipo) {
            var items = grouped[tipo];
            var primeiro = items[0];
            var maxSev = getMaxSeveridade(items);
            var totalAfetados = items.reduce(function(sum, a) { return sum + (a.total_afetados || 0); }, 0);

            html += '<div class="bg-white rounded-lg border border-gray-200 overflow-hidden">';
            html += '<div class="p-5 sm:p-6 cursor-pointer hover:bg-gray-50 transition-colors alerta-grupo-header" data-tipo="' + escapeHtml(tipo) + '">';
            html += '<div class="flex items-center justify-between">';
            html += '<div class="flex items-center gap-3 min-w-0">';
            html += severidadeDot(maxSev);
            html += '<h3 class="text-sm sm:text-base font-medium text-gray-900 truncate">' + escapeHtml(formatTipoLabel(tipo)) + '</h3>';
            html += '<span class="hidden sm:inline-flex">' + categoriaBadge(primeiro.categoria) + '</span>';
            html += '</div>';
            html += '<div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">';
            html += '<span class="text-xs sm:text-sm text-gray-500">' + totalAfetados + ' afetados</span>';
            html += severidadeBadge(maxSev);
            html += '<svg class="w-5 h-5 text-gray-400 transition-transform alerta-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>';
            html += '</div>';
            html += '</div>';
            html += '<p class="mt-1 text-xs sm:text-sm text-gray-500 line-clamp-1">' + escapeHtml(primeiro.descricao || '') + '</p>';
            html += '</div>';

            html += '<div class="alerta-grupo-conteudo hidden border-t border-gray-100">';
            items.forEach(function(a) {
                html += renderAlertaCard(a);
            });
            html += '</div>';
            html += '</div>';
        });
        html += '</div>';
        return html;
    }

    // ─── Render: Por Cliente ──────────────────────────────────

    function renderAlertasPorCliente(alertas) {
        var grouped = {};
        alertas.forEach(function(a) {
            var key = a.cliente_id ? String(a.cliente_id) : 'sem_cliente';
            if (!grouped[key]) {
                grouped[key] = {
                    nome: a.cliente_nome || 'Sem cliente',
                    cnpj: a.cliente_cnpj || '',
                    alertas: []
                };
            }
            grouped[key].alertas.push(a);
        });

        var html = '<div class="space-y-4">';
        Object.keys(grouped).forEach(function(key) {
            var grupo = grouped[key];
            var items = grupo.alertas;
            var contagem = contarSeveridades(items);

            html += '<div class="bg-white rounded-lg border border-gray-200 overflow-hidden">';
            html += '<div class="p-5 sm:p-6 cursor-pointer hover:bg-gray-50 transition-colors alerta-grupo-header" data-cliente="' + escapeHtml(key) + '">';
            html += '<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">';
            html += '<div class="min-w-0">';
            html += '<h3 class="text-sm sm:text-base font-medium text-gray-900 truncate">' + escapeHtml(grupo.nome) + '</h3>';
            if (grupo.cnpj) {
                html += '<p class="text-xs text-gray-400 mt-0.5">' + escapeHtml(grupo.cnpj) + '</p>';
            }
            html += '</div>';
            html += '<div class="flex items-center gap-3 flex-shrink-0">';
            if (contagem.alta > 0) {
                html += '<span class="flex items-center gap-1 text-xs"><span class="w-2 h-2 bg-red-500 rounded-full"></span>' + contagem.alta + ' Alta</span>';
            }
            if (contagem.media > 0) {
                html += '<span class="flex items-center gap-1 text-xs"><span class="w-2 h-2 bg-yellow-500 rounded-full"></span>' + contagem.media + ' Média</span>';
            }
            if (contagem.baixa > 0) {
                html += '<span class="flex items-center gap-1 text-xs"><span class="w-2 h-2 bg-gray-400 rounded-full"></span>' + contagem.baixa + ' Baixa</span>';
            }
            html += '<svg class="w-5 h-5 text-gray-400 transition-transform alerta-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>';
            html += '</div>';
            html += '</div>';
            html += '</div>';

            html += '<div class="alerta-grupo-conteudo hidden border-t border-gray-100">';
            items.forEach(function(a) {
                html += '<div class="flex items-center justify-between px-5 sm:px-6 py-3 border-b border-gray-50 last:border-b-0 hover:bg-gray-50 cursor-pointer alerta-item-expand" data-alerta-id="' + a.id + '">';
                html += '<div class="flex items-center gap-2 min-w-0">';
                html += severidadeDot(a.severidade);
                html += '<span class="text-sm text-gray-900 truncate">' + escapeHtml(formatTipoLabel(a.tipo)) + '</span>';
                if (a.total_afetados) {
                    html += '<span class="text-xs text-gray-400">(' + a.total_afetados + ' afetados)</span>';
                }
                html += '</div>';
                html += '<div class="flex items-center gap-2 flex-shrink-0">';
                html += severidadeBadge(a.severidade);
                html += '</div>';
                html += '</div>';
                html += '<div class="alerta-detalhe-inline hidden" id="alerta-detalhe-' + a.id + '">';
                html += renderAlertaCard(a);
                html += '</div>';
            });
            html += '</div>';
            html += '</div>';
        });
        html += '</div>';
        return html;
    }

    // ─── Render: Alert Card ───────────────────────────────────

    function renderAlertaCard(alerta) {
        var html = '<div class="px-5 sm:px-6 py-4 bg-gray-50/50 border-b border-gray-100 last:border-b-0">';
        html += '<div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 mb-3">';
        html += '<div class="min-w-0">';
        html += '<div class="flex items-center gap-2 mb-1">';
        html += severidadeDot(alerta.severidade);
        html += '<span class="text-sm font-medium text-gray-900">' + escapeHtml(formatTipoLabel(alerta.tipo)) + '</span>';
        html += severidadeBadge(alerta.severidade);
        html += '</div>';
        html += '<p class="text-sm text-gray-600">' + escapeHtml(alerta.descricao || '') + '</p>';
        if (alerta.cliente_nome) {
            html += '<p class="text-xs text-gray-400 mt-1">Cliente: ' + escapeHtml(alerta.cliente_nome) + '</p>';
        }
        html += '</div>';
        html += '<div class="flex items-center gap-1 flex-shrink-0">';
        html += renderActionButtons(alerta);
        html += '</div>';
        html += '</div>';

        // Detail section
        html += renderDetalhes(alerta);

        html += '</div>';
        return html;
    }

    // ─── Render: Action Buttons ───────────────────────────────

    function renderActionButtons(alerta) {
        var html = '';
        if (alerta.status !== 'resolvido') {
            html += '<button class="alerta-action-btn inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-green-700 bg-green-50 rounded-lg hover:bg-green-100 transition-colors" data-alerta-id="' + alerta.id + '" data-action="resolvido" title="Marcar como resolvido">';
            html += '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
            html += '<span class="hidden sm:inline">Resolver</span>';
            html += '</button>';
        }
        if (alerta.status !== 'ignorado') {
            html += '<button class="alerta-action-btn inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors" data-alerta-id="' + alerta.id + '" data-action="ignorado" title="Ignorar alerta">';
            html += '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
            html += '<span class="hidden sm:inline">Ignorar</span>';
            html += '</button>';
        }
        html += '<button class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-gray-400 bg-gray-50 rounded-lg cursor-not-allowed opacity-60" disabled title="Em breve — integracao WhatsApp">';
        html += '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>';
        html += '<svg class="w-3 h-3 -ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>';
        html += '</button>';
        return html;
    }

    // ─── Render: Detail Tables ────────────────────────────────

    function renderDetalhes(alerta) {
        var detalhes = alerta.detalhes;
        if (!detalhes) return '';

        var tipo = alerta.tipo || '';
        var html = '<div class="mt-3">';

        if (tipo === 'notas_duplicadas' || tipo === 'notas_sem_participante' || tipo === 'notas_valor_zerado' || tipo === 'notas_sem_itens' || tipo === 'notas_data_futura') {
            html += renderTabelaNotas(detalhes);
        } else if (tipo === 'participante_inativo' || tipo === 'participante_sem_ie' || tipo === 'cnpj_situacao_irregular' || tipo === 'situacao_irregular' || tipo === 'consulta_vencida' || tipo === 'nunca_consultado') {
            html += renderTabelaCompliance(detalhes);
        } else if (tipo === 'fornecedor_irregular') {
            html += renderFornecedorIrregular(detalhes);
        } else if (tipo === 'gap_importacao') {
            html += renderGapTemporal(detalhes);
        } else if (tipo === 'gap_temporal') {
            html += renderGapTemporal(detalhes);
        } else if (tipo === 'pis_cofins_incompleto') {
            html += renderPisCofins(detalhes);
        } else {
            html += renderDetalhesGenerico(detalhes);
        }

        html += '</div>';
        return html;
    }

    function renderTabelaNotas(detalhes) {
        var itens = detalhes.itens || detalhes.notas || [];
        if (!Array.isArray(itens) || itens.length === 0) {
            return renderDetalhesGenerico(detalhes);
        }

        var html = '<div class="overflow-x-auto">';
        html += '<table class="min-w-full text-xs">';
        html += '<thead><tr class="border-b border-gray-200">';
        html += '<th class="text-left py-2 px-3 font-medium text-gray-500">Número</th>';
        html += '<th class="text-left py-2 px-3 font-medium text-gray-500">Série</th>';
        html += '<th class="text-left py-2 px-3 font-medium text-gray-500">Modelo</th>';
        html += '<th class="text-left py-2 px-3 font-medium text-gray-500">Participante</th>';
        html += '<th class="text-left py-2 px-3 font-medium text-gray-500">Data</th>';
        html += '<th class="text-right py-2 px-3 font-medium text-gray-500">Valor</th>';
        html += '</tr></thead><tbody>';

        itens.forEach(function(item) {
            html += '<tr class="border-b border-gray-50">';
            html += '<td class="py-1.5 px-3 text-gray-700">' + escapeHtml(item.numero || item.num_doc || '-') + '</td>';
            html += '<td class="py-1.5 px-3 text-gray-700">' + escapeHtml(item.serie || '-') + '</td>';
            html += '<td class="py-1.5 px-3 text-gray-700">' + escapeHtml(item.modelo || item.cod_mod || '-') + '</td>';
            html += '<td class="py-1.5 px-3 text-gray-700 max-w-[150px] truncate">' + escapeHtml(item.participante || item.participante_nome || '-') + '</td>';
            html += '<td class="py-1.5 px-3 text-gray-700">' + formatarData(item.data || item.dt_doc) + '</td>';
            html += '<td class="py-1.5 px-3 text-gray-700 text-right">' + formatarMoeda(item.valor || item.vl_doc) + '</td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        return html;
    }

    function renderTabelaCompliance(detalhes) {
        var itens = detalhes.itens || detalhes.participantes || [];
        if (!Array.isArray(itens) || itens.length === 0) {
            return renderDetalhesGenerico(detalhes);
        }

        var html = '<div class="overflow-x-auto">';
        html += '<table class="min-w-full text-xs">';
        html += '<thead><tr class="border-b border-gray-200">';
        html += '<th class="text-left py-2 px-3 font-medium text-gray-500">Participante</th>';
        html += '<th class="text-left py-2 px-3 font-medium text-gray-500">CNPJ</th>';
        html += '<th class="text-left py-2 px-3 font-medium text-gray-500">Status / Info</th>';
        html += '</tr></thead><tbody>';

        itens.forEach(function(item) {
            html += '<tr class="border-b border-gray-50">';
            html += '<td class="py-1.5 px-3 text-gray-700 max-w-[200px] truncate">' + escapeHtml(item.razao_social || item.nome || item.participante || '-') + '</td>';
            html += '<td class="py-1.5 px-3 text-gray-700">' + escapeHtml(item.cnpj || '-') + '</td>';
            html += '<td class="py-1.5 px-3 text-gray-700">' + escapeHtml(item.status || item.situacao || item.info || '-') + '</td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        return html;
    }

    function renderGapTemporal(detalhes) {
        var meses = detalhes.meses_faltantes || detalhes.meses || [];
        if (!Array.isArray(meses) || meses.length === 0) {
            return renderDetalhesGenerico(detalhes);
        }

        var html = '<div class="flex flex-wrap gap-2">';
        meses.forEach(function(mes) {
            html += '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">' + escapeHtml(mes) + '</span>';
        });
        html += '</div>';
        if (detalhes.mensagem) {
            html += '<p class="text-xs text-gray-500 mt-2">' + escapeHtml(detalhes.mensagem) + '</p>';
        }
        return html;
    }

    function renderPisCofins(detalhes) {
        var stats = detalhes.stats || detalhes;
        var html = '<div class="grid grid-cols-3 gap-3">';

        var items = [
            { label: 'Total de Notas', value: stats.total_notas || stats.total || 0 },
            { label: 'Com PIS/COFINS', value: stats.com_pis_cofins || stats.completas || 0 },
            { label: 'Sem PIS/COFINS', value: stats.sem_pis_cofins || stats.incompletas || 0 }
        ];

        items.forEach(function(item) {
            html += '<div class="bg-white rounded-lg border border-gray-200 p-3 text-center">';
            html += '<p class="text-lg font-semibold text-gray-900">' + escapeHtml(String(item.value)) + '</p>';
            html += '<p class="text-xs text-gray-500">' + escapeHtml(item.label) + '</p>';
            html += '</div>';
        });

        html += '</div>';
        if (detalhes.mensagem) {
            html += '<p class="text-xs text-gray-500 mt-2">' + escapeHtml(detalhes.mensagem) + '</p>';
        }
        return html;
    }

    function renderFornecedorIrregular(detalhes) {
        var html = '<div class="grid grid-cols-2 sm:grid-cols-4 gap-3">';
        var items = [
            { label: 'CNPJ', value: detalhes.cnpj || '-' },
            { label: 'Situação', value: detalhes.situacao_cadastral || '-' },
            { label: 'Notas vinculadas', value: detalhes.total_notas || 0 },
            { label: 'Valor em risco', value: formatarMoeda(detalhes.valor_em_risco) }
        ];
        items.forEach(function(item) {
            html += '<div class="bg-white rounded-lg border border-gray-200 p-3 text-center">';
            html += '<p class="text-sm font-semibold text-gray-900">' + escapeHtml(String(item.value)) + '</p>';
            html += '<p class="text-xs text-gray-500">' + escapeHtml(item.label) + '</p>';
            html += '</div>';
        });
        html += '</div>';
        return html;
    }

    function renderDetalhesGenerico(detalhes) {
        if (typeof detalhes === 'string') {
            return '<p class="text-sm text-gray-600">' + escapeHtml(detalhes) + '</p>';
        }

        var html = '<div class="space-y-1">';
        Object.keys(detalhes).forEach(function(key) {
            var val = detalhes[key];
            if (typeof val === 'object' && val !== null) {
                if (Array.isArray(val)) {
                    html += '<p class="text-xs text-gray-600"><span class="font-medium text-gray-700">' + escapeHtml(key) + ':</span> ' + escapeHtml(val.join(', ')) + '</p>';
                }
            } else {
                html += '<p class="text-xs text-gray-600"><span class="font-medium text-gray-700">' + escapeHtml(key) + ':</span> ' + escapeHtml(String(val)) + '</p>';
            }
        });
        html += '</div>';
        return html;
    }

    // ─── Render: Empty State ──────────────────────────────────

    function renderEmptyState() {
        var html = '<div class="bg-white rounded-lg border border-gray-200">';
        html += '<div class="flex flex-col items-center justify-center py-20 sm:py-28 text-gray-400">';
        html += '<div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mb-4">';
        html += '<svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
        html += '</div>';
        html += '<p class="text-base font-medium text-gray-500 mb-1">Nenhum alerta encontrado</p>';
        html += '<p class="text-sm text-gray-400">Ajuste os filtros ou aguarde o próximo cálculo de alertas.</p>';
        html += '</div></div>';
        return html;
    }

    // ─── Render: Skeleton ─────────────────────────────────────

    function renderSkeleton() {
        var html = '<div class="space-y-4">';
        for (var i = 0; i < 3; i++) {
            html += '<div class="bg-white rounded-lg border border-gray-200 p-5 sm:p-6">';
            html += '<div class="flex items-center gap-3 mb-3">';
            html += '<div class="alerta-skeleton w-3 h-3 rounded-full">&nbsp;</div>';
            html += '<div class="alerta-skeleton h-5 w-48">&nbsp;</div>';
            html += '<div class="ml-auto alerta-skeleton h-5 w-20">&nbsp;</div>';
            html += '</div>';
            html += '<div class="alerta-skeleton h-4 w-full mb-2">&nbsp;</div>';
            html += '<div class="alerta-skeleton h-4 w-2/3">&nbsp;</div>';
            html += '</div>';
        }
        html += '</div>';
        return html;
    }

    // ─── Render: Pagination ───────────────────────────────────

    function renderPaginacao(data) {
        var pagEl = document.getElementById('alertas-paginacao');
        if (!pagEl) return;

        var lastPage = data.last_page || 1;
        var currentPage = data.current_page || 1;

        if (lastPage <= 1) {
            pagEl.innerHTML = '';
            pagEl.classList.add('hidden');
            return;
        }

        pagEl.classList.remove('hidden');
        var html = '<div class="flex items-center justify-center gap-1">';

        // Previous
        html += '<button class="alerta-page-btn px-3 py-1.5 text-sm rounded-lg border ' + (currentPage <= 1 ? 'border-gray-200 text-gray-300 cursor-not-allowed' : 'border-gray-300 text-gray-700 hover:bg-gray-50') + '" data-page="' + (currentPage - 1) + '" ' + (currentPage <= 1 ? 'disabled' : '') + '>';
        html += '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>';
        html += '</button>';

        // Page numbers
        var start = Math.max(1, currentPage - 2);
        var end = Math.min(lastPage, currentPage + 2);

        if (start > 1) {
            html += '<button class="alerta-page-btn px-3 py-1.5 text-sm rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50" data-page="1">1</button>';
            if (start > 2) {
                html += '<span class="px-2 text-gray-400">...</span>';
            }
        }

        for (var p = start; p <= end; p++) {
            if (p === currentPage) {
                html += '<button class="px-3 py-1.5 text-sm rounded-lg bg-amber-600 text-white font-medium" disabled>' + p + '</button>';
            } else {
                html += '<button class="alerta-page-btn px-3 py-1.5 text-sm rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50" data-page="' + p + '">' + p + '</button>';
            }
        }

        if (end < lastPage) {
            if (end < lastPage - 1) {
                html += '<span class="px-2 text-gray-400">...</span>';
            }
            html += '<button class="alerta-page-btn px-3 py-1.5 text-sm rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50" data-page="' + lastPage + '">' + lastPage + '</button>';
        }

        // Next
        html += '<button class="alerta-page-btn px-3 py-1.5 text-sm rounded-lg border ' + (currentPage >= lastPage ? 'border-gray-200 text-gray-300 cursor-not-allowed' : 'border-gray-300 text-gray-700 hover:bg-gray-50') + '" data-page="' + (currentPage + 1) + '" ' + (currentPage >= lastPage ? 'disabled' : '') + '>';
        html += '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>';
        html += '</button>';

        html += '</div>';
        pagEl.innerHTML = html;

        // Bind page buttons
        pagEl.querySelectorAll('.alerta-page-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var page = parseInt(this.getAttribute('data-page'));
                if (!isNaN(page) && page >= 1) {
                    loadAlertas(page);
                    var container = document.getElementById('alertas-central-container');
                    if (container) container.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    }

    // ─── Helpers: Grouping ────────────────────────────────────

    function getMaxSeveridade(items) {
        var order = { alta: 3, media: 2, baixa: 1 };
        var max = 0;
        var maxSev = 'baixa';
        items.forEach(function(a) {
            var val = order[a.severidade] || 0;
            if (val > max) { max = val; maxSev = a.severidade; }
        });
        return maxSev;
    }

    function contarSeveridades(items) {
        var r = { alta: 0, media: 0, baixa: 0 };
        items.forEach(function(a) {
            if (r.hasOwnProperty(a.severidade)) r[a.severidade]++;
        });
        return r;
    }

    function formatTipoLabel(tipo) {
        var labels = {
            notas_duplicadas: 'Notas Duplicadas',
            notas_sem_participante: 'Notas sem Participante',
            notas_valor_zerado: 'Notas com Valor Zerado',
            notas_sem_itens: 'Notas sem Itens',
            notas_data_futura: 'Notas com Data Futura',
            participante_inativo: 'Participante Inativo',
            participante_sem_ie: 'Participante sem IE',
            cnpj_situacao_irregular: 'CNPJ com Situação Irregular',
            gap_temporal: 'Gap Temporal de Importação',
            pis_cofins_incompleto: 'PIS/COFINS Incompleto',
            situacao_irregular: 'Situação Cadastral Irregular',
            consulta_vencida: 'Consulta Vencida',
            nunca_consultado: 'Nunca Consultado'
        };
        return labels[tipo] || tipo.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });
    }

    // ─── Actions ──────────────────────────────────────────────

    async function marcarStatus(id, status, btn) {
        if (!id || !status) return;

        var originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>';

        try {
            await postJson('/app/alertas/' + id + '/status', { status: status });

            // Refresh KPIs and list
            try {
                var novoResumo = await fetchJson('/app/alertas/resumo');
                resumoData = novoResumo;
                renderKpis(resumoData);
            } catch (e) { /* ignore KPI refresh error */ }

            loadAlertas(paginaAtual);
        } catch (e) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            alert('Erro ao atualizar status do alerta. Tente novamente.');
        }
    }

    async function recalcularAlertas() {
        if (isRecalculando) return;
        isRecalculando = true;

        var btnIcon = document.getElementById('recalcular-icon');
        var btnSpinner = document.getElementById('recalcular-spinner');
        var btn = document.getElementById('btn-recalcular');

        if (btnIcon) btnIcon.classList.add('hidden');
        if (btnSpinner) btnSpinner.classList.remove('hidden');
        if (btn) btn.disabled = true;

        try {
            var result = await postJson('/app/alertas/recalcular', {});

            if (result.resumo) {
                resumoData = result.resumo;
                renderKpis(resumoData);
            }

            loadAlertas(1);
            loadEvolucao();
        } catch (e) {
            alert('Erro ao recalcular alertas. Tente novamente.');
        } finally {
            isRecalculando = false;
            if (btnIcon) btnIcon.classList.remove('hidden');
            if (btnSpinner) btnSpinner.classList.add('hidden');
            if (btn) btn.disabled = false;
        }
    }

    // ─── Setup: Event Listeners ───────────────────────────────

    function setupAlertaActions() {
        document.querySelectorAll('.alerta-action-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                var id = this.getAttribute('data-alerta-id');
                var action = this.getAttribute('data-action');
                marcarStatus(id, action, this);
            });
        });
    }

    function setupExpandToggle() {
        document.querySelectorAll('.alerta-grupo-header').forEach(function(header) {
            header.addEventListener('click', function() {
                var conteudo = this.nextElementSibling;
                var chevron = this.querySelector('.alerta-chevron');
                if (conteudo && conteudo.classList.contains('alerta-grupo-conteudo')) {
                    conteudo.classList.toggle('hidden');
                    if (chevron) {
                        chevron.style.transform = conteudo.classList.contains('hidden') ? '' : 'rotate(180deg)';
                    }
                }
            });
        });

        document.querySelectorAll('.alerta-item-expand').forEach(function(item) {
            item.addEventListener('click', function() {
                var id = this.getAttribute('data-alerta-id');
                var detalhe = document.getElementById('alerta-detalhe-' + id);
                if (detalhe) {
                    detalhe.classList.toggle('hidden');
                }
            });
        });
    }

    function setupFiltros() {
        var btnFiltrar = document.getElementById('btn-filtrar-alertas');
        if (btnFiltrar) {
            btnFiltrar.addEventListener('click', function() {
                filtros.severidade = document.getElementById('alerta-filtro-severidade').value;
                filtros.categoria = document.getElementById('alerta-filtro-categoria').value;
                filtros.cliente_id = document.getElementById('alerta-filtro-cliente').value;
                filtros.status = document.getElementById('alerta-filtro-status').value;
                loadAlertas(1);
            });
        }
    }

    function setupVistaToggle() {
        var btnTipo = document.getElementById('vista-tipo');
        var btnCliente = document.getElementById('vista-cliente');

        function updateToggle(vista) {
            vistaAtual = vista;
            if (btnTipo && btnCliente) {
                if (vista === 'tipo') {
                    btnTipo.className = 'px-3 py-2 text-sm font-medium bg-amber-600 text-white transition-colors';
                    btnCliente.className = 'px-3 py-2 text-sm font-medium bg-white text-gray-700 hover:bg-gray-50 transition-colors';
                } else {
                    btnCliente.className = 'px-3 py-2 text-sm font-medium bg-amber-600 text-white transition-colors';
                    btnTipo.className = 'px-3 py-2 text-sm font-medium bg-white text-gray-700 hover:bg-gray-50 transition-colors';
                }
            }
            loadAlertas(1);
        }

        if (btnTipo) {
            btnTipo.addEventListener('click', function() { updateToggle('tipo'); });
        }
        if (btnCliente) {
            btnCliente.addEventListener('click', function() { updateToggle('cliente'); });
        }
    }

    function setupKpiClicks() {
        var kpiCards = document.querySelectorAll('[data-filtro-severidade]');
        kpiCards.forEach(function(card) {
            card.addEventListener('click', function() {
                var sev = this.getAttribute('data-filtro-severidade');
                filtros.severidade = sev;
                var selectEl = document.getElementById('alerta-filtro-severidade');
                if (selectEl) selectEl.value = sev;

                // Visual feedback
                kpiCards.forEach(function(c) { c.classList.remove('ring-2', 'ring-amber-400'); });
                this.classList.add('ring-2', 'ring-amber-400');

                loadAlertas(1);
            });
        });
    }

    function setupRecalcular() {
        var btn = document.getElementById('btn-recalcular');
        if (btn) {
            btn.addEventListener('click', function() {
                recalcularAlertas();
            });
        }
    }

    // ─── Cleanup ──────────────────────────────────────────────

    function cleanup() {
        if (evolucaoChart) {
            evolucaoChart.destroy();
            evolucaoChart = null;
        }
        alertasData = null;
        resumoData = null;
        expandedAlerts = {};
    }

    // ─── Init ─────────────────────────────────────────────────

    renderKpis(resumoData);
    loadEvolucao();
    loadAlertas();
    setupFiltros();
    setupVistaToggle();
    setupKpiClicks();
    setupRecalcular();

    // Register cleanup for SPA navigation
    window._cleanupFunctions = window._cleanupFunctions || {};
    window._cleanupFunctions.alertasCentral = cleanup;
})();
</script>
