{{-- Monitoramento - Historico de Consultas --}}
<div class="min-h-screen bg-gray-50" id="monitoramento-historico-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Historico de Consultas</h1>
                    <p class="mt-1 text-sm text-gray-600">Visualize todas as consultas realizadas.</p>
                </div>
                <a
                    href="/app/monitoramento"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50"
                    data-link
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Voltar
                </a>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                {{-- Filtro por tipo --}}
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700">Tipo:</label>
                    <select id="filtro-tipo" class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos</option>
                        <option value="avulso">Avulso</option>
                        <option value="assinatura">Assinatura</option>
                    </select>
                </div>

                {{-- Filtro por status --}}
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700">Status:</label>
                    <select id="filtro-status" class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos</option>
                        <option value="sucesso">Sucesso</option>
                        <option value="pendente">Pendente</option>
                        <option value="processando">Processando</option>
                        <option value="erro">Erro</option>
                    </select>
                </div>

                {{-- Filtro por plano --}}
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700">Plano:</label>
                    <select id="filtro-plano" class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos</option>
                        <option value="basico">Basico</option>
                        <option value="cadastral">Cadastral+</option>
                        <option value="fiscal_federal">Fiscal Federal</option>
                        <option value="fiscal_completo">Fiscal Completo</option>
                        <option value="due_diligence">Due Diligence</option>
                    </select>
                </div>

                {{-- Busca --}}
                <div class="flex-1">
                    <div class="relative">
                        <input
                            type="text"
                            id="busca-historico"
                            placeholder="Buscar por CNPJ..."
                            class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lista de Consultas --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">CNPJ</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Razao Social</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Plano</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Creditos</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="historico-tbody">
                        @forelse($consultas ?? [] as $consulta)
                            <tr class="hover:bg-gray-50 transition-colors" data-consulta-id="{{ $consulta->id }}">
                                <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                    {{ $consulta->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-sm font-mono text-gray-900 whitespace-nowrap">
                                    {{ preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $consulta->participante->cnpj ?? '') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                                    {{ $consulta->participante->razao_social ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">
                                    {{ $consulta->plano->nome ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($consulta->tipo === 'avulso')
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-700">
                                            Avulso
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-purple-100 text-purple-700">
                                            Assinatura
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($consulta->status === 'sucesso')
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-green-100 text-green-700">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Sucesso
                                        </span>
                                    @elseif($consulta->status === 'pendente')
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-amber-100 text-amber-700">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Pendente
                                        </span>
                                    @elseif($consulta->status === 'processando')
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-700">
                                            <svg class="w-3 h-3 mr-1 animate-spin" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                            </svg>
                                            Processando
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-red-100 text-red-700">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Erro
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">
                                    {{ $consulta->creditos_cobrados }} cred.
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <button
                                        type="button"
                                        class="btn-ver-resultado inline-flex items-center p-2 rounded-lg text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                        data-consulta-id="{{ $consulta->id }}"
                                        title="Ver resultado"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                        </svg>
                                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Nenhuma consulta realizada</h3>
                                        <p class="text-sm text-gray-600 mb-4">Suas consultas aparecerao aqui apos serem realizadas.</p>
                                        <a
                                            href="/app/monitoramento/avulso"
                                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700"
                                            data-link
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                            </svg>
                                            Fazer Consulta Avulsa
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if(isset($consultas) && $consultas->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $consultas->links() }}
                </div>
            @endif
        </div>

        {{-- Resumo do periodo --}}
        <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <p class="text-sm text-gray-500">Total de consultas</p>
                <p class="text-2xl font-bold text-gray-900">{{ $totalConsultas ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <p class="text-sm text-gray-500">Creditos utilizados</p>
                <p class="text-2xl font-bold text-gray-900">{{ $totalCreditos ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <p class="text-sm text-gray-500">Taxa de sucesso</p>
                <p class="text-2xl font-bold text-green-600">{{ $taxaSucesso ?? 0 }}%</p>
            </div>
        </div>
    </div>
</div>

{{-- Modal Ver Resultado --}}
<div id="modal-ver-resultado" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Resultado da Consulta</h3>
                <button type="button" class="modal-close text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div class="p-6" id="modal-resultado-content">
            {{-- Conteudo sera preenchido via JavaScript --}}
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end">
            <button type="button" class="modal-close px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50">
                Fechar
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    function initMonitoramentoHistorico() {
        const container = document.getElementById('monitoramento-historico-container');
        if (!container) return;

        if (container.dataset.initialized === '1') return;
        container.dataset.initialized = '1';

        console.log('[Monitoramento Historico] Inicializando...');

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const modalVerResultado = document.getElementById('modal-ver-resultado');
        const modalResultadoContent = document.getElementById('modal-resultado-content');
        const buscaInput = document.getElementById('busca-historico');
        const filtroTipo = document.getElementById('filtro-tipo');
        const filtroStatus = document.getElementById('filtro-status');
        const filtroPlano = document.getElementById('filtro-plano');

        // Funcao para filtrar tabela
        function filtrarTabela() {
            const busca = buscaInput ? buscaInput.value.toLowerCase().trim() : '';
            const tipo = filtroTipo ? filtroTipo.value : '';
            const status = filtroStatus ? filtroStatus.value : '';
            const plano = filtroPlano ? filtroPlano.value : '';

            const linhas = document.querySelectorAll('#historico-tbody tr[data-consulta-id]');

            linhas.forEach(function(linha) {
                const cnpj = linha.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const razaoSocial = linha.querySelector('td:nth-child(3)').textContent.toLowerCase();
                const tipoCell = linha.querySelector('td:nth-child(5)').textContent.toLowerCase();
                const statusCell = linha.querySelector('td:nth-child(6)').textContent.toLowerCase();
                const planoCell = linha.querySelector('td:nth-child(4)').textContent.toLowerCase();

                let mostrar = true;

                // Filtro de busca
                if (busca && !cnpj.includes(busca) && !razaoSocial.includes(busca)) {
                    mostrar = false;
                }

                // Filtro de tipo
                if (tipo && !tipoCell.includes(tipo)) {
                    mostrar = false;
                }

                // Filtro de status
                if (status && !statusCell.includes(status)) {
                    mostrar = false;
                }

                // Filtro de plano
                if (plano) {
                    const planoNomes = {
                        'basico': 'basico',
                        'cadastral': 'cadastral',
                        'fiscal_federal': 'fiscal federal',
                        'fiscal_completo': 'fiscal completo',
                        'due_diligence': 'due diligence',
                    };
                    if (!planoCell.includes(planoNomes[plano] || plano)) {
                        mostrar = false;
                    }
                }

                linha.style.display = mostrar ? '' : 'none';
            });
        }

        // Event listeners para filtros
        [buscaInput, filtroTipo, filtroStatus, filtroPlano].forEach(function(el) {
            if (el) {
                el.addEventListener('input', filtrarTabela);
                el.addEventListener('change', filtrarTabela);
            }
        });

        // Botoes ver resultado
        document.querySelectorAll('.btn-ver-resultado').forEach(function(btn) {
            btn.addEventListener('click', async function() {
                const consultaId = this.dataset.consultaId;

                modalResultadoContent.innerHTML = '<div class="flex items-center justify-center py-8"><svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>';
                modalVerResultado.classList.remove('hidden');
                document.body.style.overflow = 'hidden';

                try {
                    const response = await fetch('/app/monitoramento/consulta/' + consultaId, {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });

                    if (!response.ok) {
                        throw new Error('Erro ao carregar resultado');
                    }

                    const data = await response.json();
                    renderizarResultado(data);
                } catch (err) {
                    console.error('[Monitoramento Historico] Erro:', err);
                    modalResultadoContent.innerHTML = '<div class="text-center py-8 text-red-600">Erro ao carregar resultado. Tente novamente.</div>';
                }
            });
        });

        // Funcao para renderizar resultado no modal
        function renderizarResultado(data) {
            if (!data || !data.resultado) {
                modalResultadoContent.innerHTML = '<div class="text-center py-8 text-gray-500">Resultado nao disponivel.</div>';
                return;
            }

            const r = data.resultado;
            const cnpjFormatado = r.cnpj ? r.cnpj.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5') : '-';

            let html = '<div class="space-y-4">';

            // Header
            html += '<div class="border-b border-gray-200 pb-4">';
            html += '<h4 class="text-lg font-semibold text-gray-900">' + (r.razao_social || 'Razao Social nao informada') + '</h4>';
            html += '<p class="text-sm text-gray-600 font-mono">' + cnpjFormatado + '</p>';
            html += '</div>';

            // Informacoes basicas
            html += '<div class="grid grid-cols-2 gap-4">';
            html += '<div><p class="text-xs text-gray-500">Situacao Cadastral</p><p class="text-sm font-semibold text-gray-900">' + (r.situacao_cadastral || '-') + '</p></div>';
            html += '<div><p class="text-xs text-gray-500">Regime Tributario</p><p class="text-sm font-semibold text-gray-900">' + (r.regime_tributario || '-') + '</p></div>';
            html += '</div>';

            // Detalhes adicionais (se houver)
            if (r.detalhes && Object.keys(r.detalhes).length > 0) {
                html += '<div class="border-t border-gray-200 pt-4">';
                html += '<h5 class="text-sm font-semibold text-gray-900 mb-3">Detalhes da Consulta</h5>';
                html += '<div class="grid grid-cols-2 gap-4">';

                if (r.detalhes.cnd_federal) {
                    const cndClass = r.detalhes.cnd_federal.status === 'NEGATIVA' ? 'text-green-600' : 'text-red-600';
                    html += '<div class="bg-gray-50 rounded-lg p-3">';
                    html += '<p class="text-xs text-gray-500">CND Federal</p>';
                    html += '<p class="text-sm font-semibold ' + cndClass + '">' + r.detalhes.cnd_federal.status + '</p>';
                    if (r.detalhes.cnd_federal.validade) {
                        html += '<p class="text-xs text-gray-500 mt-1">Validade: ' + r.detalhes.cnd_federal.validade + '</p>';
                    }
                    html += '</div>';
                }

                if (r.detalhes.fgts) {
                    const fgtsClass = r.detalhes.fgts.status === 'REGULAR' ? 'text-green-600' : 'text-red-600';
                    html += '<div class="bg-gray-50 rounded-lg p-3">';
                    html += '<p class="text-xs text-gray-500">FGTS</p>';
                    html += '<p class="text-sm font-semibold ' + fgtsClass + '">' + r.detalhes.fgts.status + '</p>';
                    if (r.detalhes.fgts.validade) {
                        html += '<p class="text-xs text-gray-500 mt-1">Validade: ' + r.detalhes.fgts.validade + '</p>';
                    }
                    html += '</div>';
                }

                if (r.detalhes.cndt) {
                    const cndtClass = r.detalhes.cndt.status === 'NEGATIVA' ? 'text-green-600' : 'text-red-600';
                    html += '<div class="bg-gray-50 rounded-lg p-3">';
                    html += '<p class="text-xs text-gray-500">CNDT (Trabalhista)</p>';
                    html += '<p class="text-sm font-semibold ' + cndtClass + '">' + r.detalhes.cndt.status + '</p>';
                    html += '</div>';
                }

                if (r.detalhes.protestos !== undefined) {
                    const protestosClass = r.detalhes.protestos === 0 ? 'text-green-600' : 'text-red-600';
                    html += '<div class="bg-gray-50 rounded-lg p-3">';
                    html += '<p class="text-xs text-gray-500">Protestos</p>';
                    html += '<p class="text-sm font-semibold ' + protestosClass + '">' + r.detalhes.protestos + ' registro(s)</p>';
                    html += '</div>';
                }

                html += '</div>';
                html += '</div>';
            }

            // Metadados
            html += '<div class="border-t border-gray-200 pt-4 text-xs text-gray-500">';
            html += '<p>Consulta realizada em: ' + (data.executado_em || data.created_at || '-') + '</p>';
            html += '<p>Creditos utilizados: ' + (data.creditos_cobrados || 0) + '</p>';
            html += '</div>';

            html += '</div>';

            modalResultadoContent.innerHTML = html;
        }

        // Fechar modais
        document.querySelectorAll('.modal-close').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const modal = btn.closest('[id^="modal-"]');
                if (modal) {
                    modal.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            });
        });

        // Fechar modal clicando fora
        if (modalVerResultado) {
            modalVerResultado.addEventListener('click', function(e) {
                if (e.target === modalVerResultado) {
                    modalVerResultado.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            });
        }

        console.log('[Monitoramento Historico] Inicializacao concluida');
    }

    // Expor globalmente para SPA
    window.initMonitoramentoHistorico = initMonitoramentoHistorico;

    // Auto-inicializar
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMonitoramentoHistorico, { once: true });
    } else {
        initMonitoramentoHistorico();
    }
})();
</script>
