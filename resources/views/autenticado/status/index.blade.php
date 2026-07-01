<div class="min-h-screen bg-gray-100">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="mb-4 sm:mb-6">
            <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Status dos serviços</h1>
            <p class="text-xs text-gray-500 mt-0.5">Disponibilidade atual das integrações usadas pela plataforma.</p>
        </div>

        {{-- Legenda --}}
        <div class="flex flex-wrap gap-3 mb-5 text-[11px] text-gray-500">
            <span>🟢 Operacional</span>
            <span>🟡 Degradado</span>
            <span>🔴 Fora do ar</span>
            <span>🔵 Em manutenção</span>
        </div>

        @foreach($grupos as $grupo)
            @if($grupo['itens']->isNotEmpty())
                <h2 class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mt-4 mb-2">{{ $grupo['titulo'] }}</h2>
                <div class="space-y-2 mb-4">
                    @foreach($grupo['itens'] as $i)
                        <div class="bg-white border border-gray-300 rounded p-3 flex items-start gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900">{{ $i->nome }}</p>
                                @if($i->mensagem)
                                    <p class="text-xs text-gray-600 mt-0.5 whitespace-pre-line">{{ $i->mensagem }}</p>
                                @endif
                                <p class="text-[11px] text-gray-400 mt-1">
                                    atualizado {{ $i->updated_at->diffForHumans() }}
                                    @if($i->atualizadoPor) · por {{ $i->atualizadoPor->name }} @endif
                                </p>
                            </div>
                            <span class="text-xs font-semibold px-2 py-1 rounded whitespace-nowrap {{ $i->corClasse }}">
                                {{ $i->emoji }} {{ $i->label }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        @endforeach
    </div>
</div>
