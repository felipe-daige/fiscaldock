@props([
    'tipo',        // 'ncm_divergente' | 'sem_catalogo'
    'codigo',      // codigo_item
    'dispensado' => false,
])
{{-- Botão Ignorar/Restaurar de um alerta de catálogo. Pill com borda + ícone; alvo de
     toque >=44px garantido pelo padding vertical (py-1.5) somado ao -my-1 da célula. --}}
@if ($dispensado)
    <button type="button" onclick="catalogoAlerta.restaurar(@js($tipo), @js((string) $codigo))"
        class="inline-flex items-center gap-1 -my-1 py-1.5 px-2 text-[11px] font-medium rounded border cursor-pointer transition-colors"
        style="color:#1d4ed8;border-color:#bfdbfe;background-color:#eff6ff">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" class="shrink-0" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7L3 8m0 0V3m0 5h5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Restaurar
    </button>
@else
    <button type="button" onclick="catalogoAlerta.pedir(@js($tipo), @js((string) $codigo))"
        class="inline-flex items-center gap-1 -my-1 py-1.5 px-2 text-[11px] font-medium rounded border cursor-pointer transition-colors"
        style="color:#4b5563;border-color:#d1d5db;background-color:#f9fafb">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" class="shrink-0" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Ignorar
    </button>
@endif
