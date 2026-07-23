{{-- Card retrátil DS — padrão canônico de exibição de certidões/fontes (origem: detalhe-blocos,
     commit 2c6c6c5). Colapsado por padrão; o slot `badges` fica no header (status sempre à vista);
     o corpo (slot default) só abre sob demanda. Toggle por onclick inline (DS cache-robusto):
     funciona em qualquer página, sem depender de handler em JS-file.
     Props: titulo, acento (hex — borda esquerda de status), id (auto).
     Slots: badges (header, à direita antes do chevron), subheader (linhas sob o título). --}}
@props(['titulo', 'acento' => null, 'id' => null, 'truncarTitulo' => false])
@php
    $cardId = $id ?? 'ret-'.bin2hex(random_bytes(6));
@endphp
<div {{ $attributes->merge(['class' => 'min-w-0 rounded border border-gray-300 bg-white overflow-hidden']) }}@if($acento) style="border-left: 3px solid {{ $acento }}"@endif>
    <button type="button" aria-expanded="false" aria-controls="{{ $cardId }}"
            onclick="(function(b){var t=document.getElementById('{{ $cardId }}');if(!t)return;var h=t.classList.toggle('hidden');b.setAttribute('aria-expanded',h?'false':'true');var c=b.querySelector('.detalhe-chevron');if(c)c.style.transform=h?'':'rotate(90deg)';var l=b.querySelector('[data-toggle-ver]');if(l)l.textContent=h?'Ver tudo':'Ocultar'})(this)"
            class="w-full flex items-start justify-between gap-2 px-3 py-2 bg-gray-50 hover:bg-gray-100 text-left transition-colors">
        <span class="min-w-0 flex-1">
            <span class="text-[11px] font-semibold text-gray-600 uppercase tracking-wide {{ $truncarTitulo ? 'block truncate' : '' }}"@if($truncarTitulo) title="{{ $titulo }}"@endif>{{ $titulo }}</span>
            @isset($subheader)
                <span class="block mt-0.5">{{ $subheader }}</span>
            @endisset
        </span>
        <span class="flex items-center gap-2 shrink-0 pt-0.5">
            @isset($badges){{ $badges }}@endisset
            <span class="flex items-center gap-1 text-gray-400">
                <span data-toggle-ver class="text-[10px] uppercase tracking-wide hidden sm:inline">Ver tudo</span>
                <svg class="detalhe-chevron w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </span>
        </span>
    </button>
    <div id="{{ $cardId }}" class="hidden px-3 py-2.5 space-y-2.5 border-t border-gray-200">
        {{ $slot }}
    </div>
</div>
