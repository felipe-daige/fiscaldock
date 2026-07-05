@props([
    'tipo',        // 'ncm_divergente' | 'sem_catalogo'
    'codigo',      // codigo_item
    'dispensado' => false,
])
{{-- Botão Ignorar/Restaurar de um alerta de catálogo. Alvo de toque >=44px (py-2 -my-1). --}}
@if ($dispensado)
    <button type="button" onclick="catalogoAlerta.restaurar(@js($tipo), @js((string) $codigo))"
        class="inline-block -my-1 py-2 text-[11px] text-blue-600 underline cursor-pointer">Restaurar</button>
@else
    <button type="button" onclick="catalogoAlerta.pedir(@js($tipo), @js((string) $codigo))"
        class="inline-block -my-1 py-2 text-[11px] text-red-600 cursor-pointer">Ignorar</button>
@endif
