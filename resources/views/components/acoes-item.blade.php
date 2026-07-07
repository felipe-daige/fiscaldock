@props([
    'href' => null,
    'variant' => 'default',
    'icon' => null,
    'disabled' => false,  // item inerte (feature ainda não disponível)
    'badge' => null,       // pílula à direita (ex.: "Em breve")
])

@php
    // Design system DANFE: cor por style inline (Tailwind v4 cor → oklch quebra em alguns browsers).
    $cor = $variant === 'danger' ? '#b91c1c' : '#374151';
    $hover = $variant === 'danger' ? '#fef2f2' : '#f9fafb';
    $base = 'flex w-full items-center gap-2 whitespace-nowrap px-3 py-2 text-left text-[13px] transition-colors';
@endphp

@if ($disabled)
    {{-- inerte: sem data-acoes-item (não navega) — o clique só fecha o menu via handler do painel --}}
    <div role="menuitem" aria-disabled="true"
        class="{{ $base }} cursor-not-allowed select-none" style="color: #9ca3af;">
        @if ($icon)
            <span class="shrink-0 text-gray-300">{!! $icon !!}</span>
        @endif
        <span>{{ $slot }}</span>
        @if ($badge)
            <span class="ml-auto shrink-0 rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-500">{{ $badge }}</span>
        @endif
    </div>
@elseif ($href)
    <a href="{{ $href }}" role="menuitem" data-acoes-item
        class="{{ $base }}" style="color: {{ $cor }};"
        onmouseover="this.style.backgroundColor='{{ $hover }}'" onmouseout="this.style.backgroundColor=''"
        {{ $attributes }}>
        @if ($icon)
            <span class="shrink-0 text-gray-400">{!! $icon !!}</span>
        @endif
        <span>{{ $slot }}</span>
    </a>
@else
    <button type="button" role="menuitem" data-acoes-item
        class="{{ $base }}" style="color: {{ $cor }};"
        onmouseover="this.style.backgroundColor='{{ $hover }}'" onmouseout="this.style.backgroundColor=''"
        {{ $attributes }}>
        @if ($icon)
            <span class="shrink-0 text-gray-400">{!! $icon !!}</span>
        @endif
        <span>{{ $slot }}</span>
    </button>
@endif
