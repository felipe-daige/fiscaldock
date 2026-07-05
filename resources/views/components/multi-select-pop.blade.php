@props([
    'grupo',                // id lógico p/ o JS (catFiltro): usa `${grupo}Box` e `${grupo}Count`
    'label',               // rótulo do campo
    'selecionados' => [],   // list<string> já marcados
    'width' => 'w-80',       // largura do painel
    'panelAlign' => 'left-0', // classes de ancoragem horizontal do painel
    'temOpcoes' => true,     // controla o rodapé "Marcar visíveis / Limpar"
    'placeholder' => 'buscar…',
])
{{--
    Dropdown multi-select ancorado (CFOP/CST em catalogo-itens). A casca (botão, busca,
    contador, rodapé) é padrão; as linhas <label data-row> vêm no slot. O JS vive na view
    (catFiltro / data-pop) e casa pelos ids `${grupo}Box`/`${grupo}Count`.
--}}
@php $n = count($selecionados); @endphp
<div class="relative" data-pop>
    <label class="block text-[11px] text-gray-500 mb-1">{{ $label }}</label>
    <button type="button" data-pop-toggle
        class="w-full flex items-center justify-between gap-2 text-[13px] py-2.5 px-3 border border-gray-300 rounded bg-white hover:border-gray-400"
        aria-haspopup="listbox" aria-expanded="false">
        <span class="truncate {{ $n ? 'text-gray-900 font-medium' : 'text-gray-500' }}">{{ $n ? $n.' selec.' : 'Todos' }}</span>
        <svg data-pop-chevron class="w-4 h-4 text-gray-400 transition-transform shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </button>
    <div data-pop-panel class="hidden absolute z-30 {{ $panelAlign }} mt-1 {{ $width }} max-w-[calc(100vw-2.5rem)] bg-white border border-gray-300 rounded shadow-lg">
        <div class="flex items-center gap-2 p-2 border-b border-gray-200 bg-gray-50 rounded-t">
            <input type="text" oninput="catFiltro.buscar(@js($grupo), this.value)" placeholder="{{ $placeholder }}" class="flex-1 min-w-0 text-[12px] py-1.5 px-2.5 border border-gray-300 rounded" aria-label="Buscar em {{ $label }}">
            <span id="{{ $grupo }}Count" class="text-[11px] font-semibold whitespace-nowrap" style="color:#1d4ed8">{{ $n ? $n.' sel.' : '' }}</span>
        </div>
        <div id="{{ $grupo }}Box" class="max-h-[240px] overflow-y-auto divide-y divide-gray-100">
            {{ $slot }}
        </div>
        @if ($temOpcoes)
            <div class="px-2.5 py-1.5 border-t border-gray-200 bg-gray-50 rounded-b flex gap-3">
                <button type="button" onclick="catFiltro.marcar(@js($grupo), true)" class="text-[11px] text-blue-600 cursor-pointer">Marcar visíveis</button>
                <button type="button" onclick="catFiltro.marcar(@js($grupo), false)" class="text-[11px] text-gray-500 cursor-pointer">Limpar seleção</button>
            </div>
        @endif
    </div>
</div>
