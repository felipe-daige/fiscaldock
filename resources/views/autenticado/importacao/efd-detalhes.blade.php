{{-- Importação EFD - Detalhes --}}
@php
    [$badgeClass, $badgeLabel] = match($importacao->status) {
        'concluido'   => ['bg-green-100 text-green-700', 'Concluído'],
        'processando' => ['bg-blue-100 text-blue-700', 'Processando'],
        'erro'        => ['bg-red-100 text-red-700', 'Erro'],
        default       => ['bg-gray-100 text-gray-700', 'Pendente'],
    };
    $tipoClass = $importacao->tipo_efd === 'EFD PIS/COFINS'
        ? 'bg-purple-100 text-purple-700'
        : 'bg-blue-100 text-blue-700';
@endphp

<div class="min-h-screen bg-gray-50" id="efd-detalhes-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <style>
            @keyframes card-slide-in {
                from { opacity: 0; transform: translateY(60px); }
                to   { opacity: 1; transform: translateY(0); }
            }
            .efd-animate {
                opacity: 0;
                animation: card-slide-in 0.65s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            }
            @media (prefers-reduced-motion: reduce) {
                .efd-animate { opacity: 1; animation: none; }
            }
        </style>

        {{-- Header --}}
        <div class="mb-6 efd-animate">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-2xl font-bold text-gray-900 truncate">{{ $importacao->filename ?? 'Importação #' . $importacao->id }}</h1>
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $badgeClass }}">{{ $badgeLabel }}</span>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Detalhes da importação EFD</p>
                </div>
                <a
                    href="/app/importacao/efd"
                    data-link
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50 flex-shrink-0"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Voltar
                </a>
            </div>
        </div>

        {{-- Barra de navegação ancorada (sticky) --}}
        @if($importacao->status === 'concluido')
        <nav class="efd-animate sticky top-0 z-20 bg-white/95 backdrop-blur border border-gray-200 rounded-xl shadow-sm mb-6 px-4 py-2 flex items-center gap-1 overflow-x-auto" id="efd-sticky-nav" style="animation-delay: 0.02s">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide mr-2 flex-shrink-0">Ir para:</span>
            <a href="#info-section" class="efd-nav-link px-3 py-1.5 text-xs font-medium text-gray-600 rounded-lg hover:bg-gray-100 transition whitespace-nowrap">Info</a>
            <a href="#participantes-section" class="efd-nav-link px-3 py-1.5 text-xs font-medium text-gray-600 rounded-lg hover:bg-gray-100 transition whitespace-nowrap">Participantes</a>
            @if(!empty($resumoFinal))
            <a href="#resumo-final-section" class="efd-nav-link px-3 py-1.5 text-xs font-medium text-gray-600 rounded-lg hover:bg-gray-100 transition whitespace-nowrap">Notas</a>
            @endif
            @if(isset($catalogoItens) && ($catalogoItens instanceof \Illuminate\Pagination\LengthAwarePaginator ? $catalogoItens->total() > 0 : $catalogoItens->count() > 0))
            <a href="#catalogo-section" class="efd-nav-link px-3 py-1.5 text-xs font-medium text-gray-600 rounded-lg hover:bg-gray-100 transition whitespace-nowrap">Catálogo</a>
            @endif
            @if($apuracaoIcms)
            <a href="#apuracao-icms-section" class="efd-nav-link px-3 py-1.5 text-xs font-medium text-gray-600 rounded-lg hover:bg-gray-100 transition whitespace-nowrap">ICMS/IPI</a>
            @endif
            @if(isset($retencoesFonte) && $retencoesFonte->isNotEmpty())
            <a href="#retencoes-section" class="efd-nav-link px-3 py-1.5 text-xs font-medium text-gray-600 rounded-lg hover:bg-gray-100 transition whitespace-nowrap">Retenções</a>
            @endif
            @if($apuracaoContribuicao)
            <a href="#apuracao-pis-cofins-section" class="efd-nav-link px-3 py-1.5 text-xs font-medium text-gray-600 rounded-lg hover:bg-gray-100 transition whitespace-nowrap">PIS/COFINS</a>
            @endif
        </nav>
        @endif

        {{-- Banner de erro --}}
        @if($importacao->status === 'erro')
        <div class="efd-animate mb-6 bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="text-sm font-semibold text-red-800">Esta importação terminou com erro</p>
                <p class="text-sm text-red-700 mt-0.5">Verifique o arquivo enviado e tente novamente.</p>
            </div>
        </div>
        @endif

        {{-- Info Card --}}
        <div class="efd-animate bg-white rounded-xl border border-gray-200 shadow-sm mb-6" id="info-section" style="animation-delay: 0.05s">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-base font-semibold text-gray-900">Informações da Importação</h2>
            </div>
            <div class="p-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-6">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Tipo EFD</p>
                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $tipoClass }}">
                        {{ $importacao->tipo_efd === 'efd-contrib' ? 'EFD PIS/COFINS' : 'EFD ICMS/IPI' }}
                    </span>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Enviado em</p>
                    <p class="text-sm font-medium text-gray-900">{{ $importacao->created_at->format('d/m/Y') }}</p>
                    <p class="text-xs text-gray-500">{{ $importacao->created_at->format('H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Concluído em</p>
                    @if($importacao->concluido_em)
                        <p class="text-sm font-medium text-gray-900">{{ $importacao->concluido_em->format('d/m/Y') }}</p>
                        <p class="text-xs text-gray-500">{{ $importacao->concluido_em->format('H:i') }}</p>
                    @else
                        <p class="text-sm text-gray-400">—</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Tempo</p>
                    <p class="text-sm font-medium text-gray-900">{{ $importacao->tempo_processamento }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Créditos cobrados</p>
                    <p class="text-sm font-medium text-gray-900">{{ $importacao->creditos_cobrados ?? 0 }}</p>
                </div>
            </div>
        </div>

        {{-- Stats Bar --}}
        <div class="efd-animate grid grid-cols-2 sm:grid-cols-3 {{ $importacao->extrair_notas ? 'lg:grid-cols-6' : 'lg:grid-cols-5' }} gap-4 mb-6" style="animation-delay: 0.1s">
            @php
                $stats = [
                    ['label' => 'Total Participantes', 'value' => ($importacao->novos ?? 0) + ($importacao->duplicados ?? 0), 'color' => 'text-gray-900'],
                    ['label' => 'Novos',               'value' => $importacao->novos ?? 0,              'color' => 'text-green-600'],
                    ['label' => 'Duplicados',           'value' => $importacao->duplicados ?? 0,         'color' => 'text-yellow-600'],
                    ['label' => 'CNPJs únicos',         'value' => $importacao->total_cnpjs_unicos ?? 0, 'color' => 'text-blue-600'],
                    ['label' => 'CPFs únicos',          'value' => $importacao->total_cpfs_unicos ?? 0,  'color' => 'text-purple-600'],
                ];
                if ($importacao->extrair_notas) {
                    $stats[] = ['label' => 'Notas Extraídas', 'value' => $importacao->notas_extraidas ?? 0, 'color' => 'text-indigo-600'];
                }
            @endphp
            @foreach($stats as $stat)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-4">
                <p class="text-xs font-medium text-gray-500">{{ $stat['label'] }}</p>
                <p class="text-2xl font-bold {{ $stat['color'] }} mt-1">{{ number_format($stat['value']) }}</p>
            </div>
            @endforeach
        </div>

        {{-- Resumo Executivo Tributário --}}
        @if($apuracaoIcms || $apuracaoContribuicao || (isset($retencoesFonte) && $retencoesFonte->isNotEmpty()))
        @php
            $tribIcms     = $apuracaoIcms ? (float) $apuracaoIcms->icms_a_recolher : 0;
            $tribIcmsSt   = $apuracaoIcms && $apuracaoIcms->tem_st ? (float) $apuracaoIcms->st_icms_recolher : 0;
            $tribPis      = $apuracaoContribuicao ? (float) $apuracaoContribuicao->pis_total_recolher : 0;
            $tribCofins   = $apuracaoContribuicao ? (float) $apuracaoContribuicao->cofins_total_recolher : 0;
            $tribRetPis   = isset($retencoesFonte) ? (float) $retencoesFonte->sum('valor_pis') : 0;
            $tribRetCof   = isset($retencoesFonte) ? (float) $retencoesFonte->sum('valor_cofins') : 0;
            $tribRetTotal = $tribRetPis + $tribRetCof;
            $tribTotal    = $tribIcms + $tribIcmsSt + $tribPis + $tribCofins;
        @endphp
        <div class="efd-animate bg-gradient-to-r from-indigo-50 to-blue-50 rounded-xl border border-indigo-200 shadow-sm mb-6" style="animation-delay: 0.12s">
            <div class="px-6 py-4 border-b border-indigo-200">
                <h2 class="text-base font-semibold text-indigo-900">Resumo Tributário</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-4">
                    @if($apuracaoIcms)
                    <div class="text-center">
                        <p class="text-xs font-medium text-gray-500">ICMS</p>
                        <p class="text-lg font-bold text-blue-700 mt-0.5">R$ {{ number_format($tribIcms, 2, ',', '.') }}</p>
                    </div>
                    @if($apuracaoIcms->tem_st)
                    <div class="text-center">
                        <p class="text-xs font-medium text-gray-500">ICMS-ST</p>
                        <p class="text-lg font-bold text-amber-700 mt-0.5">R$ {{ number_format($tribIcmsSt, 2, ',', '.') }}</p>
                    </div>
                    @endif
                    @endif
                    @if($apuracaoContribuicao)
                    <div class="text-center">
                        <p class="text-xs font-medium text-gray-500">PIS</p>
                        <p class="text-lg font-bold text-blue-600 mt-0.5">R$ {{ number_format($tribPis, 2, ',', '.') }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs font-medium text-gray-500">COFINS</p>
                        <p class="text-lg font-bold text-purple-700 mt-0.5">R$ {{ number_format($tribCofins, 2, ',', '.') }}</p>
                    </div>
                    @endif
                    @if($tribRetTotal > 0)
                    <div class="text-center">
                        <p class="text-xs font-medium text-gray-500">Retenções</p>
                        <p class="text-lg font-bold text-emerald-700 mt-0.5">R$ {{ number_format($tribRetTotal, 2, ',', '.') }}</p>
                    </div>
                    @endif
                </div>
                <div class="pt-3 border-t border-indigo-200 flex justify-between items-center">
                    <span class="text-sm font-bold text-indigo-900">Total Tributos a Recolher</span>
                    <span class="text-xl font-bold text-indigo-700">R$ {{ number_format($tribTotal, 2, ',', '.') }}</span>
                </div>
                @if($tribRetTotal > 0)
                <p class="text-xs text-gray-500 mt-1">Retenções na fonte (PIS R$ {{ number_format($tribRetPis, 2, ',', '.') }} + COFINS R$ {{ number_format($tribRetCof, 2, ',', '.') }}) podem ser compensadas na apuração.</p>
                @endif
            </div>
        </div>
        @endif

        {{-- Card Cliente --}}
        <div class="efd-animate bg-white rounded-xl border border-gray-200 shadow-sm mb-6" style="animation-delay: 0.15s">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-base font-semibold text-gray-900">Cliente Associado</h2>
            </div>
            <div class="p-6">
                @if($importacao->cliente)
                    <div class="flex items-center gap-4 flex-wrap">
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">Razão Social</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $importacao->cliente->razao_social }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">{{ $importacao->cliente->tipo_pessoa === 'PJ' ? 'CNPJ' : 'CPF' }}</p>
                            <p class="text-sm font-mono text-gray-900">{{ $importacao->cliente->documento_formatado ?? $importacao->cliente->documento ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">Tipo</p>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-700">
                                {{ $importacao->cliente->tipo_pessoa === 'PF' ? 'Pessoa Física' : 'Pessoa Jurídica' }}
                            </span>
                        </div>
                        <div class="ml-auto">
                            @php
                                $docBusca = $importacao->cliente->documento ?? '';
                            @endphp
                            <a
                                href="/app/clientes?search={{ urlencode($docBusca) }}"
                                data-link
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-300 bg-white text-gray-700 text-xs font-semibold hover:bg-gray-50 transition"
                            >
                                Ver no cadastro
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-400 italic">Nenhum cliente associado a esta importação.</p>
                @endif
            </div>
        </div>

        {{-- Participantes --}}
        <div class="efd-animate bg-white rounded-xl border border-gray-200 shadow-sm" id="participantes-section" style="animation-delay: 0.2s">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between gap-4 flex-wrap">
                <h2 class="text-base font-semibold text-gray-900">
                    Participantes
                    @if($participantes->total() > 0)
                        <span class="ml-1.5 px-2 py-0.5 bg-gray-100 text-gray-600 text-xs font-medium rounded-full">{{ $participantes->total() }}</span>
                    @endif
                </h2>
                @if($participantes->total() > 0)
                <div class="relative">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        id="busca-participantes-efd"
                        placeholder="Buscar participante..."
                        class="pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-64"
                    >
                </div>
                @endif
            </div>

            @if($participantes->total() > 0)
            {{-- Desktop: Table --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="tabela-participantes-efd">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">CNPJ/CPF</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Razão Social</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">UF</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Endereço</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Inscrição Estadual</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="tbody-participantes-efd">
                        @foreach($participantes as $part)
                        <tr
                            class="hover:bg-gray-50 cursor-pointer transition-colors"
                            data-href="/app/participante/{{ $part->id }}"
                            data-razao="{{ strtolower($part->razao_social ?? '') }}"
                            data-doc="{{ $part->cnpj ?? $part->cpf ?? '' }}"
                        >
                            <td class="px-6 py-4 text-sm font-mono text-gray-900 whitespace-nowrap">{{ $part->cnpj ?? $part->cpf ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 max-w-[280px] truncate">{{ $part->razao_social ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">{{ $part->uf ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">{{ $part->endereco ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">{{ $part->inscricao_estadual ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile: Cards --}}
            <div class="md:hidden divide-y divide-gray-200" id="mobile-participantes-efd">
                @foreach($participantes as $part)
                <div
                    class="px-4 py-4 cursor-pointer hover:bg-gray-50 transition-colors"
                    data-href="/app/participante/{{ $part->id }}"
                    data-razao="{{ strtolower($part->razao_social ?? '') }}"
                    data-doc="{{ $part->cnpj ?? $part->cpf ?? '' }}"
                >
                    <p class="text-sm font-medium text-gray-900">{{ $part->razao_social ?? '—' }}</p>
                    <p class="text-xs font-mono text-gray-500 mt-0.5">{{ $part->cnpj ?? $part->cpf ?? '—' }}</p>
                    <div class="flex items-center gap-3 mt-1 text-xs text-gray-400">
                        @if($part->uf) <span>{{ $part->uf }}</span> @endif
                        @if($part->endereco) <span>&middot;</span><span>{{ $part->endereco }}</span> @endif
                        @if($part->inscricao_estadual) <span>&middot;</span><span>IE: {{ $part->inscricao_estadual }}</span> @endif
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Paginação --}}
            @if($participantes->hasPages())
            <div class="px-6 py-4 flex items-center justify-between gap-4 text-sm border-t border-gray-100">
                <span class="text-gray-500 text-xs">
                    Mostrando {{ $participantes->firstItem() }}–{{ $participantes->lastItem() }} de {{ $participantes->total() }} participantes
                </span>
                <div class="flex items-center gap-1">
                    @if($participantes->onFirstPage())
                        <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-300 text-xs cursor-not-allowed">Anterior</span>
                    @else
                        <a href="{{ $participantes->previousPageUrl() }}" data-link class="px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 text-xs font-medium hover:bg-gray-50 transition">Anterior</a>
                    @endif

                    <span class="px-3 py-1.5 text-xs text-gray-500">{{ $participantes->currentPage() }} / {{ $participantes->lastPage() }}</span>

                    @if($participantes->hasMorePages())
                        <a href="{{ $participantes->nextPageUrl() }}" data-link class="px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 text-xs font-medium hover:bg-gray-50 transition">Próxima</a>
                    @else
                        <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-300 text-xs cursor-not-allowed">Próxima</span>
                    @endif
                </div>
            </div>
            @endif

            {{-- Zero-state de busca --}}
            <div id="zero-state-busca" class="hidden px-6 py-12 text-center">
                <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <p class="text-sm text-gray-500">Nenhum participante encontrado para esta busca.</p>
            </div>

            @else
            {{-- Zero-state --}}
            <div class="px-6 py-12 text-center">
                @if($importacao->status === 'processando' || $importacao->status === 'pendente')
                    <svg class="w-10 h-10 text-blue-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <p class="text-sm font-medium text-gray-700">Importação em andamento</p>
                    <p class="text-xs text-gray-500 mt-1">Os participantes aparecerão aqui quando o processamento for concluído.</p>
                @elseif($importacao->status === 'erro')
                    <svg class="w-10 h-10 text-red-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm font-medium text-gray-700">Nenhum participante extraído</p>
                    <p class="text-xs text-gray-500 mt-1">A importação terminou com erro. Nenhum participante foi extraído.</p>
                @else
                    <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <p class="text-sm font-medium text-gray-700">Nenhum participante encontrado</p>
                    <p class="text-xs text-gray-500 mt-1">Esta importação não gerou participantes.</p>
                @endif
            </div>
            @endif
        </div>

        {{-- Resumo Final de Notas EFD --}}
        @if(!empty($resumoFinal))
        <div class="efd-animate bg-white rounded-xl border border-gray-200 shadow-sm mt-6" style="animation-delay: 0.25s" id="resumo-final-section">

            {{-- Mini-painel de totais --}}
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-base font-semibold text-gray-900 mb-3">Resumo de Notas Importadas</h2>
                <div class="font-mono text-sm bg-gray-50 rounded-lg p-3 border border-gray-200 space-y-1" id="resumo-final-detalhes-content">
                    @php
                        $rf = $resumoFinal;
                        $nomesBloco = [
                            'notas_servicos' => 'Notas de Serviço (PIS/COFINS)',
                            'notas_mercadorias' => 'NF-e Mercadorias (ICMS/IPI)',
                            'notas_transportes' => 'CT-e Transportes',
                            // Retrocompatibilidade com dados antigos
                            'A' => 'Notas de Serviço (PIS/COFINS)',
                            'C' => 'NF-e Mercadorias (ICMS/IPI)',
                            'D' => 'CT-e Transportes',
                        ];
                    @endphp

                    {{-- Participantes — normaliza tanto rf.participantes (spec) quanto rf.estatisticas (n8n atual) --}}
                    @php
                        $rfParticipantes = $rf['participantes'] ?? null;
                        if (!$rfParticipantes && !empty($rf['estatisticas'])) {
                            $rfParticipantes = [
                                'total'      => ($rf['estatisticas']['participantes_novos'] ?? 0)
                                              + ($rf['estatisticas']['participantes_repetidos'] ?? 0),
                                'novos'      => $rf['estatisticas']['participantes_novos'] ?? 0,
                                'duplicados' => $rf['estatisticas']['participantes_repetidos'] ?? 0,
                            ];
                        }
                    @endphp
                    @if(!empty($rfParticipantes))
                    <div class="flex items-center gap-2 py-1">
                        <span class="text-green-600 font-bold w-4">✓</span>
                        <span class="w-52 text-gray-700">Participantes</span>
                        <span class="text-gray-900 font-medium">{{ $rfParticipantes['total'] ?? 0 }} registros</span>
                        <span class="text-gray-400 text-xs ml-2">{{ $rfParticipantes['novos'] ?? 0 }} novos · {{ $rfParticipantes['duplicados'] ?? 0 }} já existentes</span>
                    </div>
                    @endif

                    {{-- Produtos e Serviços (catálogo 0200) --}}
                    @if(!empty($rf['produtos_servicos']))
                    @php $ps = $rf['produtos_servicos']; @endphp
                    <div class="flex items-center gap-2 py-1">
                        <span class="text-green-600 font-bold w-4">✓</span>
                        <span class="w-52 text-gray-700">Produtos e Serviços</span>
                        <span class="text-gray-900 font-medium">{{ $ps['total'] ?? 0 }} itens</span>
                        <span class="text-gray-400 text-xs ml-2">{{ $ps['novos'] ?? 0 }} novos · {{ $ps['existentes'] ?? 0 }} já existentes</span>
                    </div>
                    @endif

                    {{-- Blocos --}}
                    @foreach(['notas_servicos', 'notas_mercadorias', 'notas_transportes', 'A', 'C', 'D'] as $bloco)
                        @if(isset($rf['blocos'][$bloco]))
                            @php
                                $bd = $rf['blocos'][$bloco];
                                $isSkip = ($bd['total_notas'] ?? 0) == 0 && ($bd['valor_total'] ?? 0) == 0;
                            @endphp
                            <div class="flex items-center gap-2 py-1">
                                @if($isSkip)
                                    <span class="text-gray-400 w-4">—</span>
                                @else
                                    <span class="text-green-600 font-bold w-4">✓</span>
                                @endif
                                <span class="w-52 text-gray-700">{{ $nomesBloco[$bloco] ?? 'Bloco '.$bloco }}</span>
                                @if($isSkip)
                                    <span class="text-gray-400 text-xs">Vazio</span>
                                @else
                                    <span class="text-gray-900 font-medium">{{ $bd['total_notas'] ?? 0 }} notas</span>
                                    <span class="text-gray-500 text-xs ml-2">R$ {{ number_format($bd['valor_total'] ?? 0, 2, ',', '.') }}</span>
                                @endif
                            </div>
                        @endif
                    @endforeach

                    {{-- Total --}}
                    @if(!empty($rf['totais']))
                    <div class="border-t border-gray-300 pt-1 mt-1 flex items-center gap-2 py-1">
                        <span class="w-4"></span>
                        <span class="w-52 text-gray-700 font-semibold">Total</span>
                        <span class="text-gray-900 font-bold">{{ $rf['totais']['notas'] ?? 0 }} notas</span>
                        <span class="text-gray-500 text-xs ml-2">R$ {{ number_format($rf['totais']['valor'] ?? 0, 2, ',', '.') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Tabela de participantes enriquecida --}}
            @if(!empty($rf['participantes_resumo']) && $participantes->count() > 0)
            @php
                $resumoIndexado = collect($rf['participantes_resumo'])->keyBy('participante_id');
            @endphp
            <div class="px-6 py-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Participantes — Detalhes de Notas</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm" id="tabela-notas-participantes-detalhes">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">CNPJ/CPF</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Razão Social</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Notas</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Entradas</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Saídas</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="tbody-notas-participantes-detalhes">
                            @foreach($participantes as $part)
                            @php
                                $pr = $resumoIndexado->get($part->id);
                                $temNotas = $pr && !empty($pr['nota_ids']);
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors" data-participante-id="{{ $part->id }}">
                                <td class="px-4 py-3 text-xs font-mono text-gray-900 whitespace-nowrap">{{ $part->cnpj ?? $part->cpf ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 max-w-[240px] truncate" title="{{ $part->razao_social ?? '' }}">{{ $part->razao_social ?? '—' }}</td>
                                <td class="px-4 py-3 text-right text-xs">
                                    @if($pr)
                                        <span class="font-medium text-gray-900">{{ $pr['total_notas'] ?? 0 }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-xs">
                                    @if($pr && isset($pr['entradas']))
                                        <span class="text-green-700">{{ $pr['entradas']['count'] ?? 0 }}</span>
                                        <span class="text-gray-400 ml-1">R$ {{ number_format($pr['entradas']['valor'] ?? 0, 2, ',', '.') }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-xs">
                                    @if($pr && isset($pr['saidas']))
                                        <span class="text-amber-700">{{ $pr['saidas']['count'] ?? 0 }}</span>
                                        <span class="text-gray-400 ml-1">R$ {{ number_format($pr['saidas']['valor'] ?? 0, 2, ',', '.') }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($pr)
                                        <button
                                            type="button"
                                            class="btn-expand-notas-detalhes text-blue-600 hover:text-blue-800 text-xs font-medium px-1.5 py-0.5 rounded border border-blue-200 hover:bg-blue-50 transition"
                                            data-participante-id="{{ $part->id }}"
                                            data-importacao-id="{{ $importacao->id }}"
                                            data-nota-ids="{{ json_encode($pr['nota_ids'] ?? []) }}"
                                            data-bi="{{ json_encode($pr['bi'] ?? []) }}"
                                            title="Ver notas"
                                        >▶</button>
                                        @endif
                                        <a href="/app/participante/{{ $part->id }}" class="text-xs font-medium text-blue-600 hover:underline" data-link>Ver</a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{-- Paginação --}}
                    @if($participantes->hasPages())
                    <div class="mt-4 flex items-center justify-between gap-4 text-sm">
                        <span class="text-gray-500 text-xs">
                            Mostrando {{ $participantes->firstItem() }}–{{ $participantes->lastItem() }} de {{ $participantes->total() }} participantes
                        </span>
                        <div class="flex items-center gap-1">
                            @if($participantes->onFirstPage())
                                <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-300 text-xs cursor-not-allowed">Anterior</span>
                            @else
                                <a href="{{ $participantes->previousPageUrl() }}" data-link class="px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 text-xs font-medium hover:bg-gray-50 transition">Anterior</a>
                            @endif

                            <span class="px-3 py-1.5 text-xs text-gray-500">{{ $participantes->currentPage() }} / {{ $participantes->lastPage() }}</span>

                            @if($participantes->hasMorePages())
                                <a href="{{ $participantes->nextPageUrl() }}" data-link class="px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 text-xs font-medium hover:bg-gray-50 transition">Próxima</a>
                            @else
                                <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-300 text-xs cursor-not-allowed">Próxima</span>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- Catálogo de Itens — Registro 0200                               --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        @if(isset($catalogoItens) && ($catalogoItens instanceof \Illuminate\Pagination\LengthAwarePaginator ? $catalogoItens->total() > 0 : $catalogoItens->count() > 0))
        @php $totalCatalogo = $catalogoItens instanceof \Illuminate\Pagination\LengthAwarePaginator ? $catalogoItens->total() : $catalogoItens->count(); @endphp
        <div class="efd-animate bg-white rounded-xl border border-gray-200 shadow-sm mt-6" id="catalogo-section" style="animation-delay: 0.3s">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between gap-4 flex-wrap">
                <h2 class="text-base font-semibold text-gray-900">
                    Catálogo de Produtos/Serviços
                    <span class="ml-1.5 px-2 py-0.5 bg-gray-100 text-gray-600 text-xs font-medium rounded-full">{{ $totalCatalogo }}</span>
                </h2>
                <div class="relative">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" id="busca-catalogo" placeholder="Buscar por código, descrição ou NCM..." class="pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-72">
                </div>
            </div>

            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm" id="tabela-catalogo">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">NCM</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unidade</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Alíq. ICMS</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="tbody-catalogo">
                        @foreach($catalogoItens as $item)
                        <tr class="hover:bg-gray-50 transition-colors" data-cod="{{ strtolower($item->cod_item ?? '') }}" data-desc="{{ strtolower($item->descr_item ?? '') }}" data-ncm="{{ $item->cod_ncm ?? '' }}">
                            <td class="px-4 py-2.5 text-xs font-mono text-gray-900 whitespace-nowrap">{{ $item->cod_item ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-sm text-gray-900 max-w-[320px] truncate" title="{{ $item->descr_item ?? '' }}">{{ $item->descr_item ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-xs font-mono text-gray-700">{{ $item->cod_ncm ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-xs text-gray-600">{{ $item->tipo_item ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-xs text-gray-600">{{ $item->unid_inv ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-xs text-right font-mono text-gray-700">{{ $item->aliq_icms ? number_format($item->aliq_icms, 2, ',', '.') . '%' : '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile cards --}}
            <div class="md:hidden divide-y divide-gray-200" id="mobile-catalogo">
                @foreach($catalogoItens as $item)
                <div class="px-4 py-3" data-cod="{{ strtolower($item->cod_item ?? '') }}" data-desc="{{ strtolower($item->descr_item ?? '') }}" data-ncm="{{ $item->cod_ncm ?? '' }}">
                    <p class="text-sm font-medium text-gray-900">{{ $item->descr_item ?? '—' }}</p>
                    <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
                        <span class="font-mono">{{ $item->cod_item ?? '—' }}</span>
                        @if($item->cod_ncm)<span>&middot;</span><span>NCM: {{ $item->cod_ncm }}</span>@endif
                        @if($item->aliq_icms)<span>&middot;</span><span>ICMS: {{ number_format($item->aliq_icms, 2, ',', '.') }}%</span>@endif
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Paginação --}}
            @if($catalogoItens instanceof \Illuminate\Pagination\LengthAwarePaginator && $catalogoItens->hasPages())
            <div class="px-6 py-4 flex items-center justify-between gap-4 text-sm border-t border-gray-100">
                <span class="text-gray-500 text-xs">Mostrando {{ $catalogoItens->firstItem() }}–{{ $catalogoItens->lastItem() }} de {{ $catalogoItens->total() }} itens</span>
                <div class="flex items-center gap-1">
                    @if($catalogoItens->onFirstPage())
                        <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-300 text-xs cursor-not-allowed">Anterior</span>
                    @else
                        <a href="{{ $catalogoItens->previousPageUrl() }}" data-link class="px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 text-xs font-medium hover:bg-gray-50 transition">Anterior</a>
                    @endif
                    <span class="px-3 py-1.5 text-xs text-gray-500">{{ $catalogoItens->currentPage() }} / {{ $catalogoItens->lastPage() }}</span>
                    @if($catalogoItens->hasMorePages())
                        <a href="{{ $catalogoItens->nextPageUrl() }}" data-link class="px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 text-xs font-medium hover:bg-gray-50 transition">Próxima</a>
                    @else
                        <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-300 text-xs cursor-not-allowed">Próxima</span>
                    @endif
                </div>
            </div>
            @endif

            {{-- Zero-state busca --}}
            <div id="zero-state-catalogo" class="hidden px-6 py-8 text-center">
                <p class="text-sm text-gray-500">Nenhum item encontrado para esta busca.</p>
            </div>
        </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- Apuração ICMS/IPI — Bloco E (só EFD ICMS/IPI)                   --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        @if($apuracaoIcms)
        @php $ai = $apuracaoIcms; @endphp
        <div class="efd-animate bg-white rounded-xl border border-gray-200 shadow-sm mt-6" id="apuracao-icms-section" style="animation-delay: 0.35s">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Apuração ICMS/IPI — Bloco E</h2>
                    @if($ai->periodo_inicio && $ai->periodo_fim)
                    <p class="text-xs text-gray-500 mt-0.5">Período: {{ $ai->periodo_inicio->format('d/m/Y') }} a {{ $ai->periodo_fim->format('d/m/Y') }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    @if($ai->tem_st)<span class="px-2 py-0.5 text-xs font-medium rounded-full bg-amber-100 text-amber-700">ICMS-ST</span>@endif
                    @if($ai->tem_difal)<span class="px-2 py-0.5 text-xs font-medium rounded-full bg-cyan-100 text-cyan-700">DIFAL/FCP</span>@endif
                    @if($ai->tem_ipi)<span class="px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">IPI</span>@endif
                </div>
            </div>
            <div class="p-6">
                {{-- ICMS Próprio (E110) --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span> ICMS Próprio
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-2 text-sm">
                        @php
                            $icmsCampos = [
                                ['Total de Débitos', $ai->icms_tot_debitos],
                                ['Ajustes a Débito', $ai->icms_aj_debitos],
                                ['Total Ajustes Débito', $ai->icms_tot_aj_debitos],
                                ['Estornos de Crédito', $ai->icms_estornos_credito],
                                ['Total de Créditos', $ai->icms_tot_creditos],
                                ['Ajustes a Crédito', $ai->icms_aj_creditos],
                                ['Total Ajustes Crédito', $ai->icms_tot_aj_creditos],
                                ['Estornos de Débito', $ai->icms_estornos_debito],
                                ['Saldo Credor Anterior', $ai->icms_sld_credor_ant],
                                ['Saldo Apurado', $ai->icms_sld_apurado],
                                ['Total Deduções', $ai->icms_tot_deducoes],
                                ['Débitos Especiais', $ai->icms_deb_especiais],
                            ];
                        @endphp
                        @foreach($icmsCampos as $campo)
                            <div class="flex justify-between items-center py-1">
                                <span class="text-gray-600">{{ $campo[0] }}</span>
                                <span class="text-gray-700 font-mono text-xs">R$ {{ number_format($campo[1] ?? 0, 2, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-200 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex justify-between items-center bg-blue-50 rounded-lg px-4 py-2">
                            <span class="text-sm font-semibold text-blue-900">ICMS a Recolher</span>
                            <span class="text-sm font-bold text-blue-700">R$ {{ number_format($ai->icms_a_recolher ?? 0, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center bg-gray-50 rounded-lg px-4 py-2">
                            <span class="text-sm font-medium text-gray-700">Saldo Credor a Transportar</span>
                            <span class="text-sm font-bold text-gray-900">R$ {{ number_format($ai->icms_sld_credor_transportar ?? 0, 2, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Obrigações ICMS (E116) --}}
                    @if(!empty($ai->icms_obrigacoes['items']))
                    <div class="mt-3">
                        <button type="button" class="efd-collapse-toggle text-xs font-medium text-blue-600 hover:text-blue-800 flex items-center gap-1" data-target="obrigacoes-icms">
                            <svg class="w-3.5 h-3.5 transition-transform efd-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            Obrigações a Recolher ({{ count($ai->icms_obrigacoes['items']) }})
                        </button>
                        <div id="obrigacoes-icms" class="hidden mt-2 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-xs">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 uppercase">Cód. Obrigação</th>
                                        <th class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 uppercase">Valor</th>
                                        <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 uppercase">Vencimento</th>
                                        <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 uppercase">Cód. Receita</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($ai->icms_obrigacoes['items'] as $obr)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-1.5 font-mono">{{ $obr['cod_obrigacao'] ?? $obr['COD_OR'] ?? '—' }}</td>
                                        <td class="px-3 py-1.5 text-right font-mono">R$ {{ number_format((float)($obr['valor'] ?? $obr['VL_OR'] ?? 0), 2, ',', '.') }}</td>
                                        <td class="px-3 py-1.5">{{ $obr['data_vencimento'] ?? $obr['DT_VCTO'] ?? '—' }}</td>
                                        <td class="px-3 py-1.5 font-mono">{{ $obr['cod_receita'] ?? $obr['COD_REC'] ?? '—' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- ICMS-ST (E210) — condicional --}}
                @if($ai->tem_st)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> ICMS Substituição Tributária
                        @if($ai->st_uf)<span class="text-xs text-gray-500 font-normal ml-1">UF: {{ $ai->st_uf }}</span>@endif
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-2 text-sm">
                        @php
                            $stCampos = [
                                ['Saldo Credor Anterior', $ai->st_sld_credor_ant],
                                ['Devoluções', $ai->st_devolucoes],
                                ['Ressarcimentos', $ai->st_ressarcimentos],
                                ['Outros Créditos', $ai->st_outros_creditos],
                                ['Ajustes a Crédito', $ai->st_aj_creditos],
                                ['Retenção', $ai->st_retencao],
                                ['Outros Débitos', $ai->st_outros_debitos],
                                ['Ajustes a Débito', $ai->st_aj_debitos],
                                ['Saldo Devedor Anterior', $ai->st_sld_devedor_ant],
                                ['Deduções', $ai->st_deducoes],
                                ['Débitos Especiais', $ai->st_deb_especiais],
                            ];
                        @endphp
                        @foreach($stCampos as $campo)
                            <div class="flex justify-between items-center py-1">
                                <span class="text-gray-600">{{ $campo[0] }}</span>
                                <span class="text-gray-700 font-mono text-xs">R$ {{ number_format($campo[1] ?? 0, 2, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-200 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex justify-between items-center bg-amber-50 rounded-lg px-4 py-2">
                            <span class="text-sm font-semibold text-amber-900">ICMS-ST a Recolher</span>
                            <span class="text-sm font-bold text-amber-700">R$ {{ number_format($ai->st_icms_recolher ?? 0, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center bg-gray-50 rounded-lg px-4 py-2">
                            <span class="text-sm font-medium text-gray-700">Saldo Credor a Transportar</span>
                            <span class="text-sm font-bold text-gray-900">R$ {{ number_format($ai->st_sld_credor_transportar ?? 0, 2, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Obrigações ICMS-ST (E250) --}}
                    @if(!empty($ai->st_obrigacoes['items']))
                    <div class="mt-3">
                        <button type="button" class="efd-collapse-toggle text-xs font-medium text-amber-600 hover:text-amber-800 flex items-center gap-1" data-target="obrigacoes-st">
                            <svg class="w-3.5 h-3.5 transition-transform efd-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            Obrigações ST a Recolher ({{ count($ai->st_obrigacoes['items']) }})
                        </button>
                        <div id="obrigacoes-st" class="hidden mt-2 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-xs">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 uppercase">Cód. Obrigação</th>
                                        <th class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 uppercase">Valor</th>
                                        <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 uppercase">Vencimento</th>
                                        <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 uppercase">Cód. Receita</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($ai->st_obrigacoes['items'] as $obr)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-1.5 font-mono">{{ $obr['cod_obrigacao'] ?? $obr['COD_OR'] ?? '—' }}</td>
                                        <td class="px-3 py-1.5 text-right font-mono">R$ {{ number_format((float)($obr['valor'] ?? $obr['VL_OR'] ?? 0), 2, ',', '.') }}</td>
                                        <td class="px-3 py-1.5">{{ $obr['data_vencimento'] ?? $obr['DT_VCTO'] ?? '—' }}</td>
                                        <td class="px-3 py-1.5 font-mono">{{ $obr['cod_receita'] ?? $obr['COD_REC'] ?? '—' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                {{-- DIFAL/FCP (E300/E310) — condicional --}}
                @if($ai->tem_difal && !empty($ai->difal_fcp))
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-cyan-500"></span> DIFAL/FCP — Diferencial de Alíquota
                    </h3>
                    @php $difal = $ai->difal_fcp; @endphp
                    @if(!empty($difal['items']))
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 uppercase">UF</th>
                                    <th class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 uppercase">DIFAL Origem</th>
                                    <th class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 uppercase">DIFAL Destino</th>
                                    <th class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 uppercase">FCP</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($difal['items'] as $d)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-1.5 text-xs font-medium">{{ $d['UF'] ?? $d['uf'] ?? '—' }}</td>
                                    <td class="px-3 py-1.5 text-xs text-right font-mono">R$ {{ number_format((float)($d['VL_SLD_DEV_ANT_DIFAL'] ?? $d['difal_origem'] ?? 0), 2, ',', '.') }}</td>
                                    <td class="px-3 py-1.5 text-xs text-right font-mono">R$ {{ number_format((float)($d['VL_ICMS_RECOLHER_DIFAL'] ?? $d['difal_destino'] ?? $d['icms_recolher'] ?? 0), 2, ',', '.') }}</td>
                                    <td class="px-3 py-1.5 text-xs text-right font-mono">R$ {{ number_format((float)($d['VL_FCP_RECOLHER'] ?? $d['fcp'] ?? 0), 2, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600">UF</span>
                            <span class="text-gray-700">{{ $difal['UF'] ?? $difal['uf'] ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600">DIFAL a Recolher</span>
                            <span class="text-gray-700 font-mono">R$ {{ number_format((float)($difal['VL_ICMS_RECOLHER_DIFAL'] ?? $difal['icms_recolher'] ?? 0), 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600">FCP a Recolher</span>
                            <span class="text-gray-700 font-mono">R$ {{ number_format((float)($difal['VL_FCP_RECOLHER'] ?? $difal['fcp'] ?? 0), 2, ',', '.') }}</span>
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                {{-- IPI (E500/E520) — condicional --}}
                @if($ai->tem_ipi && !empty($ai->ipi))
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> IPI — Imposto sobre Produtos Industrializados
                    </h3>
                    @php $ipiData = $ai->ipi; @endphp
                    @if(!empty($ipiData['items']))
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 uppercase">Período</th>
                                    <th class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 uppercase">Débitos</th>
                                    <th class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 uppercase">Créditos</th>
                                    <th class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 uppercase">Saldo</th>
                                    <th class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 uppercase">A Recolher</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($ipiData['items'] as $ip)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-1.5 text-xs">{{ ($ip['DT_INI'] ?? $ip['periodo_inicio'] ?? '—') }} a {{ ($ip['DT_FIN'] ?? $ip['periodo_fim'] ?? '') }}</td>
                                    <td class="px-3 py-1.5 text-xs text-right font-mono">R$ {{ number_format((float)($ip['VL_TOT_DEBITOS'] ?? $ip['debitos'] ?? 0), 2, ',', '.') }}</td>
                                    <td class="px-3 py-1.5 text-xs text-right font-mono">R$ {{ number_format((float)($ip['VL_TOT_CREDITOS'] ?? $ip['creditos'] ?? 0), 2, ',', '.') }}</td>
                                    <td class="px-3 py-1.5 text-xs text-right font-mono">R$ {{ number_format((float)($ip['VL_SLD_APURADO'] ?? $ip['saldo'] ?? 0), 2, ',', '.') }}</td>
                                    <td class="px-3 py-1.5 text-xs text-right font-mono font-semibold">R$ {{ number_format((float)($ip['VL_IPI_RECOLHER'] ?? $ip['a_recolher'] ?? 0), 2, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600">Débitos</span>
                            <span class="text-gray-700 font-mono">R$ {{ number_format((float)($ipiData['VL_TOT_DEBITOS'] ?? $ipiData['debitos'] ?? 0), 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600">Créditos</span>
                            <span class="text-gray-700 font-mono">R$ {{ number_format((float)($ipiData['VL_TOT_CREDITOS'] ?? $ipiData['creditos'] ?? 0), 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600">Saldo Apurado</span>
                            <span class="text-gray-700 font-mono">R$ {{ number_format((float)($ipiData['VL_SLD_APURADO'] ?? $ipiData['saldo'] ?? 0), 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1 font-semibold">
                            <span class="text-gray-900">IPI a Recolher</span>
                            <span class="text-emerald-700 font-mono">R$ {{ number_format((float)($ipiData['VL_IPI_RECOLHER'] ?? $ipiData['a_recolher'] ?? 0), 2, ',', '.') }}</span>
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Total Geral --}}
                <div class="mt-6 pt-4 border-t-2 border-gray-300 flex justify-between items-center">
                    <span class="text-base font-bold text-gray-900">Total a Recolher (ICMS + ST)</span>
                    <span class="text-lg font-bold text-blue-700">R$ {{ number_format($ai->total_recolher ?? 0, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- Retenções na Fonte PIS/COFINS — F600                             --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        @if(isset($retencoesFonte) && $retencoesFonte->isNotEmpty())
        <div class="efd-animate bg-white rounded-xl border border-gray-200 shadow-sm mt-6" id="retencoes-section" style="animation-delay: 0.4s">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-base font-semibold text-gray-900">Retenções na Fonte PIS/COFINS — F600</h2>
                <p class="text-xs text-gray-500 mt-0.5">{{ $retencoesFonte->count() }} retenções encontradas</p>
            </div>
            <div class="p-6">
                {{-- Resumo --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                    <div class="bg-gray-50 rounded-lg px-4 py-3 text-center">
                        <p class="text-xs text-gray-500">Total Retenções</p>
                        <p class="text-lg font-bold text-gray-900">{{ $retencoesFonte->count() }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg px-4 py-3 text-center">
                        <p class="text-xs text-gray-500">Base de Cálculo</p>
                        <p class="text-sm font-bold text-gray-900">R$ {{ number_format($retencoesFonte->sum('base_calculo'), 2, ',', '.') }}</p>
                    </div>
                    <div class="bg-blue-50 rounded-lg px-4 py-3 text-center">
                        <p class="text-xs text-blue-600">PIS Retido</p>
                        <p class="text-sm font-bold text-blue-700">R$ {{ number_format($retencoesFonte->sum('valor_pis'), 2, ',', '.') }}</p>
                    </div>
                    <div class="bg-purple-50 rounded-lg px-4 py-3 text-center">
                        <p class="text-xs text-purple-600">COFINS Retido</p>
                        <p class="text-sm font-bold text-purple-700">R$ {{ number_format($retencoesFonte->sum('valor_cofins'), 2, ',', '.') }}</p>
                    </div>
                </div>

                {{-- Tabela Desktop --}}
                <div class="hidden sm:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">CNPJ</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Natureza</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Base Cálculo</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">PIS</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">COFINS</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($retencoesFonte as $ret)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-2.5 text-xs font-mono text-gray-900 whitespace-nowrap">{{ $ret->cnpj_formatado }}</td>
                                <td class="px-4 py-2.5 text-xs text-gray-700">{{ $ret->data_retencao?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-4 py-2.5 text-xs text-gray-700">{{ $ret->natureza_formatada }}</td>
                                <td class="px-4 py-2.5 text-xs text-right text-gray-700 font-mono">R$ {{ number_format($ret->base_calculo ?? 0, 2, ',', '.') }}</td>
                                <td class="px-4 py-2.5 text-xs text-right text-blue-700 font-mono">R$ {{ number_format($ret->valor_pis ?? 0, 2, ',', '.') }}</td>
                                <td class="px-4 py-2.5 text-xs text-right text-purple-700 font-mono">R$ {{ number_format($ret->valor_cofins ?? 0, 2, ',', '.') }}</td>
                                <td class="px-4 py-2.5 text-xs text-right text-gray-900 font-semibold font-mono">R$ {{ number_format($ret->valor_total ?? 0, 2, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Cards Mobile --}}
                <div class="sm:hidden space-y-3">
                    @foreach($retencoesFonte as $ret)
                    <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                        <div class="flex justify-between items-start">
                            <span class="text-xs font-mono text-gray-900">{{ $ret->cnpj_formatado }}</span>
                            <span class="text-xs text-gray-500">{{ $ret->data_retencao?->format('d/m/Y') ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-gray-600">{{ $ret->natureza_formatada }}</span>
                            <span class="font-semibold text-gray-900">R$ {{ number_format($ret->valor_total ?? 0, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex gap-4 text-xs">
                            <span class="text-blue-700">PIS: R$ {{ number_format($ret->valor_pis ?? 0, 2, ',', '.') }}</span>
                            <span class="text-purple-700">COFINS: R$ {{ number_format($ret->valor_cofins ?? 0, 2, ',', '.') }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- Apuração PIS/COFINS — Bloco M (só EFD Contribuições)            --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        @if($apuracaoContribuicao)
        @php
            $ac = $apuracaoContribuicao;
            $regimeBadge = match($ac->regime) {
                'nao_cumulativo' => ['bg-blue-100 text-blue-700', 'Não Cumulativo (Lucro Real)'],
                'misto'          => ['bg-amber-100 text-amber-700', 'Misto'],
                default          => ['bg-gray-100 text-gray-700', 'Cumulativo (Lucro Presumido)'],
            };
        @endphp
        <div class="efd-animate bg-white rounded-xl border border-gray-200 shadow-sm mt-6" id="apuracao-pis-cofins-section" style="animation-delay: 0.45s">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-900">Apuração PIS/COFINS — Bloco M</h2>
                <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $regimeBadge[0] }}">{{ $regimeBadge[1] }}</span>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- PIS --}}
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span> PIS
                        </h3>
                        <div class="space-y-2 text-sm">
                            @php
                                $pisCampos = [
                                    ['Contribuição Não Cumulativa', $ac->pis_nao_cumulativo],
                                    ['(-) Crédito Descontado', $ac->pis_credito_descontado],
                                    ['(-) Crédito Descontado Anterior', $ac->pis_credito_desc_ant],
                                    ['(=) Devida NC', $ac->pis_nc_devida],
                                    ['(-) Retenção NC', $ac->pis_retencao_nc],
                                    ['(-) Outras Deduções NC', $ac->pis_outras_deducoes_nc],
                                    ['(=) PIS NC a Recolher', $ac->pis_nc_recolher, true],
                                    ['Contribuição Cumulativa', $ac->pis_cumulativo],
                                    ['(-) Retenção Cumulativa', $ac->pis_retencao_cum],
                                    ['(-) Outras Deduções Cum.', $ac->pis_outras_deducoes_cum],
                                    ['(=) PIS Cum. a Recolher', $ac->pis_cum_recolher, true],
                                ];
                            @endphp
                            @foreach($pisCampos as $campo)
                                <div class="flex justify-between items-center {{ ($campo[2] ?? false) ? 'pt-2 border-t border-gray-200 font-semibold' : '' }}">
                                    <span class="text-gray-600">{{ $campo[0] }}</span>
                                    <span class="{{ ($campo[2] ?? false) ? 'text-gray-900' : 'text-gray-700' }}">R$ {{ number_format($campo[1] ?? 0, 2, ',', '.') }}</span>
                                </div>
                            @endforeach
                            <div class="flex justify-between items-center pt-2 border-t-2 border-gray-300 font-bold text-gray-900">
                                <span>Total PIS a Recolher</span>
                                <span>R$ {{ number_format($ac->pis_total_recolher ?? 0, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- COFINS --}}
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-purple-500"></span> COFINS
                        </h3>
                        <div class="space-y-2 text-sm">
                            @php
                                $cofinsCampos = [
                                    ['Contribuição Não Cumulativa', $ac->cofins_nao_cumulativo],
                                    ['(-) Crédito Descontado', $ac->cofins_credito_descontado],
                                    ['(-) Crédito Descontado Anterior', $ac->cofins_credito_desc_ant],
                                    ['(=) Devida NC', $ac->cofins_nc_devida],
                                    ['(-) Retenção NC', $ac->cofins_retencao_nc],
                                    ['(-) Outras Deduções NC', $ac->cofins_outras_deducoes_nc],
                                    ['(=) COFINS NC a Recolher', $ac->cofins_nc_recolher, true],
                                    ['Contribuição Cumulativa', $ac->cofins_cumulativo],
                                    ['(-) Retenção Cumulativa', $ac->cofins_retencao_cum],
                                    ['(-) Outras Deduções Cum.', $ac->cofins_outras_deducoes_cum],
                                    ['(=) COFINS Cum. a Recolher', $ac->cofins_cum_recolher, true],
                                ];
                            @endphp
                            @foreach($cofinsCampos as $campo)
                                <div class="flex justify-between items-center {{ ($campo[2] ?? false) ? 'pt-2 border-t border-gray-200 font-semibold' : '' }}">
                                    <span class="text-gray-600">{{ $campo[0] }}</span>
                                    <span class="{{ ($campo[2] ?? false) ? 'text-gray-900' : 'text-gray-700' }}">R$ {{ number_format($campo[1] ?? 0, 2, ',', '.') }}</span>
                                </div>
                            @endforeach
                            <div class="flex justify-between items-center pt-2 border-t-2 border-gray-300 font-bold text-gray-900">
                                <span>Total COFINS a Recolher</span>
                                <span>R$ {{ number_format($ac->cofins_total_recolher ?? 0, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Total Geral --}}
                <div class="mt-6 pt-4 border-t-2 border-gray-300 flex justify-between items-center">
                    <span class="text-base font-bold text-gray-900">Total PIS + COFINS a Recolher</span>
                    <span class="text-lg font-bold text-indigo-700">R$ {{ number_format($ac->total_recolher ?? 0, 2, ',', '.') }}</span>
                </div>

                {{-- Indicadores de créditos NC --}}
                @if($ac->tem_creditos_nc)
                <div class="mt-3">
                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Créditos Não Cumulativos Apurados</span>
                </div>
                @endif

                {{-- ── Detalhes por CST — M210 (PIS) e M610 (COFINS) ── --}}
                @if(!empty($ac->pis_detalhes['items']) || !empty($ac->cofins_detalhes['items']))
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <button type="button" class="efd-collapse-toggle text-sm font-semibold text-gray-700 flex items-center gap-2 mb-3" data-target="detalhes-cst">
                        <svg class="w-4 h-4 transition-transform efd-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        Detalhes por CST
                    </button>
                    <div id="detalhes-cst" class="hidden">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            {{-- PIS por CST (M210) --}}
                            @if(!empty($ac->pis_detalhes['items']))
                            <div>
                                <h4 class="text-xs font-semibold text-blue-700 mb-2 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> PIS por CST (M210)
                                </h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-1.5 text-left font-medium text-gray-500">CST</th>
                                                <th class="px-3 py-1.5 text-right font-medium text-gray-500">Base Cálc.</th>
                                                <th class="px-3 py-1.5 text-right font-medium text-gray-500">Alíquota</th>
                                                <th class="px-3 py-1.5 text-right font-medium text-gray-500">Contribuição</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @foreach($ac->pis_detalhes['items'] as $cst)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-3 py-1.5 font-mono font-medium">{{ $cst['COD_CONT'] ?? $cst['cst'] ?? '—' }}</td>
                                                <td class="px-3 py-1.5 text-right font-mono">R$ {{ number_format((float)($cst['VL_BC_CONT'] ?? $cst['base_calculo'] ?? 0), 2, ',', '.') }}</td>
                                                <td class="px-3 py-1.5 text-right font-mono">{{ number_format((float)($cst['ALIQ_PIS'] ?? $cst['aliquota'] ?? 0), 4, ',', '.') }}%</td>
                                                <td class="px-3 py-1.5 text-right font-mono font-semibold">R$ {{ number_format((float)($cst['VL_CONT_APUR'] ?? $cst['valor_contribuicao'] ?? $cst['VL_CONT'] ?? 0), 2, ',', '.') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif

                            {{-- COFINS por CST (M610) --}}
                            @if(!empty($ac->cofins_detalhes['items']))
                            <div>
                                <h4 class="text-xs font-semibold text-purple-700 mb-2 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span> COFINS por CST (M610)
                                </h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-1.5 text-left font-medium text-gray-500">CST</th>
                                                <th class="px-3 py-1.5 text-right font-medium text-gray-500">Base Cálc.</th>
                                                <th class="px-3 py-1.5 text-right font-medium text-gray-500">Alíquota</th>
                                                <th class="px-3 py-1.5 text-right font-medium text-gray-500">Contribuição</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @foreach($ac->cofins_detalhes['items'] as $cst)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-3 py-1.5 font-mono font-medium">{{ $cst['COD_CONT'] ?? $cst['cst'] ?? '—' }}</td>
                                                <td class="px-3 py-1.5 text-right font-mono">R$ {{ number_format((float)($cst['VL_BC_CONT'] ?? $cst['base_calculo'] ?? 0), 2, ',', '.') }}</td>
                                                <td class="px-3 py-1.5 text-right font-mono">{{ number_format((float)($cst['ALIQ_COFINS'] ?? $cst['aliquota'] ?? 0), 4, ',', '.') }}%</td>
                                                <td class="px-3 py-1.5 text-right font-mono font-semibold">R$ {{ number_format((float)($cst['VL_CONT_APUR'] ?? $cst['valor_contribuicao'] ?? $cst['VL_CONT'] ?? 0), 2, ',', '.') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                {{-- ── Receitas Não Tributadas — M400/M410 ── --}}
                @if(!empty($ac->pis_nao_tributado['items']))
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <button type="button" class="efd-collapse-toggle text-sm font-semibold text-gray-700 flex items-center gap-2 mb-3" data-target="receitas-nao-tributadas">
                        <svg class="w-4 h-4 transition-transform efd-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        Receitas Não Tributadas / Isentas ({{ count($ac->pis_nao_tributado['items']) }})
                    </button>
                    <div id="receitas-nao-tributadas" class="hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-xs">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-1.5 text-left font-medium text-gray-500">CST</th>
                                        <th class="px-3 py-1.5 text-left font-medium text-gray-500">Nat. Receita</th>
                                        <th class="px-3 py-1.5 text-right font-medium text-gray-500">Valor PIS</th>
                                        <th class="px-3 py-1.5 text-right font-medium text-gray-500">Valor COFINS</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($ac->pis_nao_tributado['items'] as $nt)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-1.5 font-mono font-medium">{{ $nt['CST_PIS'] ?? $nt['cst'] ?? '—' }}</td>
                                        <td class="px-3 py-1.5">{{ $nt['NAT_REC'] ?? $nt['natureza_receita'] ?? '—' }}</td>
                                        <td class="px-3 py-1.5 text-right font-mono">R$ {{ number_format((float)($nt['VL_REC'] ?? $nt['valor'] ?? 0), 2, ',', '.') }}</td>
                                        <td class="px-3 py-1.5 text-right font-mono">R$ {{ number_format((float)($nt['VL_REC_COFINS'] ?? $nt['valor_cofins'] ?? 0), 2, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                {{-- ── Créditos Não Cumulativos — M100/M110 (PIS) e M500/M510 (COFINS) ── --}}
                @if($ac->tem_creditos_nc)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <button type="button" class="efd-collapse-toggle text-sm font-semibold text-gray-700 flex items-center gap-2 mb-3" data-target="creditos-nc">
                        <svg class="w-4 h-4 transition-transform efd-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        Créditos Não Cumulativos (Lucro Real)
                    </button>
                    <div id="creditos-nc" class="hidden">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            {{-- PIS NC (M100/M105/M110) --}}
                            @if(!empty($ac->pis_creditos_nc['items']))
                            <div>
                                <h4 class="text-xs font-semibold text-blue-700 mb-2 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Créditos PIS (M100/M110)
                                </h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-1.5 text-left font-medium text-gray-500">Tipo</th>
                                                <th class="px-3 py-1.5 text-right font-medium text-gray-500">Base Cálc.</th>
                                                <th class="px-3 py-1.5 text-right font-medium text-gray-500">Alíquota</th>
                                                <th class="px-3 py-1.5 text-right font-medium text-gray-500">Crédito</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @foreach($ac->pis_creditos_nc['items'] as $cr)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-3 py-1.5 font-mono">{{ $cr['COD_CRED'] ?? $cr['tipo'] ?? '—' }}</td>
                                                <td class="px-3 py-1.5 text-right font-mono">R$ {{ number_format((float)($cr['VL_BC_PIS'] ?? $cr['base_calculo'] ?? 0), 2, ',', '.') }}</td>
                                                <td class="px-3 py-1.5 text-right font-mono">{{ number_format((float)($cr['ALIQ_PIS'] ?? $cr['aliquota'] ?? 0), 4, ',', '.') }}%</td>
                                                <td class="px-3 py-1.5 text-right font-mono font-semibold">R$ {{ number_format((float)($cr['VL_CRED'] ?? $cr['valor'] ?? 0), 2, ',', '.') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif

                            {{-- COFINS NC (M500/M505/M510) --}}
                            @if(!empty($ac->cofins_creditos_nc['items']))
                            <div>
                                <h4 class="text-xs font-semibold text-purple-700 mb-2 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span> Créditos COFINS (M500/M510)
                                </h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-1.5 text-left font-medium text-gray-500">Tipo</th>
                                                <th class="px-3 py-1.5 text-right font-medium text-gray-500">Base Cálc.</th>
                                                <th class="px-3 py-1.5 text-right font-medium text-gray-500">Alíquota</th>
                                                <th class="px-3 py-1.5 text-right font-medium text-gray-500">Crédito</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @foreach($ac->cofins_creditos_nc['items'] as $cr)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-3 py-1.5 font-mono">{{ $cr['COD_CRED'] ?? $cr['tipo'] ?? '—' }}</td>
                                                <td class="px-3 py-1.5 text-right font-mono">R$ {{ number_format((float)($cr['VL_BC_COFINS'] ?? $cr['base_calculo'] ?? 0), 2, ',', '.') }}</td>
                                                <td class="px-3 py-1.5 text-right font-mono">{{ number_format((float)($cr['ALIQ_COFINS'] ?? $cr['aliquota'] ?? 0), 4, ',', '.') }}%</td>
                                                <td class="px-3 py-1.5 text-right font-mono font-semibold">R$ {{ number_format((float)($cr['VL_CRED'] ?? $cr['valor'] ?? 0), 2, ',', '.') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>

<script>
// ── Helpers (fora do init para que _efdRenderNotas seja referenciável internamente) ──
function _efdFormatBRL(valor) {
    return 'R$ ' + Number(valor || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function _efdFormatDate(val) {
    if (!val) return '-';
    var p = val.split('T')[0].split('-');
    return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : val;
}

function _efdRenderNotas(contentDiv, notas, biHtml, cache, pid) {
    cache[pid] = notas;
    var notasHtml = '';
    if (notas && notas.length > 0) {
        notasHtml = '<div class="overflow-x-auto mt-2"><table class="w-full text-xs border border-gray-200 rounded">' +
            '<thead class="bg-gray-100"><tr>' +
            '<th class="px-2 py-1 text-left text-gray-500">Nº Doc</th>' +
            '<th class="px-2 py-1 text-left text-gray-500">Série</th>' +
            '<th class="px-2 py-1 text-left text-gray-500">Modelo</th>' +
            '<th class="px-2 py-1 text-left text-gray-500">Emissão</th>' +
            '<th class="px-2 py-1 text-center text-gray-500">Tipo</th>' +
            '<th class="px-2 py-1 text-right text-gray-500">Valor</th>' +
            '</tr></thead><tbody class="divide-y divide-gray-200">' +
            notas.slice(0, 50).map(function(n) {
                var tipoHtml = n.tipo_operacao === 'entrada'
                    ? '<span class="text-green-700">E</span>'
                    : '<span class="text-amber-700">S</span>';
                return '<tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location=\'/app/notas-fiscais/efd/' + n.id + '\'">' +
                    '<td class="px-2 py-1 font-mono">' + (n.numero  || '-') + '</td>' +
                    '<td class="px-2 py-1">'           + (n.serie   || '-') + '</td>' +
                    '<td class="px-2 py-1">'           + (n.modelo  || '-') + '</td>' +
                    '<td class="px-2 py-1">'           + _efdFormatDate(n.data_emissao) + '</td>' +
                    '<td class="px-2 py-1 text-center">' + tipoHtml + '</td>' +
                    '<td class="px-2 py-1 text-right">' + _efdFormatBRL(n.valor_total) + '</td>' +
                    '</tr>';
            }).join('') +
            '</tbody></table>' +
            (notas.length > 50 ? '<p class="text-xs text-gray-400 mt-1">Mostrando 50 de ' + notas.length + ' notas.</p>' : '') +
            '</div>';
    } else {
        notasHtml = '<p class="text-xs text-gray-400 mt-2">Nenhuma nota disponivel.</p>';
    }
    contentDiv.innerHTML = biHtml + notasHtml;
}

// ── Collapse toggles ──
function _efdInitCollapseToggles() {
    document.querySelectorAll('.efd-collapse-toggle').forEach(function(btn) {
        if (btn._efdBound) return;
        btn._efdBound = true;
        btn.addEventListener('click', function() {
            var targetId = this.getAttribute('data-target');
            var target = document.getElementById(targetId);
            if (!target) return;
            var isHidden = target.classList.contains('hidden');
            target.classList.toggle('hidden');
            var chevron = this.querySelector('.efd-chevron');
            if (chevron) {
                chevron.style.transform = isHidden ? 'rotate(90deg)' : '';
            }
        });
    });
}

// ── Scroll-spy for sticky nav ──
function _efdInitScrollSpy() {
    var nav = document.getElementById('efd-sticky-nav');
    if (!nav) return;
    var links = nav.querySelectorAll('.efd-nav-link');
    if (!links.length) return;

    var sections = [];
    links.forEach(function(link) {
        var id = link.getAttribute('href');
        if (id && id.startsWith('#')) {
            var el = document.getElementById(id.substring(1));
            if (el) sections.push({ link: link, el: el });
        }
    });

    function updateActive() {
        var scrollY = window.scrollY + 120;
        var active = null;
        for (var i = sections.length - 1; i >= 0; i--) {
            if (sections[i].el.offsetTop <= scrollY) {
                active = sections[i].link;
                break;
            }
        }
        links.forEach(function(l) {
            l.classList.remove('bg-blue-100', 'text-blue-700');
        });
        if (active) {
            active.classList.add('bg-blue-100', 'text-blue-700');
        }
    }

    window.addEventListener('scroll', updateActive, { passive: true });
    updateActive();
}

// ── Catálogo search ──
function _efdInitCatalogoSearch() {
    var input = document.getElementById('busca-catalogo');
    if (!input || input._efdBound) return;
    input._efdBound = true;
    input.addEventListener('input', function() {
        var q = this.value.toLowerCase().trim();
        var rows  = document.querySelectorAll('#tbody-catalogo tr');
        var cards = document.querySelectorAll('#mobile-catalogo > div');
        var zero  = document.getElementById('zero-state-catalogo');
        var visible = 0;
        function filterEl(el) {
            var cod  = el.getAttribute('data-cod')  || '';
            var desc = el.getAttribute('data-desc') || '';
            var ncm  = el.getAttribute('data-ncm')  || '';
            var match = !q || cod.includes(q) || desc.includes(q) || ncm.includes(q);
            el.style.display = match ? '' : 'none';
            if (match) visible++;
        }
        rows.forEach(filterEl);
        cards.forEach(filterEl);
        if (zero) zero.classList.toggle('hidden', visible > 0 || !q);
    });
}

window.initImportacao = function() {
    // Row-click navigation (SPA-aware)
    function navigateToHref(el) {
        var href = el.getAttribute('data-href');
        if (!href) return;
        var link = document.createElement('a');
        link.setAttribute('data-link', '');
        link.href = href;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    document.querySelectorAll('[data-href]').forEach(function(row) {
        row.addEventListener('click', function() { navigateToHref(this); });
    });

    // ── Expansão inline de notas ──────────────────────────────────────────
    var notasCache = {};
    var container = document.getElementById('tabela-notas-participantes-detalhes');
    if (container && !container._efdInitDone) {
        container._efdInitDone = true;
        container.addEventListener('click', function(e) {
            var btn = e.target.closest('.btn-expand-notas-detalhes');
            if (!btn) return;
            e.stopPropagation();

            var pid          = parseInt(btn.dataset.participanteId);
            var importacaoId = parseInt(btn.dataset.importacaoId);
            var notaIds      = [];
            var bi           = {};
            try { notaIds = JSON.parse(btn.dataset.notaIds || '[]'); } catch(x) {}
            try { bi      = JSON.parse(btn.dataset.bi     || '{}'); } catch(x) {}

            var parentTr = btn.closest('tr');
            if (!parentTr) return;

            var existingRow = parentTr.nextElementSibling;
            if (existingRow && existingRow.classList.contains('expand-notas-row-detalhes')) {
                existingRow.remove();
                btn.textContent = '\u25B6';
                return;
            }
            btn.textContent = '\u25BC';

            var expandTr = document.createElement('tr');
            expandTr.className = 'expand-notas-row-detalhes bg-blue-50';
            expandTr.innerHTML = '<td colspan="6" class="px-4 py-3"><div class="expand-content text-sm"><div class="text-gray-500 text-xs">Carregando notas...</div></div></td>';
            parentTr.after(expandTr);
            var contentDiv = expandTr.querySelector('.expand-content');

            var biHtml = '';
            if (bi && Object.keys(bi).length > 0) {
                biHtml = '<div class="flex flex-wrap gap-4 mb-2">' +
                    Object.entries(bi).map(function(kv) {
                        return '<span class="text-xs text-gray-600"><span class="font-medium text-gray-700">' + kv[0].replace(/_/g,' ') + ':</span> ' + kv[1] + '</span>';
                    }).join('') + '</div>';
            }

            if (notasCache[pid] !== undefined) {
                _efdRenderNotas(contentDiv, notasCache[pid], biHtml, notasCache, pid);
                return;
            }

            var url = notaIds.length > 0
                ? '/app/importacao/efd/notas?' + notaIds.map(function(id) { return 'ids[]=' + id; }).join('&')
                : '/app/importacao/efd/notas-participante?participante_id=' + pid + '&importacao_id=' + importacaoId;

            fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r) { return r.ok ? r.json() : []; })
                .catch(function() { return []; })
                .then(function(notas) { _efdRenderNotas(contentDiv, notas, biHtml, notasCache, pid); });
        });
    }
    // ── fim expansão inline ────────────────────────────────────────────

    // Init collapse toggles, scroll-spy, catálogo search
    _efdInitCollapseToggles();
    _efdInitScrollSpy();
    _efdInitCatalogoSearch();

    // Client-side search filter
    var input = document.getElementById('busca-participantes-efd');
    if (!input) return;
    input.addEventListener('input', function() {
        var q = this.value.toLowerCase().trim();
        var rows  = document.querySelectorAll('#tbody-participantes-efd tr');
        var cards = document.querySelectorAll('#mobile-participantes-efd > div');
        var zeroBusca = document.getElementById('zero-state-busca');
        var visible = 0;
        function filterEl(el) {
            var razao = el.getAttribute('data-razao') || '';
            var doc   = el.getAttribute('data-doc')   || '';
            var match = !q || razao.includes(q) || doc.includes(q);
            el.style.display = match ? '' : 'none';
            if (match) visible++;
        }
        rows.forEach(filterEl);
        cards.forEach(filterEl);
        if (zeroBusca) zeroBusca.classList.toggle('hidden', visible > 0 || !q);
    });
};

// Execução imediata para carregamento direto (F5) — spa.js chama novamente via initImportacao()
window.initImportacao();
</script>
