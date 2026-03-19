@php
    $periodos = [
        'mes_atual'       => 'Mês atual',
        'mes_anterior'    => 'Mês anterior',
        'trimestre_atual' => 'Trimestre atual',
        'semestre_atual'  => 'Semestre atual',
        'ano_atual'       => 'Ano atual',
        'personalizado'   => 'Personalizado',
    ];
    $periodoAtivo = $periodoAtivo ?? 'mes_atual';
    $filtros      = $filtros ?? [];
@endphp

<div class="bg-white border border-gray-200 rounded-lg px-4 py-3 flex flex-wrap items-center gap-3">
    <form id="bi-filtro-form" class="flex flex-wrap items-center gap-3 w-full">

        <label for="bi-periodo-select" class="text-sm font-medium text-gray-700 shrink-0">
            Período:
        </label>

        <select
            id="bi-periodo-select"
            name="periodo"
            class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-1 focus:ring-gray-400 focus:outline-none bg-white text-gray-700"
        >
            @foreach($periodos as $valor => $label)
                <option value="{{ $valor }}" {{ $periodoAtivo === $valor ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        {{-- Inputs de data personalizada (ocultos por padrão via JS) --}}
        <div id="bi-datas-personalizadas" class="items-center gap-2" style="display: none;">
            <input
                type="date"
                id="bi-data-inicio"
                name="data_inicio"
                value="{{ $filtros['data_inicio_iso'] ?? '' }}"
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-1 focus:ring-gray-400 focus:outline-none"
            >
            <span class="text-gray-400 text-sm">até</span>
            <input
                type="date"
                id="bi-data-fim"
                name="data_fim"
                value="{{ $filtros['data_fim_iso'] ?? '' }}"
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-1 focus:ring-gray-400 focus:outline-none"
            >
        </div>

        <button
            type="submit"
            class="px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors shrink-0"
        >
            Aplicar
        </button>

    </form>

    @if(!empty($filtros['data_inicio']) && !empty($filtros['data_fim']))
        <p class="w-full text-xs text-gray-500 mt-1">
            Exibindo dados de <strong>{{ $filtros['data_inicio'] }}</strong> a <strong>{{ $filtros['data_fim'] }}</strong>
        </p>
    @endif
</div>

<script src="{{ asset('js/bi/filtro.js') }}"></script>
