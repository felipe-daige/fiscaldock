{{-- Card modular de Notas Fiscais (EFD + XML unificadas) --}}
{{-- Variáveis: $notas (paginator), $totalNotas (int), $ajaxUrl (string), $contexto ('participante'|'cliente'), $entityId (int) --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm" id="notas-fiscais-card">
    {{-- Header --}}
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Notas Fiscais</h2>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500">{{ $totalNotas }} nota(s)</span>
                @if($totalNotas > 0)
                <a href="/app/notas-fiscais?{{ $contexto }}_id={{ $entityId }}" data-link
                   class="text-sm text-blue-600 hover:underline font-medium">
                    Ver todas
                </a>
                @endif
            </div>
        </div>
    </div>

    @if($totalNotas === 0)
        {{-- Estado vazio --}}
        <div class="px-4 sm:px-6 py-8 text-center">
            <svg class="mx-auto w-10 h-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="text-sm text-gray-500">Nenhuma nota fiscal encontrada</p>
        </div>
    @else
        {{-- Tabela desktop --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Origem</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">N&ordm; / S&eacute;rie</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Modelo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Emiss&atilde;o</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                        @if($contexto !== 'participante')
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Participante</th>
                        @endif
                        @if($contexto !== 'cliente')
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                        @endif
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Valor</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase w-12"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($notas as $n)
                    @php
                        $origemClass = $n['origem'] === 'efd' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700';
                        $origemLabel = strtoupper($n['origem']);
                        $tipoClass = $n['tipo_operacao'] === 'entrada' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
                        $tipoLabel = $n['tipo_operacao'] === 'entrada' ? 'Entrada' : 'Saída';
                        $dataFormatada = $n['data_emissao'] ? \Carbon\Carbon::parse($n['data_emissao'])->format('d/m/Y') : '—';
                        $numero = $n['numero'] ?? '—';
                        $serie = $n['serie'] ? ' / ' . $n['serie'] : '';
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors nf-card-row" data-origem="{{ $n['origem'] }}" data-id="{{ $n['id'] }}">
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $origemClass }}">{{ $origemLabel }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm font-mono text-gray-900 whitespace-nowrap">{{ $numero }}{{ $serie }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">{{ $n['modelo_label'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $dataFormatada }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $tipoClass }}">{{ $tipoLabel }}</span>
                        </td>
                        @if($contexto !== 'participante')
                        <td class="px-4 py-3 text-sm text-gray-700 max-w-xs truncate">
                            @if($n['participante_id'])
                                <a href="/app/participante/{{ $n['participante_id'] }}" data-link class="hover:text-blue-600 hover:underline">
                                    <div class="truncate">{{ $n['participante_nome'] ?? '—' }}</div>
                                </a>
                            @else
                                <div class="truncate">{{ $n['participante_nome'] ?? '—' }}</div>
                            @endif
                            @if($n['participante_doc'])
                            <div class="text-xs font-mono text-gray-400">{{ $n['participante_doc'] }}</div>
                            @endif
                        </td>
                        @endif
                        @if($contexto !== 'cliente')
                        <td class="px-4 py-3 text-sm text-gray-700 max-w-[10rem]">
                            @if($n['cliente_id'])
                                <a href="/app/cliente/{{ $n['cliente_id'] }}" data-link class="hover:text-blue-600 hover:underline truncate block">{{ $n['cliente_nome'] ?? '—' }}</a>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        @endif
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right whitespace-nowrap">
                            R$ {{ number_format($n['valor_total'], 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button type="button" class="nf-card-expand-btn text-gray-400 hover:text-blue-600 transition-colors p-1" data-origem="{{ $n['origem'] }}" data-id="{{ $n['id'] }}" title="Ver detalhes">
                                <svg class="w-5 h-5 nf-card-expand-icon transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    <tr class="nf-card-detail-row hidden" data-detail-for="{{ $n['origem'] }}-{{ $n['id'] }}">
                        <td colspan="{{ $contexto === 'participante' || $contexto === 'cliente' ? 8 : 9 }}" class="px-0 py-0">
                            <div class="nf-card-detail-content bg-gray-50 border-t border-gray-100"></div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Cards mobile --}}
        <div class="md:hidden divide-y divide-gray-200">
            @foreach($notas as $n)
            @php
                $origemClass = $n['origem'] === 'efd' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700';
                $origemLabel = strtoupper($n['origem']);
                $tipoClass = $n['tipo_operacao'] === 'entrada' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
                $tipoLabel = $n['tipo_operacao'] === 'entrada' ? 'Entrada' : 'Saída';
                $dataFormatada = $n['data_emissao'] ? \Carbon\Carbon::parse($n['data_emissao'])->format('d/m/Y') : '—';
                $numero = $n['numero'] ?? '—';
                $serie = $n['serie'] ? ' / ' . $n['serie'] : '';
            @endphp
            <div class="px-4 py-4 nf-card-mobile" data-origem="{{ $n['origem'] }}" data-id="{{ $n['id'] }}">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $origemClass }}">{{ $origemLabel }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">{{ $n['modelo_label'] }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $tipoClass }}">{{ $tipoLabel }}</span>
                    </div>
                    <button type="button" class="nf-card-expand-btn text-gray-400 hover:text-blue-600 p-2 -mr-2 min-w-[40px] min-h-[40px] flex items-center justify-center" data-origem="{{ $n['origem'] }}" data-id="{{ $n['id'] }}">
                        <svg class="w-5 h-5 nf-card-expand-icon transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>
                <div class="flex items-baseline justify-between gap-2">
                    <span class="text-sm font-mono font-medium text-gray-900">{{ $numero }}{{ $serie }}</span>
                    <span class="text-sm font-medium text-gray-900">R$ {{ number_format($n['valor_total'], 2, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between mt-1">
                    <span class="text-xs text-gray-500">{{ $dataFormatada }}</span>
                    @if($contexto !== 'participante' && ($n['participante_nome'] ?? null))
                        <span class="text-xs text-gray-500 truncate max-w-[50%]">{{ $n['participante_nome'] }}</span>
                    @elseif($contexto !== 'cliente' && ($n['cliente_nome'] ?? null))
                        <span class="text-xs text-gray-500 truncate max-w-[50%]">{{ $n['cliente_nome'] }}</span>
                    @endif
                </div>
                <div class="nf-card-detail-mobile hidden mt-3" data-detail-for="{{ $n['origem'] }}-{{ $n['id'] }}">
                    <div class="nf-card-detail-content bg-gray-50 rounded-lg border border-gray-100 p-2"></div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Paginação --}}
        @if($notas->hasPages())
        <div class="px-4 sm:px-6 py-3 border-t border-gray-200 flex items-center justify-between">
            <p class="text-xs text-gray-500">
                Mostrando {{ $notas->firstItem() }}&ndash;{{ $notas->lastItem() }} de {{ $totalNotas }}
            </p>
            <div class="flex items-center gap-1">
                @if($notas->currentPage() > 1)
                <button type="button" data-nf-page="{{ $notas->currentPage() - 1 }}"
                    class="inline-flex items-center px-3 py-1.5 rounded-lg border border-gray-300 bg-white text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Anterior
                </button>
                @endif
                <span class="px-3 py-1.5 text-sm text-gray-600">{{ $notas->currentPage() }} / {{ $notas->lastPage() }}</span>
                @if($notas->hasMorePages())
                <button type="button" data-nf-page="{{ $notas->currentPage() + 1 }}"
                    class="inline-flex items-center px-3 py-1.5 rounded-lg border border-gray-300 bg-white text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                    Próxima
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                @endif
            </div>
        </div>
        @endif
    @endif
</div>

@once
<script>
(function() {
    var ajaxUrl = @json($ajaxUrl);
    var detailCache = {};

    document.addEventListener('click', function(e) {
        var container = document.getElementById('notas-fiscais-card');
        if (!container || !container.contains(e.target)) return;

        // Paginação AJAX
        var pageBtn = e.target.closest('[data-nf-page]');
        if (pageBtn) {
            e.preventDefault();
            e.stopPropagation();
            var page = pageBtn.dataset.nfPage;
            fetch(ajaxUrl + '?page=' + page, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.text(); })
            .then(function(html) {
                var tmp = document.createElement('div');
                tmp.innerHTML = html;
                var newCard = tmp.querySelector('#notas-fiscais-card');
                if (newCard) {
                    container.innerHTML = newCard.innerHTML;
                }
            });
            return;
        }

        // Expansão inline de detalhes
        var expandBtn = e.target.closest('.nf-card-expand-btn');
        if (expandBtn) {
            e.preventDefault();
            e.stopPropagation();
            var origem = expandBtn.dataset.origem;
            var id = expandBtn.dataset.id;
            var key = origem + '-' + id;

            // Desktop: toggle detail row
            var detailRow = container.querySelector('tr.nf-card-detail-row[data-detail-for="' + key + '"]');
            // Mobile: toggle detail div
            var detailMobile = container.querySelector('.nf-card-detail-mobile[data-detail-for="' + key + '"]');
            var target = detailRow || detailMobile;
            if (!target) return;

            var icon = expandBtn.querySelector('.nf-card-expand-icon');
            var isOpen = !target.classList.contains('hidden');

            if (isOpen) {
                target.classList.add('hidden');
                if (icon) icon.style.transform = '';
                return;
            }

            target.classList.remove('hidden');
            if (icon) icon.style.transform = 'rotate(180deg)';

            var contentEl = target.querySelector('.nf-card-detail-content');
            if (!contentEl) return;

            // Cache: não refazer chamada se já tem conteúdo
            if (detailCache[key]) {
                contentEl.innerHTML = detailCache[key];
                return;
            }

            contentEl.innerHTML = '<div class="p-4 text-center text-sm text-gray-500">Carregando...</div>';

            fetch('/app/notas-fiscais/' + origem + '/' + id, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
            })
            .then(function(r) { return r.text(); })
            .then(function(html) {
                detailCache[key] = html;
                contentEl.innerHTML = html;
            })
            .catch(function() {
                contentEl.innerHTML = '<div class="p-4 text-center text-sm text-red-500">Erro ao carregar detalhes</div>';
            });
        }
    });
})();
</script>
@endonce
