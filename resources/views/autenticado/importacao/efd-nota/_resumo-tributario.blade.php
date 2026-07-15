<section class="overflow-hidden rounded border border-gray-300 bg-white" data-resumo-tributario>
    <div class="border-b border-gray-200 bg-gray-50 px-4 py-2">
        <span class="text-[10px] font-semibold uppercase tracking-widest text-gray-500">Resumo tributário</span>
        <p class="mt-1 text-[11px] text-gray-500">ICMS do consolidado e PIS/COFINS dos itens.</p>
    </div>
    <dl class="flex flex-wrap divide-y divide-gray-200 sm:divide-x sm:divide-y-0">
        <div class="w-full px-4 py-3 sm:w-2/5 lg:w-2/5">
            <dt class="text-[9px] font-semibold uppercase tracking-wide text-gray-400">Total de tributos</dt>
            <dd class="mt-1 text-xl font-bold text-gray-900">R$&nbsp;{{ number_format($totalTributos, 2, ',', '.') }}</dd>
        </div>
        @foreach([
            ['label' => 'ICMS', 'valor' => $totalIcms],
            ['label' => 'PIS', 'valor' => $totalPis],
            ['label' => 'COFINS', 'valor' => $totalCofins],
        ] as $tributo)
            <div class="w-1/3 flex-1 px-4 py-3 sm:w-1/5">
                <dt class="text-[9px] font-semibold uppercase tracking-wide text-gray-400">{{ $tributo['label'] }}</dt>
                <dd class="mt-1 font-mono text-sm font-semibold text-gray-900">R$&nbsp;{{ number_format($tributo['valor'], 2, ',', '.') }}</dd>
            </div>
        @endforeach
    </dl>
</section>
