{{-- Página inteira em paywall (Free): header real + skeleton borrado + card.
     Usada pelos controllers de telas 100% gateadas (catálogo de itens, cruzamentos)
     quando o plano não tem `bi_completo` — os dados nem são computados. --}}
<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="mb-4 sm:mb-6">
            <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">{{ $paginaTitulo }}</h1>
            @if(!empty($paginaSubtitulo))
                <p class="text-xs text-gray-500 mt-0.5">{{ $paginaSubtitulo }}</p>
            @endif
        </div>

        <x-paywall-overlay :titulo="$paywallTitulo" :descricao="$paywallDescricao" />
    </div>
</div>
