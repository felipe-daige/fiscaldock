<div class="min-h-screen bg-gray-50">

    {{-- Header --}}
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <h1 class="text-xl font-bold text-gray-900">Business Intelligence</h1>
            <p class="text-sm text-gray-500 mt-0.5">Análise de notas fiscais importadas (EFD)</p>
        </div>
    </div>

    {{-- Conteúdo --}}
    <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">

        {{-- Filtro de período --}}
        @include('autenticado.bi.partials.filtro-periodo', [
            'periodoAtivo' => $periodoAtivo ?? 'mes_atual',
            'filtros'      => $filtros ?? [],
        ])

        {{-- Área de módulos BI --}}
        <div id="bi-content">
            <p class="text-gray-400 text-sm">Selecione um período para visualizar os dados.</p>
        </div>

    </div>

</div>
