@php
    $jsVersion = @filemtime(public_path('js/consulta-lote-detalhe.js')) ?: time();
    $statusLote = $statusLote ?? \App\Models\ConsultaLote::normalizeStatus($lote->status ?? 'pendente');
    $statusMeta = $statusMeta ?? ['label' => 'Pendente', 'hex' => '#9ca3af'];
    $etapasJson = e(json_encode($etapas ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    $resultados = $resultados ?? new \Illuminate\Pagination\LengthAwarePaginator(collect(), 0, 20, 1);
    $temResultadosNoLote = $temResultadosNoLote ?? false;
    $erroCriticoLote = $lote->publicErrorUi([
        'url' => request()->getPathInfo(),
    ]);
@endphp

<div
    class="min-h-screen bg-gray-100"
    id="consulta-lote-detalhe-root"
    data-status="{{ $statusLote }}"
    data-tab-id="{{ $lote->tab_id ?? '' }}"
    data-stream-url="{{ url('/app/consulta/progresso/stream') }}"
    data-status-url="{{ route('app.consulta.lote.status', ['id' => $lote->id]) }}"
    data-resultados-url="{{ route('app.consulta.lote.resultados', ['id' => $lote->id]) }}"
    data-detail-url="{{ request()->fullUrlWithoutQuery(['page_resultados', 'per_page_resultados']) }}"
    data-await-result="{{ $aguardaPersistencia ? '1' : '0' }}"
    data-etapas="{{ $etapasJson }}"
    {{-- Cronômetro conta do início do processamento ATUAL: na reconsulta o lote foi virado p/
         processando (updated_at), não da consulta original (created_at) — senão marcaria horas. --}}
    data-iniciado-em="{{ optional($statusLote === 'processando' ? $lote->updated_at : $lote->created_at)->timestamp }}"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="mb-4 sm:mb-6 flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <a href="/app/consulta/historico" data-link class="inline-flex items-center gap-2 text-xs text-gray-600 hover:text-gray-900 hover:underline mb-3">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Voltar para histórico
                </a>
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Detalhe da Consulta</h1>
                <p class="text-xs text-gray-500 mt-1">O lote abre nesta página e acompanha o processamento até a disponibilidade do resultado final.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if($statusLote === 'finalizado')
                    {{-- Export — padrão canônico x-export-menu (mesmo das telas de BI/Clearance) --}}
                    <x-export-menu id="modal-export-lote" titulo="Exportar consulta"
                                   descricao="Resultado deste lote de consultas — mesmo conteúdo da tela."
                                   overlay="download-overlay-lote">
                        @if($lote->hasResultados())
                            <x-export-grupo label="Documento" />
                            <x-export-option format="pdf"
                                path="/app/consulta/lote/{{ $lote->id }}/baixar" query="formato=pdf"
                                modalId="modal-export-lote"
                                overlay="download-overlay-lote"
                                descricao="Relatório do lote com o parecer por CNPJ — pronto para enviar." />
                        @endif
                        <x-export-grupo label="Planilhas" />
                        @if($lote->hasResultados())
                            <x-export-option format="xlsx"
                                path="/app/consulta/lote/{{ $lote->id }}/baixar" query="formato=xlsx"
                                modalId="modal-export-lote"
                                overlay="download-overlay-lote"
                                descricao="Relatório completo do lote em planilha." />
                        @endif
                        <x-export-option format="csv"
                            path="/app/consulta/lote/{{ $lote->id }}/baixar" query="formato=csv"
                            modalId="modal-export-lote"
                            overlay="download-overlay-lote"
                            descricao="Separador ; com BOM UTF-8 (compatível com Excel)." />
                    </x-export-menu>
                @endif
                <a href="/app/consulta/nova" data-link class="px-3 py-1.5 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded text-xs font-medium">Nova consulta</a>
                <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $statusMeta['hex'] }}">
                    {{ $statusMeta['label'] }}
                </span>
            </div>
        </div>

        @if($statusLote === 'finalizado')
            {{-- Overlay de download (spinner) — compartilhado pelas opções do export --}}
            <x-download-overlay id="download-overlay-lote" texto="Gerando relatório…" />
        @endif

        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-4">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Resumo Operacional</span>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-6 divide-x divide-y lg:divide-y-0 divide-gray-200">
                <div class="px-4 py-3 min-h-[96px] flex flex-col">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Lote</p>
                    <div class="flex-1 flex items-center">
                        <p class="text-lg font-bold text-gray-900">#{{ $lote->id }}</p>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">{{ $lote->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="px-4 py-3 min-h-[96px] flex flex-col">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Produto</p>
                    <div class="flex-1 flex items-center">
                        <p class="text-sm font-bold text-gray-900">{{ $lote->plano?->nome ?? 'Sem plano' }}</p>
                    </div>
                    @php $custoPlano = (float) ($lote->plano?->custo_creditos ?? 0); @endphp
                    <span class="mt-1 inline-flex items-center self-start gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide" style="background-color: #eef2ff; color: #4338ca">
                        {{ $custoPlano > 0 ? \App\Support\Dinheiro::brl(app(\App\Services\PricingCatalogService::class)->creditsToCurrency($custoPlano)).'/consulta' : 'Grátis' }}
                    </span>
                </div>
                <div class="px-4 py-3 min-h-[96px] flex flex-col">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Participantes</p>
                    <div class="flex-1 flex items-center">
                        <p class="text-lg font-bold text-gray-900">{{ number_format((int) ($lote->total_participantes ?? 0), 0, ',', '.') }}</p>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">incluídos no lote</p>
                </div>
                <div class="px-4 py-3 min-h-[96px] flex flex-col">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Custo</p>
                    <div class="flex-1 flex items-center">
                        <p class="text-lg font-bold text-gray-900">{{ \App\Support\Dinheiro::brl(app(\App\Services\PricingCatalogService::class)->creditsToCurrency((float) ($lote->creditos_cobrados ?? 0))) }}</p>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">total cobrado</p>
                </div>
                <div class="px-4 py-3 min-h-[96px] flex flex-col">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Cliente</p>
                    <div class="flex-1 flex items-center">
                        <p class="text-sm font-bold text-gray-900">{{ $lote->cliente?->razao_social ?? 'Não informado' }}</p>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">vínculo do lote</p>
                </div>
                <div class="px-4 py-3 min-h-[96px] flex flex-col">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Processado em</p>
                    <div class="flex-1 flex items-center">
                        <p class="text-sm font-bold text-gray-900">{{ $lote->processado_em?->format('d/m/Y H:i') ?? 'Em andamento' }}</p>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">última atualização persistida</p>
                </div>
            </div>
        </div>

        @php
            // A reconsulta reusa o MESMO card de progresso do processamento inicial (padrão do
            // projeto — ver ProgressoAutomacao). Fica oculto até o JS revelar no submit do retry,
            // assim o usuário vê a barra "de verdade" sem perder a análise/resultados já visíveis.
            $temRetryPossivel = $statusLote === 'finalizado' && ! $aguardaPersistencia && ! empty($retryPendentes['alvos'] ?? []);
        @endphp
        @if(in_array($statusLote, ['pendente', 'processando'], true) || $temRetryPossivel)
            <div id="consulta-lote-progresso-card" class="bg-white rounded border border-gray-300 overflow-hidden mb-4{{ $temRetryPossivel ? ' hidden' : '' }}">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between">
                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Andamento da Consulta</span>
                    <span id="consulta-lote-percent" class="text-[10px] text-gray-500 font-mono">0%</span>
                </div>
                <div class="p-4">
                    <div class="flex items-center justify-between gap-3 mb-2">
                        <p id="consulta-lote-mensagem" class="text-sm text-gray-700">Iniciando consulta...</p>
                    </div>
                    <div class="w-full h-1.5 rounded-full overflow-hidden" style="background-color: #e5e7eb">
                        <div id="consulta-lote-bar" class="h-full" style="background-color: #1f2937; width: 6%; transition: width 350ms ease-out"></div>
                    </div>
                    @include('autenticado.partials.progresso-tempo', [
                        'prefixo' => 'consulta-lote',
                        'dica' => 'consultamos as fontes oficiais em tempo real — alguns órgãos podem levar alguns segundos.',
                    ])
                    <p id="consulta-lote-etapa" class="text-xs text-gray-600 mt-3 hidden"></p>
                    <div id="consulta-lote-steps" class="mt-3 flex flex-wrap gap-2">
                        @foreach(($etapas ?? []) as $etapa)
                            <div class="etapa-item inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-gray-100 text-xs font-medium text-gray-400" data-etapa="{{ $etapa['numero'] ?? '' }}">
                                <span class="etapa-icon flex items-center justify-center w-3.5 h-3.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                    </svg>
                                </span>
                                <span>{{ $etapa['label'] ?? ('Etapa '.($etapa['numero'] ?? '?')) }}</span>
                            </div>
                        @endforeach
                    </div>
                    {{-- Checklist por fonte: cresce conforme cada verificação conclui (✓), com a atual
                         em spinner. Mais granular que o strip de etapas (que agrupa fontes). Montada
                         dos campos fonte_nome/indice/total do stream de progresso. --}}
                    <div id="consulta-lote-fontes" class="mt-3 space-y-1 hidden"></div>
                </div>
            </div>

            @unless($temRetryPossivel)
                <div class="bg-white rounded border border-gray-300 p-6 text-center">
                    <p class="text-sm text-gray-500">O resultado consolidado aparecerá aqui assim que o processamento terminar.</p>
                    <p class="text-xs text-gray-400 mt-1">Você pode fechar esta aba e voltar depois pelo histórico sem perder o lote.</p>
                </div>
            @endunless
        @endif

        @if($statusLote === 'erro')
            @include('autenticado.partials.system-critical-error', ['errorUi' => $erroCriticoLote])
        @endif

        @if($statusLote === 'finalizado' && $aguardaPersistencia)
            <div class="bg-white rounded border border-gray-300 p-4 border-l-4 border-l-blue-500">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Aguardando resultado final</p>
                <p class="mt-2 text-sm text-gray-700">A consulta já foi concluída pelo provedor, mas o resultado consolidado ainda não ficou disponível. Esta página continuará verificando automaticamente.</p>
            </div>
        @endif

        @if($statusLote === 'finalizado' && ! $aguardaPersistencia)
            {{-- Envolve TODO o resultado anterior (resumo, análise, tabela de participantes) num
                 único wrapper: durante a reconsulta o JS esconde isto por completo e mostra só o
                 card de progresso acima — a tela de carregamento fica só o carregamento mesmo. --}}
            <div id="consulta-lote-resultado-anterior">
            <div class="bg-white rounded border border-gray-300 overflow-hidden mb-4">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Resultado Consolidado</span>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-5 divide-x divide-y lg:divide-y-0 divide-gray-200">
                    <div class="px-4 py-3">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Total do lote</p>
                        <p class="text-lg font-bold text-gray-900">{{ number_format((int) ($resumo['total_lote'] ?? 0), 0, ',', '.') }}</p>
                    </div>
                    <div class="px-4 py-3">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Resultados</p>
                        <p class="text-lg font-bold text-gray-900">{{ number_format((int) ($resumo['total_resultados'] ?? 0), 0, ',', '.') }}</p>
                    </div>
                    <div class="px-4 py-3">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Sucesso</p>
                        <p class="text-lg font-bold text-gray-900">{{ number_format((int) ($resumo['sucesso'] ?? 0), 0, ',', '.') }}</p>
                    </div>
                    <div class="px-4 py-3">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Falha</p>
                        <p class="text-lg font-bold text-gray-900">{{ number_format((int) ($resumo['erro'] ?? 0), 0, ',', '.') }}</p>
                    </div>
                    <div class="px-4 py-3">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Com Sinalização</p>
                        <p class="text-lg font-bold text-gray-900">{{ number_format((int) ($resumo['com_parecer'] ?? 0), 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            @if(!empty($analise))
                @php $cn = $analise['cnpjs'] ?? []; @endphp
                @php $retryElegiveis = $retryPendentes['elegiveis'] ?? []; @endphp
                @php $retryAlvos = $retryPendentes['alvos'] ?? []; @endphp
                <div class="bg-white rounded border border-gray-300 overflow-hidden mb-4">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between gap-3 flex-wrap">
                        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Análise da Consulta</span>
                        {{-- Reconsultar (retry ilimitado) e Comunicar com o suporte COEXISTEM: o
                             suporte aparece quando uma fonte já foi reconsultada ≥1× e ainda falha. --}}
                        <div class="flex items-center gap-2 flex-wrap">
                            @if(!empty($retryAlvos))
                                <button type="button"
                                    id="btn-abrir-retry-{{ $lote->id }}"
                                    onclick="document.getElementById('modal-retry-{{ $lote->id }}').classList.remove('hidden')"
                                    class="text-[11px] font-semibold px-3 py-1.5 rounded text-white"
                                    style="background-color: #b45309">
                                    ↻ Reconsultar {{ count($retryAlvos) }} CNPJ(s) com falha — plano {{ $retryPendentes['desconto_pct_efetivo'] ?? 50 }}% off
                                </button>
                            @endif
                            @if(!empty($retryPendentes['suporte']))
                                <a href="{{ route('app.suporte.index', ['contexto' => $retryPendentes['suporte']['contexto'], 'mensagem' => $retryPendentes['suporte']['mensagem'], 'url' => route('app.consulta.lote.show', ['id' => $lote->id])]) }}"
                                    data-link
                                    class="text-[11px] font-semibold px-3 py-1.5 rounded text-white"
                                    style="background-color: #1f2937">
                                    Comunicar com o suporte
                                </a>
                            @endif
                            @if(!empty($reconsultaTudo))
                                <button type="button"
                                    onclick="document.getElementById('modal-reconsulta-{{ $lote->id }}').classList.remove('hidden')"
                                    class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-3 py-1.5 rounded text-white"
                                    style="background-color: #334155">
                                    <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M21 12a9 9 0 1 1-2.64-6.36" />
                                        <polyline points="21 3 21 9 15 9" />
                                    </svg>
                                    Consultar novamente
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="p-4 space-y-4">
                        @if(!empty($retryPendentes['motivos']))
                            <div class="space-y-1.5">
                                @foreach($retryPendentes['motivos'] as $m)
                                    <div class="flex items-start gap-2 rounded px-3 py-2 text-[11px] leading-snug" style="background-color: #fffbeb; border: 1px solid #fcd34d; color: #92400e">
                                        <span class="shrink-0">{{ $m['icone'] }}</span>
                                        <span>{{ $m['orientacao'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        <div>
                            <p class="text-sm text-gray-800 leading-relaxed">{{ $analise['texto'] }}</p>
                            <div class="flex flex-wrap gap-2 mt-3">
                                @foreach([
                                    ['k' => 'regular', 'label' => 'regulares', 'hex' => '#047857'],
                                    ['k' => 'pendencia', 'label' => 'com pendência', 'hex' => '#dc2626'],
                                    ['k' => 'indeterminado', 'label' => 'indeterminados', 'hex' => '#b45309'],
                                    ['k' => 'sem_info', 'label' => 'sem fontes de regularidade', 'hex' => '#9ca3af'],
                                ] as $chip)
                                    @if((int) ($cn[$chip['k']] ?? 0) > 0)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium text-white" style="background-color: {{ $chip['hex'] }}">
                                            {{ (int) $cn[$chip['k']] }} {{ $chip['label'] }}
                                        </span>
                                    @endif
                                @endforeach
                                {{-- Falhas de integração são por FONTE (não por CNPJ): consulta que não
                                     rodou por instabilidade do provedor, reconsultável. Fora do rollup. --}}
                                @if((int) ($analise['falhas'] ?? 0) > 0)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium text-white" style="background-color: #b45309" title="Fontes que falharam por instabilidade do provedor — podem ser reconsultadas.">
                                        {{ (int) $analise['falhas'] }} {{ (int) $analise['falhas'] > 1 ? 'consultas com falha' : 'consulta com falha' }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if(!empty($analise['por_fonte']))
                            {{-- Desktop: tabela --}}
                            <div class="hidden md:block overflow-x-auto">
                                <table class="min-w-full text-xs">
                                    <thead>
                                        <tr class="border-b border-gray-200 text-[10px] text-gray-400 uppercase tracking-wide">
                                            <th class="text-left py-1.5 pr-3">Fonte</th>
                                            <th class="text-center px-2 whitespace-nowrap">Regular</th>
                                            <th class="text-center px-2 whitespace-nowrap">Atenção</th>
                                            <th class="text-center px-2 whitespace-nowrap">Indeterm.</th>
                                            <th class="text-center px-2 whitespace-nowrap">Falha</th>
                                            <th class="text-center px-2 whitespace-nowrap">N/Consult.</th>
                                            <th class="text-left pl-3 w-2/5">Distribuição</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($analise['por_fonte'] as $f)
                                            <tr>
                                                <td class="py-2 pr-3 text-gray-800">{{ $f['titulo'] }}</td>
                                                <td class="text-center px-2 font-medium" style="color: {{ ($f['regular'] ?? 0) > 0 ? '#047857' : '#9ca3af' }}">{{ (int) ($f['regular'] ?? 0) }}</td>
                                                <td class="text-center px-2 font-medium" style="color: {{ ($f['atencao'] ?? 0) > 0 ? '#dc2626' : '#9ca3af' }}">{{ (int) ($f['atencao'] ?? 0) }}</td>
                                                <td class="text-center px-2 font-medium" style="color: {{ ($f['indeterminado'] ?? 0) > 0 ? '#b45309' : '#9ca3af' }}">{{ (int) ($f['indeterminado'] ?? 0) }}</td>
                                                <td class="text-center px-2 font-medium" style="color: {{ ($f['falha'] ?? 0) > 0 ? '#b45309' : '#9ca3af' }}">{{ (int) ($f['falha'] ?? 0) }}</td>
                                                <td class="text-center px-2 text-gray-400">{{ (int) ($f['neutro'] ?? 0) }}</td>
                                                <td class="pl-3">
                                                    @include('autenticado.consulta.partials._analise-distribuicao', ['f' => $f])
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Mobile: lista empilhada por fonte (sem scroll horizontal) --}}
                            <div class="md:hidden space-y-2">
                                @foreach($analise['por_fonte'] as $f)
                                    <div class="border border-gray-100 rounded p-2">
                                        <p class="text-xs font-medium text-gray-800 mb-1">{{ $f['titulo'] }}</p>
                                        <div class="flex flex-wrap gap-x-3 gap-y-0.5 text-[11px] mb-1.5">
                                            <span class="font-medium" style="color: {{ ($f['regular'] ?? 0) > 0 ? '#047857' : '#9ca3af' }}">Regular {{ (int) ($f['regular'] ?? 0) }}</span>
                                            <span class="font-medium" style="color: {{ ($f['atencao'] ?? 0) > 0 ? '#dc2626' : '#9ca3af' }}">Atenção {{ (int) ($f['atencao'] ?? 0) }}</span>
                                            <span class="font-medium" style="color: {{ ($f['indeterminado'] ?? 0) > 0 ? '#b45309' : '#9ca3af' }}">Indeterm. {{ (int) ($f['indeterminado'] ?? 0) }}</span>
                                            <span class="font-medium" style="color: {{ ($f['falha'] ?? 0) > 0 ? '#b45309' : '#9ca3af' }}">Falha {{ (int) ($f['falha'] ?? 0) }}</span>
                                            <span class="text-gray-400">N/Consult. {{ (int) ($f['neutro'] ?? 0) }}</span>
                                        </div>
                                        @include('autenticado.consulta.partials._analise-distribuicao', ['f' => $f])
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                @if(!empty($retryPendentes['elegiveis']) || !empty($retryPendentes['inelegiveis']))
                    <div id="modal-retry-{{ $lote->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(17,24,39,.5)">
                        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg max-h-[85vh] overflow-y-auto">
                            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                                <span class="text-sm font-semibold text-gray-800">Reconsultar fontes com falha</span>
                                <button type="button" onclick="document.getElementById('modal-retry-{{ $lote->id }}').classList.add('hidden')" class="text-gray-400 text-xl leading-none">&times;</button>
                            </div>
                            <form id="form-retry-{{ $lote->id }}" class="p-4 space-y-3">
                                <p class="text-[11px] text-gray-500">Reconsulta os CNPJs com fontes que falharam por instabilidade, no plano <strong>{{ $lote->plano->nome }}</strong> com {{ $retryPendentes['desconto_pct_efetivo'] ?? 50 }}% de desconto. Reconsultamos apenas as fontes que falharam de cada CNPJ.</p>
                                @foreach($retryPendentes['alvos'] ?? [] as $a)
                                    <div class="flex items-start justify-between gap-2 text-xs border border-gray-100 rounded p-2">
                                        <div class="text-gray-700">
                                            <strong>{{ $a['cnpj'] }}</strong> <span class="text-gray-500">({{ $a['razao'] }})</span>
                                            <span class="block text-[11px] text-gray-400 mt-0.5">Reconsultaremos: {{ implode(', ', $a['fontes']) }}</span>
                                        </div>
                                        <span class="text-amber-700 font-medium whitespace-nowrap">{{ \App\Support\Dinheiro::brl($a['valor_reais']) }}</span>
                                    </div>
                                @endforeach
                                @foreach($retryPendentes['inelegiveis'] as $i)
                                    <div class="flex items-start gap-2 text-xs text-gray-400">
                                        <span class="inline-flex px-1.5 py-0.5 rounded text-white text-[10px] shrink-0" style="background-color: #9ca3af">Tente mais tarde</span>
                                        <span>{{ $i['titulo'] }} — {{ $i['cnpj'] }}</span>
                                    </div>
                                @endforeach
                                @php
                                    $valorRetryReais = (float) $retryPendentes['valor_total_reais'];
                                    $saldoAposReais = (float) $saldoReais - $valorRetryReais;
                                @endphp
                                <div class="border-t border-gray-200 pt-3 text-xs text-gray-600">
                                    Custo: <strong>{{ \App\Support\Dinheiro::brl($valorRetryReais) }}</strong> · Saldo: {{ \App\Support\Dinheiro::brl($saldoReais) }} <span class="text-gray-400">→</span> <strong style="color: {{ $saldoAposReais < 0 ? '#dc2626' : '#047857' }}">{{ \App\Support\Dinheiro::brl($saldoAposReais) }}</strong>
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button" onclick="document.getElementById('modal-retry-{{ $lote->id }}').classList.add('hidden')" class="text-xs px-3 py-1.5 text-gray-600">Cancelar</button>
                                    <button type="submit" class="text-xs px-3 py-1.5 rounded text-white" style="background-color: #047857">Confirmar reconsulta</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <script>
                    (function () {
                        var form = document.getElementById('form-retry-{{ $lote->id }}');
                        if (!form || form.dataset.bound) return;
                        form.dataset.bound = '1';

                        var abrirBtn = document.getElementById('btn-abrir-retry-{{ $lote->id }}');

                        // Reaproveita o MESMO card "Andamento da Consulta" e o mesmo JS
                        // (consulta-lote-detalhe.js) do processamento inicial, em vez de inventar uma
                        // barra própria — assim a reconsulta mostra a barra/etapas/checklist padrão do
                        // projeto. O resultado anterior fica oculto enquanto reconsulta: a tela de
                        // carregamento deve mostrar só o carregamento (a página recarrega ao concluir).
                        function iniciarAcompanhamentoRetry(tabId) {
                            var root = document.getElementById('consulta-lote-detalhe-root');
                            var card = document.getElementById('consulta-lote-progresso-card');
                            var resultadoAnterior = document.getElementById('consulta-lote-resultado-anterior');
                            if (!root || !card || typeof window.initConsultaLoteDetalhe !== 'function') {
                                window.location.reload();
                                return;
                            }
                            if (resultadoAnterior) resultadoAnterior.classList.add('hidden');
                            card.classList.remove('hidden');
                            root.dataset.status = 'processando';
                            root.dataset.iniciadoEm = String(Math.floor(Date.now() / 1000));
                            // A reconsulta troca o tab_id do lote (novo batch) — sem atualizar aqui, o
                            // SSE fica ouvindo o canal antigo e nunca recebe o progresso real, caindo
                            // pro polling de 5 em 5s (parece "travado"/demorado pra iniciar).
                            if (tabId) root.dataset.tabId = tabId;
                            delete root.dataset.initialized;
                            window.initConsultaLoteDetalhe();
                        }

                        form.addEventListener('submit', function (ev) {
                            ev.preventDefault();
                            var btn = form.querySelector('button[type=submit]');
                            if (btn) { btn.disabled = true; btn.textContent = 'Reconsultando…'; }
                            fetch('{{ route('app.consulta.lote.retry', ['id' => $lote->id]) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify({})
                            }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
                              .then(function (res) {
                                  if (res.ok) {
                                      document.getElementById('modal-retry-{{ $lote->id }}').classList.add('hidden');
                                      if (abrirBtn) abrirBtn.classList.add('hidden');
                                      iniciarAcompanhamentoRetry(res.j && res.j.tab_id);
                                  } else {
                                      alert((res.j && (res.j.message || res.j.error)) || 'Não foi possível reconsultar.');
                                      if (btn) { btn.disabled = false; btn.textContent = 'Confirmar reconsulta'; }
                                  }
                              }).catch(function () {
                                  alert('Falha de rede. Tente novamente.');
                                  if (btn) { btn.disabled = false; btn.textContent = 'Confirmar reconsulta'; }
                              });
                        });
                    })();
                    </script>
                @endif

                {{-- Reconsulta TOTAL ("Consultar novamente"): plano escolhível, preço integral,
                     cria LOTE NOVO via executar (histórico preservado). Só em status terminal. --}}
                @if(!empty($reconsultaTudo))
                    <div id="modal-reconsulta-{{ $lote->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(17,24,39,.5)">
                        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg max-h-[85vh] overflow-y-auto">
                            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                                <span class="text-sm font-semibold text-gray-800">Consultar novamente</span>
                                <button type="button" onclick="document.getElementById('modal-reconsulta-{{ $lote->id }}').classList.add('hidden')" class="text-gray-400 text-xl leading-none">&times;</button>
                            </div>
                            <form id="form-reconsulta-{{ $lote->id }}" class="p-4 space-y-3">
                                <p class="text-[11px] text-gray-500">Refaz a consulta dos <strong>{{ $reconsultaTudo['total_alvos'] }} CNPJ(s)</strong> deste lote no plano escolhido, a preço integral. Cria uma <strong>nova consulta</strong> — esta permanece no histórico.</p>
                                <div>
                                    <label class="text-[11px] text-gray-500 block mb-1">Plano</label>
                                    <select id="select-reconsulta-plano-{{ $lote->id }}" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                                        @foreach($reconsultaTudo['planos'] as $pl)
                                            <option value="{{ $pl['id'] }}"{{ $pl['id'] === (int) $lote->plano_id ? ' selected' : '' }} data-valor="{{ $pl['valor_unitario_reais'] }}" data-rotulo="{{ $pl['rotulo_preco'] }}">
                                                {{ $pl['nome'] }} — {{ $pl['rotulo_preco'] }}/CNPJ
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="border-t border-gray-200 pt-3 text-xs text-gray-600">
                                    Custo: <strong id="custo-reconsulta-{{ $lote->id }}"></strong> · Saldo: {{ \App\Support\Dinheiro::brl($saldoReais) }}
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button" onclick="document.getElementById('modal-reconsulta-{{ $lote->id }}').classList.add('hidden')" class="text-xs px-3 py-1.5 text-gray-600">Cancelar</button>
                                    <button type="submit" class="text-xs px-3 py-1.5 rounded text-white" style="background-color: #047857">Confirmar consulta</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <script>
                    (function () {
                        var form = document.getElementById('form-reconsulta-{{ $lote->id }}');
                        if (!form || form.dataset.bound) return;
                        form.dataset.bound = '1';
                        var alvos = {!! json_encode(['participante_ids' => $reconsultaTudo['participante_ids'], 'cliente_ids' => $reconsultaTudo['cliente_ids']]) !!};
                        var totalAlvos = {{ (int) $reconsultaTudo['total_alvos'] }};
                        var sel = document.getElementById('select-reconsulta-plano-{{ $lote->id }}');
                        var custoEl = document.getElementById('custo-reconsulta-{{ $lote->id }}');
                        function brl(valor) {
                            return 'R$ ' + Number(valor || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        }
                        function atualizaCusto() {
                            var opt = sel.options[sel.selectedIndex];
                            var unit = Number(opt.dataset.valor || '0');
                            custoEl.textContent = totalAlvos + ' × ' + opt.dataset.rotulo + ' = ' + brl(totalAlvos * unit);
                        }
                        sel.addEventListener('change', atualizaCusto);
                        atualizaCusto();
                        form.addEventListener('submit', function (ev) {
                            ev.preventDefault();
                            var btn = form.querySelector('button[type=submit]');
                            if (btn) { btn.disabled = true; btn.textContent = 'Consultando…'; }
                            var tabId = (window.crypto && crypto.randomUUID) ? crypto.randomUUID() : 'rc-' + Date.now();
                            fetch('{{ route('app.consulta.nova.executar') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify({
                                    participante_ids: alvos.participante_ids,
                                    cliente_ids: alvos.cliente_ids,
                                    plano_id: parseInt(sel.value, 10),
                                    tab_id: tabId
                                })
                            }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
                              .then(function (res) {
                                  if (res.ok && res.j.redirect_url) { window.location = res.j.redirect_url; }
                                  else {
                                      alert((res.j && (res.j.message || res.j.error)) || 'Não foi possível iniciar a consulta.');
                                      if (btn) { btn.disabled = false; btn.textContent = 'Confirmar consulta'; }
                                  }
                              }).catch(function () {
                                  alert('Falha de rede. Tente novamente.');
                                  if (btn) { btn.disabled = false; btn.textContent = 'Confirmar consulta'; }
                              });
                        });
                    })();
                    </script>
                @endif
            @endif

            @if($temResultadosNoLote)
                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between gap-3 flex-wrap">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Participantes Consultados</span>
                            <span class="text-[10px] font-semibold text-gray-400 bg-gray-200 px-2 py-0.5 rounded">{{ number_format((int) $resultados->total(), 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-[10px] text-gray-500 uppercase tracking-wide">20 por página</span>
                        </div>
                    </div>

                    {{-- Tooltip rápido (sem delay) dos badges de certidão. O `.cert-tip` no markup é só
                         a FONTE do conteúdo (fica display:none); o balão real é um singleton no <body>
                         com position:fixed — sobrepõe qualquer card/overflow (o container do card tem
                         overflow-hidden) e, se o texto não couber acima do chip, abre para baixo. --}}
                    <style>
                        .cert-chip { position: relative; cursor: default; }
                        .cert-tip { display: none; }
                        #cert-tip-float {
                            display: none; position: fixed; z-index: 9999; max-width: 280px;
                            background: #111827; color: #fff; padding: 7px 9px; border-radius: 7px;
                            font-size: 11px; line-height: 1.4; font-weight: 400; text-transform: none;
                            letter-spacing: normal; text-align: left; white-space: normal; overflow-wrap: anywhere;
                            box-shadow: 0 6px 18px rgba(17,24,39,.22); pointer-events: none; overflow: hidden;
                        }
                        #cert-tip-float strong { display: block; font-weight: 700; margin-bottom: 2px; }
                        #cert-tip-arrow {
                            display: none; position: fixed; z-index: 9999; width: 0; height: 0;
                            border: 5px solid transparent; pointer-events: none;
                        }
                    </style>
                    <script>
                    (function () {
                        if (window.__certTipInit) return; // página pode ser re-renderizada via AJAX (data-link)
                        window.__certTipInit = true;

                        var MARGIN = 8, GAP = 7;
                        var float = null, arrow = null, atual = null;

                        function ensure() {
                            if (float) return;
                            float = document.createElement('div');
                            float.id = 'cert-tip-float';
                            arrow = document.createElement('div');
                            arrow.id = 'cert-tip-arrow';
                            document.body.appendChild(float);
                            document.body.appendChild(arrow);
                        }

                        function hide() {
                            atual = null;
                            if (float) { float.style.display = 'none'; arrow.style.display = 'none'; }
                        }

                        function show(chip) {
                            var src = chip.querySelector('.cert-tip');
                            if (!src || !src.innerHTML.trim()) { hide(); return; }
                            ensure();
                            atual = chip;

                            float.innerHTML = src.innerHTML;
                            float.style.display = 'block';
                            float.style.visibility = 'hidden';
                            float.style.maxHeight = '';
                            float.style.left = '0px';
                            float.style.top = '0px';

                            var vw = window.innerWidth, vh = window.innerHeight;
                            var r = chip.getBoundingClientRect();
                            var espacoAcima = r.top - GAP - MARGIN;
                            var espacoAbaixo = vh - r.bottom - GAP - MARGIN;
                            var th = float.offsetHeight;

                            // Prefere acima; vira pra baixo quando o texto não cabe e há mais espaço lá.
                            var acima = th <= espacoAcima || espacoAcima >= espacoAbaixo;
                            var maxH = Math.max(40, acima ? espacoAcima : espacoAbaixo);
                            if (th > maxH) {
                                float.style.maxHeight = maxH + 'px';
                                th = float.offsetHeight;
                            }

                            var tw = float.offsetWidth;
                            var left = Math.round(Math.min(Math.max(MARGIN, r.left + r.width / 2 - tw / 2), vw - tw - MARGIN));
                            var top = Math.round(acima ? r.top - GAP - th : r.bottom + GAP);
                            float.style.left = left + 'px';
                            float.style.top = top + 'px';
                            float.style.visibility = 'visible';

                            // Seta segue o centro do chip, presa à largura do balão.
                            var setaLeft = Math.round(Math.min(Math.max(r.left + r.width / 2 - 5, left + 4), left + tw - 14));
                            arrow.style.left = setaLeft + 'px';
                            arrow.style.top = (acima ? r.top - GAP : r.bottom + GAP - 5) + 'px';
                            arrow.style.borderTopColor = acima ? '#111827' : 'transparent';
                            arrow.style.borderBottomColor = acima ? 'transparent' : '#111827';
                            arrow.style.display = 'block';
                        }

                        document.addEventListener('mouseover', function (e) {
                            var chip = e.target.closest ? e.target.closest('.cert-chip') : null;
                            if (chip === atual) return;
                            if (chip) { show(chip); } else if (atual) { hide(); }
                        });
                        window.addEventListener('scroll', hide, true);
                        window.addEventListener('resize', hide);
                        document.addEventListener('click', hide, true);
                    })();
                    </script>

                    @php $autoAbrirDetalhe = $resultados->total() === 1; @endphp
                    <div class="hidden md:block overflow-visible">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b border-gray-300">
                                    <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Participante</th>
                                    <th class="px-3 py-2.5 text-center text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Situação</th>
                                    <th class="px-3 py-2.5 text-center text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50 whitespace-nowrap">Regime Tributário</th>
                                    <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Certidões</th>
                                    <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Sinalizações</th>
                                    <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Status</th>
                                    <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Consultado em</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($resultados as $resultado)
                                    @php
                                        $regimeTributario = $resultado['regime_tributario'] ?? null;
                                        $certidoes = $resultado['certidoes'] ?? [];
                                    @endphp
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-3 py-3">
                                            <div class="flex items-start gap-2">
                                                <button type="button" data-detalhe-toggle="consulta-detalhe-d-{{ $loop->index }}"
                                                        class="mt-0.5 flex-shrink-0 w-5 h-5 inline-flex items-center justify-center rounded text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors"
                                                        title="Ver detalhes da consulta" aria-expanded="{{ $autoAbrirDetalhe ? 'true' : 'false' }}">
                                                    <svg class="detalhe-chevron w-3.5 h-3.5 transition-transform" @if($autoAbrirDetalhe) style="transform: rotate(90deg)"@endif fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                    </svg>
                                                </button>
                                                <div class="min-w-0 max-w-[280px]">
                                                    <div class="text-sm text-gray-900">
                                                        @if(!empty($resultado['participante_id']))
                                                            <a href="/app/participante/{{ $resultado['participante_id'] }}" data-link title="{{ $resultado['razao_social'] }}" class="block truncate text-gray-900 hover:text-gray-600 hover:underline font-medium">{{ $resultado['razao_social'] ?: 'Sem razão social' }}</a>
                                                        @else
                                                            <span class="block truncate font-medium" title="{{ $resultado['razao_social'] }}">{{ $resultado['razao_social'] ?: 'Sem razão social' }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-[11px] text-gray-500 mt-1 font-mono">
                                                        {{ $resultado['documento_formatado'] ?: '—' }}@if(!empty($resultado['uf'])) · {{ $resultado['uf'] }}@endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-3 text-sm text-gray-700 text-center">{{ $resultado['situacao_cadastral'] ?: '—' }}</td>
                                        <td class="px-3 py-3 text-sm text-gray-700 text-center whitespace-nowrap">{{ $regimeTributario ?: '—' }}</td>
                                        <td class="px-3 py-3">
                                            @if(!empty($certidoes))
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($certidoes as $cert)
                                                        <span class="cert-chip inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white"
                                                              style="background-color: {{ $cert['hex'] }}">
                                                            {{ $cert['sigla'] }} {{ $cert['glyph'] }}
                                                            <span class="cert-tip">
                                                                <strong>{{ $cert['titulo'] }} · {{ $cert['label'] }}</strong>
                                                                @if(!empty($cert['descricao'])){{ $cert['descricao'] }}@endif
                                                            </span>
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3">
                                            @if(!empty($resultado['parecer']))
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($resultado['parecer'] as $parecer)
                                                        <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $parecer['hex'] ?? '#374151' }}" title="{{ $parecer['tooltip'] ?? ($parecer['descricao'] ?? '') }}">
                                                            {{ $parecer['badge_label'] ?? ($parecer['titulo'] ?? ($parecer['chave'] ?? 'Parecer')) }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3">
                                            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $resultado['status_hex'] }}">
                                                {{ $resultado['status_label'] }}
                                            </span>
                                            @if(!empty($resultado['mensagem_exibivel']))
                                                {{-- Trecho curto: o texto integral do órgão fica no card da fonte (detalhe expansível).
                                                     overflow-wrap:anywhere — mensagens de fonte trazem URLs longas sem espaço;
                                                     sem isso a palavra inquebrável alarga a tabela além do container (que corta). --}}
                                                <p class="text-[11px] text-gray-500 mt-1" style="overflow-wrap: anywhere" title="{{ $resultado['mensagem_exibivel'] }}">{{ \Illuminate\Support\Str::limit($resultado['mensagem_exibivel'], 180) }}</p>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 text-sm text-gray-700">{{ $resultado['consultado_em_label'] ?: '—' }}</td>
                                    </tr>
                                    <tr id="consulta-detalhe-d-{{ $loop->index }}" class="{{ $autoAbrirDetalhe ? '' : 'hidden' }}">
                                        <td colspan="7" class="px-4 py-4 bg-gray-50/60 border-t border-gray-100">
                                            @include('autenticado.consulta.partials.detalhe-blocos', ['blocos' => $resultado['detalhe_blocos'] ?? [], 'resumo' => $resultado['resumo_texto'] ?? null, 'certidoes' => $resultado['certidoes'] ?? [], 'cabecalho' => ['razao' => $resultado['razao_social'] ?? null, 'documento' => $resultado['documento_formatado'] ?? null, 'uf' => $resultado['uf'] ?? null, 'situacao' => $resultado['situacao_cadastral'] ?? null]])
                                            @include('autenticado.consulta.partials.relacionamento-fiscal', ['fiscal' => $resultado['fiscal_resumo'] ?? null, 'cabecalho' => ['razao' => $resultado['razao_social'] ?? null, 'documento' => $resultado['documento_formatado'] ?? null, 'uf' => $resultado['uf'] ?? null]])
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="divide-y divide-gray-100 md:hidden">
                        @foreach($resultados as $resultado)
                            @php
                                $regimeTributario = $resultado['regime_tributario'] ?? null;
                                $certidoes = $resultado['certidoes'] ?? [];
                            @endphp
                            <div class="px-4 py-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm">
                                            @if(!empty($resultado['participante_id']))
                                                <a href="/app/participante/{{ $resultado['participante_id'] }}" data-link title="{{ $resultado['razao_social'] }}" class="block truncate text-gray-900 hover:text-gray-600 hover:underline font-medium">{{ $resultado['razao_social'] ?: 'Sem razão social' }}</a>
                                            @else
                                                <span class="block truncate text-gray-900 font-medium" title="{{ $resultado['razao_social'] }}">{{ $resultado['razao_social'] ?: 'Sem razão social' }}</span>
                                            @endif
                                        </p>
                                        <p class="text-[11px] text-gray-500 mt-1 font-mono">{{ $resultado['documento_formatado'] ?: '—' }}@if(!empty($resultado['uf'])) · {{ $resultado['uf'] }}@endif</p>
                                    </div>
                                    <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $resultado['status_hex'] }}">{{ $resultado['status_label'] }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-3 mt-3 text-sm text-gray-700">
                                    <div class="text-center">
                                        <p class="text-[10px] text-gray-400 uppercase">Situação</p>
                                        <p>{{ $resultado['situacao_cadastral'] ?: '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 uppercase">Consultado em</p>
                                        <p>{{ $resultado['consultado_em_label'] ?: '—' }}</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-[10px] text-gray-400 uppercase whitespace-nowrap">Regime Tributário</p>
                                        <p>{{ $regimeTributario ?: '—' }}</p>
                                    </div>
                                </div>
                                @if(!empty($certidoes))
                                    <div class="mt-3">
                                        <p class="text-[10px] text-gray-400 uppercase">Certidões</p>
                                        <div class="flex flex-col gap-1 mt-1">
                                            @foreach($certidoes as $cert)
                                                {{-- Mobile não tem hover: mostra sigla + status + resumo inline. --}}
                                                <div class="flex items-start gap-2">
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white shrink-0"
                                                          style="background-color: {{ $cert['hex'] }}">
                                                        {{ $cert['sigla'] }} {{ $cert['glyph'] }}
                                                    </span>
                                                    <span class="text-[11px] text-gray-600 leading-snug">
                                                        <span class="text-gray-800">{{ $cert['label'] }}</span>@if(!empty($cert['descricao'])) — {{ $cert['descricao'] }}@endif
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                @if(!empty($resultado['parecer']))
                                    <div class="flex flex-wrap gap-1 mt-3">
                                        @foreach($resultado['parecer'] as $parecer)
                                            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $parecer['hex'] ?? '#374151' }}" title="{{ $parecer['tooltip'] ?? ($parecer['descricao'] ?? '') }}">
                                                {{ $parecer['badge_label'] ?? ($parecer['titulo'] ?? ($parecer['chave'] ?? 'Parecer')) }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                                @if(!empty($resultado['mensagem_exibivel']))
                                    <p class="text-xs text-gray-500 mt-3" style="overflow-wrap: anywhere">{{ \Illuminate\Support\Str::limit($resultado['mensagem_exibivel'], 180) }}</p>
                                @endif
                                <button type="button" data-detalhe-toggle="consulta-detalhe-m-{{ $loop->index }}"
                                        class="mt-3 inline-flex items-center gap-1 text-[11px] font-medium text-gray-600 hover:text-gray-900" aria-expanded="{{ $autoAbrirDetalhe ? 'true' : 'false' }}">
                                    <svg class="detalhe-chevron w-3.5 h-3.5 transition-transform" @if($autoAbrirDetalhe) style="transform: rotate(90deg)"@endif fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                    Ver detalhes da consulta
                                </button>
                                <div id="consulta-detalhe-m-{{ $loop->index }}" class="{{ $autoAbrirDetalhe ? '' : 'hidden ' }}mt-3">
                                    @include('autenticado.consulta.partials.detalhe-blocos', ['blocos' => $resultado['detalhe_blocos'] ?? [], 'resumo' => $resultado['resumo_texto'] ?? null, 'certidoes' => $resultado['certidoes'] ?? [], 'cabecalho' => ['razao' => $resultado['razao_social'] ?? null, 'documento' => $resultado['documento_formatado'] ?? null, 'uf' => $resultado['uf'] ?? null, 'situacao' => $resultado['situacao_cadastral'] ?? null]])
                                    @include('autenticado.consulta.partials.relacionamento-fiscal', ['fiscal' => $resultado['fiscal_resumo'] ?? null, 'cabecalho' => ['razao' => $resultado['razao_social'] ?? null, 'documento' => $resultado['documento_formatado'] ?? null, 'uf' => $resultado['uf'] ?? null]])
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t border-gray-300 px-4 py-3 flex items-center justify-between gap-3 flex-wrap">
                        <span class="text-[10px] text-gray-500 uppercase tracking-wide">
                            Mostrando {{ $resultados->firstItem() }}–{{ $resultados->lastItem() }} de {{ $resultados->total() }} participantes
                        </span>
                        <div class="flex items-center gap-2">
                            @if($resultados->onFirstPage())
                                <span class="px-3 py-1.5 text-[10px] text-gray-400 bg-gray-100 border border-gray-200 rounded">Anterior</span>
                            @else
                                <a href="{{ $resultados->previousPageUrl() }}" data-link data-consulta-lote-pagination class="px-3 py-1.5 text-[10px] text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">Anterior</a>
                            @endif

                            <span class="text-[10px] text-gray-500 uppercase tracking-wide">{{ $resultados->currentPage() }} / {{ $resultados->lastPage() }}</span>

                            @if($resultados->hasMorePages())
                                <a href="{{ $resultados->nextPageUrl() }}" data-link data-consulta-lote-pagination class="px-3 py-1.5 text-[10px] text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">Próxima</a>
                            @else
                                <span class="px-3 py-1.5 text-[10px] text-gray-400 bg-gray-100 border border-gray-200 rounded">Próxima</span>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white rounded border border-gray-300 p-4 border-l-4 border-l-blue-500">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Sem resultados individuais</p>
                    <p class="mt-2 text-sm text-gray-700">O lote foi finalizado, mas ainda não há linhas individuais disponíveis para exibição.</p>
                </div>
            @endif
            </div>
        @endif
    </div>
</div>

<script src="/js/progresso-automacao.js?v={{ @filemtime(public_path('js/progresso-automacao.js')) ?: time() }}"></script>
<script src="/js/consulta-lote-detalhe.js?v={{ $jsVersion }}"></script>
<script>
(function() {
    const scrollKey = 'consulta-lote-detalhe-scroll-y';

    function storePaginationScroll() {
        try {
            window.sessionStorage.setItem(scrollKey, String(window.scrollY || 0));
        } catch (_) {}
    }

    function restorePaginationScroll() {
        try {
            const raw = window.sessionStorage.getItem(scrollKey);
            if (raw === null) {
                return;
            }

            window.sessionStorage.removeItem(scrollKey);

            const scrollY = parseInt(raw, 10);
            if (Number.isNaN(scrollY) || scrollY < 0) {
                return;
            }

            window.requestAnimationFrame(function() {
                window.scrollTo(0, scrollY);
            });
        } catch (_) {}
    }

    window.storeConsultaLoteScroll = storePaginationScroll;

    document.querySelectorAll('[data-consulta-lote-pagination]').forEach(function(link) {
        link.addEventListener('click', storePaginationScroll);
    });

    // Toggle do detalhe expansível por CNPJ (desktop = linha; mobile = bloco). Delegação no
    // document (registrada 1x, com cleanup) → sobrevive a re-render/paginação e ao swap SPA.
    if (!window.__consultaDetalheToggleBound) {
        window.__consultaDetalheToggleBound = true;
        var detalheToggleHandler = function(e) {
            var btn = e.target.closest('[data-detalhe-toggle]');
            if (!btn) return;
            var target = document.getElementById(btn.getAttribute('data-detalhe-toggle'));
            if (!target) return;
            var hidden = target.classList.toggle('hidden');
            btn.setAttribute('aria-expanded', hidden ? 'false' : 'true');
            var chevron = btn.querySelector('.detalhe-chevron');
            if (chevron) chevron.style.transform = hidden ? '' : 'rotate(90deg)';
            // Cards de fonte só têm altura mensurável quando a linha do CNPJ está aberta.
            if (!hidden) ajustarFontesClampadas();
        };
        document.addEventListener('click', detalheToggleHandler);
        window._cleanupFunctions = window._cleanupFunctions || {};
        window._cleanupFunctions.consultaDetalheToggle = function() {
            document.removeEventListener('click', detalheToggleHandler);
            window.__consultaDetalheToggleBound = false;
        };
    }

    // "Ver tudo / Ver menos" dos cards de fonte pesados: alterna o clamp de altura (preview ~11rem
    // → altura total) sem esconder o conteúdo. Delegado no document, 1x, com cleanup (SPA-safe).
    var FONTE_CLAMP = '11rem';
    if (!window.__consultaFonteExpandBound) {
        window.__consultaFonteExpandBound = true;
        var fonteExpandHandler = function(e) {
            var btn = e.target.closest('[data-fonte-expand]');
            if (!btn) return;
            var wrap = btn.closest('[data-fonte-bloco]');
            if (!wrap) return;
            var corpo = wrap.querySelector('[data-fonte-corpo]');
            if (!corpo) return;
            var fade = wrap.querySelector('[data-fonte-fade]');
            var label = btn.querySelector('[data-fonte-expand-label]');
            var chev = btn.querySelector('.detalhe-chevron');
            var clamped = corpo.style.maxHeight !== '' && corpo.style.maxHeight !== 'none';
            if (clamped) {
                corpo.style.maxHeight = '';
                if (fade) fade.classList.add('hidden');
                if (label) label.textContent = 'Ver menos';
                if (chev) chev.style.transform = 'rotate(180deg)';
            } else {
                corpo.style.maxHeight = FONTE_CLAMP;
                if (fade) fade.classList.remove('hidden');
                if (label) label.textContent = 'Ver tudo';
                if (chev) chev.style.transform = '';
            }
        };
        document.addEventListener('click', fonteExpandHandler);
        window._cleanupFunctions = window._cleanupFunctions || {};
        window._cleanupFunctions.consultaFonteExpand = function() {
            document.removeEventListener('click', fonteExpandHandler);
            window.__consultaFonteExpandBound = false;
        };
    }

    // Guard: se o card clampado na verdade cabe no preview, remove fade + botão (nada a expandir).
    function ajustarFontesClampadas() {
        document.querySelectorAll('[data-fonte-corpo]').forEach(function(corpo) {
            if (corpo.style.maxHeight === '' || corpo.style.maxHeight === 'none') return;
            if (corpo.offsetParent === null) return; // linha do CNPJ fechada → mede 0, não mexer
            if (corpo.scrollHeight <= corpo.clientHeight + 2) {
                corpo.style.maxHeight = '';
                var wrap = corpo.closest('[data-fonte-bloco]') || corpo.parentElement;
                var fade = wrap.querySelector('[data-fonte-fade]');
                var btn = wrap.querySelector('[data-fonte-expand]');
                var footer = wrap.querySelector('[data-fonte-footer]');
                if (fade) fade.remove();
                if (btn) btn.remove();
                // Card cabe e não tem comprovante → rodapé fica vazio: remove pra não sobrar borda.
                if (footer && !footer.querySelector('a')) footer.remove();
            }
        });
    }

    restorePaginationScroll();

    function tryInit(attempts) {
        if (typeof window.initConsultaLoteDetalhe === 'function') {
            window.initConsultaLoteDetalhe();
        } else if (attempts < 50) {
            setTimeout(function() { tryInit(attempts + 1); }, 100);
        }
    }

    tryInit(0);
    ajustarFontesClampadas();
})();
</script>
