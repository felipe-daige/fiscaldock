@php
    $ultimasVerificacoesDfe = $ultimasVerificacoesDfe ?? collect();
@endphp

<section class="mt-4 sm:mt-6 bg-white rounded border border-gray-300 overflow-hidden" data-history-flow="clearance-lote">
    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between gap-3">
        <div>
            <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Histórico de verificações</span>
            <p class="text-[10px] text-gray-400 mt-0.5">Lotes disparados em Verificar Notas</p>
        </div>
        <span class="text-[10px] font-semibold text-gray-400 bg-gray-200 px-2 py-0.5 rounded">{{ $ultimasVerificacoesDfe->count() }}</span>
    </div>

    @if($ultimasVerificacoesDfe->isEmpty())
        <div class="px-4 py-6 text-center">
            <p class="text-sm font-semibold text-gray-900">Nenhuma verificação ainda</p>
            <p class="text-xs text-gray-500 mt-1">Os lotes de notas selecionadas nesta tela aparecerão aqui.</p>
        </div>
    @else
        <div class="divide-y divide-gray-100">
            @foreach($ultimasVerificacoesDfe as $loteHistorico)
                @php
                    $statusMeta = match(\App\Models\ConsultaLote::normalizeStatus($loteHistorico->status)) {
                        'finalizado' => ['label' => 'Finalizado', 'hex' => '#047857'],
                        'processando' => ['label' => 'Processando', 'hex' => '#b45309'],
                        'erro' => ['label' => 'Erro', 'hex' => '#dc2626'],
                        default => ['label' => 'Pendente', 'hex' => '#6b7280'],
                    };
                    $tier = ($loteHistorico->resultado_resumo['tier'] ?? 'basico') === 'full' ? 'Completa' : 'Básica';
                @endphp
                <a href="{{ route('app.clearance.notas.resultado', ['consultaLoteId' => $loteHistorico->id]) }}"
                   data-link
                   class="grid grid-cols-2 sm:grid-cols-[minmax(0,1.4fr)_auto_auto_auto] items-center gap-3 px-4 py-3 hover:bg-gray-50">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900">Lote #{{ $loteHistorico->id }}</p>
                        <p class="text-[11px] text-gray-500 mt-0.5">{{ $loteHistorico->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase">Escopo</p>
                        <p class="text-xs text-gray-700">{{ $tier }} · {{ number_format($loteHistorico->total_participantes, 0, ',', '.') }} doc.</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase">Custo</p>
                        <p class="text-xs font-mono font-semibold text-gray-900">{{ \App\Support\Dinheiro::brl(app(\App\Services\PricingCatalogService::class)->creditsToCurrency((int) $loteHistorico->creditos_cobrados)) }}</p>
                    </div>
                    <div class="col-span-2 sm:col-span-1 sm:text-right">
                        <span class="whitespace-nowrap px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $statusMeta['hex'] }}">{{ $statusMeta['label'] }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</section>
