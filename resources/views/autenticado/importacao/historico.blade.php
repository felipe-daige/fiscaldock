{{-- Histórico Unificado de Importações --}}
<div class="min-h-screen bg-gray-50" id="historico-importacoes-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <style>
            @keyframes hist-slide-in {
                from { opacity: 0; transform: translateY(40px); }
                to   { opacity: 1; transform: translateY(0); }
            }
            .hist-animate {
                opacity: 0;
                animation: hist-slide-in 0.55s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            }
            @media (prefers-reduced-motion: reduce) {
                .hist-animate { opacity: 1; animation: none; }
            }
        </style>

        {{-- Header --}}
        <div class="mb-6 hist-animate">
            <h1 class="text-2xl font-bold text-gray-900">Histórico de Importações</h1>
            <p class="mt-1 text-sm text-gray-500">Todas as importações EFD e XML, ordenadas por data.</p>
        </div>

        @if($importacoes->isNotEmpty())
        {{-- Filtro rápido --}}
        <div class="mb-5 hist-animate flex items-center gap-2 flex-wrap" style="animation-delay: 0.05s" id="filtro-tipo-wrapper">
            <button
                type="button"
                data-tipo="todos"
                class="filtro-tipo px-3 py-1.5 rounded-full text-sm font-medium border transition-colors bg-blue-600 text-white border-blue-600"
            >Todos</button>
            <button
                type="button"
                data-tipo="efd"
                class="filtro-tipo px-3 py-1.5 rounded-full text-sm font-medium border transition-colors bg-white text-gray-700 border-gray-300 hover:border-blue-400 hover:text-blue-600"
            >EFD</button>
            <button
                type="button"
                data-tipo="xml"
                class="filtro-tipo px-3 py-1.5 rounded-full text-sm font-medium border transition-colors bg-white text-gray-700 border-gray-300 hover:border-blue-400 hover:text-blue-600"
            >XML</button>
        </div>

        {{-- Grid de cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 hist-animate" style="animation-delay: 0.1s" id="cards-grid">
            @foreach($importacoes as $imp)
            @php
                $tipo = $imp['_tipo'];
                $id   = $imp['id'];

                // Status badge
                [$statusClass, $statusLabel] = match($imp['status'] ?? '') {
                    'concluido'   => ['bg-green-100 text-green-700', 'Concluído'],
                    'processando' => ['bg-blue-100 text-blue-700', 'Processando'],
                    'erro'        => ['bg-red-100 text-red-700', 'Erro'],
                    default       => ['bg-gray-100 text-gray-700', 'Pendente'],
                };

                // Tipo badge + label
                if ($tipo === 'efd') {
                    $href = '/app/importacao/efd/' . $id;
                    $tipoDocLabel = ($imp['tipo_efd'] ?? '') === 'EFD PIS/COFINS' ? 'EFD PIS/COFINS' : 'EFD ICMS/IPI';
                    $tipoBadgeClass = ($imp['tipo_efd'] ?? '') === 'EFD PIS/COFINS'
                        ? 'bg-purple-100 text-purple-700'
                        : 'bg-blue-100 text-blue-700';
                } else {
                    $href = '/app/importacao/xml/' . $id;
                    [$tipoDocLabel, $tipoBadgeClass] = match($imp['tipo_documento'] ?? '') {
                        'nfe'  => ['NF-e',  'bg-green-100 text-green-700'],
                        'nfse' => ['NFS-e', 'bg-indigo-100 text-indigo-700'],
                        'cte'  => ['CT-e',  'bg-orange-100 text-orange-700'],
                        default => ['XML',  'bg-gray-100 text-gray-700'],
                    };
                }

                // Filename
                $filename = $imp['filename'] ?? ('Importação #' . $id);

                // Cliente
                $clienteNome = $imp['cliente']['razao_social'] ?? null;

                // Data
                $dataFormatada = isset($imp['created_at'])
                    ? \Carbon\Carbon::parse($imp['created_at'])->format('d/m/Y H:i')
                    : '—';

                // Tempo de processamento
                $tempoProc = '—';
                if (!empty($imp['iniciado_em']) && !empty($imp['concluido_em'])) {
                    $inicio = \Carbon\Carbon::parse($imp['iniciado_em']);
                    $fim = \Carbon\Carbon::parse($imp['concluido_em']);
                    $diff = $inicio->diff($fim);
                    if ($diff->h > 0) { $tempoProc = $diff->h . 'h ' . $diff->i . 'm'; }
                    elseif ($diff->i > 0) { $tempoProc = $diff->i . 'm ' . $diff->s . 's'; }
                    elseif ($diff->s > 0) { $tempoProc = $diff->s . 's'; }
                    else { $tempoProc = '< 1s'; }
                }

                // Contador
                if ($tipo === 'efd') {
                    $contador = (($imp['novos'] ?? 0) + ($imp['duplicados'] ?? 0)) . ' participante(s)';
                } else {
                    $total = $imp['total_xmls'] ?? 0;
                    $contador = $total . ' XML' . ($total !== 1 ? 's' : '');
                }
            @endphp
            <a
                href="{{ $href }}"
                data-link
                data-tipo="{{ $tipo }}"
                class="hist-card group bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex flex-col gap-3 hover:shadow-md hover:border-gray-300 transition-all"
            >
                {{-- Top badges --}}
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $tipoBadgeClass }}">{{ $tipoDocLabel }}</span>
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $statusClass }}">{{ $statusLabel }}</span>
                </div>

                {{-- Filename --}}
                <p class="text-sm font-semibold text-gray-900 truncate leading-snug group-hover:text-blue-600 transition-colors">{{ $filename }}</p>

                {{-- Cliente --}}
                <p class="text-xs text-gray-500 truncate">
                    @if($clienteNome)
                        <span class="text-gray-700 font-medium">{{ $clienteNome }}</span>
                    @else
                        <span class="italic">Sem cliente</span>
                    @endif
                </p>

                {{-- Rodapé --}}
                <div class="flex items-center justify-between mt-auto pt-2 border-t border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400">{{ $dataFormatada }}</span>
                        @if($tempoProc !== '—')
                            <span class="text-xs text-gray-300">&middot;</span>
                            <span class="text-xs text-gray-400">{{ $tempoProc }}</span>
                        @endif
                    </div>
                    <span class="text-xs text-gray-500 font-medium">{{ $contador }}</span>
                </div>
            </a>
            @endforeach
        </div>

        {{-- Zero-state de filtro --}}
        <div id="zero-state-filtro" class="hidden py-16 text-center">
            <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            <p class="text-sm text-gray-500">Nenhuma importação deste tipo encontrada.</p>
        </div>

        @else
        {{-- Zero-state global --}}
        <div class="hist-animate py-20 text-center" style="animation-delay: 0.05s">
            <svg class="w-14 h-14 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            <p class="text-base font-semibold text-gray-700 mb-1">Nenhuma importação realizada ainda</p>
            <p class="text-sm text-gray-500 mb-6">Importe seus primeiros arquivos para vê-los aqui.</p>
            <div class="flex items-center justify-center gap-3 flex-wrap">
                <a href="/app/importacao/efd" data-link class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors">
                    Importar EFD
                </a>
                <a href="/app/importacao/xml" data-link class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold hover:bg-gray-50 transition-colors">
                    Importar XML
                </a>
            </div>
        </div>
        @endif

    </div>
</div>

<script>
(function () {
    var btns  = document.querySelectorAll('.filtro-tipo');
    var cards = document.querySelectorAll('.hist-card');
    var zeroFiltro = document.getElementById('zero-state-filtro');

    if (!btns.length) return;

    btns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var tipo = this.getAttribute('data-tipo');

            // Atualiza botões
            btns.forEach(function (b) {
                b.classList.remove('bg-blue-600', 'text-white', 'border-blue-600');
                b.classList.add('bg-white', 'text-gray-700', 'border-gray-300');
            });
            this.classList.add('bg-blue-600', 'text-white', 'border-blue-600');
            this.classList.remove('bg-white', 'text-gray-700', 'border-gray-300');

            // Filtra cards
            var visíveis = 0;
            cards.forEach(function (card) {
                var cardTipo = card.getAttribute('data-tipo');
                var show = tipo === 'todos' || cardTipo === tipo;
                card.style.display = show ? '' : 'none';
                if (show) visíveis++;
            });

            if (zeroFiltro) zeroFiltro.classList.toggle('hidden', visíveis > 0);
        });
    });
})();
</script>
