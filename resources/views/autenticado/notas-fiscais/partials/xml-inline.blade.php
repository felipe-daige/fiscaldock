@php
    $tipoClass = $nota->tipo_nota === \App\Models\XmlNota::TIPO_ENTRADA ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
    $tipoLabel = $nota->tipo_nota_descricao;
    $chaveFormatada = $nota->nfe_id ? implode(' ', str_split($nota->nfe_id, 4)) : null;
@endphp

<div class="px-3 py-3 sm:px-6 sm:py-4 space-y-4">

    {{-- Dados da nota --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
        <div>
            <p class="text-xs text-gray-500 mb-0.5">Data de Emissão</p>
            <p class="font-medium text-gray-800">{{ $nota->data_emissao ? $nota->data_emissao->format('d/m/Y') : '—' }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-500 mb-0.5">Valor Total</p>
            <p class="font-medium text-gray-800">{{ $nota->valor_formatado }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-500 mb-0.5">Tipo</p>
            <span class="inline-block px-2 py-0.5 rounded text-xs font-medium {{ $tipoClass }}">{{ $tipoLabel }}</span>
        </div>
        @if($nota->finalidade)
        <div>
            <p class="text-xs text-gray-500 mb-0.5">Finalidade</p>
            <p class="font-medium text-gray-800">{{ $nota->finalidade_descricao }}</p>
        </div>
        @endif
    </div>

    @if($nota->natureza_operacao)
    <div class="text-sm">
        <p class="text-xs text-gray-500 mb-0.5">Natureza da Operação</p>
        <p class="text-gray-800">{{ $nota->natureza_operacao }}</p>
    </div>
    @endif

    @if($chaveFormatada)
    <div class="text-sm">
        <p class="text-xs text-gray-500 mb-0.5">Chave de Acesso</p>
        <p class="font-mono text-xs text-gray-700 break-all">{{ $chaveFormatada }}</p>
    </div>
    @endif

    {{-- Emitente e Destinatário --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        {{-- Emitente --}}
        <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-4">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Emitente</h3>
            @if($nota->emit_participante_id)
                <a href="/app/participante/{{ $nota->emit_participante_id }}" data-link class="font-semibold text-gray-900 text-sm hover:text-blue-600 hover:underline">{{ $nota->emit_razao_social ?? '—' }}</a>
            @else
                <p class="font-semibold text-gray-900 text-sm">{{ $nota->emit_razao_social ?? '—' }}</p>
            @endif
            <div class="grid grid-cols-2 gap-2 mt-2 text-sm">
                <div>
                    <p class="text-xs text-gray-500 mb-0.5">CNPJ</p>
                    <p class="font-mono text-gray-800">{{ $nota->emit_cnpj_formatado ?? '—' }}</p>
                </div>
                @if($nota->emit_uf)
                <div>
                    <p class="text-xs text-gray-500 mb-0.5">UF</p>
                    <p class="text-gray-800">{{ $nota->emit_uf }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Destinatário --}}
        <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-4">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Destinatário</h3>
            @if($nota->dest_participante_id)
                <a href="/app/participante/{{ $nota->dest_participante_id }}" data-link class="font-semibold text-gray-900 text-sm hover:text-blue-600 hover:underline">{{ $nota->dest_razao_social ?? '—' }}</a>
            @else
                <p class="font-semibold text-gray-900 text-sm">{{ $nota->dest_razao_social ?? '—' }}</p>
            @endif
            <div class="grid grid-cols-2 gap-2 mt-2 text-sm">
                <div>
                    <p class="text-xs text-gray-500 mb-0.5">CNPJ</p>
                    <p class="font-mono text-gray-800">{{ $nota->dest_cnpj_formatado ?? '—' }}</p>
                </div>
                @if($nota->dest_uf)
                <div>
                    <p class="text-xs text-gray-500 mb-0.5">UF</p>
                    <p class="text-gray-800">{{ $nota->dest_uf }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Cliente --}}
    @if($nota->cliente)
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Cliente</h3>
        <p class="font-semibold text-gray-900 text-sm">
            <a href="/app/cliente/{{ $nota->cliente->id }}" data-link class="hover:text-blue-600 hover:underline">{{ $nota->cliente->razao_social ?? '—' }}</a>
        </p>
        @if($nota->cliente->documento_formatado)
        <p class="text-xs font-mono text-gray-500 mt-1">{{ $nota->cliente->documento_formatado }}</p>
        @endif
    </div>
    @endif

    {{-- Resumo tributário --}}
    @php
        $temTributos = ($nota->icms_valor ?? 0) > 0 || ($nota->icms_st_valor ?? 0) > 0 ||
                       ($nota->pis_valor ?? 0) > 0 || ($nota->cofins_valor ?? 0) > 0 ||
                       ($nota->ipi_valor ?? 0) > 0;
    @endphp
    @if($temTributos)
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Resumo Tributário</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 text-sm">
            <div>
                <p class="text-xs text-gray-500 mb-0.5">ICMS</p>
                <p class="font-medium text-gray-800">R$ {{ number_format((float) ($nota->icms_valor ?? 0), 2, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-0.5">ICMS ST</p>
                <p class="font-medium text-gray-800">R$ {{ number_format((float) ($nota->icms_st_valor ?? 0), 2, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-0.5">PIS</p>
                <p class="font-medium text-gray-800">R$ {{ number_format((float) ($nota->pis_valor ?? 0), 2, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-0.5">COFINS</p>
                <p class="font-medium text-gray-800">R$ {{ number_format((float) ($nota->cofins_valor ?? 0), 2, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-0.5">IPI</p>
                <p class="font-medium text-gray-800">R$ {{ number_format((float) ($nota->ipi_valor ?? 0), 2, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-0.5">Total Tributos</p>
                <p class="font-bold text-gray-900">R$ {{ number_format($nota->total_tributos_calculado, 2, ',', '.') }}</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Nota Referenciada (devoluções) --}}
    @if($nota->chave_referenciada)
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Nota Referenciada</h3>
        <p class="font-mono text-xs text-gray-700 break-all">{{ implode(' ', str_split($nota->chave_referenciada, 4)) }}</p>
        @php $notaRef = $nota->notaReferenciada(); @endphp
        @if($notaRef)
        <a href="/app/notas-fiscais/xml/{{ $notaRef->id }}" data-link class="inline-flex items-center mt-2 text-xs text-blue-600 hover:text-blue-800 hover:underline">
            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            Ver nota original — Nº {{ $notaRef->numero_nota }}{{ $notaRef->serie ? '/' . $notaRef->serie : '' }}
        </a>
        @endif
    </div>
    @endif

    {{-- Validação --}}
    @if($nota->isValidada())
    <div class="flex items-center gap-2">
        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $nota->validacao_badge_class }}">
            {{ $nota->validacao_classificacao_label }}
        </span>
        @if($nota->validacao_score !== null)
        <span class="text-xs text-gray-500">Score: {{ $nota->validacao_score }}/100</span>
        @endif
    </div>
    @endif

    {{-- Ação --}}
    <div class="flex justify-end pt-2 border-t border-gray-100">
        <a href="/app/notas-fiscais/xml/{{ $nota->id }}" data-link
           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors">
            Ver detalhes completos
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

</div>
