<div id="resumo-fiscal-container" class="min-h-screen bg-gray-50">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">

    <style>
        .rf-skeleton { background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); background-size: 200% 100%; animation: rf-shimmer 1.5s infinite; }
        @keyframes rf-shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
        .rf-section-content { transition: max-height 0.3s ease, opacity 0.2s ease; }
        .rf-section-content.collapsed { max-height: 0; opacity: 0; overflow: hidden; }
        .rf-chevron { transition: transform 0.2s ease; }
        .rf-chevron.rotated { transform: rotate(180deg); }
        .rf-nav { scrollbar-width: none; -ms-overflow-style: none; }
        .rf-nav::-webkit-scrollbar { display: none; }
    </style>

    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-1">
            <div class="p-2 bg-blue-50 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900">Painel Fiscal por Competencia</h1>
                <p class="text-sm text-gray-500">Apuracao consolidada por periodo</p>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-end gap-3">
            <div class="flex-1 min-w-0 w-full sm:w-auto">
                <label class="block text-xs font-medium text-gray-500 mb-1">Cliente</label>
                <select id="rf-cliente" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                    @foreach($clientes as $c)
                        <option value="{{ $c->id }}" {{ $c->id == ($defaultClienteId ?? '') ? 'selected' : '' }}>
                            {{ $c->razao_social ?? $c->nome }}
                            @if($c->is_empresa_propria) (Propria) @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-44">
                <label class="block text-xs font-medium text-gray-500 mb-1">Competencia</label>
                <select id="rf-competencia" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                    @foreach($competencias as $comp)
                        <option value="{{ $comp }}" {{ $comp == ($defaultCompetencia ?? '') ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::parse($comp . '-01')->translatedFormat('M/Y') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button id="rf-btn-filtrar" class="w-full sm:w-auto px-5 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                Carregar
            </button>
        </div>
    </div>

    {{-- Navegacao sticky --}}
    <nav class="rf-nav sticky top-0 z-20 bg-gray-50/95 backdrop-blur-sm py-2 mb-4 flex items-center gap-1 overflow-x-auto border-b border-gray-200" id="rf-nav">
        <a href="#secao-resumo" class="rf-nav-link whitespace-nowrap px-3 py-1.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 transition-colors">Resumo</a>
        <a href="#secao-icms" class="rf-nav-link whitespace-nowrap px-3 py-1.5 rounded-full text-xs font-medium text-gray-500 hover:bg-gray-200 transition-colors">ICMS/IPI</a>
        <a href="#secao-pis-cofins" class="rf-nav-link whitespace-nowrap px-3 py-1.5 rounded-full text-xs font-medium text-gray-500 hover:bg-gray-200 transition-colors">PIS/COFINS</a>
        <a href="#secao-retencoes" class="rf-nav-link whitespace-nowrap px-3 py-1.5 rounded-full text-xs font-medium text-gray-500 hover:bg-gray-200 transition-colors">Retencoes</a>
        <a href="#secao-cruzamentos" class="rf-nav-link whitespace-nowrap px-3 py-1.5 rounded-full text-xs font-medium text-gray-500 hover:bg-gray-200 transition-colors">Cruzamentos</a>
        <a href="#secao-alertas" class="rf-nav-link whitespace-nowrap px-3 py-1.5 rounded-full text-xs font-medium text-gray-500 hover:bg-gray-200 transition-colors">Alertas</a>
    </nav>

    {{-- Estado vazio global --}}
    <div id="rf-empty-state" class="hidden text-center py-16">
        <svg class="mx-auto w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
        </svg>
        <h3 class="text-lg font-medium text-gray-500 mb-2">Sem dados para este periodo</h3>
        <p class="text-sm text-gray-400">Importe um arquivo EFD para ver o resumo fiscal desta competencia.</p>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- SECAO 1: RESUMO EXECUTIVO --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <section id="secao-resumo" class="rf-section mb-6">
        <div class="rf-section-header flex items-center justify-between cursor-pointer select-none group" data-toggle="resumo">
            <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <span class="w-1.5 h-6 bg-blue-600 rounded-full"></span>
                Resumo Executivo
            </h2>
            <svg class="rf-chevron w-5 h-5 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>
        <div class="rf-section-content mt-4" data-section="resumo">
            <div id="rf-resumo-content">
                {{-- Skeleton --}}
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3" id="rf-resumo-skeleton">
                    @for($i = 0; $i < 5; $i++)
                    <div class="bg-white rounded-xl border border-gray-200 p-4">
                        <div class="rf-skeleton h-3 w-20 rounded mb-3"></div>
                        <div class="rf-skeleton h-7 w-28 rounded mb-2"></div>
                        <div class="rf-skeleton h-3 w-16 rounded"></div>
                    </div>
                    @endfor
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- SECAO 2: APURACAO ICMS/IPI --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <section id="secao-icms" class="rf-section mb-6">
        <div class="rf-section-header flex items-center justify-between cursor-pointer select-none group" data-toggle="icms">
            <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <span class="w-1.5 h-6 bg-emerald-600 rounded-full"></span>
                Apuracao ICMS/IPI
            </h2>
            <svg class="rf-chevron w-5 h-5 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>
        <div class="rf-section-content mt-4" data-section="icms">
            <div id="rf-icms-content">
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="rf-skeleton h-4 w-40 rounded mb-4"></div>
                    <div class="rf-skeleton h-48 w-full rounded"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- SECAO 3: APURACAO PIS/COFINS --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <section id="secao-pis-cofins" class="rf-section mb-6">
        <div class="rf-section-header flex items-center justify-between cursor-pointer select-none group" data-toggle="pis-cofins">
            <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <span class="w-1.5 h-6 bg-violet-600 rounded-full"></span>
                Apuracao PIS/COFINS
            </h2>
            <svg class="rf-chevron w-5 h-5 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>
        <div class="rf-section-content mt-4" data-section="pis-cofins">
            <div id="rf-piscofins-content">
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="rf-skeleton h-4 w-40 rounded mb-4"></div>
                    <div class="rf-skeleton h-48 w-full rounded"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- SECAO 4: RETENCOES NA FONTE --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <section id="secao-retencoes" class="rf-section mb-6">
        <div class="rf-section-header flex items-center justify-between cursor-pointer select-none group" data-toggle="retencoes">
            <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <span class="w-1.5 h-6 bg-amber-600 rounded-full"></span>
                Retencoes na Fonte
            </h2>
            <svg class="rf-chevron w-5 h-5 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>
        <div class="rf-section-content mt-4" data-section="retencoes">
            <div id="rf-retencoes-content">
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="rf-skeleton h-4 w-40 rounded mb-4"></div>
                    <div class="rf-skeleton h-32 w-full rounded"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- SECAO 5: CRUZAMENTOS E DIVERGENCIAS --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <section id="secao-cruzamentos" class="rf-section mb-6">
        <div class="rf-section-header flex items-center justify-between cursor-pointer select-none group" data-toggle="cruzamentos">
            <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <span class="w-1.5 h-6 bg-rose-600 rounded-full"></span>
                Cruzamentos e Divergencias
            </h2>
            <svg class="rf-chevron w-5 h-5 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>
        <div class="rf-section-content mt-4" data-section="cruzamentos">
            <div id="rf-cruzamentos-content">
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="rf-skeleton h-4 w-40 rounded mb-4"></div>
                    <div class="rf-skeleton h-32 w-full rounded"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- SECAO 6: ALERTAS FISCAIS --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <section id="secao-alertas" class="rf-section mb-6">
        <div class="rf-section-header flex items-center justify-between cursor-pointer select-none group" data-toggle="alertas">
            <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <span class="w-1.5 h-6 bg-red-600 rounded-full"></span>
                Alertas Fiscais
                <span id="rf-alertas-badge" class="hidden ml-1 px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">0</span>
            </h2>
            <svg class="rf-chevron w-5 h-5 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>
        <div class="rf-section-content mt-4" data-section="alertas">
            <div id="rf-alertas-content">
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="rf-skeleton h-4 w-40 rounded mb-4"></div>
                    <div class="rf-skeleton h-32 w-full rounded"></div>
                </div>
            </div>
        </div>
    </section>

</div>
</div>

<script>
(function() {
    'use strict';

    var container = document.getElementById('resumo-fiscal-container');
    if (!container) return;

    var loadedSections = {};

    // ── Helpers ──

    function fBrl(v) {
        if (v === null || v === undefined) return 'R$ 0,00';
        return 'R$ ' + Number(v).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function fPct(v) {
        if (v === null || v === undefined) return '-';
        return (v >= 0 ? '+' : '') + v.toFixed(1) + '%';
    }

    function getParams() {
        var p = new URLSearchParams();
        p.set('cliente_id', document.getElementById('rf-cliente')?.value || '');
        p.set('competencia', document.getElementById('rf-competencia')?.value || '');
        return p.toString();
    }

    function semDados(el, msg) {
        el.innerHTML = '<div class="text-center py-8"><p class="text-sm text-gray-400">' + (msg || 'Sem dados para este periodo.') + '</p></div>';
    }

    function deltaHtml(delta) {
        if (!delta || (delta.valor === 0 && delta.percentual === 0)) return '<span class="text-xs text-gray-400">--</span>';
        var up = delta.valor > 0;
        var color = up ? 'text-red-500' : 'text-green-600';
        var arrow = up ? '&#9650;' : '&#9660;';
        return '<span class="text-xs ' + color + '">' + arrow + ' ' + fPct(delta.percentual) + '</span>';
    }

    function semaforoHtml(status, label) {
        var cores = {
            verde: 'bg-green-100 text-green-800 border-green-300',
            amarelo: 'bg-amber-50 text-amber-800 border-amber-300',
            vermelho: 'bg-red-100 text-red-800 border-red-300',
            sem_dados: 'bg-gray-100 text-gray-500 border-gray-300'
        };
        var icones = {
            verde: '<svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
            amarelo: '<svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 2l10 18H2L12 2z"/></svg>',
            vermelho: '<svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>',
            sem_dados: '<svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01"/></svg>'
        };
        var c = cores[status] || cores.sem_dados;
        var ic = icones[status] || icones.sem_dados;
        return '<div class="flex items-center gap-2 px-3 py-1.5 rounded-lg border ' + c + '">' + ic + '<span class="text-sm font-medium">' + (label || status) + '</span></div>';
    }

    // ── Loaders ──

    async function loadSection(id, url, renderFn) {
        if (loadedSections[id]) return;
        try {
            var resp = await fetch(url + '?' + getParams(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            if (!resp.ok) throw new Error('HTTP ' + resp.status);
            var data = await resp.json();
            renderFn(data);
            loadedSections[id] = true;
        } catch (err) {
            console.error('[RF] Erro ' + id + ':', err);
            var el = document.getElementById('rf-' + id.replace('secao-', '') + '-content') || document.getElementById('rf-' + id + '-content');
            if (el) el.innerHTML = '<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-600">Erro ao carregar dados.</div>';
        }
    }

    // ── Renders ──

    function renderResumo(data) {
        var el = document.getElementById('rf-resumo-content');
        if (!data.tem_dados) { semDados(el); document.getElementById('rf-empty-state')?.classList.remove('hidden'); return; }
        document.getElementById('rf-empty-state')?.classList.add('hidden');

        var kpis = data.kpis;
        var cards = [
            { label: 'ICMS a Recolher', key: 'icms_a_recolher', color: 'blue' },
            { label: 'PIS a Recolher', key: 'pis_a_recolher', color: 'violet' },
            { label: 'COFINS a Recolher', key: 'cofins_a_recolher', color: 'purple' },
            { label: 'Retencoes Compensaveis', key: 'retencoes_compensaveis', color: 'amber' },
            { label: 'Saldo Liquido', key: 'saldo_liquido', color: 'emerald' }
        ];

        var html = '<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">';
        cards.forEach(function(c) {
            var k = kpis[c.key];
            var val = k ? k.valor : 0;
            var isSaldo = c.key === 'saldo_liquido';
            var isRetencao = c.key === 'retencoes_compensaveis';
            var valColor = isSaldo ? (val <= 0 ? 'text-green-700' : 'text-red-700') : (isRetencao ? 'text-amber-700' : 'text-gray-900');

            html += '<div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-shadow">';
            html += '<p class="text-xs font-medium text-gray-500 mb-1">' + c.label + '</p>';
            html += '<p class="text-lg sm:text-xl font-bold ' + valColor + ' truncate">' + fBrl(val) + '</p>';
            html += '<div class="mt-1">' + deltaHtml(k ? k.delta : null) + '</div>';
            html += '</div>';
        });
        html += '</div>';
        el.innerHTML = html;
    }

    function renderIcms(data) {
        var el = document.getElementById('rf-icms-content');
        if (!data.tem_dados) { semDados(el, 'Sem apuracao ICMS para este periodo. Importe um EFD ICMS/IPI.'); return; }

        var p = data.icms_proprio;
        var html = '';

        // Periodo
        if (data.periodo_inicio) {
            html += '<div class="mb-4 text-xs text-gray-500">Periodo: ' + data.periodo_inicio + ' a ' + data.periodo_fim + '</div>';
        }

        // ICMS Proprio
        html += '<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-4">';
        html += '<div class="px-4 py-3 bg-emerald-50 border-b border-emerald-200"><h3 class="font-semibold text-emerald-800 text-sm">ICMS Proprio (E110)</h3></div>';
        html += '<div class="p-4">';

        // Tabela de fluxo
        html += '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';

        // Coluna Debitos
        html += '<div class="space-y-2">';
        html += '<h4 class="text-xs font-bold text-red-600 uppercase tracking-wider">Debitos</h4>';
        html += fluxoRow('Total Debitos', p.tot_debitos, 'red');
        html += fluxoRow('(+) Ajustes Debitos', p.aj_debitos);
        html += fluxoRow('(+) Estornos Credito', p.estornos_credito);
        html += fluxoRow('(=) Total Aj. Debitos', p.tot_aj_debitos, 'red', true);
        html += '</div>';

        // Coluna Creditos
        html += '<div class="space-y-2">';
        html += '<h4 class="text-xs font-bold text-green-600 uppercase tracking-wider">Creditos</h4>';
        html += fluxoRow('Total Creditos', p.tot_creditos, 'green');
        html += fluxoRow('(+) Ajustes Creditos', p.aj_creditos);
        html += fluxoRow('(+) Estornos Debito', p.estornos_debito);
        html += fluxoRow('(=) Total Aj. Creditos', p.tot_aj_creditos, 'green', true);
        html += '</div>';

        html += '</div>'; // grid

        // Resultado
        html += '<div class="mt-4 pt-4 border-t border-gray-200 space-y-2">';
        html += fluxoRow('Saldo Credor Anterior', p.sld_credor_ant, 'green');
        html += fluxoRow('Saldo Apurado', p.sld_apurado, p.sld_apurado >= 0 ? 'red' : 'green', true);
        html += fluxoRow('(-) Deducoes', p.tot_deducoes);
        html += '<div class="pt-2 border-t border-gray-300">';
        if (p.a_recolher > 0) {
            html += fluxoRow('ICMS a Recolher', p.a_recolher, 'red', true, true);
        } else {
            html += fluxoRow('Saldo Credor a Transportar', p.sld_credor_transportar, 'green', true, true);
        }
        if (p.deb_especiais > 0) {
            html += fluxoRow('(+) Debitos Especiais', p.deb_especiais, 'red');
        }
        html += '</div></div>';
        html += '</div></div>';

        // ICMS-ST
        if (data.tem_st && data.icms_st) {
            var st = data.icms_st;
            html += '<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-4">';
            html += '<div class="px-4 py-3 bg-orange-50 border-b border-orange-200">';
            html += '<h3 class="font-semibold text-orange-800 text-sm">ICMS-ST (E210)';
            if (st.uf) html += ' <span class="ml-2 px-2 py-0.5 bg-orange-200 rounded text-xs">' + st.uf + '</span>';
            html += '</h3></div>';
            html += '<div class="p-4 space-y-2">';
            html += fluxoRow('Saldo Credor Anterior', st.sld_credor_ant, 'green');
            html += fluxoRow('(+) Devolucoes', st.devolucoes);
            html += fluxoRow('(+) Ressarcimentos', st.ressarcimentos);
            html += fluxoRow('(+) Outros Creditos', st.outros_creditos);
            html += fluxoRow('(-) Retencao', st.retencao, 'red');
            html += fluxoRow('(-) Outros Debitos', st.outros_debitos);
            html += '<div class="pt-2 border-t border-gray-300">';
            html += fluxoRow('ICMS-ST a Recolher', st.icms_recolher, 'red', true, true);
            html += '</div></div></div>';
        }

        // DIFAL/FCP
        if (data.tem_difal && data.difal_fcp) {
            html += '<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-4">';
            html += '<div class="px-4 py-3 bg-teal-50 border-b border-teal-200"><h3 class="font-semibold text-teal-800 text-sm">DIFAL/FCP (E310)</h3></div>';
            html += '<div class="p-4">';
            var df = data.difal_fcp;
            var dfItems = Array.isArray(df) ? df : (df.items && Array.isArray(df.items) ? df.items : null);
            if (dfItems && dfItems.length > 0) {
                // Múltiplos registros por UF
                html += '<div class="overflow-x-auto"><table class="w-full text-sm">';
                html += '<thead><tr class="bg-gray-50 text-xs text-gray-500 uppercase">';
                html += '<th class="px-4 py-2 text-left">UF</th>';
                html += '<th class="px-4 py-2 text-right">DIFAL Origem</th>';
                html += '<th class="px-4 py-2 text-right">DIFAL Destino</th>';
                html += '<th class="px-4 py-2 text-right">FCP a Recolher</th>';
                html += '</tr></thead><tbody>';
                var totDifalOri = 0, totDifalDst = 0, totFcp = 0;
                dfItems.forEach(function(d) {
                    var vlOri = parseFloat(d.VL_SLD_DEV_ANT_DIFAL ?? d.difal_origem ?? 0);
                    var vlDst = parseFloat(d.VL_ICMS_RECOLHER_DIFAL ?? d.difal_destino ?? d.icms_recolher ?? 0);
                    var vlFcp = parseFloat(d.VL_FCP_RECOLHER ?? d.fcp ?? 0);
                    totDifalOri += vlOri; totDifalDst += vlDst; totFcp += vlFcp;
                    html += '<tr class="border-t hover:bg-gray-50">';
                    html += '<td class="px-4 py-2 font-medium text-xs">' + (d.UF ?? d.uf ?? '—') + '</td>';
                    html += '<td class="px-4 py-2 text-right font-mono text-xs">' + fBrl(vlOri) + '</td>';
                    html += '<td class="px-4 py-2 text-right font-mono text-xs">' + fBrl(vlDst) + '</td>';
                    html += '<td class="px-4 py-2 text-right font-mono text-xs">' + fBrl(vlFcp) + '</td>';
                    html += '</tr>';
                });
                if (dfItems.length > 1) {
                    html += '<tr class="border-t-2 border-gray-300 bg-gray-50 font-bold text-sm">';
                    html += '<td class="px-4 py-2">Total</td>';
                    html += '<td class="px-4 py-2 text-right font-mono">' + fBrl(totDifalOri) + '</td>';
                    html += '<td class="px-4 py-2 text-right font-mono">' + fBrl(totDifalDst) + '</td>';
                    html += '<td class="px-4 py-2 text-right font-mono">' + fBrl(totFcp) + '</td>';
                    html += '</tr>';
                }
                html += '</tbody></table></div>';
                html += '<div class="mt-3 pt-3 border-t border-gray-200 flex justify-between items-center">';
                html += '<span class="text-sm font-semibold text-teal-800">DIFAL + FCP a Recolher</span>';
                html += '<span class="text-base font-bold text-red-700 font-mono">' + fBrl(totDifalDst + totFcp) + '</span>';
                html += '</div>';
            } else {
                // Objeto único
                var vlDifalOri = parseFloat(df.VL_SLD_DEV_ANT_DIFAL ?? df.difal_origem ?? 0);
                var vlDifalDst = parseFloat(df.VL_ICMS_RECOLHER_DIFAL ?? df.difal_destino ?? df.icms_recolher ?? 0);
                var vlFcp2     = parseFloat(df.VL_FCP_RECOLHER ?? df.fcp ?? 0);
                html += '<div class="space-y-2">';
                if (df.UF || df.uf) {
                    html += '<div class="flex justify-between items-center py-1 text-sm"><span class="text-gray-600">UF Destino</span><span class="text-gray-700 font-medium">' + (df.UF ?? df.uf) + '</span></div>';
                }
                html += fluxoRow('DIFAL Origem (Saldo Dev. Ant.)', vlDifalOri);
                html += fluxoRow('DIFAL Destino (ICMS a Recolher)', vlDifalDst, 'red', true);
                html += fluxoRow('FCP a Recolher', vlFcp2, 'red');
                html += '</div>';
                html += '<div class="mt-3 pt-3 border-t border-teal-200 flex justify-between items-center">';
                html += '<span class="text-sm font-semibold text-teal-800">DIFAL + FCP a Recolher</span>';
                html += '<span class="text-base font-bold text-red-700 font-mono">' + fBrl(vlDifalDst + vlFcp2) + '</span>';
                html += '</div>';
            }
            html += '</div>';
            html += '</div>';
        }

        // Obrigacoes
        var obs = (data.icms_obrigacoes || []).concat(data.st_obrigacoes || []);
        if (obs.length > 0) {
            html += '<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">';
            html += '<div class="px-4 py-3 bg-red-50 border-b border-red-200"><h3 class="font-semibold text-red-800 text-sm">Obrigacoes a Recolher (E116/E250)</h3></div>';
            html += '<div class="overflow-x-auto"><table class="w-full text-sm">';
            html += '<thead><tr class="bg-gray-50 text-xs text-gray-500 uppercase"><th class="px-4 py-2 text-left">Codigo</th><th class="px-4 py-2 text-right">Valor</th><th class="px-4 py-2 text-center">Vencimento</th></tr></thead><tbody>';
            obs.forEach(function(ob) {
                var dtVcto = ob.dt_vcto || ob.data_vencimento || '';
                var valor = ob.vl_or || ob.valor_obrigacao || 0;
                var vencido = dtVcto && new Date(dtVcto) < new Date();
                html += '<tr class="border-t ' + (vencido ? 'bg-red-50' : '') + '">';
                html += '<td class="px-4 py-2 font-mono text-xs">' + (ob.cod_or || ob.codigo_credito || '-') + '</td>';
                html += '<td class="px-4 py-2 text-right font-medium">' + fBrl(valor) + '</td>';
                html += '<td class="px-4 py-2 text-center">';
                if (vencido) html += '<span class="px-2 py-0.5 bg-red-200 text-red-800 rounded text-xs font-bold">VENCIDO</span> ';
                html += (dtVcto ? new Date(dtVcto).toLocaleDateString('pt-BR') : '-');
                html += '</td></tr>';
            });
            html += '</tbody></table></div></div>';
        }

        el.innerHTML = html;
    }

    function fluxoRow(label, valor, color, bold, big) {
        var valClass = 'text-gray-700';
        if (color === 'red') valClass = 'text-red-700';
        if (color === 'green') valClass = 'text-green-700';
        if (bold) valClass += ' font-bold';
        if (big) valClass += ' text-base';
        var labelClass = bold ? 'font-semibold text-gray-800' : 'text-gray-600';
        return '<div class="flex justify-between items-center py-1 ' + (big ? 'text-base' : 'text-sm') + '">' +
            '<span class="' + labelClass + '">' + label + '</span>' +
            '<span class="' + valClass + ' font-mono">' + fBrl(valor) + '</span></div>';
    }

    function renderPisCofins(data) {
        var el = document.getElementById('rf-piscofins-content');
        if (!data.tem_dados) { semDados(el, 'Sem apuracao PIS/COFINS para este periodo. Importe um EFD Contribuicoes.'); return; }

        var html = '';

        // Regime badge
        var regimeLabel = { nao_cumulativo: 'Nao-Cumulativo', cumulativo: 'Cumulativo', misto: 'Misto' };
        var regimeColor = { nao_cumulativo: 'bg-blue-100 text-blue-800', cumulativo: 'bg-gray-100 text-gray-800', misto: 'bg-purple-100 text-purple-800' };
        html += '<div class="mb-4"><span class="px-3 py-1.5 rounded-full text-sm font-bold ' + (regimeColor[data.regime] || 'bg-gray-100 text-gray-700') + '">Regime: ' + (regimeLabel[data.regime] || data.regime) + '</span></div>';

        // PIS
        html += renderContribuicao('PIS', data.pis, 'violet');

        // COFINS
        html += renderContribuicao('COFINS', data.cofins, 'purple');

        // Creditos NC
        if (data.tem_creditos_nc) {
            var credPis = data.pis_creditos_nc || [];
            var credCofins = data.cofins_creditos_nc || [];
            if (credPis.length > 0 || credCofins.length > 0) {
                html += '<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-4">';
                html += '<div class="px-4 py-3 bg-indigo-50 border-b border-indigo-200"><h3 class="font-semibold text-indigo-800 text-sm">Creditos Nao-Cumulativos (M100/M500)</h3></div>';
                html += '<div class="p-4 text-sm">';
                if (credPis.length > 0) {
                    html += '<h4 class="font-semibold text-gray-700 mb-2">PIS</h4>';
                    html += renderCreditosTable(credPis);
                }
                if (credCofins.length > 0) {
                    html += '<h4 class="font-semibold text-gray-700 mb-2 mt-4">COFINS</h4>';
                    html += renderCreditosTable(credCofins);
                }
                html += '</div></div>';
            }
        }

        // Receitas nao tributadas
        var naoTrib = data.pis_nao_tributado || [];
        if (naoTrib.length > 0) {
            html += '<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">';
            html += '<div class="px-4 py-3 bg-gray-50 border-b border-gray-200"><h3 class="font-semibold text-gray-700 text-sm">Receitas Nao Tributadas (M400/M410)</h3></div>';
            html += '<div class="p-4 overflow-x-auto"><table class="w-full text-sm"><thead><tr class="text-xs text-gray-500 uppercase"><th class="px-3 py-2 text-left">CST</th><th class="px-3 py-2 text-right">Valor</th></tr></thead><tbody>';
            naoTrib.forEach(function(r) {
                html += '<tr class="border-t"><td class="px-3 py-2 font-mono">' + (r.cst || r.cod_cst || '-') + '</td><td class="px-3 py-2 text-right">' + fBrl(r.vl_rec || r.valor || 0) + '</td></tr>';
            });
            html += '</tbody></table></div></div>';
        }

        el.innerHTML = html;
    }

    function renderContribuicao(nome, dados, color) {
        var html = '<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-4">';
        html += '<div class="px-4 py-3 bg-' + color + '-50 border-b border-' + color + '-200"><h3 class="font-semibold text-' + color + '-800 text-sm">Apuracao ' + nome + '</h3></div>';
        html += '<div class="p-4">';

        var temNc = dados.nao_cumulativo > 0 || dados.nc_recolher > 0;
        var temCum = dados.cumulativo > 0 || dados.cum_recolher > 0;

        if (temNc) {
            html += '<h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Nao-Cumulativo</h4>';
            html += '<div class="space-y-1 mb-4">';
            html += fluxoRow('Contribuicao Apurada', dados.nao_cumulativo, 'red');
            html += fluxoRow('(-) Credito Descontado', dados.credito_descontado, 'green');
            html += fluxoRow('(-) Credito Desc. Anterior', dados.credito_desc_ant);
            html += fluxoRow('(=) Contribuicao Devida NC', dados.nc_devida, 'red', true);
            html += fluxoRow('(-) Retencao NC', dados.retencao_nc);
            html += fluxoRow('(-) Outras Deducoes NC', dados.outras_deducoes_nc);
            html += fluxoRow(nome + ' NC a Recolher', dados.nc_recolher, 'red', true);
            html += '</div>';
        }

        if (temCum) {
            html += '<h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Cumulativo</h4>';
            html += '<div class="space-y-1 mb-4">';
            html += fluxoRow('Contribuicao Apurada', dados.cumulativo, 'red');
            html += fluxoRow('(-) Retencao Cum.', dados.retencao_cum);
            html += fluxoRow('(-) Outras Deducoes Cum.', dados.outras_deducoes_cum);
            html += fluxoRow(nome + ' Cum. a Recolher', dados.cum_recolher, 'red', true);
            html += '</div>';
        }

        html += '<div class="pt-3 border-t border-gray-300">';
        html += fluxoRow('Total ' + nome + ' a Recolher', dados.total_recolher, 'red', true, true);
        html += '</div>';

        html += '</div></div>';
        return html;
    }

    function renderCreditosTable(creditos) {
        var html = '<table class="w-full text-xs mb-2"><thead><tr class="text-gray-500 uppercase"><th class="px-2 py-1 text-left">Tipo</th><th class="px-2 py-1 text-right">Apropriado</th><th class="px-2 py-1 text-right">Desc. Anterior</th><th class="px-2 py-1 text-right">Desc. Periodo</th></tr></thead><tbody>';
        creditos.forEach(function(c) {
            html += '<tr class="border-t"><td class="px-2 py-1">' + (c.tipo_credito || c.cod_cred || '-') + '</td>';
            html += '<td class="px-2 py-1 text-right font-mono">' + fBrl(c.vl_cred_apur || c.valor_credito_apropriado || 0) + '</td>';
            html += '<td class="px-2 py-1 text-right font-mono">' + fBrl(c.vl_cred_desc_ant || c.valor_credito_desc_ant || 0) + '</td>';
            html += '<td class="px-2 py-1 text-right font-mono">' + fBrl(c.vl_cred_desc || c.valor_credito_desc_per || 0) + '</td>';
            html += '</tr>';
        });
        html += '</tbody></table>';
        return html;
    }

    function renderRetencoes(data) {
        var el = document.getElementById('rf-retencoes-content');
        if (!data.tem_dados) { semDados(el, 'Sem retencoes na fonte (F600) para este periodo.'); return; }

        var k = data.kpis;
        var html = '';

        // KPIs
        html += '<div class="grid grid-cols-3 gap-3 mb-4">';
        html += '<div class="bg-amber-50 rounded-xl border border-amber-200 p-4 text-center">';
        html += '<p class="text-xs text-amber-600 font-medium">Total Retido</p>';
        html += '<p class="text-lg font-bold text-amber-800">' + fBrl(k.total_retido) + '</p></div>';
        html += '<div class="bg-white rounded-xl border border-gray-200 p-4 text-center">';
        html += '<p class="text-xs text-gray-500 font-medium">Retencoes</p>';
        html += '<p class="text-lg font-bold text-gray-900">' + k.qtd_retencoes + '</p></div>';
        html += '<div class="bg-white rounded-xl border border-gray-200 p-4 text-center">';
        html += '<p class="text-xs text-gray-500 font-medium">CNPJs Retentores</p>';
        html += '<p class="text-lg font-bold text-gray-900">' + k.cnpjs_unicos + '</p></div>';
        html += '</div>';

        // Tabela
        html += '<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">';
        html += '<div class="overflow-x-auto"><table class="w-full text-sm">';
        html += '<thead><tr class="bg-gray-50 text-xs text-gray-500 uppercase">';
        html += '<th class="px-3 py-2 text-left">Data</th>';
        html += '<th class="px-3 py-2 text-left">CNPJ</th>';
        html += '<th class="px-3 py-2 text-left">Natureza</th>';
        html += '<th class="px-3 py-2 text-right">Base Calculo</th>';
        html += '<th class="px-3 py-2 text-right">PIS</th>';
        html += '<th class="px-3 py-2 text-right">COFINS</th>';
        html += '<th class="px-3 py-2 text-right">Total</th>';
        html += '<th class="px-3 py-2 text-center">Cod. Receita</th>';
        html += '</tr></thead><tbody>';

        (data.retencoes || []).forEach(function(r) {
            var natColor = { '01': 'bg-blue-100 text-blue-700', '02': 'bg-orange-100 text-orange-700', '03': 'bg-purple-100 text-purple-700' };
            html += '<tr class="border-t hover:bg-gray-50">';
            html += '<td class="px-3 py-2 whitespace-nowrap">' + r.data + '</td>';
            html += '<td class="px-3 py-2 font-mono text-xs">' + r.cnpj + '</td>';
            html += '<td class="px-3 py-2"><span class="px-2 py-0.5 rounded text-xs font-medium ' + (natColor[r.natureza_raw] || 'bg-gray-100 text-gray-700') + '">' + r.natureza + '</span></td>';
            html += '<td class="px-3 py-2 text-right font-mono">' + fBrl(r.base_calculo) + '</td>';
            html += '<td class="px-3 py-2 text-right font-mono">' + fBrl(r.valor_pis) + '</td>';
            html += '<td class="px-3 py-2 text-right font-mono">' + fBrl(r.valor_cofins) + '</td>';
            html += '<td class="px-3 py-2 text-right font-mono font-bold">' + fBrl(r.total) + '</td>';
            html += '<td class="px-3 py-2 text-center font-mono text-xs">' + (r.cod_receita || '-') + '</td>';
            html += '</tr>';
        });

        // Totalizador
        html += '<tr class="border-t-2 border-gray-300 bg-gray-50 font-bold">';
        html += '<td colspan="6" class="px-3 py-2 text-right">Total</td>';
        html += '<td class="px-3 py-2 text-right font-mono">' + fBrl(k.total_retido) + '</td>';
        html += '<td></td></tr>';
        html += '</tbody></table></div></div>';

        el.innerHTML = html;
    }

    function renderCruzamentos(data) {
        var el = document.getElementById('rf-cruzamentos-content');
        var html = '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">';

        // Card ICMS Debitos
        html += cruzamentoCard(
            'ICMS Debitos',
            'Apuracao (E110) vs Soma das Notas de Saida',
            data.icms.tem_dados,
            data.icms.declarado_debito,
            data.icms.notas_debito,
            data.icms.divergencia_debito_pct,
            data.icms.status_debito
        );

        // Card ICMS Creditos
        html += cruzamentoCard(
            'ICMS Creditos',
            'Apuracao (E110) vs Soma das Notas de Entrada',
            data.icms.tem_dados,
            data.icms.declarado_credito,
            data.icms.notas_credito,
            data.icms.divergencia_credito_pct,
            data.icms.status_credito
        );

        // Card PIS
        html += cruzamentoCard(
            'PIS a Recolher',
            'Apuracao (M200) vs Soma dos Itens',
            data.pis_cofins.tem_dados,
            data.pis_cofins.pis_declarado,
            data.pis_cofins.pis_notas,
            data.pis_cofins.pis_divergencia_pct,
            data.pis_cofins.pis_status
        );

        // Card COFINS
        html += cruzamentoCard(
            'COFINS a Recolher',
            'Apuracao (M600) vs Soma dos Itens',
            data.pis_cofins.tem_dados,
            data.pis_cofins.cofins_declarado,
            data.pis_cofins.cofins_notas,
            data.pis_cofins.cofins_divergencia_pct,
            data.pis_cofins.cofins_status
        );

        // Card Retencoes
        var ret = data.retencoes;
        html += '<div class="bg-white rounded-xl border border-gray-200 p-4 md:col-span-2 lg:col-span-2">';
        html += '<div class="flex items-center justify-between mb-3">';
        html += '<h3 class="font-semibold text-gray-800 text-sm">Retencoes vs Apuracao</h3>';
        html += semaforoHtml(ret.status, ret.status === 'verde' ? 'OK' : 'Atencao');
        html += '</div>';
        if (ret.tem_dados) {
            html += '<div class="grid grid-cols-3 gap-4 text-center">';
            html += '<div><p class="text-xs text-gray-500">Retido (F600)</p><p class="font-bold text-gray-900">' + fBrl(ret.total_retido) + '</p></div>';
            html += '<div><p class="text-xs text-gray-500">Deduzido na Apuracao</p><p class="font-bold text-gray-900">' + fBrl(ret.deduzido_apuracao) + '</p></div>';
            html += '<div><p class="text-xs text-gray-500">Nao Compensado</p><p class="font-bold ' + (ret.nao_compensado > 0 ? 'text-amber-700' : 'text-green-700') + '">' + fBrl(ret.nao_compensado) + '</p></div>';
            html += '</div>';
        } else {
            html += '<p class="text-sm text-gray-400">Sem dados de retencao para cruzamento.</p>';
        }
        html += '</div>';

        html += '</div>';
        el.innerHTML = html;
    }

    function cruzamentoCard(titulo, subtitulo, temDados, declarado, notas, divPct, status) {
        var html = '<div class="bg-white rounded-xl border border-gray-200 p-4">';
        html += '<div class="flex items-center justify-between mb-3">';
        html += '<div><h3 class="font-semibold text-gray-800 text-sm">' + titulo + '</h3>';
        html += '<p class="text-xs text-gray-400">' + subtitulo + '</p></div>';
        html += semaforoHtml(status, divPct !== null ? divPct.toFixed(1) + '%' : 'N/A');
        html += '</div>';
        if (temDados) {
            html += '<div class="grid grid-cols-2 gap-3 text-center">';
            html += '<div class="bg-gray-50 rounded-lg p-2"><p class="text-xs text-gray-500">Declarado</p><p class="font-bold text-sm">' + fBrl(declarado) + '</p></div>';
            html += '<div class="bg-gray-50 rounded-lg p-2"><p class="text-xs text-gray-500">Notas</p><p class="font-bold text-sm">' + fBrl(notas) + '</p></div>';
            html += '</div>';
        } else {
            html += '<p class="text-sm text-gray-400">Sem dados de apuracao.</p>';
        }
        html += '</div>';
        return html;
    }

    function renderAlertas(data) {
        var el = document.getElementById('rf-alertas-content');
        var badge = document.getElementById('rf-alertas-badge');

        if (!data.alertas || data.alertas.length === 0) {
            if (badge) badge.classList.add('hidden');
            el.innerHTML = '<div class="bg-green-50 border border-green-200 rounded-xl p-6 text-center">' +
                '<svg class="mx-auto w-10 h-10 text-green-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>' +
                '<p class="text-green-700 font-medium">Nenhum alerta para este periodo</p>' +
                '<p class="text-sm text-green-500 mt-1">Todos os cruzamentos estao dentro dos limites esperados.</p></div>';
            return;
        }

        if (badge) {
            badge.textContent = data.resumo.total;
            badge.classList.remove('hidden');
        }

        var html = '';

        // KPIs de severidade
        html += '<div class="grid grid-cols-3 gap-3 mb-4">';
        if (data.resumo.alta > 0) html += '<div class="bg-red-50 border border-red-200 rounded-xl p-3 text-center"><p class="text-2xl font-bold text-red-700">' + data.resumo.alta + '</p><p class="text-xs text-red-500">Criticos</p></div>';
        else html += '<div class="bg-gray-50 border border-gray-200 rounded-xl p-3 text-center"><p class="text-2xl font-bold text-gray-300">0</p><p class="text-xs text-gray-400">Criticos</p></div>';
        if (data.resumo.media > 0) html += '<div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-center"><p class="text-2xl font-bold text-amber-700">' + data.resumo.media + '</p><p class="text-xs text-amber-500">Atencao</p></div>';
        else html += '<div class="bg-gray-50 border border-gray-200 rounded-xl p-3 text-center"><p class="text-2xl font-bold text-gray-300">0</p><p class="text-xs text-gray-400">Atencao</p></div>';
        html += '<div class="bg-gray-50 border border-gray-200 rounded-xl p-3 text-center"><p class="text-2xl font-bold text-gray-400">' + (data.resumo.info || 0) + '</p><p class="text-xs text-gray-400">Info</p></div>';
        html += '</div>';

        // Lista de alertas
        html += '<div class="space-y-3">';
        data.alertas.forEach(function(a) {
            var sevColors = { alta: 'border-l-red-500 bg-red-50', media: 'border-l-amber-500 bg-amber-50', info: 'border-l-blue-500 bg-blue-50' };
            var sevBadge = { alta: 'bg-red-200 text-red-800', media: 'bg-amber-200 text-amber-800', info: 'bg-blue-200 text-blue-800' };
            html += '<div class="border-l-4 rounded-lg p-4 ' + (sevColors[a.severidade] || 'border-l-gray-300 bg-gray-50') + '">';
            html += '<div class="flex items-start justify-between gap-2">';
            html += '<div>';
            html += '<div class="flex items-center gap-2 mb-1">';
            html += '<span class="px-2 py-0.5 rounded text-xs font-bold ' + (sevBadge[a.severidade] || 'bg-gray-200 text-gray-700') + '">' + a.categoria + '</span>';
            html += '<h4 class="font-semibold text-sm text-gray-900">' + a.titulo + '</h4>';
            html += '</div>';
            html += '<p class="text-sm text-gray-600">' + a.descricao + '</p>';
            html += '</div>';
            if (a.valor) html += '<span class="text-sm font-bold text-gray-700 whitespace-nowrap">' + fBrl(a.valor) + '</span>';
            html += '</div></div>';
        });
        html += '</div>';

        el.innerHTML = html;
    }

    // ── Section map ──

    var sectionMap = {
        'resumo': { url: '/app/resumo-fiscal/resumo-executivo', render: renderResumo },
        'icms': { url: '/app/resumo-fiscal/apuracao-icms', render: renderIcms },
        'pis-cofins': { url: '/app/resumo-fiscal/apuracao-pis-cofins', render: renderPisCofins },
        'retencoes': { url: '/app/resumo-fiscal/retencoes', render: renderRetencoes },
        'cruzamentos': { url: '/app/resumo-fiscal/cruzamentos', render: renderCruzamentos },
        'alertas': { url: '/app/resumo-fiscal/alertas', render: renderAlertas }
    };

    // ── IntersectionObserver lazy loading ──

    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                var id = entry.target.id.replace('secao-', '');
                var cfg = sectionMap[id];
                if (cfg) loadSection(id, cfg.url, cfg.render);
            }
        });
    }, { rootMargin: '200px' });

    Object.keys(sectionMap).forEach(function(id) {
        var el = document.getElementById('secao-' + id);
        if (el) observer.observe(el);
    });

    // ── Sticky nav scroll + highlight ──

    document.querySelectorAll('.rf-nav-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var targetId = link.getAttribute('href').slice(1);
            var target = document.getElementById(targetId);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                // Highlight active nav
                document.querySelectorAll('.rf-nav-link').forEach(function(l) {
                    l.className = l.className.replace('bg-blue-100 text-blue-700', 'text-gray-500 hover:bg-gray-200');
                });
                link.className = link.className.replace('text-gray-500 hover:bg-gray-200', 'bg-blue-100 text-blue-700');
            }
        });
    });

    // ── Collapsible sections ──

    document.querySelectorAll('.rf-section-header[data-toggle]').forEach(function(header) {
        header.addEventListener('click', function() {
            var sectionId = header.getAttribute('data-toggle');
            var content = document.querySelector('.rf-section-content[data-section="' + sectionId + '"]');
            var chevron = header.querySelector('.rf-chevron');
            if (content) content.classList.toggle('collapsed');
            if (chevron) chevron.classList.toggle('rotated');
        });
    });

    // ── Filter button ──

    document.getElementById('rf-btn-filtrar')?.addEventListener('click', function() {
        loadedSections = {};
        document.getElementById('rf-empty-state')?.classList.add('hidden');
        document.getElementById('rf-alertas-badge')?.classList.add('hidden');

        // Reset skeletons
        Object.keys(sectionMap).forEach(function(id) {
            var el = document.getElementById('secao-' + id);
            if (el) {
                observer.unobserve(el);
                observer.observe(el);
            }
        });

        // Reload resumo immediately (always visible)
        loadSection('resumo', sectionMap.resumo.url, sectionMap.resumo.render);
    });

    // ── Cleanup for SPA ──

    function cleanup() {
        observer.disconnect();
        loadedSections = {};
    }

    window._cleanupFunctions = window._cleanupFunctions || {};
    window._cleanupFunctions.resumoFiscal = cleanup;

    // ── Initial load ──

    loadSection('resumo', sectionMap.resumo.url, sectionMap.resumo.render);

})();
</script>
