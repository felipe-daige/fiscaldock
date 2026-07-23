@php
    $filtros = $filtros ?? [];
    $busca = $filtros['busca'] ?? '';
    $tipoDocumentoFiltro = $filtros['tipo_documento'] ?? '';
    $statusFiltro = $filtros['status'] ?? '';
@endphp

<div class="min-h-screen bg-gray-100" id="clearance-historico-buscas-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between mb-4 sm:mb-6">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Histórico de Buscas Avulsas</h1>
                <p class="text-xs text-gray-500 mt-1">Chaves consultadas em Buscar Nota, separadas das verificações da base.</p>
            </div>
            <a href="{{ route('app.clearance.buscar') }}" data-link class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded text-sm font-medium self-start">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Buscar Nota
            </a>
        </div>

        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-4">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Filtros</span>
                @if(($filtrosAtivos ?? 0) > 0)
                    <span class="text-[10px] font-semibold text-gray-400 bg-gray-200 px-2 py-0.5 rounded">{{ $filtrosAtivos }} ativos</span>
                @endif
            </div>
            <form method="GET" action="{{ route('app.clearance.buscar.historico') }}">
                <div class="px-4 py-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <input type="text" name="busca" value="{{ $busca }}" placeholder="Chave, número, cliente ou parte..." class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-gray-700 focus:ring-1 focus:ring-gray-400 sm:col-span-1">
                        <select name="tipo_documento" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-gray-700 focus:ring-1 focus:ring-gray-400">
                            <option value="">Todos os documentos</option>
                            <option value="nfe" {{ $tipoDocumentoFiltro === 'nfe' ? 'selected' : '' }}>NF-e</option>
                            <option value="nfce" {{ $tipoDocumentoFiltro === 'nfce' ? 'selected' : '' }}>NFC-e</option>
                            <option value="cte" {{ $tipoDocumentoFiltro === 'cte' ? 'selected' : '' }}>CT-e</option>
                        </select>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-gray-700 focus:ring-1 focus:ring-gray-400">
                            <option value="">Todos os status</option>
                            @foreach($statusOptions as $statusOption)
                                <option value="{{ $statusOption }}" {{ $statusFiltro === $statusOption ? 'selected' : '' }}>{{ str_replace('_', ' ', $statusOption) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-4 pt-3 border-t border-gray-200">
                        <p class="text-xs text-gray-500">{{ $consultas->total() }} consulta{{ $consultas->total() === 1 ? '' : 's' }} encontrada{{ $consultas->total() === 1 ? '' : 's' }}</p>
                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-gray-800 text-white hover:bg-gray-700 rounded text-sm font-medium">Filtrar</button>
                            <a href="{{ route('app.clearance.buscar.historico') }}" data-link class="flex-1 sm:flex-none px-4 py-2 bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 rounded text-sm font-medium text-center">Limpar</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="bg-white rounded border border-gray-300 overflow-hidden" data-history-view="clearance-avulsa">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Buscas Avulsas</span>
            </div>
            <div class="w-full min-w-0">
                <table class="tabela-cards historico-tabela">
                    <colgroup>
                        <col class="w-[24%]">
                        <col class="w-[24%]">
                        <col class="w-[17%]">
                        <col class="w-[12%]">
                        <col class="w-[12%]">
                        <col class="w-[11%]">
                    </colgroup>
                    <thead class="bg-gray-50">
                        <tr class="border-b border-gray-300">
                            <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Documento consultado</th>
                            <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Operação</th>
                            <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Cliente / Vínculos</th>
                            <th class="px-3 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Valor / Eventos</th>
                            <th class="px-3 py-2.5 text-center text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Situação SEFAZ</th>
                            <th class="px-3 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide"><span class="sr-only">Ações</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($consultas as $consulta)
                            @php
                                $situacao = strtoupper((string) ($consulta->status ?? 'SALVA'));
                                $statusHex = match($situacao) {
                                    'AUTORIZADA', 'NEGATIVA' => '#047857',
                                    'CANCELADA', 'DENEGADA', 'INUTILIZADA' => '#dc2626',
                                    'INDETERMINADO', 'NAO_ENCONTRADA' => '#b45309',
                                    default => '#6b7280',
                                };
                                $tipoDocumento = strtoupper((string) ($consulta->tipo_documento ?: 'NFE'));
                                $tipoDocumentoLabel = match($tipoDocumento) {
                                    'NFE' => 'NF-e',
                                    'NFCE' => 'NFC-e',
                                    'CTE' => 'CT-e',
                                    default => $tipoDocumento,
                                };
                                $situacaoLabel = match($situacao) {
                                    'NAO_ENCONTRADA' => 'Não encontrada',
                                    'INDETERMINADO' => 'Indeterminado',
                                    default => ucfirst(mb_strtolower(str_replace('_', ' ', $situacao))),
                                };
                                $parteDestino = $consulta->dest_nome ?: $consulta->tomador_nome;
                                $documentoDestino = $consulta->dest_cnpj ?: $consulta->tomador_cnpj;
                                $operacaoTitulo = collect([$consulta->emit_nome, $parteDestino])
                                    ->filter(fn ($parte) => trim((string) $parte) !== '')
                                    ->implode(' → ');
                                $operacaoTitulo = $operacaoTitulo !== '' ? $operacaoTitulo : 'Partes não informadas';
                                $emitDocumentoLabel = $consulta->emit_cnpj ? \App\Support\Cnpj::formatar((string) $consulta->emit_cnpj) : null;
                                $destDocumentoLabel = $documentoDestino ? \App\Support\Cnpj::formatar((string) $documentoDestino) : null;
                                $resultadoUrl = $consulta->consulta_lote_id
                                    ? route('app.clearance.buscar.resultado', [
                                        'consultaLoteId' => $consulta->consulta_lote_id,
                                        'tipo_documento' => strtolower($tipoDocumento),
                                        'chave_acesso' => $consulta->chave_acesso,
                                    ])
                                    : null;
                                $detalheId = 'historico-busca-detalhe-'.strtolower($tipoDocumento).'-'.$consulta->id;
                                $valorTotalLabel = is_numeric($consulta->valor_total)
                                    ? 'R$ '.number_format((float) $consulta->valor_total, 2, ',', '.')
                                    : '—';
                                $dataConsulta = ($consulta->consultado_em ?? $consulta->created_at)
                                    ? \Carbon\Carbon::parse($consulta->consultado_em ?? $consulta->created_at)
                                    : null;
                                $dataLabel = $dataConsulta?->isToday()
                                    ? 'Hoje'
                                    : ($dataConsulta?->isYesterday() ? 'Ontem' : $dataConsulta?->format('d/m'));
                                $partesVinculadas = collect($consulta->partes_identificadas ?? [])
                                    ->filter(fn ($parte) => ! empty($parte['cliente']) || ! empty($parte['participante']))
                                    ->count();
                                $eventosTimeline = collect(data_get($consulta, 'timeline_preview.itens', []))
                                    ->reject(fn ($item) => in_array($item['label'] ?? '', ['Emissão', 'Consultada no FiscalDock'], true))
                                    ->count()
                                    + (int) data_get($consulta, 'timeline_preview.eventos_adicionais', 0);
                            @endphp
                            <tr class="cursor-pointer hover:bg-gray-50"
                                data-history-result-url="{{ $resultadoUrl ?? '' }}"
                                data-history-fallback-details="{{ $detalheId }}">
                                <td class="px-3 py-3.5">
                                    <div class="flex w-full min-w-0 items-start gap-3">
                                        <div class="w-12 shrink-0 border-r border-gray-200 pr-3 text-center" title="{{ $consulta->momento_consulta }}">
                                            <p class="text-[10px] font-bold uppercase text-gray-500">{{ $dataLabel ?? '—' }}</p>
                                            <p class="mt-0.5 text-xs font-semibold text-gray-900">{{ $dataConsulta?->format('H:i') ?? '—' }}</p>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-1.5">
                                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: #374151">{{ $tipoDocumentoLabel }}</span>
                                                <p class="text-sm font-semibold text-gray-900">{{ $consulta->numero ? 'Nº '.$consulta->numero : 'Documento fiscal' }}</p>
                                            </div>
                                            <p class="mt-1 text-[11px] text-gray-500">Série {{ $consulta->serie ?: '—' }} · emissão {{ $consulta->data_emissao ?: '—' }}</p>
                                            <p class="mt-1 truncate font-mono text-[10px] text-gray-400" title="{{ $consulta->chave_acesso }}">Chave …{{ substr((string) $consulta->chave_acesso, -12) }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-700" data-label="Operação">
                                    <p class="max-w-[340px] truncate font-semibold text-gray-900" title="{{ $operacaoTitulo }}">{{ $operacaoTitulo }}</p>
                                    @if($emitDocumentoLabel || $destDocumentoLabel)
                                        <p class="mt-1 text-[10px] font-mono text-gray-500">{{ $emitDocumentoLabel ?: '—' }} → {{ $destDocumentoLabel ?: '—' }}</p>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-700" data-label="Cliente / Vínculos">
                                    <p class="max-w-[220px] truncate text-gray-900" title="{{ $consulta->cliente_nome ?: 'Sem cliente associado' }}">{{ $consulta->cliente_nome ?: 'Sem cliente associado' }}</p>
                                    <p class="mt-1 text-[10px] text-gray-500">{{ $partesVinculadas }} parte{{ $partesVinculadas === 1 ? '' : 's' }} vinculada{{ $partesVinculadas === 1 ? '' : 's' }}</p>
                                </td>
                                <td class="px-3 py-3 text-right" data-label="Valor / Eventos">
                                    <p class="text-sm font-mono font-semibold text-gray-900">{{ $valorTotalLabel }}</p>
                                    <p class="mt-1 text-[10px] text-gray-500">{{ $eventosTimeline }} evento{{ $eventosTimeline === 1 ? '' : 's' }} SEFAZ</p>
                                </td>
                                <td class="px-3 py-3 text-center" data-label="Situação SEFAZ"><span class="whitespace-nowrap px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $statusHex }}">{{ $situacaoLabel }}</span></td>
                                <td class="px-3 py-3 text-right whitespace-nowrap">
                                    <button type="button"
                                            class="historico-busca-details-toggle inline-flex h-8 w-8 items-center justify-center rounded text-gray-500 hover:bg-gray-100 hover:text-gray-900"
                                            data-history-details-toggle="{{ $detalheId }}"
                                            aria-controls="{{ $detalheId }}"
                                            aria-expanded="false"
                                            aria-label="Ver detalhes"
                                            title="Ver detalhes">
                                        <span class="sr-only" data-history-details-label>Ver detalhes</span>
                                        <svg class="w-3.5 h-3.5 transition-transform" data-history-details-chevron viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"></path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr id="{{ $detalheId }}" class="hidden historico-busca-detail-row" data-history-details="{{ $consulta->id }}">
                                <td colspan="6" class="px-4 py-4" style="background-color: #f9fafb">
                                    <div class="rounded border border-gray-200 bg-white overflow-hidden">
                                        <div class="px-4 py-2 border-b border-gray-200 flex flex-wrap items-center justify-between gap-2" style="background-color: #f9fafb">
                                            <div>
                                                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Resultado resumido</p>
                                                <p class="text-xs text-gray-500 mt-0.5">{{ $tipoDocumentoLabel }}{{ $consulta->numero ? ' nº '.$consulta->numero : '' }} · consultada em {{ $consulta->momento_consulta }}</p>
                                            </div>
                                            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $statusHex }}">{{ $situacaoLabel }}</span>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-gray-200">
                                            <div class="p-3">
                                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Documento</p>
                                                <p class="text-sm font-semibold text-gray-900 mt-1">{{ $tipoDocumentoLabel }}{{ $consulta->numero ? ' nº '.$consulta->numero : '' }}</p>
                                                <p class="text-[11px] text-gray-500 mt-0.5">Série {{ $consulta->serie ?: '—' }} · emissão {{ $consulta->data_emissao ?: '—' }}</p>
                                            </div>
                                            <div class="p-3">
                                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Valor</p>
                                                <p class="text-sm font-mono font-semibold text-gray-900 mt-1">{{ $valorTotalLabel }}</p>
                                                <p class="text-[11px] text-gray-500 mt-0.5">Situação oficial: {{ $situacaoLabel }}</p>
                                            </div>
                                            <div class="p-3">
                                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Cliente associado</p>
                                                <p class="text-sm text-gray-900 mt-1">{{ $consulta->cliente_nome ?: 'Sem cliente associado' }}</p>
                                                <p class="text-[11px] text-gray-500 mt-0.5">Lote {{ $consulta->consulta_lote_id ? '#'.$consulta->consulta_lote_id : 'legado' }}</p>
                                            </div>
                                            <div class="p-3">
                                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Consulta</p>
                                                <p class="text-sm text-gray-900 mt-1">{{ $consulta->momento_consulta }}</p>
                                                <p class="text-[11px] text-gray-500 mt-0.5">Origem: busca avulsa</p>
                                            </div>
                                        </div>

                                        <div class="p-3 border-t border-gray-200" data-history-identifications>
                                            <div class="flex flex-wrap items-start justify-between gap-2">
                                                <div>
                                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Identificados no sistema</p>
                                                    <p class="text-[11px] text-gray-500 mt-0.5">Vínculos encontrados nos cadastros de Clientes e Participantes.</p>
                                                </div>
                                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide" style="background-color: #f3f4f6; color: #4b5563">
                                                    {{ count($consulta->partes_identificadas) }} parte{{ count($consulta->partes_identificadas) === 1 ? '' : 's' }}
                                                </span>
                                            </div>

                                            @if($consulta->partes_identificadas !== [])
                                                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-2 mt-3">
                                                    @foreach($consulta->partes_identificadas as $parteIdentificada)
                                                        <div class="rounded border border-gray-200 p-3">
                                                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">{{ $parteIdentificada['papel'] }}</p>
                                                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $parteIdentificada['nome'] }}</p>
                                                            <p class="text-[11px] font-mono text-gray-500 mt-0.5">{{ $parteIdentificada['documento'] }}</p>

                                                            <div class="mt-3 space-y-1.5">
                                                                @if($parteIdentificada['cliente'])
                                                                    <a href="{{ $parteIdentificada['cliente']['url'] }}" data-link class="flex items-center gap-2 group">
                                                                        <span class="shrink-0 px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide" style="background-color: #dcfce7; color: #166534">Cliente</span>
                                                                        <span class="text-xs text-gray-700 group-hover:text-gray-900 group-hover:underline truncate">{{ $parteIdentificada['cliente']['nome'] }}</span>
                                                                    </a>
                                                                @endif
                                                                @if($parteIdentificada['participante'])
                                                                    <a href="{{ $parteIdentificada['participante']['url'] }}" data-link class="flex items-center gap-2 group">
                                                                        <span class="shrink-0 px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide" style="background-color: #e0e7ff; color: #3730a3">Participante</span>
                                                                        <span class="text-xs text-gray-700 group-hover:text-gray-900 group-hover:underline truncate">{{ $parteIdentificada['participante']['nome'] }}</span>
                                                                    </a>
                                                                @endif
                                                                @if(!$parteIdentificada['cliente'] && !$parteIdentificada['participante'])
                                                                    <p class="text-[11px] text-gray-400">
                                                                        {{ $parteIdentificada['mascarado'] ? 'CNPJ mascarado não identificado com segurança.' : 'Ainda não cadastrado como Cliente ou Participante.' }}
                                                                    </p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-xs text-gray-400 mt-3">As partes não foram informadas no snapshot desta consulta.</p>
                                            @endif
                                        </div>

                                        <div class="p-3 border-t border-gray-200" data-history-timeline>
                                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Linha do tempo</p>
                                            <div class="mt-3">
                                                @foreach($consulta->timeline_preview['itens'] as $itemTimeline)
                                                    <div class="flex gap-3 {{ !$loop->last ? 'pb-3' : '' }}">
                                                        <div class="flex w-3 shrink-0 flex-col items-center">
                                                            <span class="mt-1 block h-2.5 w-2.5 rounded-full ring-2 ring-white" style="background-color: {{ $itemTimeline['hex'] }}"></span>
                                                            @if(!$loop->last)
                                                                <span class="mt-1 block w-px flex-1" style="background-color: #d1d5db"></span>
                                                            @endif
                                                        </div>
                                                        <div class="min-w-0 pb-0.5">
                                                            <p class="text-xs font-semibold text-gray-900">{{ $itemTimeline['label'] }}</p>
                                                            <p class="text-[11px] text-gray-500 mt-0.5">{{ $itemTimeline['data_label'] }}</p>
                                                            @if($itemTimeline['protocolo'])
                                                                <p class="text-[10px] font-mono text-gray-400 mt-0.5">Protocolo {{ $itemTimeline['protocolo'] }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            @if($consulta->timeline_preview['eventos_adicionais'] > 0)
                                                <p class="text-[11px] text-gray-500 mt-2">+ {{ $consulta->timeline_preview['eventos_adicionais'] }} evento{{ $consulta->timeline_preview['eventos_adicionais'] === 1 ? '' : 's' }} no resultado completo.</p>
                                            @endif
                                        </div>

                                        <div class="px-3 py-3 border-t border-gray-200">
                                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Chave de acesso</p>
                                            <p class="text-[11px] font-mono text-gray-900 mt-1 break-all">{{ $consulta->chave_acesso }}</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-10 text-center"><p class="text-sm font-semibold text-gray-900">Nenhuma busca avulsa encontrada</p><p class="text-xs text-gray-500 mt-1">Consulte uma chave ou ajuste os filtros.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($consultas->hasPages())
                <div class="border-t border-gray-200 px-4 py-3">{{ $consultas->links() }}</div>
            @endif
        </div>
    </div>
</div>

<script>
(function () {
    const root = document.getElementById('clearance-historico-buscas-container');
    if (!root || root.dataset.detailsInitialized === '1') return;

    root.dataset.detailsInitialized = '1';
    root.addEventListener('click', function (event) {
        const toggle = event.target.closest('[data-history-details-toggle]');
        if (toggle && root.contains(toggle)) {
            const detailRow = document.getElementById(toggle.dataset.historyDetailsToggle);
            if (!detailRow) return;

            const willOpen = detailRow.classList.contains('hidden');
            detailRow.classList.toggle('hidden', !willOpen);
            toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');

            const label = toggle.querySelector('[data-history-details-label]');
            if (label) label.textContent = willOpen ? 'Ocultar detalhes' : 'Ver detalhes';
            toggle.setAttribute('aria-label', willOpen ? 'Ocultar detalhes' : 'Ver detalhes');
            toggle.title = willOpen ? 'Ocultar detalhes' : 'Ver detalhes';

            const chevron = toggle.querySelector('[data-history-details-chevron]');
            if (chevron) chevron.classList.toggle('rotate-180', willOpen);
            return;
        }

        if (event.target.closest('a, button, input, label, select, [data-acoes-menu]')) return;

        const row = event.target.closest('[data-history-result-url]');
        if (!row || !root.contains(row)) return;

        const url = row.dataset.historyResultUrl;
        if (url) {
            window.navigateTo ? window.navigateTo(url) : window.location.href = url;
            return;
        }

        const fallbackToggle = row.querySelector('[data-history-details-toggle]');
        if (fallbackToggle) fallbackToggle.click();
    });
})();
</script>
