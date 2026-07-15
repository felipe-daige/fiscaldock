@props([
    'containerId',
    'titulo',
    'subtitulo' => null,
    'eyebrow' => 'Cadastro',
    'resumoTitulo' => 'Visão Geral',
])

<div class="min-h-screen bg-gray-100" id="{{ $containerId }}">
    <main {{ $attributes->merge(['class' => 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-4 sm:space-y-6']) }}
          data-cockpit-layout="stack">
        <section class="bg-white rounded border border-gray-300 overflow-hidden" data-cockpit-identidade>
            <header class="bg-gray-50 px-4 py-4 sm:px-5 border-b border-gray-200">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">{{ $eyebrow }}</p>
                        <h1 class="mt-1 text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide break-words">{{ $titulo }}</h1>
                        @if($subtitulo)
                            <p class="mt-1 max-w-3xl text-xs text-gray-500">{{ $subtitulo }}</p>
                        @endif
                        @isset($badges)
                            <div class="mt-3 flex flex-wrap items-center gap-2" data-cockpit-badges>
                                {{ $badges }}
                            </div>
                        @endisset
                    </div>

                    @isset($principal)
                        <div class="shrink-0">
                            {{ $principal }}
                        </div>
                    @endisset
                </div>
            </header>

            @isset($acoes)
                <nav class="px-4 py-3 sm:px-5 border-b border-gray-200 flex flex-wrap items-center gap-2"
                     aria-label="Ações da página" data-cockpit-acoes>
                    {{ $acoes }}
                </nav>
            @endisset

            @isset($resumo)
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">{{ $resumoTitulo }}</span>
                </div>
                {{ $resumo }}
            @endisset
        </section>

        {{ $slot }}
    </main>
</div>
