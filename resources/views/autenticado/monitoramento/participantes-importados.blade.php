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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Importação</label>
                    <select name="importacao" id="filtro-importacao" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todas as importações</option>
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
                            placeholder="CNPJ ou Razão Social..."
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

        {{-- Estatísticas --}}
        <div class="mb-6 grid grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <p class="text-sm text-gray-500">Total de participantes</p>
                <p class="text-2xl font-bold text-gray-900">{{ $participantes->total() ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <p class="text-sm text-gray-500">Nesta página</p>
                <p class="text-2xl font-bold text-gray-900">{{ $participantes->count() ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <p class="text-sm text-gray-500">Total de importações</p>
                <p class="text-2xl font-bold text-gray-900">{{ count($importacoes ?? []) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <p class="text-sm text-gray-500">Seus créditos</p>
                <p class="text-2xl font-bold text-green-600">{{ $credits ?? 0 }}</p>
            </div>
        </div>

        {{-- Acoes em lote (aparece quando ha selecao) --}}
        <div id="acoes-lote" class="hidden bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-600 text-white text-sm font-bold" id="count-selecionados">0</span>
                    <span class="text-sm font-medium text-blue-900">participante(s) selecionado(s)</span>
                </div>
                <div class="flex gap-2">
                    <button type="button" id="btn-monitorar-selecionados" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Consultar Selecionados
                    </button>
                    <button type="button" id="btn-limpar-selecao" class="px-4 py-2 rounded-lg border border-blue-300 bg-white text-blue-700 text-sm font-semibold shadow-sm transition hover:bg-blue-50">
                        Limpar seleção
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
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Razão Social</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Situação</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Cliente</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Origem</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Data</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="participantes-tbody">
                        @forelse($participantes ?? [] as $part)
                            <tr class="hover:bg-gray-50 transition-colors cursor-pointer" data-participante-id="{{ $part->id }}" data-href="/app/monitoramento/participante/{{ $part->id }}">
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
                                    <button type="button" class="acoes-btn p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
                                        data-id="{{ $part->id }}"
                                        data-nome="{{ $part->razao_social }}"
                                        data-cnpj="{{ $part->cnpj_formatado }}">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                        </svg>
                                    </button>
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
    </div>

    </div>
</div>

{{-- Modais (fora do container para overlay correto) --}}

{{-- Dropdown de acoes do participante (menu kebab) --}}
<div id="dropdown-acoes" class="hidden fixed z-[9999] bg-white rounded-xl shadow-lg ring-1 ring-gray-200 w-56 py-1">
    <div class="px-3 py-2 border-b border-gray-100">
        <p class="text-sm font-semibold text-gray-900 truncate" id="dropdown-acoes-nome"></p>
        <p class="text-xs text-gray-500 font-mono whitespace-nowrap tabular-nums" id="dropdown-acoes-cnpj"></p>
    </div>
    <a id="dropdown-acoes-ver" href="#"
       class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
       data-link>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
        </svg>
        Ver detalhes
    </a>
    <a id="dropdown-acoes-editar" href="#"
       class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
       data-link>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
        </svg>
        Editar
    </a>
    <button type="button" id="dropdown-acoes-excluir"
        class="flex items-center gap-2 w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
        </svg>
        Excluir
    </button>
</div>

{{-- Modal de confirmacao de exclusao --}}
<div id="modal-excluir-participante" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/50 transition-opacity" id="modal-excluir-overlay"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6 z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Excluir participante?</h3>
            </div>
            <div class="mb-4">
                <p class="text-sm text-gray-700 mb-2">
                    <span class="font-medium" id="modal-excluir-cnpj"></span> — <span id="modal-excluir-nome"></span>
                </p>
                <p class="text-sm text-gray-500">
                    Todo o historico associado sera removido (assinaturas, consultas, scores). As notas fiscais onde este participante aparece serao mantidas.
                </p>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" id="btn-cancelar-exclusao" class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="button" id="btn-confirmar-exclusao" class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold shadow-sm transition hover:bg-red-700">
                    Excluir
                </button>
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

        // Botao consultar selecionados
        if (btnMonitorar) {
            btnMonitorar.addEventListener('click', function() {
                const selecionados = Array.from(document.querySelectorAll('.checkbox-participante:checked')).map(cb => cb.value);
                if (selecionados.length === 0) {
                    alert('Selecione pelo menos um participante.');
                    return;
                }
                // Redirecionar para nova consulta com IDs pre-selecionados
                window.location.href = '/app/consultas/nova?participantes=' + selecionados.join(',');
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

        // === Clique na linha abre perfil do participante ===
        const tbody = document.getElementById('participantes-tbody');
        if (tbody) {
            tbody.addEventListener('click', function(e) {
                // Ignorar cliques em checkbox, botoes e links
                if (e.target.closest('input[type="checkbox"], button, a')) return;

                const row = e.target.closest('tr[data-href]');
                if (!row) return;

                // Navegar via SPA
                const href = row.dataset.href;
                if (href && window.navigateTo) {
                    window.navigateTo(href);
                } else if (href) {
                    window.location.href = href;
                }
            });
        }

        // === Exclusao de participante com modal ===
        const modal = document.getElementById('modal-excluir-participante');
        const modalOverlay = document.getElementById('modal-excluir-overlay');
        const modalCnpj = document.getElementById('modal-excluir-cnpj');
        const modalNome = document.getElementById('modal-excluir-nome');
        const btnCancelar = document.getElementById('btn-cancelar-exclusao');
        const btnConfirmar = document.getElementById('btn-confirmar-exclusao');
        let participanteIdParaExcluir = null;

        function abrirModalExclusao(id, cnpj, nome) {
            participanteIdParaExcluir = id;
            if (modalCnpj) modalCnpj.textContent = cnpj;
            if (modalNome) modalNome.textContent = nome || 'Sem razao social';
            if (modal) modal.classList.remove('hidden');
        }

        function fecharModalExclusao() {
            if (modal) modal.classList.add('hidden');
            participanteIdParaExcluir = null;
        }

        // === Dropdown de acoes (kebab menu) ===
        const dropdownAcoes = document.getElementById('dropdown-acoes');
        const dropdownAcoesNome = document.getElementById('dropdown-acoes-nome');
        const dropdownAcoesCnpj = document.getElementById('dropdown-acoes-cnpj');
        const dropdownAcoesVer = document.getElementById('dropdown-acoes-ver');
        const dropdownAcoesEditar = document.getElementById('dropdown-acoes-editar');
        const dropdownAcoesExcluir = document.getElementById('dropdown-acoes-excluir');
        let acaoParticipanteId = null;
        let dropdownBtnAtual = null;

        function posicionarDropdown(btnElement) {
            if (!dropdownAcoes || !btnElement) return;
            // Temporarily show to measure height
            dropdownAcoes.style.visibility = 'hidden';
            dropdownAcoes.classList.remove('hidden');
            const dropdownHeight = dropdownAcoes.offsetHeight;
            const dropdownWidth = dropdownAcoes.offsetWidth;
            dropdownAcoes.classList.add('hidden');
            dropdownAcoes.style.visibility = '';

            const rect = btnElement.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceAbove = rect.top;

            // Horizontal: align right edge of dropdown with right edge of button
            let left = rect.right - dropdownWidth;
            if (left < 8) left = 8;

            // Vertical: prefer below, fall back to above
            let top;
            if (spaceBelow >= dropdownHeight + 4) {
                top = rect.bottom + 4;
            } else if (spaceAbove >= dropdownHeight + 4) {
                top = rect.top - dropdownHeight - 4;
            } else {
                top = rect.bottom + 4;
            }

            dropdownAcoes.style.top = top + 'px';
            dropdownAcoes.style.left = left + 'px';
        }

        function abrirDropdownAcoes(btnElement, id, nome, cnpj) {
            // Toggle: if clicking the same button, close
            if (!dropdownAcoes.classList.contains('hidden') && dropdownBtnAtual === btnElement) {
                fecharDropdownAcoes();
                return;
            }
            acaoParticipanteId = id;
            dropdownBtnAtual = btnElement;
            if (dropdownAcoesNome) dropdownAcoesNome.textContent = nome || 'Sem razao social';
            if (dropdownAcoesCnpj) dropdownAcoesCnpj.textContent = cnpj || '';
            if (dropdownAcoesVer) dropdownAcoesVer.href = '/app/monitoramento/participante/' + id;
            if (dropdownAcoesEditar) dropdownAcoesEditar.href = '/app/monitoramento/participante/' + id + '/editar';
            posicionarDropdown(btnElement);
            dropdownAcoes.classList.remove('hidden');
        }

        function fecharDropdownAcoes() {
            if (dropdownAcoes) dropdownAcoes.classList.add('hidden');
            dropdownBtnAtual = null;
        }

        // Abrir dropdown ao clicar no kebab
        container.addEventListener('click', function(e) {
            const acaoBtn = e.target.closest('.acoes-btn');
            if (acaoBtn) {
                e.stopPropagation();
                abrirDropdownAcoes(acaoBtn, acaoBtn.dataset.id, acaoBtn.dataset.nome, acaoBtn.dataset.cnpj);
            }
        });

        // Close on click outside
        document.addEventListener('click', function(e) {
            if (dropdownAcoes && !dropdownAcoes.classList.contains('hidden') && !dropdownAcoes.contains(e.target)) {
                fecharDropdownAcoes();
            }
        });

        // Close on Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && dropdownAcoes && !dropdownAcoes.classList.contains('hidden')) {
                fecharDropdownAcoes();
            }
        });

        // Close on scroll (capture mode to catch scrolling in any container)
        window.addEventListener('scroll', function() {
            if (dropdownAcoes && !dropdownAcoes.classList.contains('hidden')) {
                fecharDropdownAcoes();
            }
        }, true);

        // "Ver detalhes" — fechar dropdown (SPA navega via data-link)
        if (dropdownAcoesVer) {
            dropdownAcoesVer.addEventListener('click', fecharDropdownAcoes);
        }

        // "Excluir" — fechar dropdown, abrir modal de exclusao
        if (dropdownAcoesExcluir) {
            dropdownAcoesExcluir.addEventListener('click', function() {
                const id = acaoParticipanteId;
                const cnpj = dropdownAcoesCnpj ? dropdownAcoesCnpj.textContent : '';
                const nome = dropdownAcoesNome ? dropdownAcoesNome.textContent : '';
                fecharDropdownAcoes();
                abrirModalExclusao(id, cnpj, nome);
            });
        }

        if (btnCancelar) btnCancelar.addEventListener('click', fecharModalExclusao);
        if (modalOverlay) modalOverlay.addEventListener('click', fecharModalExclusao);

        if (btnConfirmar) {
            btnConfirmar.addEventListener('click', async function() {
                if (!participanteIdParaExcluir) return;

                btnConfirmar.disabled = true;
                btnConfirmar.textContent = 'Excluindo...';

                try {
                    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                    const res = await fetch('/app/monitoramento/participante/' + participanteIdParaExcluir, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': tokenMeta ? tokenMeta.content : '',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    const data = await res.json();

                    if (!res.ok || !data.success) {
                        throw new Error(data.error || 'Erro ao excluir participante');
                    }

                    window.showToast && window.showToast(data.message || 'Participante excluido com sucesso!', 'success');

                    // Remover linha da tabela
                    const row = document.querySelector('tr[data-participante-id="' + participanteIdParaExcluir + '"]');
                    if (row) row.remove();

                    fecharModalExclusao();
                    atualizarAcoesLote();

                } catch (err) {
                    console.error('[Monitoramento Participantes] Erro:', err);
                    window.showToast && window.showToast(err.message || 'Erro ao excluir participante', 'error');
                    fecharModalExclusao();
                } finally {
                    btnConfirmar.disabled = false;
                    btnConfirmar.textContent = 'Excluir';
                }
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
