{{-- Sub-abas reutilizadas: Tudo / Clientes / Participantes
     Props: $tipoAtivo (string), $contagens (array com chaves tudo/cliente/participante), $rota (nome da rota) --}}
<div class="bg-white rounded border border-gray-300 overflow-hidden mb-6">
    <nav class="flex border-b border-gray-200" aria-label="Filtro por tipo de alvo">
        @foreach (['tudo' => 'Tudo', 'cliente' => 'Clientes', 'participante' => 'Participantes'] as $valor => $rotulo)
            <a href="{{ route($rota, ['tipo' => $valor]) }}"
               data-sub-tab="{{ $valor }}"
               data-tipo="{{ $valor }}"
               class="px-4 py-3 text-xs font-semibold uppercase tracking-wide transition
                      {{ ($tipoAtivo ?? 'tudo') === $valor
                          ? 'text-gray-900 border-b-2 border-gray-900'
                          : 'text-gray-500 hover:text-gray-700 border-b-2 border-transparent' }}">
                {{ $rotulo }}
                <span class="ml-1 text-[10px] text-gray-400 font-mono">({{ $contagens[$valor] ?? 0 }})</span>
            </a>
        @endforeach
    </nav>
</div>
