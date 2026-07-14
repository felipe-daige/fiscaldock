{{-- Tabela de itens negociados da nota (XML/EFD). Espera $itens (numero_item, codigo_item,
     descricao, ncm, cfop, quantidade, unidade_medida, valor_unitario, valor_total e, quando
     $mostrarTributos, valor_icms/valor_pis/valor_cofins). --}}
@php
    $wrapperClass = $wrapperClass ?? '';
    $titulo = $titulo ?? 'Itens da Nota';
    $mostrarTributos = $mostrarTributos ?? false;
    $itensFmtDecimal = function ($valor, int $casas = 4): string {
        if ($valor === null) {
            return '—';
        }
        $texto = number_format((float) $valor, $casas, ',', '.');

        return str_contains($texto, ',') ? (rtrim(rtrim($texto, '0'), ',') ?: '0') : $texto;
    };
    // Moeda com no mínimo 2 casas; abre pra 4 só quando o unitário tem precisão real
    $itensFmtMoedaUnit = fn ($valor): string => $valor === null ? '—' : number_format(
        (float) $valor,
        fmod(round((float) $valor * 10000), 100) == 0 ? 2 : 4,
        ',',
        '.'
    );
    $itensFmtMoeda = fn ($valor): string => $valor === null ? '—' : number_format((float) $valor, 2, ',', '.');
@endphp
@if($itens->isNotEmpty())
    <div class="bg-white rounded border border-gray-300 overflow-hidden {{ $wrapperClass }}">
        <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between">
            <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">{{ $titulo }}</span>
            <span class="text-[10px] font-semibold text-gray-400 bg-gray-200 px-2 py-0.5 rounded">{{ $itens->count() }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide">#</th>
                        <th class="px-4 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Código</th>
                        <th class="px-4 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Descrição</th>
                        <th class="px-4 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide">NCM</th>
                        <th class="px-4 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide">CFOP</th>
                        <th class="px-4 py-2 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Qtd</th>
                        <th class="px-4 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Un</th>
                        <th class="px-4 py-2 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Vlr unit.</th>
                        <th class="px-4 py-2 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Vlr total</th>
                        @if($mostrarTributos)
                            <th class="px-4 py-2 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide">ICMS</th>
                            <th class="px-4 py-2 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide">PIS</th>
                            <th class="px-4 py-2 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide">COFINS</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($itens as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 font-mono text-gray-400">{{ $item->numero_item }}</td>
                            <td class="px-4 py-2 font-mono text-gray-600">{{ $item->codigo_item ?: '—' }}</td>
                            <td class="px-4 py-2 text-gray-900">{{ $item->descricao ?: '—' }}</td>
                            <td class="px-4 py-2 font-mono text-gray-600">{{ $item->ncm ?? '—' }}</td>
                            <td class="px-4 py-2 font-mono text-gray-600">{{ $item->cfop ?: '—' }}</td>
                            <td class="px-4 py-2 text-right font-mono text-gray-900">{{ $itensFmtDecimal($item->quantidade) }}</td>
                            <td class="px-4 py-2 text-gray-600">{{ $item->unidade_medida ?: '—' }}</td>
                            <td class="px-4 py-2 text-right font-mono text-gray-900">R$&nbsp;{{ $itensFmtMoedaUnit($item->valor_unitario) }}</td>
                            <td class="px-4 py-2 text-right font-mono font-semibold text-gray-900">R$&nbsp;{{ $itensFmtMoeda($item->valor_total) }}</td>
                            @if($mostrarTributos)
                                <td class="px-4 py-2 text-right font-mono text-gray-700">{{ $itensFmtMoeda($item->valor_icms ?? null) }}</td>
                                <td class="px-4 py-2 text-right font-mono text-gray-700">{{ $itensFmtMoeda($item->valor_pis ?? null) }}</td>
                                <td class="px-4 py-2 text-right font-mono text-gray-700">{{ $itensFmtMoeda($item->valor_cofins ?? null) }}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="bg-gray-50 px-4 py-2 border-t border-gray-200 flex flex-wrap items-center justify-end gap-x-4 gap-y-1 text-[11px]">
            <span class="text-gray-500">Total itens <span class="font-mono font-semibold text-gray-900">R$&nbsp;{{ number_format((float) $itens->sum('valor_total'), 2, ',', '.') }}</span></span>
            @if($mostrarTributos)
                <span class="text-gray-500">ICMS <span class="font-mono text-gray-700">{{ number_format((float) $itens->sum('valor_icms'), 2, ',', '.') }}</span></span>
                <span class="text-gray-500">PIS <span class="font-mono text-gray-700">{{ number_format((float) $itens->sum('valor_pis'), 2, ',', '.') }}</span></span>
                <span class="text-gray-500">COFINS <span class="font-mono text-gray-700">{{ number_format((float) $itens->sum('valor_cofins'), 2, ',', '.') }}</span></span>
            @endif
        </div>
    </div>
@endif
