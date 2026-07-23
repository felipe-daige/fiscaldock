@php
    $buscaFiltro = $filtros['busca'] ?? '';
    $statusFiltro = $filtros['status'] ?? '';
    $planoFiltro = $filtros['plano_id'] ?? '';
    $dataInicio = $filtros['data_inicio'] ?? '';
    $dataFim = $filtros['data_fim'] ?? '';
@endphp

<div class="bg-gray-100 min-h-screen" id="consultas-historico-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="flex items-start justify-between gap-4 mb-4 sm:mb-6">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Histórico de Consultas</h1>
                <p class="text-xs text-gray-500 mt-1">Consolidado dos lotes executados, valores consumidos e relatórios disponíveis para exportação.</p>
            </div>
            <a
                href="/app/consulta/nova"
                class="inline-flex items-center gap-2 px-4 py-2 rounded border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
                data-link
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nova Consulta
            </a>
        </div>

        @if(($retencaoMeses ?? null) !== null)
            <div class="bg-white rounded border border-gray-300 border-l-4 mb-4 p-3 text-xs text-gray-600" style="border-left-color: #b45309">
                Seu plano exibe o histórico dos últimos <strong>{{ $retencaoMeses }} meses</strong>. Consultas mais antigas continuam guardadas e voltam a aparecer ao fazer upgrade. <a href="/app/plano" data-link class="font-semibold underline text-gray-700">Ver planos</a>
            </div>
        @endif

        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-4">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Resumo Operacional</span>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-6 divide-x divide-y lg:divide-y-0 divide-gray-200">
                <div class="p-4">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Consultas</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($kpis['total_lotes'] ?? 0, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">lotes filtrados</p>
                </div>
                <div class="p-4">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Participantes</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($kpis['total_participantes'] ?? 0, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">processados</p>
                </div>
                <div class="p-4">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Custo</p>
                    <p class="text-lg font-bold text-gray-900">{{ \App\Support\Dinheiro::brl((($kpis['total_creditos'] ?? 0))) }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">consumidos</p>
                </div>
                <div class="p-4">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Finalizadas</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($kpis['finalizados'] ?? 0, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">com relatório</p>
                </div>
                <div class="p-4">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Processando</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($kpis['processando'] ?? 0, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">em andamento</p>
                </div>
                <div class="p-4">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Erro</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($kpis['erro'] ?? 0, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">lotes com falha</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-6">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 hidden sm:block">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Filtros</span>
                    @if(($filtrosAtivos ?? 0) > 0)
                        <span class="text-[10px] font-semibold text-gray-400 bg-gray-200 px-2 py-0.5 rounded">{{ $filtrosAtivos }} ativos</span>
                    @endif
                </div>
            </div>
            <form method="GET" action="/app/consulta/historico" data-mobile-filters>
                <div class="px-4 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-3">
                        <input
                            type="text"
                            name="busca"
                            value="{{ $buscaFiltro }}"
                            placeholder="Buscar lote, produto ou erro..."
                            class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-gray-700 focus:ring-1 focus:ring-gray-400 focus:border-gray-400 xl:col-span-2"
                        >
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-gray-700 focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                            <option value="">Todos os status</option>
                            <option value="pendente" {{ $statusFiltro === 'pendente' ? 'selected' : '' }}>Pendente</option>
                            <option value="processando" {{ $statusFiltro === 'processando' ? 'selected' : '' }}>Processando</option>
                            <option value="finalizado" {{ in_array($statusFiltro, ['finalizado', 'concluido'], true) ? 'selected' : '' }}>Finalizado</option>
                            <option value="erro" {{ $statusFiltro === 'erro' ? 'selected' : '' }}>Erro</option>
                        </select>
                        <select name="plano_id" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-gray-700 focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                            <option value="">Todos os produtos</option>
                            @foreach(($planosFiltro ?? collect()) as $plano)
                                <option value="{{ $plano->id }}" {{ (string) $planoFiltro === (string) $plano->id ? 'selected' : '' }}>{{ $plano->nome }}</option>
                            @endforeach
                        </select>
                        <input
                            type="date"
                            name="data_inicio"
                            value="{{ $dataInicio }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-gray-700 focus:ring-1 focus:ring-gray-400 focus:border-gray-400"
                        >
                        <input
                            type="date"
                            name="data_fim"
                            value="{{ $dataFim }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-gray-700 focus:ring-1 focus:ring-gray-400 focus:border-gray-400"
                        >
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-4 pt-3 border-t border-gray-200">
                        <div class="text-xs text-gray-500">
                            {{ $lotes->total() }} lote{{ $lotes->total() === 1 ? '' : 's' }} encontrado{{ $lotes->total() === 1 ? '' : 's' }}
                        </div>
                        <div class="mobile-filter-actions flex gap-2 w-full sm:w-auto">
                            <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-gray-800 text-white hover:bg-gray-700 rounded text-sm font-medium">
                                Filtrar
                            </button>
                            <a href="/app/consulta/historico" data-link class="flex-1 sm:flex-none px-4 py-2 bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 rounded text-sm font-medium text-center">
                                Limpar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        @if($lotes->isNotEmpty())
            <div id="lista-consultas" data-spa-list class="bg-white rounded border border-gray-300 overflow-hidden">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Consultas Recentes</span>
                        <span class="text-[10px] font-semibold text-gray-400 bg-gray-200 px-2 py-0.5 rounded">{{ $lotes->total() }} no histórico</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full tabela-cards">
                        <thead>
                            <tr class="border-b border-gray-300">
                                <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Consulta realizada</th>
                                <th class="px-3 py-2.5 text-center text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Produto</th>
                                <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Resultado</th>
                                <th class="px-3 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Custo</th>
                                <th class="px-3 py-2.5 text-center text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Status</th>
                                <th class="w-12 px-3 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">
                                    <span class="sr-only">Ações</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($lotes as $lote)
                                @php
                                    $preview = $previewsLotes->get($lote->id);
                                    $statusMeta = $preview['status'];
                                    $produtoPreview = $preview['produto'];
                                    $alvoPreview = $preview['alvo'];
                                    $resultadoPreview = $preview['resultado'];
                                    $erroCritico = $lote->publicErrorUi([
                                        'url' => '/app/consulta/historico',
                                    ]);
                                @endphp
                                <tr class="hover:bg-gray-50/50 transition-colors cursor-pointer"
                                    onclick="if(event.target.closest('a,button,[data-acoes-menu]'))return; var u='/app/consulta/lote/{{ $lote->id }}'; window.navigateTo?window.navigateTo(u):window.location.href=u;">
                                    <td class="px-3 py-3.5">
                                        <div class="flex min-w-[280px] items-start gap-3">
                                            <div class="w-12 shrink-0 border-r border-gray-200 pr-3 text-center" title="{{ $lote->created_at->format('d/m/Y H:i') }}">
                                                <p class="text-[10px] font-bold uppercase text-gray-500">{{ $preview['data_label'] }}</p>
                                                <p class="mt-0.5 text-xs font-semibold text-gray-900">{{ $lote->created_at->format('H:i') }}</p>
                                            </div>
                                            <div class="min-w-0 max-w-[390px]">
                                                <p class="truncate text-sm font-semibold text-gray-900" title="{{ $alvoPreview['nome'] }}">{{ $alvoPreview['nome'] }}</p>
                                                <div class="mt-0.5 flex flex-wrap items-center gap-x-1.5 gap-y-0.5 text-[11px] text-gray-500">
                                                    @if($alvoPreview['documento'])
                                                        <span class="font-mono">{{ $alvoPreview['documento'] }}</span>
                                                    @endif
                                                    @if($alvoPreview['outros_total'] > 0)
                                                        <span aria-hidden="true">•</span>
                                                        <span class="font-semibold text-gray-700">+ {{ number_format($alvoPreview['outros_total'], 0, ',', '.') }} CNPJ{{ $alvoPreview['outros_total'] === 1 ? '' : 's' }}</span>
                                                    @endif
                                                </div>
                                                @if($alvoPreview['outros_nomes'] !== '')
                                                    <p class="mt-1 truncate text-[10px] text-gray-400" title="{{ $alvoPreview['outros_nomes'] }}">Também: {{ $alvoPreview['outros_nomes'] }}</p>
                                                @endif
                                                <p class="mt-1 text-[10px] uppercase text-gray-400">Lote #{{ $lote->id }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-label="Produto" class="px-3 py-3 text-center text-sm text-gray-700">
                                        <div class="text-center">
                                            <span class="inline-block whitespace-nowrap rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $produtoPreview['hex'] }}">{{ $produtoPreview['nome'] }}</span>
                                            <p class="mt-1.5 text-[11px] text-gray-500">{{ $produtoPreview['origem'] }}</p>
                                        </div>
                                        @if($lote->eh_monitoramento ?? false)
                                            <span class="sr-only">Monitoramento</span>
                                        @endif
                                    </td>
                                    <td data-label="Resultado" class="px-3 py-3 text-sm text-gray-700">
                                        <div class="text-left">
                                            <p class="whitespace-nowrap text-xs font-semibold text-gray-900">
                                                <span class="mr-1 inline-block h-2 w-2 rounded-full" style="background-color: {{ $resultadoPreview['hex'] }}"></span>
                                                {{ $resultadoPreview['titulo'] }}
                                            </p>
                                            @if($resultadoPreview['detalhe'])
                                                <p class="mt-1 text-[11px] text-gray-500">{{ $resultadoPreview['detalhe'] }}</p>
                                            @endif

                                            @if($lote->isErro())
                                                <a href="{{ $erroCritico['action_url'] ?? config('support.whatsapp_url') }}"
                                                   target="{{ $erroCritico['action_target'] ?? '_blank' }}"
                                                   rel="{{ $erroCritico['action_rel'] ?? 'noopener noreferrer' }}"
                                                   class="mt-1 inline-flex text-[11px] text-gray-600 hover:text-gray-900 hover:underline">
                                                    {{ $erroCritico['action_label'] ?? config('support.contact_label') }}
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                    <td data-label="Custo" class="px-3 py-3 text-right text-sm font-semibold text-gray-900 font-mono">
                                        <span>{{ \App\Support\Dinheiro::brl(($lote->creditos_cobrados)) }}</span>
                                    </td>
                                    <td data-label="Status" class="px-3 py-3 text-center">
                                        <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $statusMeta['hex'] }}">{{ $statusMeta['label'] }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-right">
                                        <x-acoes-menu trigger="kebab">
                                            <x-acoes-item href="/app/consulta/lote/{{ $lote->id }}" data-link>Abrir</x-acoes-item>
                                            @if($lote->isFinalizado() && $resultadoPreview['sucessos'] > 0)
                                                <x-acoes-item href="/app/consulta/lote/{{ $lote->id }}/baixar?formato=csv">CSV</x-acoes-item>
                                                <x-acoes-item href="/app/consulta/lote/{{ $lote->id }}/baixar?formato=xlsx">Excel (XLSX)</x-acoes-item>
                                                <x-acoes-item href="/app/consulta/lote/{{ $lote->id }}/baixar?formato=pdf">PDF</x-acoes-item>
                                            @endif
                                        </x-acoes-menu>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($lotes->hasPages())
                    <div class="border-t border-gray-300 px-4 py-3">
                        {{ $lotes->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        @elseif($relatoriosLegados->isNotEmpty())
            <div class="bg-white rounded border border-gray-300 overflow-hidden">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Relatórios Legados</span>
                </div>
                <div class="px-4 py-6 text-sm text-gray-700">
                    Há apenas relatórios do sistema antigo disponíveis no momento.
                </div>
            </div>
        @else
            <div class="bg-white rounded border border-gray-300 overflow-hidden">
                <div class="px-6 py-10 text-center">
                    <svg class="w-14 h-14 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg font-bold text-gray-900 uppercase tracking-wide">Nenhuma consulta registrada</h3>
                    <p class="text-sm text-gray-600 mt-2 mb-6">Execute sua primeira consulta em lote para começar a consolidar o histórico de relatórios e valores consumidos.</p>
                    <a href="/app/consulta/nova" data-link class="inline-flex items-center gap-2 px-4 py-2 bg-gray-800 text-white hover:bg-gray-700 rounded text-sm font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Iniciar Consulta
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
