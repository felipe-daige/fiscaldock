{{-- Importação EFD - Detalhes --}}
@php
    [$badgeClass, $badgeLabel] = match($importacao->status) {
        'concluido'   => ['bg-green-100 text-green-700', 'Concluído'],
        'processando' => ['bg-blue-100 text-blue-700', 'Processando'],
        'erro'        => ['bg-red-100 text-red-700', 'Erro'],
        default       => ['bg-gray-100 text-gray-700', 'Pendente'],
    };
    $tipoClass = $importacao->tipo_efd === 'efd-contrib'
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
        <div class="efd-animate bg-white rounded-xl border border-gray-200 shadow-sm mb-6" style="animation-delay: 0.05s">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-base font-semibold text-gray-900">Informações da Importação</h2>
            </div>
            <div class="p-6 grid grid-cols-2 sm:grid-cols-4 gap-6">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Tipo EFD</p>
                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $tipoClass }}">
                        {{ $importacao->tipo_efd === 'efd-contrib' ? 'EFD Contribuições' : 'EFD Fiscal' }}
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
        <div class="efd-animate bg-white rounded-xl border border-gray-200 shadow-sm" style="animation-delay: 0.2s">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between gap-4 flex-wrap">
                <h2 class="text-base font-semibold text-gray-900">
                    Participantes
                    @if($participantes->count() > 0)
                        <span class="ml-1.5 px-2 py-0.5 bg-gray-100 text-gray-600 text-xs font-medium rounded-full">{{ $participantes->count() }}</span>
                    @endif
                </h2>
                @if($participantes->count() > 0)
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

            @if($participantes->count() > 0)
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

    </div>
</div>

<script>
(function () {
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

    document.querySelectorAll('[data-href]').forEach(function (row) {
        row.addEventListener('click', function () { navigateToHref(this); });
    });

    // Client-side search filter
    var input = document.getElementById('busca-participantes-efd');
    if (!input) return;

    input.addEventListener('input', function () {
        var q = this.value.toLowerCase().trim();
        var rows   = document.querySelectorAll('#tbody-participantes-efd tr');
        var cards  = document.querySelectorAll('#mobile-participantes-efd > div');
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
})();
</script>
