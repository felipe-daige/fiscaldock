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

        {{-- KPIs --}}
        @include('autenticado.bi.partials.kpis', ['kpis' => $kpis])

        {{-- Gráficos --}}
        @include('autenticado.bi.partials.graficos-fluxo')

    </div>

</div>

{{-- Chart.js via CDN (carregado apenas nesta view) --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

{{-- Dados para os gráficos --}}
<script>
    window.biFluxoMensal  = @json($fluxoMensal ?? []);
    window.biVolumeBlocos = @json($volumeBlocos ?? []);
</script>
<script src="{{ asset('js/bi/graficos-home.js') }}"></script>
