@php
    $tipoBadge = $nota->tipo_nota === \App\Models\XmlNota::TIPO_ENTRADA
        ? ['label' => strtoupper($nota->tipo_nota_descricao), 'hex' => '#047857']
        : ['label' => strtoupper($nota->tipo_nota_descricao), 'hex' => '#d97706'];

    $finalidadeBadgeMap = [
        \App\Models\XmlNota::FINALIDADE_NORMAL => '#374151',
        \App\Models\XmlNota::FINALIDADE_COMPLEMENTAR => '#0891b2',
        \App\Models\XmlNota::FINALIDADE_AJUSTE => '#7c3aed',
        \App\Models\XmlNota::FINALIDADE_DEVOLUCAO => '#d97706',
    ];

    $finalidadeBadge = $nota->finalidade
        ? ['label' => strtoupper($nota->finalidade_descricao), 'hex' => $finalidadeBadgeMap[$nota->finalidade] ?? '#374151']
        : null;

    $modeloLabel = match ((string) ($nota->tipo_documento ?? '')) {
        '55' => 'NF-e',
        '65' => 'NFC-e',
        '57' => 'CT-e',
        '67' => 'CT-e OS',
        default => $nota->tipo_documento ? 'MODELO ' . $nota->tipo_documento : 'XML',
    };

    $modeloSubtitulo = match ((string) ($nota->tipo_documento ?? '')) {
        '55' => 'Nota Fiscal Eletronica',
        '65' => 'Nota Fiscal de Consumidor Eletronica',
        '57' => 'Conhecimento de Transporte Eletronico',
        '67' => 'Conhecimento de Transporte para Outros Servicos',
        default => 'Documento fiscal importado via XML',
    };

    $chaveFormatada = $nota->nfe_id ? implode(' ', str_split($nota->nfe_id, 4)) : null;
    $notaRef = $nota->notaReferenciada();

    $temTributos = ($nota->icms_valor ?? 0) > 0
        || ($nota->icms_st_valor ?? 0) > 0
        || ($nota->pis_valor ?? 0) > 0
        || ($nota->cofins_valor ?? 0) > 0
        || ($nota->ipi_valor ?? 0) > 0;

    $emitentePrincipal = $nota->emitente;
    $destinatarioPrincipal = $nota->destinatario;

    $validacaoBadgeHex = match (strtolower((string) ($nota->validacao_classificacao_label ?? ''))) {
        'ok', 'conforme' => '#047857',
        'divergente', 'atencao', 'atenção' => '#d97706',
        'critico', 'crítico', 'erro' => '#dc2626',
        default => '#374151',
    };

    $clientePapel = $nota->cliente
        ? ($nota->cliente->id === $nota->emit_cliente_id ? 'Cliente vinculado ao emitente' : ($nota->cliente->id === $nota->dest_cliente_id ? 'Cliente vinculado ao destinatario' : 'Cliente relacionado'))
        : null;
@endphp

