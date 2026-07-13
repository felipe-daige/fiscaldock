@props([
    'id' => 'modal-exportar',
    'label' => 'Exportar',
    'titulo' => 'Exportar',
    'descricao' => null,
])

{{-- Botão único "Exportar" + modal de formato. As opções vêm no slot como
     export-option (download GET, download POST com ids[], ou encadeia num 2º modal).
     Cache-robusto: abre por onclick inline, sem JS-file (padrão do design system). --}}

<button type="button" data-export-menu="{{ $id }}"
    onclick="document.getElementById('{{ $id }}').classList.remove('hidden')"
    {{ $attributes->merge(['class' => 'auth-control inline-flex items-center justify-center gap-1.5 rounded border border-gray-300 bg-white text-gray-700 transition-colors hover:bg-gray-50']) }}>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
    </svg>
    <span>{{ $label }}</span>
</button>

<x-modal :id="$id" :titulo="$titulo">
    @if ($descricao)
        <p class="text-[13px] text-gray-600 mb-3">{{ $descricao }}</p>
    @endif
    <div class="space-y-2">
        {{ $slot }}
    </div>
</x-modal>
