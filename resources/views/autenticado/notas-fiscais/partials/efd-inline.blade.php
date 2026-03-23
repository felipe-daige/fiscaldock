@php
    $tipoClass = $nota->tipo_operacao === 'entrada' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700';
    $tipoLabel = $nota->tipo_operacao === 'entrada' ? 'Entrada' : 'Saída';

    $origemLabel = match($nota->origem_arquivo ?? '') {
        'fiscal' => 'EFD ICMS/IPI',
        'contribuicoes' => 'EFD PIS/COFINS',
        default => null,
    };
    $origemBadgeClass = match($nota->origem_arquivo ?? '') {
        'fiscal' => 'bg-indigo-100 text-indigo-700',
        'contribuicoes' => 'bg-teal-100 text-teal-700',
        default => 'bg-gray-100 text-gray-600',
    };

    $totalIcms = $nota->itens->sum('valor_icms');
    $totalPis = $nota->itens->sum('valor_pis');
    $totalCofins = $nota->itens->sum('valor_cofins');
    $totalItensValor = $nota->itens->sum('valor_total');
@endphp

<div class="px-3 py-3 sm:px-6 sm:py-4 space-y-4">

    {{-- Dados da nota --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
        <div>
            <p class="text-xs text-gray-500 mb-0.5">Data de Emissão</p>
            <p class="font-medium text-gray-800">{{ $nota->data_emissao ? \Carbon\Carbon::parse($nota->data_emissao)->format('d/m/Y') : '—' }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-500 mb-0.5">Valor Total</p>
            <p class="font-medium text-gray-800">R$ {{ $nota->valor_total !== null ? number_format($nota->valor_total, 2, ',', '.') : '—' }}</p>
        </div>
        @if($nota->valor_desconto)
        <div>
            <p class="text-xs text-gray-500 mb-0.5">Desconto</p>
            <p class="font-medium text-gray-800">R$ {{ number_format($nota->valor_desconto, 2, ',', '.') }}</p>
        </div>
        @endif
        <div>
            <p class="text-xs text-gray-500 mb-0.5">Tipo</p>
            <div class="flex gap-1.5 flex-wrap">
                <span class="inline-block px-2 py-0.5 rounded text-xs font-medium {{ $tipoClass }}">{{ $tipoLabel }}</span>
                @if($origemLabel)
                <span class="inline-block px-2 py-0.5 rounded text-xs font-medium {{ $origemBadgeClass }}">{{ $origemLabel }}</span>
                @endif
            </div>
        </div>
    </div>

    @if($nota->chave_acesso)
    <div class="text-sm">
        <p class="text-xs text-gray-500 mb-0.5">Chave de Acesso</p>
        <p class="font-mono text-xs text-gray-700 break-all">{{ implode(' ', str_split($nota->chave_acesso, 4)) }}</p>
    </div>
    @endif

    {{-- Participante --}}
    @if($nota->participante)
    @php $p = $nota->participante; @endphp
    <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-4">
        <h3 class="text-xs font-semibold text-gray-700 mb-2">Participante</h3>
        <div class="flex flex-wrap items-start gap-3">
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-900 text-sm">
                    <a href="/app/participante/{{ $p->id }}" data-link class="hover:text-blue-600 hover:underline">{{ $p->razao_social ?? '—' }}</a>
                </p>
                @if($p->nome_fantasia)
                <p class="text-xs text-gray-500">{{ $p->nome_fantasia }}</p>
                @endif
            </div>
            <div class="flex gap-2 flex-wrap shrink-0">
                @if($p->situacao_cadastral)
                @php $sitClass = strtolower($p->situacao_cadastral) === 'ativa' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; @endphp
                <span class="px-2 py-0.5 rounded text-xs font-medium {{ $sitClass }}">{{ $p->situacao_cadastral }}</span>
                @endif
                @if($p->regime_tributario)
                <span class="px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">{{ $p->regime_tributario }}</span>
                @endif
            </div>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-3 text-sm">
            <div>
                <p class="text-xs text-gray-500 mb-0.5">CNPJ / CPF</p>
                <p class="font-mono text-gray-800">{{ $p->cnpj_formatado ?? '—' }}</p>
            </div>
            @if($p->uf)
            <div>
                <p class="text-xs text-gray-500 mb-0.5">UF</p>
                <p class="text-gray-800">{{ $p->uf }}</p>
            </div>
            @endif
            @if($p->municipio)
            <div>
                <p class="text-xs text-gray-500 mb-0.5">Município</p>
                <p class="text-gray-800">{{ $p->municipio }}</p>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Cliente --}}
    @if($nota->cliente)
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <h3 class="text-xs font-semibold text-gray-700 mb-2">Cliente</h3>
        <p class="font-semibold text-gray-900 text-sm">
            <a href="/app/cliente/{{ $nota->cliente->id }}" data-link class="hover:text-blue-600 hover:underline">{{ $nota->cliente->razao_social ?? '—' }}</a>
        </p>
        @if($nota->cliente->documento_formatado)
        <p class="text-xs font-mono text-gray-500 mt-1">{{ $nota->cliente->documento_formatado }}</p>
        @endif
    </div>
    @endif

    {{-- Itens --}}
    @if($nota->itens->isNotEmpty())
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-3 sm:px-4 py-3 border-b border-gray-100">
            <h3 class="text-xs font-semibold text-gray-700">
                Itens <span class="ml-1 text-gray-400 font-normal">({{ $nota->itens->count() }})</span>
            </h3>
        </div>

        {{-- Mobile: cards empilhados --}}
        <div class="md:hidden divide-y divide-gray-100">
            @foreach($nota->itens as $item)
            <div class="px-3 py-3">
                <p class="text-xs font-medium text-gray-800 mb-1.5 leading-snug">{{ $item->descricao ?? '—' }}</p>
                <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs">
                    <span class="text-gray-500">Qtd: <span class="text-gray-700">{{ $item->quantidade !== null ? number_format($item->quantidade, 2, ',', '.') : '—' }} {{ $item->unidade_medida ?? '' }}</span></span>
                    <span class="text-right text-gray-500">CFOP: <span class="font-mono text-gray-700">{{ $item->cfop ?? '—' }}</span></span>
                    <span class="text-gray-500">Unit: <span class="text-gray-700">{{ $item->valor_unitario !== null ? 'R$ ' . number_format($item->valor_unitario, 2, ',', '.') : '—' }}</span></span>
                    <span class="text-right font-medium text-gray-800">R$ {{ $item->valor_total !== null ? number_format($item->valor_total, 2, ',', '.') : '—' }}</span>
                </div>
                @if(($item->valor_icms ?? 0) > 0 || ($item->valor_pis ?? 0) > 0 || ($item->valor_cofins ?? 0) > 0)
                <div class="flex flex-wrap gap-x-3 gap-y-0.5 mt-1.5 text-xs text-gray-400">
                    @if(($item->valor_icms ?? 0) > 0)<span>ICMS: {{ number_format($item->valor_icms, 2, ',', '.') }}</span>@endif
                    @if(($item->valor_pis ?? 0) > 0)<span>PIS: {{ number_format($item->valor_pis, 2, ',', '.') }}</span>@endif
                    @if(($item->valor_cofins ?? 0) > 0)<span>COFINS: {{ number_format($item->valor_cofins, 2, ',', '.') }}</span>@endif
                </div>
                @endif
            </div>
            @endforeach
        </div>

        {{-- Desktop: tabela completa --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-3 py-2 text-left text-gray-500 font-medium">Nº</th>
                        <th class="px-3 py-2 text-left text-gray-500 font-medium">Cód</th>
                        <th class="px-3 py-2 text-left text-gray-500 font-medium">Descrição</th>
                        <th class="px-3 py-2 text-right text-gray-500 font-medium">Qtd</th>
                        <th class="px-3 py-2 text-left text-gray-500 font-medium">UN</th>
                        <th class="px-3 py-2 text-right text-gray-500 font-medium">Vlr Unit</th>
                        <th class="px-3 py-2 text-right text-gray-500 font-medium">Vlr Total</th>
                        <th class="px-3 py-2 text-center text-gray-500 font-medium">CFOP</th>
                        <th class="px-3 py-2 text-center text-gray-500 font-medium">CST ICMS</th>
                        <th class="px-3 py-2 text-right text-gray-500 font-medium">ICMS</th>
                        <th class="px-3 py-2 text-right text-gray-500 font-medium">PIS</th>
                        <th class="px-3 py-2 text-right text-gray-500 font-medium">COFINS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($nota->itens as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 text-gray-700">{{ $item->numero_item ?? '—' }}</td>
                        <td class="px-3 py-2 font-mono text-gray-700">{{ $item->codigo_item ?? '—' }}</td>
                        <td class="px-3 py-2 text-gray-800 max-w-xs truncate">{{ $item->descricao ?? '—' }}</td>
                        <td class="px-3 py-2 text-right text-gray-700">{{ $item->quantidade !== null ? number_format($item->quantidade, 2, ',', '.') : '—' }}</td>
                        <td class="px-3 py-2 text-gray-700">{{ $item->unidade_medida ?? '—' }}</td>
                        <td class="px-3 py-2 text-right text-gray-700">{{ $item->valor_unitario !== null ? number_format($item->valor_unitario, 2, ',', '.') : '—' }}</td>
                        <td class="px-3 py-2 text-right font-medium text-gray-800">{{ $item->valor_total !== null ? number_format($item->valor_total, 2, ',', '.') : '—' }}</td>
                        <td class="px-3 py-2 text-center font-mono text-gray-700">{{ $item->cfop ?? '—' }}</td>
                        <td class="px-3 py-2 text-center text-gray-700">{{ $item->cst_icms ?? '—' }}</td>
                        <td class="px-3 py-2 text-right text-gray-700">{{ $item->valor_icms !== null ? number_format($item->valor_icms, 2, ',', '.') : '—' }}</td>
                        <td class="px-3 py-2 text-right text-gray-700">{{ $item->valor_pis !== null ? number_format($item->valor_pis, 2, ',', '.') : '—' }}</td>
                        <td class="px-3 py-2 text-right text-gray-700">{{ $item->valor_cofins !== null ? number_format($item->valor_cofins, 2, ',', '.') : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-200">
                    <tr class="font-medium text-xs text-gray-800">
                        <td class="px-3 py-2" colspan="6" class="text-right">Total</td>
                        <td class="px-3 py-2 text-right font-bold">{{ number_format($totalItensValor, 2, ',', '.') }}</td>
                        <td class="px-3 py-2"></td>
                        <td class="px-3 py-2"></td>
                        <td class="px-3 py-2 text-right">{{ number_format($totalIcms, 2, ',', '.') }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($totalPis, 2, ',', '.') }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($totalCofins, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @else
    <p class="text-sm text-gray-400 text-center py-3">Nenhum item registrado para esta nota.</p>
    @endif

    {{-- Ação --}}
    <div class="flex justify-end pt-2 border-t border-gray-100">
        <a href="/app/notas-fiscais/efd/{{ $nota->id }}" data-link
           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors">
            Ver detalhes completos
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

</div>
