@props([
    'titulo',
    'subtitulo' => null,
    'contagem' => null,
    'bodyClass' => 'p-4 sm:p-5',
])

<section {{ $attributes->merge(['class' => 'bg-white rounded border border-gray-300 overflow-hidden']) }} data-cockpit-secao>
    <header class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">{{ $titulo }}</p>
            @if($subtitulo)
                <p class="mt-0.5 text-[11px] text-gray-400">{{ $subtitulo }}</p>
            @endif
        </div>
        @if($contagem !== null || isset($acao))
            <div class="flex shrink-0 items-center gap-3">
                @if($contagem !== null)
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold text-gray-600" style="background-color: #e5e7eb">{{ $contagem }}</span>
                @endif
                @isset($acao)
                    {{ $acao }}
                @endisset
            </div>
        @endif
    </header>
    <div class="{{ $bodyClass }}">
        {{ $slot }}
    </div>
</section>