<div class="bg-gray-100 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="mb-4 sm:mb-8">
            <a href="/app/notas/acervo" data-link class="inline-flex items-center gap-1 text-xs text-gray-600 hover:text-gray-900 hover:underline">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Voltar para Notas Fiscais
            </a>

            <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">
                        Nota Fiscal {{ $nota->numero_nota ?? 'Sem numero' }}{{ $nota->serie ? ' / Serie ' . $nota->serie : '' }}
                    </h1>
                    <p class="text-xs text-gray-500">{{ $modeloSubtitulo }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $tipoBadge['hex'] }}">{{ $tipoBadge['label'] }}</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #374151">{{ $modeloLabel }}</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #0f766e">XML</span>
                    @if($finalidadeBadge)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $finalidadeBadge['hex'] }}">{{ $finalidadeBadge['label'] }}</span>
                    @endif
                </div>
            </div>

            @if($nota->nfe_id)
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    @if($nota->situacao_sefaz)
                        <a href="{{ route('app.clearance.nota.comparar', ['chave' => $nota->nfe_id]) }}"
                           data-link
                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded text-xs font-medium text-white"
                           style="background-color: #1d4ed8;">
                            Comparar declarado vs SEFAZ ↗
                        </a>
                    @else
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded text-xs font-medium border border-gray-300 text-gray-500">
                            Sem snapshot SEFAZ
                        </span>
                        <a href="{{ route('app.clearance.notas') }}?selecionar={{ $nota->nfe_id }}"
                           data-link
                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded text-xs font-medium text-white"
                           style="background-color: #b45309;">
                            Incluir em lote de clearance ↗
                        </a>
                    @endif
                </div>
            @endif
        </div>

        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-4">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Resumo da Nota</span>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-5 divide-x divide-y lg:divide-y-0 divide-gray-200">
                <div class="p-4">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Emissao</p>
                    <p class="text-lg font-bold text-gray-900">{{ $nota->data_emissao ? $nota->data_emissao->format('d/m/Y') : '—' }}</p>
                    <p class="text-[11px] text-gray-500">{{ $nota->data_emissao ? $nota->data_emissao->format('H:i') : 'Horario nao informado' }}</p>
                </div>
                <div class="p-4">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Valor Total</p>
                    <p class="text-lg font-bold text-gray-900">{{ $nota->valor_formatado }}</p>
                    <p class="text-[11px] text-gray-500">Documento importado via XML</p>
                </div>
                <div class="p-4">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Natureza</p>
                    <p class="text-sm text-gray-700">{{ $nota->natureza_operacao ?: 'Nao informada' }}</p>
                </div>
                <div class="p-4">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Finalidade</p>
                    <p class="text-sm text-gray-700">{{ $nota->finalidade_descricao }}</p>
                </div>
                <div class="p-4">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Tributos</p>
                    <p class="text-lg font-bold text-gray-900">R$ {{ number_format($nota->total_tributos_calculado, 2, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500">Soma dos tributos capturados</p>
                </div>
            </div>
            @if($chaveFormatada)
                <div class="px-4 py-3 border-t border-gray-200">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Chave de Acesso</p>
                    <p class="text-xs font-mono text-gray-700 break-all">{{ $chaveFormatada }}</p>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
            <div class="bg-white rounded border border-gray-300 overflow-hidden">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Emitente</span>
                </div>
                <div class="p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            @if($nota->emit_participante_id)
                                <a href="/app/participante/{{ $nota->emit_participante_id }}" data-link class="text-sm font-semibold text-gray-900 hover:text-gray-600 hover:underline">{{ $nota->emit_razao_social ?? '—' }}</a>
                            @else
                                <p class="text-sm font-semibold text-gray-900">{{ $nota->emit_razao_social ?? '—' }}</p>
                            @endif
                            @if($emitentePrincipal?->nome_fantasia)
                                <p class="text-[11px] text-gray-500 mt-1">{{ $emitentePrincipal->nome_fantasia }}</p>
                            @endif
                        </div>
                        @if($emitentePrincipal?->situacao_cadastral)
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ strtolower((string) $emitentePrincipal->situacao_cadastral) === 'ativa' ? '#047857' : '#dc2626' }}">
                                {{ strtoupper($emitentePrincipal->situacao_cadastral) }}
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mt-4">
                        <div>
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">CNPJ</p>
                            <p class="text-sm font-mono text-gray-700">{{ $nota->emit_cnpj_formatado ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">UF</p>
                            <p class="text-sm text-gray-700">{{ $nota->emit_uf ?: '—' }}</p>
                        </div>
                        @if($emitentePrincipal?->municipio)
                            <div>
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Municipio</p>
                                <p class="text-sm text-gray-700">{{ $emitentePrincipal->municipio }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white rounded border border-gray-300 overflow-hidden">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Destinatario</span>
                </div>
                <div class="p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            @if($nota->dest_participante_id)
                                <a href="/app/participante/{{ $nota->dest_participante_id }}" data-link class="text-sm font-semibold text-gray-900 hover:text-gray-600 hover:underline">{{ $nota->dest_razao_social ?? '—' }}</a>
                            @else
                                <p class="text-sm font-semibold text-gray-900">{{ $nota->dest_razao_social ?? '—' }}</p>
                            @endif
                            @if($destinatarioPrincipal?->nome_fantasia)
                                <p class="text-[11px] text-gray-500 mt-1">{{ $destinatarioPrincipal->nome_fantasia }}</p>
                            @endif
                        </div>
                        @if($destinatarioPrincipal?->situacao_cadastral)
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ strtolower((string) $destinatarioPrincipal->situacao_cadastral) === 'ativa' ? '#047857' : '#dc2626' }}">
                                {{ strtoupper($destinatarioPrincipal->situacao_cadastral) }}
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mt-4">
                        <div>
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">CNPJ</p>
                            <p class="text-sm font-mono text-gray-700">{{ $nota->dest_cnpj_formatado ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">UF</p>
                            <p class="text-sm text-gray-700">{{ $nota->dest_uf ?: '—' }}</p>
                        </div>
                        @if($destinatarioPrincipal?->municipio)
                            <div>
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Municipio</p>
                                <p class="text-sm text-gray-700">{{ $destinatarioPrincipal->municipio }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($nota->cliente)
            <div class="bg-white rounded border border-gray-300 overflow-hidden mb-4">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Cliente</span>
                </div>
                <div class="p-4">
                    <p class="text-sm font-semibold text-gray-900">
                        <a href="/app/cliente/{{ $nota->cliente->id }}" data-link class="text-gray-900 hover:text-gray-600 hover:underline">{{ $nota->cliente->razao_social ?? '—' }}</a>
                    </p>
                    @if($clientePapel)
                        <p class="text-[11px] text-gray-500 mt-1">{{ $clientePapel }}</p>
                    @endif
                    @if($nota->cliente->documento_formatado)
                        <p class="text-[11px] font-mono text-gray-500 mt-1">{{ $nota->cliente->documento_formatado }}</p>
                    @endif
                </div>
            </div>
        @endif

        @if($temTributos)
            <div class="bg-white rounded border border-gray-300 overflow-hidden mb-4">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Resumo Tributario</span>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 divide-x divide-y lg:divide-y-0 divide-gray-200">
                    <div class="p-4">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">ICMS</p>
                        <p class="text-lg font-bold text-gray-900">R$ {{ number_format((float) ($nota->icms_valor ?? 0), 2, ',', '.') }}</p>
                    </div>
                    <div class="p-4">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">ICMS ST</p>
                        <p class="text-lg font-bold text-gray-900">R$ {{ number_format((float) ($nota->icms_st_valor ?? 0), 2, ',', '.') }}</p>
                    </div>
                    <div class="p-4">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">PIS</p>
                        <p class="text-lg font-bold text-gray-900">R$ {{ number_format((float) ($nota->pis_valor ?? 0), 2, ',', '.') }}</p>
                    </div>
                    <div class="p-4">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">COFINS</p>
                        <p class="text-lg font-bold text-gray-900">R$ {{ number_format((float) ($nota->cofins_valor ?? 0), 2, ',', '.') }}</p>
                    </div>
                    <div class="p-4">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">IPI</p>
                        <p class="text-lg font-bold text-gray-900">R$ {{ number_format((float) ($nota->ipi_valor ?? 0), 2, ',', '.') }}</p>
                    </div>
                    <div class="p-4">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Total</p>
                        <p class="text-lg font-bold text-gray-900">R$ {{ number_format($nota->total_tributos_calculado, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @php
            $catalogoPorItem = $catalogoPorItem ?? [];
            $badgeDivergenciaXml = [
                'ncm' => ['label' => 'NCM', 'hex' => '#d97706'],
                'unidade' => ['label' => 'UN', 'hex' => '#d97706'],
                'aliquota' => ['label' => 'Alíq.', 'hex' => '#b45309'],
            ];
        @endphp

        @if($nota->itens->isNotEmpty())
            <div class="bg-white rounded border border-gray-300 overflow-hidden mb-4">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between">
                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Itens</span>
                    <span class="text-[11px] text-gray-400">{{ $nota->itens->count() }} {{ $nota->itens->count() === 1 ? 'item declarado' : 'itens declarados' }}</span>
                </div>

                <div class="md:hidden divide-y divide-gray-100">
                    @foreach($nota->itens as $item)
                        @php
                            $cmp = $catalogoPorItem[$item->id] ?? ['cadastro' => null, 'divergencias' => []];
                            $cad = $cmp['cadastro'];
                            $divs = $cmp['divergencias'] ?? [];
                        @endphp
                        <div class="px-4 py-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm text-gray-900">{{ $item->descricao ?? '—' }}</p>
                                    <p class="text-[11px] text-gray-500 mt-1">
                                        Item {{ $item->numero_item ?? '—' }} · Cod. {{ $item->codigo_item ?? '—' }}
                                    </p>
                                    <div class="flex flex-wrap items-center gap-1 mt-1.5">
                                        @if($cad === null)
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: #dc2626">Sem cadastro</span>
                                        @else
                                            @foreach($divs as $div)
                                                @php $b = $badgeDivergenciaXml[$div] ?? null; @endphp
                                                @if($b)
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $b['hex'] }}">{{ $b['label'] }}</span>
                                                @endif
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                                @if($item->cfop)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #4338ca">{{ $item->cfop }}</span>
                                @endif
                            </div>
                            <div class="grid grid-cols-2 gap-x-4 gap-y-2 mt-3">
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase">Quantidade</p>
                                    <p class="text-sm text-gray-700">{{ $item->quantidade !== null ? number_format($item->quantidade, 2, ',', '.') : '—' }} {{ $item->unidade_medida ?? '' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase">Total</p>
                                    <p class="text-sm font-mono font-semibold text-gray-900">R$ {{ $item->valor_total !== null ? number_format($item->valor_total, 2, ',', '.') : '—' }}</p>
                                </div>
                                @if($cad)
                                    <div class="col-span-2 border-t border-gray-100 pt-2 mt-1">
                                        <p class="text-[10px] text-gray-400 uppercase">Catálogo (cad. 0200)</p>
                                        <p class="text-[11px] text-gray-600 mt-0.5">
                                            NCM: <span class="font-mono {{ in_array('ncm', $divs, true) ? 'text-orange-700 font-semibold' : '' }}">{{ $cad['cod_ncm'] ?? '—' }}</span>
                                            · UN: <span class="font-mono {{ in_array('unidade', $divs, true) ? 'text-orange-700 font-semibold' : '' }}">{{ $cad['unid_inv'] ?? '—' }}</span>
                                            · Alíq: <span class="font-mono {{ in_array('aliquota', $divs, true) ? 'text-orange-700 font-semibold' : '' }}">{{ $cad['aliq_icms'] !== null ? number_format($cad['aliq_icms'], 2, ',', '.').'%' : '—' }}</span>
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-300">
                                <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">N</th>
                                <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Codigo</th>
                                <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Descricao</th>
                                <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">NCM</th>
                                <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">NCM (cad.)</th>
                                <th class="px-3 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Qtd</th>
                                <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">UN</th>
                                <th class="px-3 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Vlr Total</th>
                                <th class="px-3 py-2.5 text-center text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">CFOP</th>
                                <th class="px-3 py-2.5 text-center text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">CST ICMS</th>
                                <th class="px-3 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Alíq.</th>
                                <th class="px-3 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">ICMS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($nota->itens as $item)
                                @php
                                    $cmp = $catalogoPorItem[$item->id] ?? ['cadastro' => null, 'divergencias' => []];
                                    $cad = $cmp['cadastro'];
                                    $divs = $cmp['divergencias'] ?? [];
                                @endphp
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-3 py-2 text-sm text-gray-700">{{ $item->numero_item ?? '—' }}</td>
                                    <td class="px-3 py-2 text-sm font-mono text-gray-700">
                                        <div class="flex flex-wrap items-center gap-1">
                                            <span>{{ $item->codigo_item ?? '—' }}</span>
                                            @if($cad === null)
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: #dc2626" title="Item não está no catálogo (registro 0200)">Sem cadastro</span>
                                            @else
                                                @foreach($divs as $div)
                                                    @php $b = $badgeDivergenciaXml[$div] ?? null; @endphp
                                                    @if($b)
                                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $b['hex'] }}" title="{{ $b['label'] }} divergente do catálogo">{{ $b['label'] }}</span>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-700 max-w-xs truncate">{{ $item->descricao ?? '—' }}</td>
                                    <td class="px-3 py-2 text-sm font-mono {{ in_array('ncm', $divs, true) ? 'text-orange-700 font-semibold' : 'text-gray-700' }}">{{ $item->ncm ?? '—' }}</td>
                                    <td class="px-3 py-2 text-sm font-mono whitespace-nowrap">
                                        @if($cad && $cad['cod_ncm'])
                                            <span class="{{ in_array('ncm', $divs, true) ? 'text-orange-700 font-semibold' : 'text-gray-500' }}">{{ $cad['cod_ncm'] }}</span>
                                        @else
                                            <span class="text-gray-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-sm text-right text-gray-700">{{ $item->quantidade !== null ? number_format($item->quantidade, 2, ',', '.') : '—' }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-700">{{ $item->unidade_medida ?? '—' }}</td>
                                    <td class="px-3 py-2 text-sm font-semibold text-gray-900 text-right font-mono">{{ $item->valor_total !== null ? number_format($item->valor_total, 2, ',', '.') : '—' }}</td>
                                    <td class="px-3 py-2 text-sm text-center font-mono text-gray-700">{{ $item->cfop ?? '—' }}</td>
                                    <td class="px-3 py-2 text-sm text-center text-gray-700">{{ $item->cst_icms ?? '—' }}</td>
                                    <td class="px-3 py-2 text-sm text-right font-mono {{ in_array('aliquota', $divs, true) ? 'text-orange-700 font-semibold' : 'text-gray-700' }}">{{ $item->aliquota_icms !== null ? number_format($item->aliquota_icms, 2, ',', '.').'%' : '—' }}</td>
                                    <td class="px-3 py-2 text-sm text-right font-mono text-gray-700">{{ $item->valor_icms !== null ? number_format($item->valor_icms, 2, ',', '.') : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white rounded border border-gray-300 overflow-hidden mb-4">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Itens</span>
                </div>
                <div class="p-4">
                    <p class="text-sm text-gray-500">Itens ainda não foram tipados para esta nota. Rode <code class="font-mono text-xs bg-gray-100 px-1.5 py-0.5 rounded">php artisan xml:backfill-itens</code> ou aguarde a próxima importação.</p>
                </div>
            </div>
        @endif

        @if($notaRef || $nota->chave_referenciada)
            <div class="bg-white rounded border border-gray-300 overflow-hidden mb-4">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Nota Referenciada</span>
                </div>
                <div class="p-4">
                    @if($nota->chave_referenciada)
                        <p class="text-xs font-mono text-gray-700 break-all">{{ implode(' ', str_split($nota->chave_referenciada, 4)) }}</p>
                    @endif
                    @if($notaRef)
                        <a href="/app/notas/xml/{{ $notaRef->id }}" data-link class="inline-flex items-center mt-2 text-xs text-gray-700 hover:text-gray-900 hover:underline">
                            Ver nota original: Nº {{ $notaRef->numero_nota }}{{ $notaRef->serie ? ' / ' . $notaRef->serie : '' }}
                        </a>
                    @endif
                </div>
            </div>
        @endif

        @if($nota->isValidada())
            <div class="bg-white rounded border border-gray-300 overflow-hidden">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Validacao</span>
                </div>
                <div class="p-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $validacaoBadgeHex }}">
                            {{ strtoupper($nota->validacao_classificacao_label) }}
                        </span>
                        @if($nota->validacao_score !== null)
                            <span class="text-sm text-gray-700">Score: <span class="font-mono text-gray-900">{{ $nota->validacao_score }}/100</span></span>
                        @endif
                    </div>
                    <a href="/app/clearance/dashboard" data-link class="text-xs text-gray-600 hover:text-gray-900 hover:underline">Abrir modulo de clearance</a>
                </div>
            </div>
        @endif
    </div>
</div>
