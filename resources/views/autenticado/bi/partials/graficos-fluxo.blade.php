<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Gráfico 1: Evolução Mensal --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Evolução Mensal (12 meses)</h3>
        <div class="relative">
            <canvas id="grafico-fluxo-mensal" height="200"></canvas>
        </div>
    </div>

    {{-- Gráfico 2: Volume por Bloco EFD --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Volume por Bloco EFD</h3>
        <div class="relative flex items-center justify-center" style="min-height: 200px;">
            <canvas id="grafico-volume-blocos" height="200"></canvas>
            <div id="grafico-blocos-sem-dados"
                 class="absolute inset-0 items-center justify-center text-gray-400 text-sm"
                 style="display: none;">
                Sem dados no período
            </div>
        </div>
    </div>

</div>
