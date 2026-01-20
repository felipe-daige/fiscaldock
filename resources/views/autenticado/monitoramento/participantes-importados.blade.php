{{-- Monitoramento - Lista de Participantes Importados --}}
<div class="min-h-screen bg-gray-50" id="monitoramento-participantes-importados-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Participantes Importados</h1>
                    <p class="mt-1 text-sm text-gray-600">Gerencie os CNPJs importados de arquivos SPED.</p>
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
        <form id="form-filtros" method="GET" action="/app/monitoramento/participantes" class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-6">
            <div class="flex flex-col lg:flex-row lg:items-end gap-4">
                {{-- Filtro por importacao --}}
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Importacao</label>
                    <select name="importacao" id="filtro-importacao" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todas as importacoes</option>
                        @foreach($importacoes ?? [] as $imp)
                            <option value="{{ $imp->id }}" {{ ($filtros['importacao'] ?? '') == $imp->id ? 'selected' : '' }}>
                                {{ $imp->filename ?? 'Importacao #' . $imp->id }} - {{ $imp->created_at->format('d/m/Y H:i') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filtro por cliente --}}
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                    <select name="cliente" id="filtro-cliente" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos os clientes</option>
                        @foreach($clientes ?? [] as $cli)
                            <option value="{{ $cli->id }}" {{ ($filtros['cliente'] ?? '') == $cli->id ? 'selected' : '' }}>
                                {{ $cli->razao_social }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filtro por origem --}}
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Origem</label>
                    <select name="origem" id="filtro-origem" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todas as origens</option>
                        @foreach($origens ?? [] as $ori)
                            <option value="{{ $ori }}" {{ ($filtros['origem'] ?? '') == $ori ? 'selected' : '' }}>
                                {{ str_replace('_', ' ', $ori) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Busca --}}
                <div class="flex-1 min-w-[250px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                    <div class="relative">
                        <input
                            type="text"
                            name="busca"
                            id="busca-participantes"
                            placeholder="CNPJ ou Razao Social..."
                            value="{{ $filtros['busca'] ?? '' }}"
                            class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>

                {{-- Botoes --}}
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700">
                        Filtrar
                    </button>
                    <a href="/app/monitoramento/participantes" class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50" data-link>
                        Limpar
                    </a>
                </div>
            </div>
        </form>

        {{-- Acoes em lote (aparece quando ha selecao) --}}
        <div id="acoes-lote" class="hidden bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-600 text-white text-sm font-bold" id="count-selecionados">0</span>
                    <span class="text-sm font-medium text-blue-900">participante(s) selecionado(s)</span>
                </div>
                <div class="flex gap-2">
                    <button type="button" id="btn-monitorar-selecionados" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700" disabled title="Em breve">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Monitorar (em breve)
                    </button>
                    <button type="button" id="btn-limpar-selecao" class="px-4 py-2 rounded-lg border border-blue-300 bg-white text-blue-700 text-sm font-semibold shadow-sm transition hover:bg-blue-50">
                        Limpar selecao
                    </button>
                </div>
            </div>
        </div>

        {{-- Lista de Participantes --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <input type="checkbox" id="select-all" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">CNPJ</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Razao Social</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Situacao</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Cliente</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Origem</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Data</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="participantes-tbody">
                        @forelse($participantes ?? [] as $part)
                            <tr class="hover:bg-gray-50 transition-colors" data-participante-id="{{ $part->id }}">
                                <td class="px-4 py-4">
                                    <input type="checkbox" class="checkbox-participante w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" value="{{ $part->id }}">
                                </td>
                                <td class="px-4 py-4 text-sm font-mono text-gray-900 whitespace-nowrap">
                                    {{ $part->cnpj_formatado }}
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-900 max-w-xs truncate" title="{{ $part->razao_social }}">
                                    {{ $part->razao_social ?? '-' }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    @if($part->situacao_cadastral === 'ATIVA')
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-green-100 text-green-700">
                                            Ativa
                                        </span>
                                    @elseif($part->situacao_cadastral)
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-amber-100 text-amber-700">
                                            {{ $part->situacao_cadastral }}
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600 whitespace-nowrap">
                                    {{ $part->cliente->razao_social ?? '-' }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    @php
                                        $origemLabel = match($part->origem_tipo) {
                                            'SPED_EFD_FISCAL' => ['label' => 'SPED Fiscal', 'class' => 'bg-indigo-100 text-indigo-700'],
                                            'SPED_EFD_CONTRIB' => ['label' => 'SPED Contrib', 'class' => 'bg-purple-100 text-purple-700'],
                                            'NFE' => ['label' => 'NF-e', 'class' => 'bg-blue-100 text-blue-700'],
                                            'NFSE' => ['label' => 'NFS-e', 'class' => 'bg-cyan-100 text-cyan-700'],
                                            'MANUAL' => ['label' => 'Manual', 'class' => 'bg-gray-100 text-gray-700'],
                                            default => ['label' => $part->origem_tipo, 'class' => 'bg-gray-100 text-gray-700'],
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium {{ $origemLabel['class'] }}">
                                        {{ $origemLabel['label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-500 whitespace-nowrap">
                                    {{ $part->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-4 text-right whitespace-nowrap">
                                    <a
                                        href="/app/monitoramento/participante/{{ $part->id }}"
                                        class="inline-flex items-center p-2 rounded-lg text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                        title="Ver detalhes"
                                        data-link
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Nenhum participante encontrado</h3>
                                        <p class="text-sm text-gray-600 mb-4">
                                            @if(($filtros['importacao'] ?? null) || ($filtros['cliente'] ?? null) || ($filtros['origem'] ?? null) || ($filtros['busca'] ?? null))
                                                Nenhum participante corresponde aos filtros aplicados.
                                            @else
                                                Importe participantes de um arquivo SPED para comecar.
                                            @endif
                                        </p>
                                        <a
                                            href="/app/monitoramento/sped"
                                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700"
                                            data-link
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                            </svg>
                                            Importar SPED
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if(isset($participantes) && $participantes->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $participantes->links() }}
                </div>
            @endif
        </div>

        {{-- Resumo --}}
        <div class="mt-6 grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <p class="text-sm text-gray-500">Total de participantes</p>
                <p class="text-2xl font-bold text-gray-900">{{ $participantes->total() ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <p class="text-sm text-gray-500">Nesta pagina</p>
                <p class="text-2xl font-bold text-gray-900">{{ $participantes->count() ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <p class="text-sm text-gray-500">Total de importacoes</p>
                <p class="text-2xl font-bold text-gray-900">{{ count($importacoes ?? []) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <p class="text-sm text-gray-500">Seus creditos</p>
                <p class="text-2xl font-bold text-blue-600">{{ $credits ?? 0 }}</p>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    function initMonitoramentoParticipantesImportados() {
        const container = document.getElementById('monitoramento-participantes-importados-container');
        if (!container) return;

        if (container.dataset.initialized === '1') return;
        container.dataset.initialized = '1';

        console.log('[Monitoramento Participantes Importados] Inicializando...');

        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.checkbox-participante');
        const acoesLote = document.getElementById('acoes-lote');
        const countSelecionados = document.getElementById('count-selecionados');
        const btnLimparSelecao = document.getElementById('btn-limpar-selecao');
        const btnMonitorar = document.getElementById('btn-monitorar-selecionados');

        // Funcao para atualizar contagem e visibilidade das acoes em lote
        function atualizarAcoesLote() {
            const selecionados = document.querySelectorAll('.checkbox-participante:checked');
            const count = selecionados.length;

            if (countSelecionados) {
                countSelecionados.textContent = count;
            }

            if (acoesLote) {
                if (count > 0) {
                    acoesLote.classList.remove('hidden');
                } else {
                    acoesLote.classList.add('hidden');
                }
            }

            // Atualizar estado do checkbox "selecionar todos"
            if (selectAll) {
                selectAll.checked = count === checkboxes.length && count > 0;
                selectAll.indeterminate = count > 0 && count < checkboxes.length;
            }
        }

        // Event listener para checkbox "selecionar todos"
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(function(cb) {
                    cb.checked = selectAll.checked;
                });
                atualizarAcoesLote();
            });
        }

        // Event listeners para checkboxes individuais
        checkboxes.forEach(function(cb) {
            cb.addEventListener('change', atualizarAcoesLote);
        });

        // Botao limpar selecao
        if (btnLimparSelecao) {
            btnLimparSelecao.addEventListener('click', function() {
                checkboxes.forEach(function(cb) {
                    cb.checked = false;
                });
                if (selectAll) selectAll.checked = false;
                atualizarAcoesLote();
            });
        }

        // Botao monitorar (preparado para implementacao futura)
        if (btnMonitorar) {
            btnMonitorar.addEventListener('click', function() {
                const selecionados = Array.from(document.querySelectorAll('.checkbox-participante:checked')).map(cb => cb.value);
                console.log('[Monitoramento Participantes] Participantes selecionados para monitoramento:', selecionados);
                // TODO: Implementar modal de selecao de plano
                alert('Funcionalidade de monitoramento em lote sera implementada em breve!');
            });
        }

        // Submit do formulario via SPA (se usar data-link)
        const formFiltros = document.getElementById('form-filtros');
        if (formFiltros) {
            formFiltros.addEventListener('submit', function(e) {
                // Deixar o form submeter normalmente se SPA router nao estiver ativo
                // Ou podemos forcar reload da pagina
            });
        }

        console.log('[Monitoramento Participantes Importados] Inicializacao concluida');
    }

    // Expor globalmente para SPA
    window.initMonitoramentoParticipantesImportados = initMonitoramentoParticipantesImportados;

    // Auto-inicializar
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMonitoramentoParticipantesImportados, { once: true });
    } else {
        initMonitoramentoParticipantesImportados();
    }
})();
</script>
