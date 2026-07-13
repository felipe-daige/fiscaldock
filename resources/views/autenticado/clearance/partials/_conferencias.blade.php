@php
    // Confronto campo-a-campo Declarado × SEFAZ, exibido SEMPRE (confira ou não) para auditoria.
    // Cores por veredito — hex inline (regra dura do design system: nunca classe Tailwind de cor).
    $conf = $conferencias ?? [];
    $meta = [
        'confere' => ['hex' => '#047857', 'icon' => '✓', 'label' => 'confere'],
        'difere' => ['hex' => '#dc2626', 'icon' => '✕', 'label' => 'difere'],
        'indeterminado' => ['hex' => '#6b7280', 'icon' => '~', 'label' => 'indeterminado'],
        'sem_dado' => ['hex' => '#9ca3af', 'icon' => '–', 'label' => 'sem dado'],
    ];
@endphp
@if(!empty($conf))
    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
        @foreach($conf as $c)
            @php $m = $meta[$c['status']] ?? $meta['indeterminado']; @endphp
            <div class="rounded border border-gray-200 bg-white px-2.5 py-2">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">{{ $c['campo'] }}</span>
                    <span class="inline-flex items-center gap-1 text-[9px] font-bold uppercase tracking-wide text-white px-1.5 py-0.5 rounded" style="background-color: {{ $m['hex'] }}">{{ $m['icon'] }} {{ $m['label'] }}</span>
                </div>
                <p class="mt-1 text-[11px] text-gray-700 leading-snug"><span class="text-gray-400">Declarado:</span> {{ $c['declarado'] }}</p>
                <p class="text-[11px] text-gray-700 leading-snug"><span class="text-gray-400">SEFAZ:</span> {{ $c['sefaz'] }}</p>
                @if(!empty($c['nota']))
                    <p class="mt-0.5 text-[10px] italic text-gray-400 leading-snug">{{ $c['nota'] }}</p>
                @endif
                @if(($permitirConsultaSintegra ?? true) && !empty($c['participante_id']) && config('consultas.infosimples_ativo'))
                    <button type="button"
                        onclick="window.clearanceSintegra && window.clearanceSintegra.abrirParticipante({{ (int) $c['participante_id'] }})"
                        class="mt-1 inline-flex items-center gap-1 rounded border border-gray-300 bg-white px-1.5 py-0.5 text-[10px] font-semibold text-gray-600 transition-colors hover:bg-gray-50">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Consultar SINTEGRA
                    </button>
                @endif
            </div>
        @endforeach
    </div>
@endif
