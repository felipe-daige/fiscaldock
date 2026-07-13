@php
    $ultimosLotes = $ultimosLotes ?? collect();
@endphp

<section class="bg-white rounded border border-gray-300 overflow-hidden" data-history-flow="consulta-cnpj">
    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between gap-3">
        <div>
            <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Histórico de consultas CNPJ</span>
            <p class="text-[10px] text-gray-400 mt-0.5">Últimos lotes executados neste produto</p>
        </div>
        <a href="{{ route('app.consulta.historico') }}" data-link class="text-[11px] font-semibold text-gray-600 hover:text-gray-900 hover:underline">Ver histórico completo</a>
    </div>

    @if($ultimosLotes->isEmpty())
        <div class="px-4 py-6 text-center">
            <p class="text-sm font-semibold text-gray-900">Nenhuma consulta CNPJ ainda</p>
            <p class="text-xs text-gray-500 mt-1">Ao executar um lote, o acesso ao resultado ficará disponível aqui.</p>
        </div>
    @else
        <div class="divide-y divide-gray-100">
            @foreach($ultimosLotes as $loteHistorico)
                @php
                    $statusMeta = match(\App\Models\ConsultaLote::normalizeStatus($loteHistorico->status)) {
                        'finalizado' => ['label' => 'Finalizado', 'hex' => '#047857'],
                        'processando' => ['label' => 'Processando', 'hex' => '#b45309'],
                        'erro' => ['label' => 'Erro', 'hex' => '#dc2626'],
                        default => ['label' => 'Pendente', 'hex' => '#6b7280'],
                    };
                @endphp
                <a href="{{ route('app.consulta.lote.show', ['id' => $loteHistorico->id]) }}"
                   data-link
                   class="grid grid-cols-2 sm:grid-cols-[minmax(0,1.4fr)_auto_auto_auto] items-center gap-3 px-4 py-3 hover:bg-gray-50">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-sm font-semibold text-gray-900">Lote #{{ $loteHistorico->id }}</p>
                            @if($loteHistorico->eh_monitoramento ?? false)
                                <span class="whitespace-nowrap px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: #7c3aed">Monitoramento</span>
                            @endif
                        </div>
                        <p class="text-[11px] text-gray-500 mt-0.5">{{ $loteHistorico->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase">Produto</p>
                        <p class="text-xs text-gray-700">{{ $loteHistorico->plano?->nome ?? 'Consulta CNPJ' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase">Participantes</p>
                        <p class="text-xs font-semibold text-gray-900">{{ number_format($loteHistorico->total_participantes, 0, ',', '.') }}</p>
                    </div>
                    <div class="col-span-2 sm:col-span-1 sm:text-right">
                        <span class="whitespace-nowrap px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $statusMeta['hex'] }}">{{ $statusMeta['label'] }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</section>
